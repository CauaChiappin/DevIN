<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Entitlements de assinatura: definidos pelo servidor, nunca pelo JavaScript.
 * Sem habilitação comercial, o site principal continua com regras anteriores.
 * Assinatura paga precisa status ACTIVE confirmado por provedor confiável.
 */
final class CommercialEntitlements // Explicação DevIN: Declara o tipo CommercialEntitlements e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function enabled(): bool // Explicação DevIN: Declara função/método enabled, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return (string)($_ENV['DEVIN_MONETIZATION_ENABLED'] // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            ?? $_SERVER['DEVIN_MONETIZATION_ENABLED'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ?? getenv('DEVIN_MONETIZATION_ENABLED') ?: '0') === '1'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function freeLimits(string $ownerType): array // Explicação DevIN: Declara função/método freeLimits, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Valores de demonstração, não promessas comerciais publicadas.
        return $ownerType === 'empresa' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            ? ['max_active_jobs'=>2,'max_applications_month'=>null,'placement_fee_bps'=>500, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                'hide_ads'=>false,'auto_search'=>false,'auto_apply'=>false,'advanced_ai'=>false] // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            : ['max_active_jobs'=>null,'max_applications_month'=>10,'placement_fee_bps'=>0, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                'hide_ads'=>false,'auto_search'=>false,'auto_apply'=>false,'advanced_ai'=>false]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function forAccount(mysqli $db, string $ownerType, int $ownerId): array // Explicação DevIN: Declara função/método forAccount, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($ownerType,['empresa','pessoa'],true) || $ownerId<1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Conta inválida para consulta de benefícios.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::enabled()) return self::freeLimits($ownerType); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // A consulta falha fechada quando a migration estiver ausente.
        $stmt=$db->prepare('SELECT p.* FROM devin_billing_subscriptions s
            JOIN devin_billing_plans p ON p.id=s.plan_id AND p.audience=s.owner_type
            WHERE s.owner_type=? AND s.owner_id=? AND s.status="active"
            AND p.is_published=1 AND p.monthly_price_cents IS NOT NULL
            AND s.starts_at <= NOW() AND s.ends_at > NOW()
            ORDER BY s.ends_at DESC LIMIT 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forAccount.
        $stmt->bind_param('si',$ownerType,$ownerId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row=$stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função forAccount.
        if (!$row) return self::freeLimits($ownerType); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'max_active_jobs'=>$row['max_active_jobs']===null?null:(int)$row['max_active_jobs'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'max_applications_month'=>$row['max_applications_month']===null?null:(int)$row['max_applications_month'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'placement_fee_bps'=>(int)$row['placement_fee_bps'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'hide_ads'=>(bool)$row['hide_ads'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'auto_search'=>(bool)$row['auto_search'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            // Auto-apply remains disabled until opt-in and approved matching algorithm exist.
            'auto_apply'=>false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'advanced_ai'=>(bool)$row['advanced_ai'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function assertMayPostJob(mysqli $db, int $companyId): void // Explicação DevIN: Declara função/método assertMayPostJob, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::enabled()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $rights=self::forAccount($db,'empresa',$companyId); // Explicação DevIN: Atribui um valor à variável PHP $rights.
        if ($rights['max_active_jobs']===null) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $stmt=$db->prepare('SELECT COUNT(*) AS n FROM vagas WHERE id_empresa=? AND encerrada_em IS NULL'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i',$companyId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $n=(int)$stmt->get_result()->fetch_assoc()['n']; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertMayPostJob.
        if ($n >= $rights['max_active_jobs']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Limite de vagas ativas alcançado no seu plano.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function assertMayApply(mysqli $db, int $personId): void // Explicação DevIN: Declara função/método assertMayApply, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::enabled()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $rights=self::forAccount($db,'pessoa',$personId); // Explicação DevIN: Atribui um valor à variável PHP $rights.
        if ($rights['max_applications_month']===null) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $stmt=$db->prepare('SELECT COUNT(*) AS n FROM candidatura
            WHERE id_pessoa=? AND data_candidatura>=DATE_FORMAT(CURDATE(),"%Y-%m-01")'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertMayApply.
        $stmt->bind_param('i',$personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $n=(int)$stmt->get_result()->fetch_assoc()['n']; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertMayApply.
        if ($n >= $rights['max_applications_month']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Limite mensal de candidaturas atingido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
