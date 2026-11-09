<?php
declare(strict_types=1);

require_once __DIR__ . '/AdminStepUp.php';

/**
 * Intercepta TODAS as mutações previstas em adm.php antes dos handlers.
 * Para formulários POST comuns, retoma a mesma operação após validação no
 * servidor, sem transformar ações de escrita em requisições GET.
 */
final class AdminGate
{
    private const ACTIONS = [
        'admin_delete_pessoa','admin_delete_empresa','admin_delete_vaga',
        'resolve_job_report','resolve_profile_report',
        'update_profile','update_settings','delete_account',
    ];
    private const MAX_PENDING_SECONDS = 300;

    public static function interceptAdminPost(): void
    {
        if (!AdminStepUp::enabled()) return; // Ativar somente após provisionar os 4 ADMs.
        if (AdminStepUp::allowed('site')) return;
        $adminId = AdminStepUp::adminId();
        $action = $_POST['action'] ?? null;
        if (!is_string($action) || !in_array($action, self::ACTIONS, true)) {
            http_response_code(403);
            exit('Operação não autorizada.');
        }
        $data = [];
        $size = 0;
        foreach ($_POST as $key => $value) {
            if (!is_string($key) || !is_string($value) || strlen($key) > 80) {
                http_response_code(400);
                exit('Formulário inválido.');
            }
            $size += strlen($key) + strlen($value);
            if ($size > 12000 || count($data) >= 30) {
                http_response_code(413);
                exit('Formulário excede o limite permitido.');
            }
            $data[$key] = $value;
        }
        $hasFile = false;
        foreach ($_FILES as $file) {
            if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $hasFile = true;
                break;
            }
        }
        // O PHP não mantém upload temporário entre o POST inicial e o desbloqueio.
        $pages = ['inicio','candidatos','posts','reports','sobre','perfil'];
        $categories = ['vagas','pessoas','empresas'];
        $page = $_GET['pagina'] ?? 'inicio';
        $category = $_GET['categoria'] ?? 'vagas';
        $page = in_array($page, $pages, true) ? $page : 'inicio';
        $category = in_array($category, $categories, true) ? $category : 'vagas';
        $_SESSION['admin_stepup_pending'] = [
            'admin_id' => $adminId,
            'created_at' => time(),
            'post' => $hasFile ? null : $data,
            'page' => $page,
            'category' => $category,
        ];
        header('Cache-Control: no-store');
        header('Location: admin_security_gate.php', true, 303);
        exit;
    }

    /** Retorna apenas valores previamente submetidos e com CSRF válido. */
    public static function consumePending(int $adminId): ?array
    {
        $pending = $_SESSION['admin_stepup_pending'] ?? null;
        unset($_SESSION['admin_stepup_pending']);
        if (!is_array($pending)
            || ($pending['admin_id'] ?? null) !== $adminId
            || time() - (int) ($pending['created_at'] ?? 0) > self::MAX_PENDING_SECONDS
            || !is_array($pending['post'] ?? null)) {
            return null;
        }
        $original = $pending['post'];
        if (!in_array($original['action'] ?? null, self::ACTIONS, true)
            || !verifyCsrfToken($original['csrf_token'] ?? null)) {
            return null;
        }
        return $pending;
    }
}
