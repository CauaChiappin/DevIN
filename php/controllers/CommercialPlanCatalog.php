<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Catálogo comercial em rascunho. Alterações exigem ADM, CSRF e auditoria.
 * Nenhuma edição ativa planos ou valores de cobrança em tempo real: integração
 * de checkout, webhook verificado e termos comerciais ainda são pré-requisitos.
 */
final class CommercialPlanCatalog // Explicação DevIN: Declara o tipo CommercialPlanCatalog e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function all(mysqli $db): array // Explicação DevIN: Declara função/método all, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $db->query('SELECT id,audience,tier,title,monthly_price_cents,
            max_active_jobs,max_applications_month,placement_fee_bps,hide_ads,
            auto_search,auto_apply,advanced_ai,stock,is_published,revision
            FROM devin_billing_plans ORDER BY audience,tier')->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function subscribers(mysqli $db): array // Explicação DevIN: Declara função/método subscribers, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Lista apenas IDs e períodos; dados de contato exigem consulta específica.
        return $db->query('SELECT s.id,s.owner_type,s.owner_id,s.status,s.starts_at,
            s.ends_at,s.next_renewal_at,p.title AS plan
            FROM devin_billing_subscriptions s JOIN devin_billing_plans p
            ON p.id=s.plan_id ORDER BY s.id DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function updateDraft(mysqli $db,int $adminId,int $id,int $revision,array $changes): array // Explicação DevIN: Declara função/método updateDraft, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if($adminId<1 || $id<1 || $revision<1) throw new InvalidArgumentException('Identificador inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $fields=['title','monthly_price_cents','max_active_jobs','max_applications_month', // Explicação DevIN: Atribui um valor à variável PHP $fields.
            'placement_fee_bps','stock','hide_ads','auto_search','advanced_ai']; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        if(array_diff(array_keys($changes),$fields)!==[]) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Campo de plano não permitido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt=$db->prepare('SELECT * FROM devin_billing_plans WHERE id=? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i',$id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $before=$stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            if(!$before || (int)$before['revision']!==$revision) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Plano alterado por outra pessoa. Recarregue antes de salvar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if((int)$before['is_published']!==0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Despublique o plano e revise contratos antes de alterar valores.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $after=$before; // Explicação DevIN: Atribui um valor à variável PHP $after.
            foreach ($changes as $name=>$value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if ($name==='title') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $v=trim((string)$value); // Explicação DevIN: Atribui um valor à variável PHP $v.
                    if($v==='' || mb_strlen($v)>100) throw new InvalidArgumentException('Título inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $after[$name]=$v; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } elseif (in_array($name,['hide_ads','auto_search','advanced_ai'],true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(!in_array($value,[0,1,'0','1'],true)) throw new InvalidArgumentException('Benefício inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $after[$name]=(int)$value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                    if($value!==null && (!is_numeric($value) || (int)$value<0)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        throw new InvalidArgumentException('Preço, estoque ou limite inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $after[$name]=$value===null?null:(int)$value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if($after['monthly_price_cents']!==null && $after['monthly_price_cents']<1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Preço mensal deve ser positivo.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if($after['placement_fee_bps']>500) throw new InvalidArgumentException('Comissão máxima de 5%.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt=$db->prepare('UPDATE devin_billing_plans
                SET title=?,monthly_price_cents=?,max_active_jobs=?,max_applications_month=?,
                placement_fee_bps=?,stock=?,hide_ads=?,auto_search=?,advanced_ai=?,
                revision=revision+1 WHERE id=? AND revision=? AND is_published=0'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $stmt->bind_param('siiiiiiiiii', // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $after['title'],$after['monthly_price_cents'],$after['max_active_jobs'], // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
                $after['max_applications_month'],$after['placement_fee_bps'], // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
                $after['stock'],$after['hide_ads'],$after['auto_search'], // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
                $after['advanced_ai'],$id,$revision); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if($stmt->affected_rows!==1) throw new DomainException('Plano foi modificado durante a edição.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            $previous=json_encode($before,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $current=json_encode($after,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $stmt=$db->prepare('INSERT INTO devin_billing_plan_audit
                (plan_id,admin_id,action,old_snapshot,new_snapshot)
                VALUES (?,?,"update_draft",?,?)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            $stmt->bind_param('iiss',$id,$adminId,$previous,$current); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            $after['revision']=$revision+1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return $after; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch(Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateDraft.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function employerPlacementFee(int $verifiedServiceCents,int $basisPoints): int // Explicação DevIN: Declara função/método employerPlacementFee, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // O valor-base NÃO é salário do candidato; exige contrato B2B validado.
        if($verifiedServiceCents<0 || $verifiedServiceCents>1000000000 || // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $basisPoints<0 || $basisPoints>500) { // Explicação DevIN: Continua expressão ou bloco pertencente a função employerPlacementFee.
            throw new InvalidArgumentException('Valor ou porcentagem de serviço inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return intdiv($verifiedServiceCents*$basisPoints+5000,10000); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
