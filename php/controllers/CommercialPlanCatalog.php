<?php
declare(strict_types=1);

/**
 * Catálogo comercial em rascunho. Alterações exigem ADM, CSRF e auditoria.
 * Nenhuma edição ativa planos ou valores de cobrança em tempo real: integração
 * de checkout, webhook verificado e termos comerciais ainda são pré-requisitos.
 */
final class CommercialPlanCatalog
{
    public static function all(mysqli $db): array
    {
        return $db->query('SELECT id,audience,tier,title,monthly_price_cents,
            max_active_jobs,max_applications_month,placement_fee_bps,hide_ads,
            auto_search,auto_apply,advanced_ai,stock,is_published,revision
            FROM devin_billing_plans ORDER BY audience,tier')->fetch_all(MYSQLI_ASSOC);
    }

    public static function subscribers(mysqli $db): array
    {
        // Lista apenas IDs e períodos; dados de contato exigem consulta específica.
        return $db->query('SELECT s.id,s.owner_type,s.owner_id,s.status,s.starts_at,
            s.ends_at,s.next_renewal_at,p.title AS plan
            FROM devin_billing_subscriptions s JOIN devin_billing_plans p
            ON p.id=s.plan_id ORDER BY s.id DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);
    }

    public static function updateDraft(mysqli $db,int $adminId,int $id,int $revision,array $changes): array
    {
        if($adminId<1 || $id<1 || $revision<1) throw new InvalidArgumentException('Identificador inválido.');
        $fields=['title','monthly_price_cents','max_active_jobs','max_applications_month',
            'placement_fee_bps','stock','hide_ads','auto_search','advanced_ai'];
        if(array_diff(array_keys($changes),$fields)!==[]) {
            throw new InvalidArgumentException('Campo de plano não permitido.');
        }
        $db->begin_transaction();
        try {
            $stmt=$db->prepare('SELECT * FROM devin_billing_plans WHERE id=? FOR UPDATE');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $before=$stmt->get_result()->fetch_assoc();
            $stmt->close();
            if(!$before || (int)$before['revision']!==$revision) {
                throw new DomainException('Plano alterado por outra pessoa. Recarregue antes de salvar.');
            }
            if((int)$before['is_published']!==0) {
                throw new DomainException('Despublique o plano e revise contratos antes de alterar valores.');
            }
            $after=$before;
            foreach ($changes as $name=>$value) {
                if ($name==='title') {
                    $v=trim((string)$value);
                    if($v==='' || mb_strlen($v)>100) throw new InvalidArgumentException('Título inválido.');
                    $after[$name]=$v;
                } elseif (in_array($name,['hide_ads','auto_search','advanced_ai'],true)) {
                    if(!in_array($value,[0,1,'0','1'],true)) throw new InvalidArgumentException('Benefício inválido.');
                    $after[$name]=(int)$value;
                } else {
                    if($value!==null && (!is_numeric($value) || (int)$value<0)) {
                        throw new InvalidArgumentException('Preço, estoque ou limite inválido.');
                    }
                    $after[$name]=$value===null?null:(int)$value;
                }
            }
            if($after['monthly_price_cents']!==null && $after['monthly_price_cents']<1) {
                throw new InvalidArgumentException('Preço mensal deve ser positivo.');
            }
            if($after['placement_fee_bps']>500) throw new InvalidArgumentException('Comissão máxima de 5%.');
            $stmt=$db->prepare('UPDATE devin_billing_plans
                SET title=?,monthly_price_cents=?,max_active_jobs=?,max_applications_month=?,
                placement_fee_bps=?,stock=?,hide_ads=?,auto_search=?,advanced_ai=?,
                revision=revision+1 WHERE id=? AND revision=? AND is_published=0');
            $stmt->bind_param('siiiiiiiiii',
                $after['title'],$after['monthly_price_cents'],$after['max_active_jobs'],
                $after['max_applications_month'],$after['placement_fee_bps'],
                $after['stock'],$after['hide_ads'],$after['auto_search'],
                $after['advanced_ai'],$id,$revision);
            $stmt->execute();
            if($stmt->affected_rows!==1) throw new DomainException('Plano foi modificado durante a edição.');
            $stmt->close();
            $previous=json_encode($before,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
            $current=json_encode($after,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
            $stmt=$db->prepare('INSERT INTO devin_billing_plan_audit
                (plan_id,admin_id,action,old_snapshot,new_snapshot)
                VALUES (?,?,"update_draft",?,?)');
            $stmt->bind_param('iiss',$id,$adminId,$previous,$current);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            $after['revision']=$revision+1;
            return $after;
        } catch(Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }

    public static function employerPlacementFee(int $verifiedServiceCents,int $basisPoints): int
    {
        // O valor-base NÃO é salário do candidato; exige contrato B2B validado.
        if($verifiedServiceCents<0 || $verifiedServiceCents>1000000000 ||
            $basisPoints<0 || $basisPoints>500) {
            throw new InvalidArgumentException('Valor ou porcentagem de serviço inválida.');
        }
        return intdiv($verifiedServiceCents*$basisPoints+5000,10000);
    }
}
