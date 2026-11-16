<?php
declare(strict_types=1);

/**
 * Controle de permissões explícito no servidor. Nunca usar cargo ou id
 * recebidos do navegador/IA. Sem a migração ou papel válido: acesso negado.
 * Ativação separada do Token 2, somente após cadastrar os quatro ADMs.
 */
final class AdminRolePolicy
{
    private const PERMISSIONS = [
        'consulta' => [],
        'moderacao' => ['resolve_job_report', 'resolve_profile_report', 'admin_delete_vaga'],
        'gestao' => [
            'resolve_job_report', 'resolve_profile_report',
            'admin_delete_vaga', 'admin_delete_pessoa', 'admin_delete_empresa',
            'update_settings', 'delete_account',
        ],
    ];

    public static function enabled(): bool
    {
        return (string) ($_ENV['DEVIN_ADMIN_RBAC_ENABLED']
            ?? $_SERVER['DEVIN_ADMIN_RBAC_ENABLED']
            ?? getenv('DEVIN_ADMIN_RBAC_ENABLED') ?: '0') === '1';
    }

    public static function can(string $role, string $action): bool
    {
        if ($action === 'update_profile') {
            return array_key_exists($role, self::PERMISSIONS);
        }
        return in_array($action, self::PERMISSIONS[$role] ?? [], true);
    }

    public static function requireAction(mysqli $db, int $adminId, string $action): void
    {
        if (!self::enabled()) {
            return;
        }
        if ($adminId <= 0) {
            throw new DomainException('Administrador não autorizado.');
        }
        $stmt = $db->prepare('SELECT nivel_permissao FROM administrador WHERE id_administrador = ? LIMIT 1');
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!is_array($row) || !self::can((string) ($row['nivel_permissao'] ?? ''), $action)) {
            throw new DomainException('Seu nível administrativo não permite esta operação.');
        }
    }
}
