<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/adm.php
 * Finalidade: adm.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/JobReports.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileReports.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/helpers.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Security/AdminGate.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Garante que apenas uma sessão administrativa alcance ações de moderação.
$usuarioAtual = requireWebAuth('adm'); // Explicação DevIN: Atribui um valor à variável PHP $usuarioAtual.

$tipo    = 'adm'; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
$nome    = $_SESSION['usuario_nome']  ?? 'Administrador'; // Explicação DevIN: Atribui um valor à variável PHP $nome.
$email   = $_SESSION['usuario_email'] ?? 'adm@devin.com'; // Explicação DevIN: Atribui um valor à variável PHP $email.
$pagina  = requestString($_GET, 'pagina'); // Explicação DevIN: Atribui um valor à variável PHP $pagina.
$categoriaReport = requestString($_GET, 'categoria'); // Explicação DevIN: Atribui um valor à variável PHP $categoriaReport.
$categoriasReport = ['vagas', 'empresas', 'pessoas']; // Explicação DevIN: Atribui um valor à variável PHP $categoriasReport.
if (!in_array($categoriaReport, $categoriasReport, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $categoriaReport = 'vagas'; // Explicação DevIN: Atribui um valor à variável PHP $categoriaReport.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$paginasPermitidas = ['inicio', 'candidatos', 'posts', 'reports', 'sobre', 'perfil']; // Explicação DevIN: Atribui um valor à variável PHP $paginasPermitidas.
if (!in_array($pagina, $paginasPermitidas, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Usa uma lista permitida para evitar que a URL selecione páginas arbitrárias.
    $pagina = 'inicio'; // Explicação DevIN: Atribui um valor à variável PHP $pagina.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| PROCESSAMENTO DE AÇÕES DE MODERAÇÃO E PERFIL (POST) // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Toda alteração administrativa precisa trazer o token associado à sessão atual.
    requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    // Gate obrigatório no backend: não depende de JS nem do modal de exclusão.
    AdminGate::interceptAdminPost(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $action = requestString($_POST, 'action'); // Explicação DevIN: Atribui um valor à variável PHP $action.

        // A mesma ação trata exclusões de três tipos; um mapa fechado escolhe tabela e chave primária.
        if (in_array($action, ['admin_delete_pessoa', 'admin_delete_empresa', 'admin_delete_vaga'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $id = (int) requestString($_POST, 'id'); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if ($id <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Registro inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $targets = [ // Explicação DevIN: Atribui um valor à variável PHP $targets.
                'admin_delete_pessoa'  => ['pessoa', 'id_pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'admin_delete_empresa' => ['empresa', 'id_empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'admin_delete_vaga'    => ['vagas', 'id_vaga'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

            /* A lista fechada limita quais tabelas/colunas podem ser usadas no SQL dinâmico.
             * O identificador do registro permanece como parâmetro preparado. */
            [$table, $column] = $targets[$action]; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$column} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.

                if ($stmt->affected_rows !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Registro não encontrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['admin_success'] = 'Registro excluído com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'posts' : 'candidatos')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'resolve_job_report') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $reportId = filter_var($_POST['id_report'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $reportId.
            $verdict = requestString($_POST, 'veredito'); // Explicação DevIN: Atribui um valor à variável PHP $verdict.
            if ($reportId === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Denúncia inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureJobReportsSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $notificationsDelivered = decideJobReport($conn, $reportId, $verdict, (int) $_SESSION['usuario_id']); // Explicação DevIN: Atribui um valor à variável PHP $notificationsDelivered.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['admin_success'] = $notificationsDelivered // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'Denúncia analisada. As notificações foram encaminhadas para a aba de conversas.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Decisão registrada; algumas notificações estão pendentes e serão reenviadas automaticamente.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: adm.php?pagina=reports'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'resolve_profile_report') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $reportId = filter_var($_POST['id_report'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $reportId.
            $categoria = requestString($_POST, 'categoria'); // Explicação DevIN: Atribui um valor à variável PHP $categoria.
            $verdict = requestString($_POST, 'veredito'); // Explicação DevIN: Atribui um valor à variável PHP $verdict.
            if ($reportId === false || !in_array($categoria, $categoriasReport, true) || !in_array($verdict, ['manter', 'excluir'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Denúncia inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureProfileReportsSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $notificationDelivered = decideProfileReport($conn, $reportId, $verdict, (int) $_SESSION['usuario_id']); // Explicação DevIN: Atribui um valor à variável PHP $notificationDelivered.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['admin_success'] = $notificationDelivered // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? ($verdict === 'excluir' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? 'Denúncia aceita. O perfil denunciado foi removido e o denunciante foi notificado.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : 'Denúncia recusada. O perfil foi mantido e o denunciante foi notificado.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Decisão registrada, mas não foi possível enviar a mensagem ao denunciante.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: adm.php?pagina=reports&categoria=' . rawurlencode($categoria)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['usuario_nome']    = trim(requestString($_POST, 'nome')); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_email']   = trim(requestString($_POST, 'email')); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . rawurlencode($pagina)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'delete_account') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            deleteProfile($tipo, (int) $_SESSION['usuario_id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            header('Location: logout.php?destino=index'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro no dashboard ADM: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif (str_starts_with($action, 'admin_delete_')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['admin_error'] = 'Não foi possível excluir o registro. Ele pode possuir dados vinculados no sistema.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'posts' : 'candidatos')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'resolve_job_report') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['admin_error'] = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível registrar a decisão da denúncia.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: adm.php?pagina=reports'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'resolve_profile_report') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['admin_error'] = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível concluir a denúncia.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $categoria = requestString($_POST, 'categoria'); // Explicação DevIN: Atribui um valor à variável PHP $categoria.
            if (!in_array($categoria, $categoriasReport, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $categoria = 'vagas'; // Explicação DevIN: Atribui um valor à variável PHP $categoria.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            header('Location: adm.php?pagina=reports&categoria=' . rawurlencode($categoria)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['profile_error'] = 'Não foi possível atualizar o perfil. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=' . rawurlencode($pagina)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $_SESSION['profile_error'] = 'Não foi possível concluir a operação. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: adm.php?pagina=perfil'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| CONSULTA DE DADOS PARA MODERAÇÃO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

$admId       = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $admId.
$perfilAtual = findProfile($tipo, $admId); // Explicação DevIN: Atribui um valor à variável PHP $perfilAtual.

if (!$perfilAtual) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: logout.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$idiomaAtual = getProfileLanguage($tipo, $admId); // Explicação DevIN: Atribui um valor à variável PHP $idiomaAtual.

$empresasAdmin = []; // Explicação DevIN: Atribui um valor à variável PHP $empresasAdmin.
$usuariosAdmin = []; // Explicação DevIN: Atribui um valor à variável PHP $usuariosAdmin.
$vagasAdmin    = []; // Explicação DevIN: Atribui um valor à variável PHP $vagasAdmin.
$reportsAdmin  = []; // Explicação DevIN: Atribui um valor à variável PHP $reportsAdmin.
$profileReportsAdmin = []; // Explicação DevIN: Atribui um valor à variável PHP $profileReportsAdmin.
$reportCounts = ['vagas' => 0, 'empresas' => 0, 'pessoas' => 0]; // Explicação DevIN: Atribui um valor à variável PHP $reportCounts.
$totalReportsAdmin = 0; // Explicação DevIN: Atribui um valor à variável PHP $totalReportsAdmin.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // A consulta reúne os dados necessários para montar a lista de empresas moderáveis.
        $resultEmpresas = $conn->query('SELECT id_empresa AS id, nome, email, cnpj FROM empresa ORDER BY nome'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $empresasAdmin  = $resultEmpresas ? $resultEmpresas->fetch_all(MYSQLI_ASSOC) : []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

        // LEFT JOIN mantém candidatos sem currículo na lista e COALESCE evita valores nulos na interface.
        $resultPessoas  = $conn->query('
            SELECT p.id_pessoa AS id, p.nome, p.email, p.cpf,
                COALESCE(cu.nome_social, "") AS nome_social,
                COALESCE(cu.grau_de_escolaridade, "") AS grau_de_escolaridade,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia,
                COALESCE(cu.idiomas, "") AS idiomas
            FROM pessoa p
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            ORDER BY p.nome
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $usuariosAdmin  = $resultPessoas ? $resultPessoas->fetch_all(MYSQLI_ASSOC) : []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

        // INNER JOIN associa cada vaga ao nome da empresa que a publicou.
        $resultVagas    = $conn->query("
            SELECT v.id_vaga AS id, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa
            FROM vagas v
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            ORDER BY v.id_vaga DESC
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $vagasAdmin     = $resultVagas ? $resultVagas->fetch_all(MYSQLI_ASSOC) : []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

        ensureJobReportsSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            if (!dispatchJobReportNotifications($conn)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $_SESSION['admin_error'] = 'Algumas notificações de análises anteriores ainda estão pendentes de entrega.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (Throwable $notificationException) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Não foi possível reenviar notificações de denúncias: ' . $notificationException->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['admin_error'] = 'Algumas notificações de análises anteriores ainda estão pendentes de entrega.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $resultReports = $conn->query('
            SELECT r.*,
                   e.email AS email_empresa,
                   COALESCE(rp.email, re.email, \'\') AS email_denunciante,
                   COALESCE(NULLIF(r.descricao_vaga, \'\'), v.descricao, \'\') AS descricao_exibicao,
                   CASE WHEN r.descricao_vaga IS NULL THEN COALESCE(v.salario_mensal, r.salario_mensal) ELSE r.salario_mensal END AS salario_exibicao,
                   CASE WHEN r.descricao_vaga IS NULL THEN COALESCE(v.limite_aprovados, r.limite_aprovados) ELSE r.limite_aprovados END AS limite_exibicao
            FROM reports_vagas r
            LEFT JOIN vagas v ON v.id_vaga = r.id_vaga
            LEFT JOIN empresa e ON e.id_empresa = r.id_empresa
            LEFT JOIN pessoa rp ON r.tipo_denunciante = "pessoa" AND rp.id_pessoa = r.id_denunciante
            LEFT JOIN empresa re ON r.tipo_denunciante = "empresa" AND re.id_empresa = r.id_denunciante
            WHERE r.status = "pendente"
               OR r.analisado_em >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ORDER BY FIELD(r.status, "pendente", "mantida", "excluida"), r.criado_em DESC, r.id DESC
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $reportsAdmin = $resultReports->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

        if ($pagina === 'reports') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            ensureProfileReportsSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                if (!dispatchProfileReportNotifications($conn)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $_SESSION['admin_error'] = 'Algumas notificações de denúncias de perfil estão pendentes de entrega.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } catch (Throwable $notificationException) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                error_log('Não foi possível reenviar notificações de denúncias de perfil: ' . $notificationException->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $_SESSION['admin_error'] = 'Algumas notificações de denúncias de perfil estão pendentes de entrega.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $resultProfileReports = $conn->query('
                SELECT r.*,
                       COALESCE(tp.email, te.email, \'\') AS email_denunciado,
                       COALESCE(te.cnpj, \'\') AS cnpj_denunciado,
                       COALESCE(rp.email, re.email, \'\') AS email_denunciante
                FROM reports_perfis r
                LEFT JOIN pessoa tp ON r.tipo_denunciado = "pessoa" AND tp.id_pessoa = r.id_denunciado
                LEFT JOIN empresa te ON r.tipo_denunciado = "empresa" AND te.id_empresa = r.id_denunciado
                LEFT JOIN pessoa rp ON r.tipo_denunciante = "pessoa" AND rp.id_pessoa = r.id_denunciante
                LEFT JOIN empresa re ON r.tipo_denunciante = "empresa" AND re.id_empresa = r.id_denunciante
                WHERE r.status = "pendente"
                   OR r.analisado_em >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                ORDER BY FIELD(r.status, "pendente", "concluida"), r.criado_em DESC, r.id DESC
            '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $profileReportsAdmin = $resultProfileReports->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $reportCounts['vagas'] = count($reportsAdmin); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            foreach ($profileReportsAdmin as $profileReport) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $category = $profileReport['tipo_denunciado'] === 'empresa' ? 'empresas' : 'pessoas'; // Explicação DevIN: Atribui um valor à variável PHP $category.
                $reportCounts[$category]++; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $totalReportsAdmin = $reportCounts[$categoriaReport]; // Explicação DevIN: Atribui um valor à variável PHP $totalReportsAdmin.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao carregar moderação ADM: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['admin_error'] = 'Não foi possível carregar os registros de moderação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= h($idiomaAtual) ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Dashboard Administrativo</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>"> <!-- Explicação DevIN: Declara conteúdo principal da página. Contém dado dinâmico processado pelo PHP. -->
        <aside class="sidebar"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
            <div class="sidebar-topo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a class="brand" href="adm.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                    <span class="brand-text">Dev<span>IN</span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                    <span></span><span></span><span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <nav class="menu-principal" aria-label="Menu principal"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                <a class="<?= ativo($pagina, 'inicio') ?>" href="adm.php?pagina=inicio"><?= dashboardIcon('building') ?><span class="menu-text">Empresas</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'posts') ?>" href="adm.php?pagina=posts"><?= dashboardIcon('briefcase') ?><span class="menu-text">Posts das empresas</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="adm.php?pagina=candidatos"><?= dashboardIcon('user') ?><span class="menu-text">Pessoas</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'reports') ?>" href="adm.php?pagina=reports"><?= dashboardIcon('info') ?><span class="menu-text">Reports</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'sobre') ?>" href="adm.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->

            <div class="conta"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <details class="perfil-dropdown"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                    <div class="perfil-menu"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configurações</span></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                        <a href="JarvisT.php"><?= dashboardIcon('settings') ?><span class="menu-text">Configurar Token 2</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </aside> <!-- Explicação DevIN: Fecha elemento aside do documento. -->

        <section class="lista-area"> <!-- Explicação DevIN: Declara seção temática. -->
            <?php if (!empty($_SESSION['admin_error'])): ?>
                <div class="dashboard-toast dashboard-toast--error" data-auto-dismiss-toast role="alert"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <p class="dashboard-toast__msg dashboard-toast__msg--error"><?= h($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                    <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <?php endif; ?>
            <?php if (!empty($_SESSION['admin_success'])): ?>
                <div class="dashboard-toast dashboard-toast--success" data-auto-dismiss-toast role="status"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                    <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <?php endif; ?>

            <?php if ($pagina !== 'sobre'): ?>
                <?= dashboardListHeader( // Explicação DevIN: Combina execução de código com conteúdo HTML gerado dinamicamente.
                    $pagina, // Explicação DevIN: Continua expressão ou bloco pertencente a marcação button.
                    $pagina === 'candidatos' ? 'Candidatos' : ($pagina === 'posts' ? 'Posts das empresas' : ($pagina === 'reports' ? 'Reports de ' . $categoriaReport : ($pagina === 'perfil' ? 'Perfil' : 'Empresas'))), // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                    'Pesquisar por nome ou habilidade...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    $pagina === 'candidatos' ? count($usuariosAdmin) . ' candidatos' : ($pagina === 'posts' ? count($vagasAdmin) . ' posts' : ($pagina === 'reports' ? $totalReportsAdmin . ' reports' : ($pagina === 'inicio' ? (count($empresasAdmin) + count($vagasAdmin)) . ' registros' : ''))) // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'candidatos', 'posts', 'reports'], true)): ?>
                    <?php if ($pagina === 'reports'): ?>
                        <nav class="report-folders" aria-label="Categorias de denúncias"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                            <a class="<?= $categoriaReport === 'vagas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=vagas"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                                <?= dashboardIcon('briefcase') ?><span>Vagas</span><strong><?= $reportCounts['vagas'] ?></strong>
                            </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                            <a class="<?= $categoriaReport === 'empresas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=empresas"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                                <?= dashboardIcon('building') ?><span>Empresas</span><strong><?= $reportCounts['empresas'] ?></strong>
                            </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                            <a class="<?= $categoriaReport === 'pessoas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=pessoas"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                                <?= dashboardIcon('user') ?><span>Pessoas</span><strong><?= $reportCounts['pessoas'] ?></strong>
                            </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                        </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
                        <p class="list-section-label"><?= $categoriaReport === 'vagas' ? 'Denúncias de vagas' : ($categoriaReport === 'empresas' ? 'Denúncias de empresas' : 'Denúncias de pessoas') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                    <?php else: ?>
                        <p class="list-section-label">Disponíveis</p> <!-- Explicação DevIN: Declara parágrafo. -->
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card"> <!-- Explicação DevIN: Declara seção temática. -->
                    <a class="fechar-card" href="adm.php?pagina=inicio">×</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <div class="perfil-topo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <?= profileAvatar($perfilAtual, 'avatar-grande') ?>
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <strong><?= h($nome) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                            <small><?= h($email) ?></small> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <button class="btn primary" type="button" data-open-profile>Editar perfil</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            <?php elseif ($pagina === 'candidatos'): ?>
                <?php if (!$usuariosAdmin): ?><p class="empty-state">Nenhum candidato cadastrado.</p><?php endif; ?>
                <?php foreach ($usuariosAdmin as $usuario): ?>
                    <?php $detalhesCandidato = dashboardCandidateDetails($usuario); ?>
                    <article class="item-card" data-detail="<?= h($detalhesCandidato['summary']) ?>" data-detail-name="<?= h($detalhesCandidato['name']) ?>" data-detail-summary="<?= h($detalhesCandidato['summary']) ?>" data-detail-role="Candidato DevIN" data-detail-tags="<?= h($detalhesCandidato['tags']) ?>" data-detail-experience="<?= h($detalhesCandidato['experience']) ?>" data-detail-action-label="Excluir registro"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($usuario['nome']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($usuario['email']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags('Candidato|Perfil ativo') ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form method="post" onsubmit="return confirm('Excluir este candidato permanentemente?');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                            <input type="hidden" name="action" value="admin_delete_pessoa"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir perfil</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
            <?php elseif ($pagina === 'posts'): ?>
                <?php if (!$vagasAdmin): ?><p class="empty-state">Nenhuma vaga publicada.</p><?php endif; ?>
                <?php foreach ($vagasAdmin as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Vaga publicada por ' . $vaga['empresa']) ?>" data-detail-tags="Vaga|<?= h($vaga['empresa']) ?>" data-detail-experience="Publicação::Registro da vaga na plataforma" data-detail-action-label="Excluir vaga"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($vaga['titulo']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($vaga['empresa']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags('Vaga|' . $vaga['empresa']) ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form method="post" onsubmit="return confirm('Excluir esta vaga?');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                            <input type="hidden" name="action" value="admin_delete_vaga"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir vaga</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
            <?php elseif ($pagina === 'reports'): ?>
                <?php if ($categoriaReport === 'vagas'): ?>
                    <?php if (!$reportsAdmin): ?><p class="empty-state">Nenhuma vaga foi denunciada.</p><?php endif; ?>
                    <?php foreach ($reportsAdmin as $report): ?>
                    <?php // Explicação DevIN: Abre código PHP interpretado no servidor.
                    if ($report['status'] === 'mantida') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $statusReport = 'Vaga mantida'; // Explicação DevIN: Atribui um valor à variável PHP $statusReport.
                    } elseif ($report['status'] === 'excluida') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $statusReport = 'Vaga excluída'; // Explicação DevIN: Atribui um valor à variável PHP $statusReport.
                    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                        $statusReport = 'Aguardando análise'; // Explicação DevIN: Atribui um valor à variável PHP $statusReport.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $motivosReport = [ // Explicação DevIN: Atribui um valor à variável PHP $motivosReport.
                        'fraude' => 'Fraude ou golpe', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'informacao_falsa' => 'Informações falsas ou enganosas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'discriminacao' => 'Discriminação', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'conteudo_inadequado' => 'Conteúdo inadequado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'vaga_expirada' => 'Vaga expirada ou indisponível', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'outro' => 'Outro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                    $descricaoVaga = trim((string) ($report['descricao_exibicao'] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $descricaoVaga.
                    $salarioVaga = (float) ($report['salario_exibicao'] ?? 0) > 0 // Explicação DevIN: Atribui um valor à variável PHP $salarioVaga.
                        ? 'R$ ' . number_format((float) $report['salario_exibicao'], 2, ',', '.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : 'A combinar'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    $limiteAprovados = (int) ($report['limite_exibicao'] ?? 0) > 0 // Explicação DevIN: Atribui um valor à variável PHP $limiteAprovados.
                        ? (string) $report['limite_exibicao'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : 'Sem limite'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    $analiseReport = $report['status'] === 'pendente' // Explicação DevIN: Atribui um valor à variável PHP $analiseReport.
                        ? 'Aguardando decisão' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : (string) $report['parecer']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ?>
                    <article class="item-card report-card" data-job-report-card data-detail="<?= h($descricaoVaga ?: 'Sem descrição informada.') ?>" data-report-job-description="<?= h($descricaoVaga ?: 'Sem descrição informada.') ?>" data-report-job-salary="<?= h($salarioVaga) ?>" data-report-job-limit="<?= h($limiteAprovados) ?>" data-detail-name="<?= h($report['titulo_vaga']) ?>" data-report-id="<?= (int) $report['id'] ?>" data-report-created-at="<?= h(date('d/m/Y H:i', strtotime($report['criado_em']))) ?>" data-report-target-id="<?= (int) $report['id_vaga'] ?>" data-report-company="<?= h($report['nome_empresa']) ?>" data-report-company-email="<?= h($report['email_empresa'] ?? '') ?>" data-report-reporter="<?= h($report['nome_denunciante']) ?>" data-report-reporter-email="<?= h($report['email_denunciante'] ?? '') ?>" data-report-reporter-type="<?= h($report['tipo_denunciante'] === 'empresa' ? 'Empresa' : 'Pessoa') ?>" data-report-reporter-id="<?= (int) $report['id_denunciante'] ?>" data-report-reason="<?= h($motivosReport[$report['motivo']] ?? $report['motivo']) ?>" data-report-message="<?= h($report['detalhes']) ?>" data-report-analysis="<?= h($analiseReport) ?>" data-report-decision="<?= $report['status'] === 'pendente' ? 'true' : 'false' ?>"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('info') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div class="report-card-content"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($report['titulo_vaga']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><span>Empresa:</span> <?= h($report['nome_empresa']) ?> · <span>Denunciante:</span> <?= h($report['nome_denunciante']) ?> (<span><?= h($report['tipo_denunciante'] === 'empresa' ? 'Empresa' : 'Pessoa') ?></span> #<?= (int) $report['id_denunciante'] ?>)</p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            <?php if (!empty($report['email_denunciante'])): ?><p class="report-card-meta"><?= h($report['email_denunciante']) ?></p><?php endif; ?>
                            <p class="report-card-meta"><span>Denúncia #</span><?= (int) $report['id'] ?> · <span>Vaga #</span><?= (int) $report['id_vaga'] ?> · <span>Enviada em</span> <?= h(date('d/m/Y H:i', strtotime($report['criado_em']))) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            <p><span>Motivo:</span> <span><?= h($motivosReport[$report['motivo']] ?? $report['motivo']) ?></span></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            <p><?= h($report['detalhes']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags($statusReport) ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <?php if ($report['status'] === 'pendente'): ?>
                            <div class="report-card-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <button class="btn primary" type="button" data-open-report-analysis>Fazer análise</button> <!-- Explicação DevIN: Declara botão de ação. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            <form hidden method="post" data-report-keep-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                <input type="hidden" name="action" value="resolve_job_report"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="veredito" value="manter"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                            <form hidden method="post" data-report-delete-form data-report-exclusion-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                <input type="hidden" name="action" value="resolve_job_report"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="veredito" value="excluir"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                        <?php endif; ?>
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php // Explicação DevIN: Abre código PHP interpretado no servidor.
                    $tipoPerfilReport = $categoriaReport === 'empresas' ? 'empresa' : 'pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $tipoPerfilReport.
                    $perfilReportsCategoria = array_values(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $perfilReportsCategoria.
                        $profileReportsAdmin, // Explicação DevIN: Continua expressão ou bloco pertencente a marcação input.
                        static fn (array $report): bool => $report['tipo_denunciado'] === $tipoPerfilReport // Explicação DevIN: Continua expressão ou bloco pertencente a marcação input.
                    )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                    $rotuloPerfilReport = $tipoPerfilReport === 'empresa' ? 'Empresa' : 'Pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $rotuloPerfilReport.
                    $motivosPerfilReport = [ // Explicação DevIN: Atribui um valor à variável PHP $motivosPerfilReport.
                        'assedio' => 'Assédio ou comportamento abusivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'fraude' => 'Fraude ou golpe', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'falsidade' => 'Informações falsas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'conteudo_inadequado' => 'Conteúdo inadequado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'outro' => 'Outro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                    ?>
                    <?php if (!$perfilReportsCategoria): ?><p class="empty-state">Nenhum perfil foi denunciado.</p><?php endif; ?>
                    <?php foreach ($perfilReportsCategoria as $report): ?>
                        <?php // Explicação DevIN: Abre código PHP interpretado no servidor.
                        $statusPerfilReport = $report['status'] === 'pendente' ? 'Aguardando análise' : 'Concluída'; // Explicação DevIN: Atribui um valor à variável PHP $statusPerfilReport.
                        $tipoDenunciante = $report['tipo_denunciante'] === 'empresa' ? 'Empresa' : 'Pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $tipoDenunciante.
                        ?>
                        <article class="item-card report-card profile-report-card"
                            data-detail="<?= h($report['detalhes']) ?>"
                            data-detail-name="<?= h($report['nome_denunciado']) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-card <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-report-id="<?= (int) $report['id'] ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-report-created-at="<?= h(date('d/m/Y H:i', strtotime($report['criado_em']))) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-report-target-id="<?= (int) $report['id_denunciado'] ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-role="<?= h($rotuloPerfilReport) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-reporter="<?= h($report['nome_denunciante']) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-reporter-email="<?= h($report['email_denunciante'] ?? '') ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-reporter-type="<?= h($tipoDenunciante) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-reporter-id="<?= (int) $report['id_denunciante'] ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-target-email="<?= h($report['email_denunciado'] ?? '') ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-target-cnpj="<?= h($report['cnpj_denunciado'] ?? '') ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-reason="<?= h($motivosPerfilReport[$report['motivo']] ?? $report['motivo']) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-message="<?= h($report['detalhes']) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-profile-report-analysis="<?= h($report['status'] === 'pendente' ? 'Aguardando decisão' : (string) $report['parecer']) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-detail-tags="<?= h($rotuloPerfilReport . '|' . $statusPerfilReport) ?>" <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            data-report-decision="<?= $report['status'] === 'pendente' ? 'true' : 'false' ?>"> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                            <span class="card-avatar"><?= dashboardIcon($tipoPerfilReport === 'empresa' ? 'building' : 'user') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                            <div class="report-card-content"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <h2><?= h($report['nome_denunciado']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                                <p><span><?= h($rotuloPerfilReport) ?></span> #<?= (int) $report['id_denunciado'] ?> · <span>Denunciante:</span> <?= h($report['nome_denunciante']) ?> (<span><?= h($tipoDenunciante) ?></span> #<?= (int) $report['id_denunciante'] ?>)</p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                                <?php if (!empty($report['email_denunciado'])): ?><p class="report-card-meta"><?= h($report['email_denunciado']) ?><?= !empty($report['cnpj_denunciado']) ? ' · CNPJ ' . h($report['cnpj_denunciado']) : '' ?></p><?php endif; ?>
                                <?php if (!empty($report['email_denunciante'])): ?><p class="report-card-meta"><span>E-mail do denunciante:</span> <?= h($report['email_denunciante']) ?></p><?php endif; ?>
                                <p class="report-card-meta"><span>Denúncia #</span><?= (int) $report['id'] ?> · <span>Enviada em</span> <?= h(date('d/m/Y H:i', strtotime($report['criado_em']))) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                                <p><span>Motivo:</span> <span><?= h($motivosPerfilReport[$report['motivo']] ?? $report['motivo']) ?></span></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                                <p><?= h($report['detalhes']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                                <?= dashboardCardTags($statusPerfilReport) ?>
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            <?php if ($report['status'] === 'pendente'): ?>
                                <div class="report-card-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                    <button class="btn primary" type="button" data-open-report-analysis>Fazer análise</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                <form hidden method="post" data-report-keep-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                    <input type="hidden" name="action" value="resolve_profile_report"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="categoria" value="<?= h($categoriaReport) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="veredito" value="manter"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                                <form hidden method="post" data-report-delete-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                    <input type="hidden" name="action" value="resolve_profile_report"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="categoria" value="<?= h($categoriaReport) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="veredito" value="excluir"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                            <?php endif; ?>
                        </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!$empresasAdmin && !$vagasAdmin): ?><p class="empty-state">Nenhum registro disponível.</p><?php endif; ?>
                <?php foreach ($empresasAdmin as $empresa): ?>
                    <article class="item-card" data-detail="<?= h('E-mail: ' . $empresa['email'] . ' | CNPJ: ' . $empresa['cnpj']) ?>" data-detail-role="Empresa cadastrada" data-detail-tags="Empresa|Perfil ativo" data-detail-experience="<?= h('Cadastro na plataforma::CNPJ ' . $empresa['cnpj']) ?>" data-detail-action-label="Excluir registro"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('building') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($empresa['nome']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($empresa['email']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags('Empresa|Perfil ativo') ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form method="post" onsubmit="return confirm('Excluir esta empresa permanentemente?');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                            <input type="hidden" name="action" value="admin_delete_empresa"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir empresa</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
                <?php foreach ($vagasAdmin as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Vaga publicada por ' . $vaga['empresa']) ?>" data-detail-tags="Vaga|<?= h($vaga['empresa']) ?>" data-detail-experience="Publicação::Registro da vaga na plataforma" data-detail-action-label="Excluir vaga"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($vaga['empresa']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags('Vaga|' . $vaga['empresa']) ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form method="post" onsubmit="return confirm('Excluir esta vaga?');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                            <input type="hidden" name="action" value="admin_delete_vaga"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir vaga</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($pagina === 'sobre'): ?>
                <?php require __DIR__ . '/components/dashboard-footer.php'; ?>
            <?php endif; ?>
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

        <?php if ($pagina === 'reports'): ?>
            <dialog class="settings-modal report-confirm-modal" data-report-confirm-dialog aria-labelledby="reportConfirmTitle"> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
                <div class="modal-form"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <span class="report-confirm-icon" aria-hidden="true"><?= dashboardIcon('info') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
                    <h2 id="reportConfirmTitle">Excluir vaga denunciada?</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                    <p class="settings-description">A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                    <div class="report-confirm-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <button class="btn secondary" type="button" data-cancel-report-exclusion>Cancelar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        <button class="btn danger" type="button" data-confirm-report-exclusion>Excluir vaga</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </dialog> <!-- Explicação DevIN: Fecha elemento dialog do documento. -->
        <?php endif; ?>

        <?php if ($pagina !== 'sobre' && $pagina !== 'perfil'): ?>
            <?= dashboardDetailPanel() ?>
        <?php else: ?>
        <aside class="detalhe-area"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p>Equipe DevIN - Acesso Restrito ADM.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Seu Perfil</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p>Controles de segurança da conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <?php else: ?>
                <h2>Explicando tudo sobre o post selecionado</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p id="detailText">Selecione um post ou candidato para analisar as informações.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <button class="btn danger fixed-action" type="button">Excluir Registro</button> <!-- Explicação DevIN: Declara botão de ação. -->
            <?php endif; ?>
        </aside> <!-- Explicação DevIN: Fecha elemento aside do documento. -->
        <?php endif; ?>
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

    <!-- Modal Editar Perfil -->
    <dialog class="settings-modal profile-modal" id="profileModal" aria-labelledby="profileModalTitle"> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
        <form method="post" class="modal-form profile-form" enctype="multipart/form-data"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <h2 class="sr-only" id="profileModalTitle">Meu perfil</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
            <?php if (!empty($_SESSION['profile_error'])): ?>
                <p class="form-error"><?= h($_SESSION['profile_error']); unset($_SESSION['profile_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <?php if (!empty($_SESSION['profile_success'])): ?>
                <p class="form-success"><?= h($_SESSION['profile_success']); unset($_SESSION['profile_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <input type="hidden" name="action" value="update_profile"> <!-- Explicação DevIN: Declara campo de entrada. -->
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
            <div class="profile-summary"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <div class="profile-photo" aria-label="Foto de perfil"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <?= profileAvatar($perfilAtual, 'profile-photo-preview') ?>
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <strong><?= h($perfilAtual['nome']) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                    <small><?= h($perfilAtual['email']) ?></small> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <div class="profile-fields"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <label>Nome<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>E-mail<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <button class="profile-save" type="submit">Salvar</button> <!-- Explicação DevIN: Declara botão de ação. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        <form method="post" class="modal-form account-delete-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <input type="hidden" name="action" value="delete_account"> <!-- Explicação DevIN: Declara campo de entrada. -->
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
            <button class="btn danger" type="submit" data-delete-account>Excluir conta</button> <!-- Explicação DevIN: Declara botão de ação. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
    </dialog> <!-- Explicação DevIN: Fecha elemento dialog do documento. -->

    <dialog class="settings-modal" id="settingsModal" aria-labelledby="settingsModalTitle"> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
        <form method="post" class="modal-form settings-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <h2 id="settingsModalTitle">Configurações</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
            <p class="settings-description">Personalize o idioma da interface da sua conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <?php if (!empty($_SESSION['settings_error'])): ?>
                <p class="form-error" role="alert"><?= h($_SESSION['settings_error']); unset($_SESSION['settings_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <?php if (!empty($_SESSION['settings_success'])): ?>
                <p class="form-success" role="status"><?= h($_SESSION['settings_success']); unset($_SESSION['settings_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <label for="settingsLanguage">Idioma <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                <select id="settingsLanguage" name="idioma"> <!-- Explicação DevIN: Declara seleção de opções. -->
                    <option value="pt-BR" <?= $idiomaAtual === 'pt-BR' ? 'selected' : '' ?>>Português</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                    <option value="en" <?= $idiomaAtual === 'en' ? 'selected' : '' ?>>Inglês</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                    <option value="es" <?= $idiomaAtual === 'es' ? 'selected' : '' ?>>Espanhol</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
            </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
            <input type="hidden" name="action" value="update_settings"> <!-- Explicação DevIN: Declara campo de entrada. -->
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
            <button class="btn primary settings-save" type="submit">Salvar alterações</button> <!-- Explicação DevIN: Declara botão de ação. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        <section class="settings-danger-zone"> <!-- Explicação DevIN: Declara seção temática. -->
            <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <h3>Excluir conta</h3> <!-- Explicação DevIN: Declara subtítulo de terceiro nível. -->
                <p>Essa ação é permanente e não pode ser desfeita.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <form method="post" class="account-delete-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <input type="hidden" name="action" value="delete_account"> <!-- Explicação DevIN: Declara campo de entrada. -->
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                <button class="btn danger" type="submit" data-delete-account>Excluir conta</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </dialog> <!-- Explicação DevIN: Fecha elemento dialog do documento. -->

    <?php require __DIR__ . '/components/jarvis-admin-widget.php'; ?>
    <link rel="stylesheet" href="../css/jarvis-admin.css?v=<?= filemtime(__DIR__ . '/../css/jarvis-admin.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/jarvis-dock.js?v=<?= filemtime(__DIR__ . '/../js/jarvis-dock.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/jarvis-admin.js?v=<?= filemtime(__DIR__ . '/../js/jarvis-admin.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script src="../js/dashboard-menu.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/dashboard.js?v=<?= filemtime(__DIR__ . '/../js/dashboard.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/konami-arcade.js?v=<?= filemtime(__DIR__ . '/../js/konami-arcade.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
