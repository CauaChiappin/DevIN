<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Security/AdminGate.php
 * Finalidade: admin gate.
 * Domínio: controle de acesso.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/AdminRolePolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Intercepta TODAS as mutações previstas em adm.php antes dos handlers.
 * Para formulários POST comuns, retoma a mesma operação após validação no
 * servidor, sem transformar ações de escrita em requisições GET.
 */
final class AdminGate // Explicação DevIN: Declara o tipo AdminGate e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const ACTIONS = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'admin_delete_pessoa','admin_delete_empresa','admin_delete_vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'resolve_job_report','resolve_profile_report', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'update_profile','update_settings','delete_account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    private const MAX_PENDING_SECONDS = 300; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhe DevIN: Método interceptAdminPost: encapsula uma responsabilidade reutilizável do módulo no contexto de admin gate. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function interceptAdminPost(): void // Explicação DevIN: Declara função/método interceptAdminPost, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // RBAC permanece independente do Token 2, com validação no servidor.
        // Nunca confiar em cargo enviado pelo formulário nem pela IA.
        if (AdminRolePolicy::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $adminId = AdminStepUp::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
            $action = $_POST['action'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $action.
            if (!is_string($action) || !in_array($action, self::ACTIONS, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit('Operação não autorizada.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
                try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                    AdminRolePolicy::requireAction($db, $adminId, $action); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
                } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                    $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                error_log('ADM RBAC deny: ' . get_class($exception)); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
                http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit('Ação não permitida para este administrador.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!AdminStepUp::enabled()) return; // Ativar somente após provisionar os 4 ADMs.
        if (AdminStepUp::allowed('site')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $adminId = AdminStepUp::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
        $action = $_POST['action'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $action.
        if (!is_string($action) || !in_array($action, self::ACTIONS, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit('Operação não autorizada.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $data = []; // Explicação DevIN: Atribui um valor à variável PHP $data.
        $size = 0; // Explicação DevIN: Atribui um valor à variável PHP $size.
        foreach ($_POST as $key => $value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (!is_string($key) || !is_string($value) || strlen($key) > 80) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                http_response_code(400); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit('Formulário inválido.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $size += strlen($key) + strlen($value); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if ($size > 12000 || count($data) >= 30) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                http_response_code(413); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit('Formulário excede o limite permitido.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $data[$key] = $value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $hasFile = false; // Explicação DevIN: Atribui um valor à variável PHP $hasFile.
        foreach ($_FILES as $file) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $hasFile = true; // Explicação DevIN: Atribui um valor à variável PHP $hasFile.
                break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // O PHP não mantém upload temporário entre o POST inicial e o desbloqueio.
        $pages = ['inicio','candidatos','posts','reports','sobre','perfil']; // Explicação DevIN: Atribui um valor à variável PHP $pages.
        $categories = ['vagas','pessoas','empresas']; // Explicação DevIN: Atribui um valor à variável PHP $categories.
        $page = $_GET['pagina'] ?? 'inicio'; // Explicação DevIN: Atribui um valor à variável PHP $page.
        $category = $_GET['categoria'] ?? 'vagas'; // Explicação DevIN: Atribui um valor à variável PHP $category.
        $page = in_array($page, $pages, true) ? $page : 'inicio'; // Explicação DevIN: Atribui um valor à variável PHP $page.
        $category = in_array($category, $categories, true) ? $category : 'vagas'; // Explicação DevIN: Atribui um valor à variável PHP $category.
        $_SESSION['admin_stepup_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'admin_id' => $adminId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'nonce' => bin2hex(random_bytes(16)), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'created_at' => time(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'post' => $hasFile ? null : $data, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'page' => $page, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'category' => $category, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        header('Cache-Control: no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        header('Location: admin_security_gate.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função interceptAdminPost.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Retorna apenas valores previamente submetidos e com CSRF válido. */
    // Detalhe DevIN: Método consumePending: encapsula uma responsabilidade reutilizável do módulo no contexto de admin gate. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function consumePending(int $adminId): ?array // Explicação DevIN: Declara função/método consumePending, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $pending = $_SESSION['admin_stepup_pending'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $pending.
        unset($_SESSION['admin_stepup_pending']); // Explicação DevIN: Continua expressão ou bloco pertencente a função consumePending.
        if (!is_array($pending) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || ($pending['admin_id'] ?? null) !== $adminId // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || time() - (int) ($pending['created_at'] ?? 0) > self::MAX_PENDING_SECONDS // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !is_array($pending['post'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $original = $pending['post']; // Explicação DevIN: Atribui um valor à variável PHP $original.
        if (!in_array($original['action'] ?? null, self::ACTIONS, true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !verifyCsrfToken($original['csrf_token'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $pending; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
