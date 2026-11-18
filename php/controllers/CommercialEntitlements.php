<?php
declare(strict_types=1);

/**
 * Entitlements de assinatura: definidos pelo servidor, nunca pelo JavaScript.
 * Sem habilitação comercial, o site principal continua com regras anteriores.
 * Assinatura paga precisa status ACTIVE confirmado por provedor confiável.
 */
final class CommercialEntitlements
{
    public static function enabled(): bool
    {
        return (string)($_ENV['DEVIN_MONETIZATION_ENABLED']
            ?? $_SERVER['DEVIN_MONETIZATION_ENABLED']
            ?? getenv('DEVIN_MONETIZATION_ENABLED') ?: '0') === '1';
    }

    public static function freeLimits(string $ownerType): array
    {
        // Valores de demonstração, não promessas comerciais publicadas.
        return $ownerType === 'empresa'
            ? ['max_active_jobs'=>2,'max_applications_month'=>null,'placement_fee_bps'=>500,
                'hide_ads'=>false,'auto_search'=>false,'auto_apply'=>false,'advanced_ai'=>false]
            : ['max_active_jobs'=>null,'max_applications_month'=>10,'placement_fee_bps'=>0,
                'hide_ads'=>false,'auto_search'=>false,'auto_apply'=>false,'advanced_ai'=>false];
    }

    public static function forAccount(mysqli $db, string $ownerType, int $ownerId): array
    {
        if (!in_array($ownerType,['empresa','pessoa'],true) || $ownerId<1) {
            throw new InvalidArgumentException('Conta inválida para consulta de benefícios.');
        }
        if (!self::enabled()) return self::freeLimits($ownerType);
        // A consulta falha fechada quando a migration estiver ausente.
        $stmt=$db->prepare('SELECT p.* FROM devin_billing_subscriptions s
            JOIN devin_billing_plans p ON p.id=s.plan_id AND p.audience=s.owner_type
            WHERE s.owner_type=? AND s.owner_id=? AND s.status="active"
            AND p.is_published=1 AND p.monthly_price_cents IS NOT NULL
            AND s.starts_at <= NOW() AND s.ends_at > NOW()
            ORDER BY s.ends_at DESC LIMIT 1');
        $stmt->bind_param('si',$ownerType,$ownerId);
        $stmt->execute();
        $row=$stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) return self::freeLimits($ownerType);
        return [
            'max_active_jobs'=>$row['max_active_jobs']===null?null:(int)$row['max_active_jobs'],
            'max_applications_month'=>$row['max_applications_month']===null?null:(int)$row['max_applications_month'],
            'placement_fee_bps'=>(int)$row['placement_fee_bps'],
            'hide_ads'=>(bool)$row['hide_ads'],
            'auto_search'=>(bool)$row['auto_search'],
            // Auto-apply remains disabled until opt-in and approved matching algorithm exist.
            'auto_apply'=>false,
            'advanced_ai'=>(bool)$row['advanced_ai'],
        ];
    }

    public static function assertMayPostJob(mysqli $db, int $companyId): void
    {
        if (!self::enabled()) return;
        $rights=self::forAccount($db,'empresa',$companyId);
        if ($rights['max_active_jobs']===null) return;
        $stmt=$db->prepare('SELECT COUNT(*) AS n FROM vagas WHERE id_empresa=? AND encerrada_em IS NULL');
        $stmt->bind_param('i',$companyId);
        $stmt->execute();
        $n=(int)$stmt->get_result()->fetch_assoc()['n'];
        $stmt->close();
        if ($n >= $rights['max_active_jobs']) {
            throw new DomainException('Limite de vagas ativas alcançado no seu plano.');
        }
    }

    public static function assertMayApply(mysqli $db, int $personId): void
    {
        if (!self::enabled()) return;
        $rights=self::forAccount($db,'pessoa',$personId);
        if ($rights['max_applications_month']===null) return;
        $stmt=$db->prepare('SELECT COUNT(*) AS n FROM candidatura
            WHERE id_pessoa=? AND data_candidatura>=DATE_FORMAT(CURDATE(),"%Y-%m-01")');
        $stmt->bind_param('i',$personId);
        $stmt->execute();
        $n=(int)$stmt->get_result()->fetch_assoc()['n'];
        $stmt->close();
        if ($n >= $rights['max_applications_month']) {
            throw new DomainException('Limite mensal de candidaturas atingido.');
        }
    }
}
