<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Controle de permissões explícito no servidor. Nunca usar cargo ou id
 * recebidos do navegador/IA. Sem a migração ou papel válido: acesso negado.
 * Ativação separada do Token 2, somente após cadastrar os quatro ADMs.
 */
final class AdminRolePolicy // Explicação DevIN: Declara o tipo AdminRolePolicy e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const PERMISSIONS = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'consulta' => [], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'moderacao' => ['resolve_job_report', 'resolve_profile_report', 'admin_delete_vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'jarvis_draft', 'jarvis_analyze'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'gestao' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'resolve_job_report', 'resolve_profile_report', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'admin_delete_vaga', 'admin_delete_pessoa', 'admin_delete_empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'update_settings', 'delete_account', 'jarvis_draft', 'jarvis_analyze', 'jarvis_send', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    public static function enabled(): bool // Explicação DevIN: Declara função/método enabled, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return (string) ($_ENV['DEVIN_ADMIN_RBAC_ENABLED'] // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            ?? $_SERVER['DEVIN_ADMIN_RBAC_ENABLED'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ?? getenv('DEVIN_ADMIN_RBAC_ENABLED') ?: '0') === '1'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function can(string $role, string $action): bool // Explicação DevIN: Declara função/método can, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return array_key_exists($role, self::PERMISSIONS); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return in_array($action, self::PERMISSIONS[$role] ?? [], true); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function requireAction(mysqli $db, int $adminId, string $action): void // Explicação DevIN: Declara função/método requireAction, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($adminId <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Administrador não autorizado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt = $db->prepare('SELECT nivel_permissao FROM administrador WHERE id_administrador = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireAction.
        if (!is_array($row) || !self::can((string) ($row['nivel_permissao'] ?? ''), $action)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Seu nível administrativo não permite esta operação.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
