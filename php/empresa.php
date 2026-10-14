<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requireWebAuth('empresa');

$tipo   = 'empresa';
$nome   =$_SESSION['usuario_nome']  ?? 'Empresa';
$email  =$_SESSION['usuario_email'] ?? 'empresa@devin.com';
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? '');
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? '');
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? '');
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? '');
unset($_SESSION['sucesso_login'],$_SESSION['erro_email_senha'], $_SESSION['sucesso_cadastro'],$_SESSION['erro_email_cadastro']);
$pagina = requestString($_GET, 'pagina');

$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil'];
if (!in_array($pagina, $paginasPermitidas, true)) {$pagina = 'inicio';
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DE AÇÕES (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();

    $action = requestString($_POST, 'action');

    try {
        if ($action === 'update_application_status') {
            $idCandidatura = (int) requestString($_POST, 'id_candidatura');
            $status        = requestString($_POST, 'status');

            if ($idCandidatura <= 0 \vert{}\vert{} !in_array($status, ['aprovado', 'recusado'], true)) {
                throw new InvalidArgumentException('Dados da candidatura inválidos.');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt =$conn->prepare('
                    UPDATE candidatura c
                    INNER JOIN vagas v ON v.id_vaga = c.id_vaga
                    SET c.status = ?
                    WHERE c.id_candidatura = ? AND v.id_empresa = ?
                ');

                if (!$stmt) {
                    throw new RuntimeException('Não foi possível atualizar a candidatura.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];$stmt->bind_param('sii', $status,$idCandidatura, $empresaId);$stmt->execute();

                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Candidatura não encontrada ou sem permissão para alterá-la.');
                }

                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['candidate_success'] = 'Candidatura ' . ($status === 'aprovado' ? 'aprovada' : 'recusada') . ' com sucesso.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if ($action === 'create_job') {
            $titulo    = trim(requestString($_POST, 'titulo'));
            $descricao = trim(requestString($_POST, 'descricao'));

            if ($titulo === '' \vert{}\vert{}$descricao === '' || mb_strlen($titulo) > 25 \vert{}\vert{} mb_strlen($descricao) > 255) {
                throw new InvalidArgumentException('Informe título (até 25 caracteres) e descrição (até 255 caracteres).');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt =$conn->prepare('INSERT INTO vagas (titulo, descricao, tempo_vaga, id_empresa) VALUES (?, ?, CURDATE(), ?)');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível criar a vaga.');
                }

                $empresaId = (int)$_SESSION['usuario_id'];
                $stmt->bind_param('ssi',$titulo, $descricao,$empresaId);
                $stmt->execute();$stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga publicada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        if ($action === 'update_job') {
            $vagaId    = (int) requestString($_POST, 'id_vaga');
            $titulo    = trim(requestString($_POST, 'titulo'));
            $descricao = trim(requestString($_POST, 'descricao'));

            if ($vagaId <= 0 || $titulo === '' \vert{}\vert{}$descricao === '' || mb_strlen($titulo) > 25 \vert{}\vert{} mb_strlen($descricao) > 255) {
                throw new InvalidArgumentException('Dados da vaga inválidos.');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt =$conn->prepare('UPDATE vagas SET titulo = ?, descricao = ? WHERE id_vaga = ? AND id_empresa = ?');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível editar a vaga.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];$stmt->bind_param('ssii', $titulo,$descricao, $vagaId,$empresaId);
                $stmt->execute();$stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga atualizada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        if ($action === 'delete_job') {
            $vagaId = (int) requestString($_POST, 'id_vaga');
            if ($vagaId <= 0) {
                throw new InvalidArgumentException('Vaga inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt =$conn->prepare('DELETE FROM vagas WHERE id_vaga = ? AND id_empresa = ?');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível excluir a vaga.');
                }

                $empresaId = (int)$_SESSION['usuario_id'];
                $stmt->bind_param('ii',$vagaId, $empresaId);$stmt->execute();
                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para excluí-la.');
                }
                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga excluída com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        if ($action === 'create_test_candidates') {
            if (APP_ENV !== 'development') {
                throw new RuntimeException('A criação de candidatos de teste está disponível apenas no ambiente local.');
            }

            $empresaId = (int) $_SESSION['usuario_id'];$conn = getDatabaseConnection();

            try {
                $conn->begin_transaction();

                $vagaStmt =$conn->prepare('SELECT id_vaga FROM vagas WHERE id_empresa = ? ORDER BY id_vaga ASC LIMIT 1');
                $vagaStmt->bind_param('i',$empresaId);
                $vagaStmt->execute();$vaga = $vagaStmt->get_result()->fetch_assoc();$vagaStmt->close();

                if (!$vaga) {
                    throw new RuntimeException('Publique uma vaga antes de criar candidatos de teste.');
                }

                $buscarPessoa       =$conn->prepare('SELECT id_pessoa FROM pessoa WHERE email = ? LIMIT 1');
                $criarPessoa        =$conn->prepare('INSERT INTO pessoa (nome, cpf, cep, email, senha_hash, telefone, created_at, lembrete_enviado) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)');
                $buscarCandidatura  =$conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1');
                $criarCandidatura   =$conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), "pendente", ?, ?)');
                $resetarCandidatura =$conn->prepare('UPDATE candidatura SET status = "pendente" WHERE id_candidatura = ?');

                if (!$buscarPessoa || !$criarPessoa \vert{}\vert{} !$buscarCandidatura || !$criarCandidatura \vert{}\vert{} !$resetarCandidatura) {
                    throw new RuntimeException('Não foi possível preparar os dados de teste.');
                }

                foreach ([1 => 'Ana Teste', 2 => 'Bruno Teste'] as $indice => $nomeTeste) {$emailTeste    = 'candidato.teste.' . $empresaId . '.' .$indice . '@devin.local';
                    $cpfTeste      = sprintf('\%011d', 90000000000 + ($empresaId * 10) + $indice);$cepTeste      = '01001000';
                    $telefoneTeste = '1199999000' .$indice;
                    $senhaTeste    = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

                    $buscarPessoa->bind_param('s', $emailTeste);$buscarPessoa->execute();
                    $pessoa =$buscarPessoa->get_result()->fetch_assoc();

                    if ($pessoa) {
                        $pessoaId = (int)$pessoa['id_pessoa'];
                    } else {
                        $criarPessoa->bind_param('ssssss',$nomeTeste, $cpfTeste,$cepTeste, $emailTeste,$senhaTeste, $telefoneTeste);$criarPessoa->execute();
                        $pessoaId = (int)$conn->insert_id;
                    }

                    $vagaId = (int)$vaga['id_vaga'];
                    $buscarCandidatura->bind_param('ii',$pessoaId, $vagaId);$buscarCandidatura->execute();
                    $candidatura =$buscarCandidatura->get_result()->fetch_assoc();

                    if ($candidatura) {$candidaturaId = (int) $candidatura['id_candidatura'];$resetarCandidatura->bind_param('i', $candidaturaId);$resetarCandidatura->execute();
                    } else {
                        $criarCandidatura->bind_param('ii',$pessoaId, $vagaId);$criarCandidatura->execute();
                    }
                }

                $buscarPessoa->close();
                $criarPessoa->close();$buscarCandidatura->close();
                $criarCandidatura->close();$resetarCandidatura->close();

                $conn->commit();
            } catch (Throwable $e) {$conn->rollback();
                throw $e;
            } finally {
                $conn->close();
            }

            $_SESSION['candidate_success'] = 'Dois candidatos de teste foram criados para a sua primeira vaga.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, (int)$_SESSION['usuario_id'], $_POST,$_FILES['foto'] ?? null);
            $_SESSION['usuario_email']   = trim(requestString($_POST, 'email'));$_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: empresa.php?pagina=perfil');
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR');
            header('Location: empresa.php?configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, (int)$_SESSION['usuario_id']);
            header('Location: logout.php');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard empresa: ' . $exception->getMessage());

        if (in_array($action, ['update_application_status', 'create_test_candidates'], true)) {
            $_SESSION['candidate_error'] =$exception instanceof mysqli_sql_exception
                ? 'Não foi possível concluir a ação. Tente novamente.'
                : $exception->getMessage();
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if (in_array($action, ['create_job', 'update_job', 'delete_job'], true)) {
            $_SESSION['job_error'] =$exception->getMessage();
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        $_SESSION['profile_error'] =$exception->getMessage();
        header('Location: empresa.php?pagina=perfil');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CARREGAMENTO DE DADOS E VAGAS
|--------------------------------------------------------------------------
*/

$empresaId   = (int) $_SESSION['usuario_id'];$perfilAtual = findProfile($tipo,$empresaId);

if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}
$idiomaAtual =$_SESSION['idioma'] ?? 'pt-BR';

$empresaPosts = [];$candidatos   = [];

try {
    $conn = getDatabaseConnection();
    try {
        // 1. Busca Vagas Criadas pela Empresa
        $stmtVagas =$conn->prepare("
            SELECT id_vaga, titulo, COALESCE(descricao, '') AS descricao, tempo_vaga
            FROM vagas
            WHERE id_empresa = ?
            ORDER BY id_vaga DESC
        ");
        if ($stmtVagas) {
            $stmtVagas->bind_param('i',$empresaId);
            $stmtVagas->execute();$empresaPosts = $stmtVagas->get_result()->fetch_all(MYSQLI_ASSOC);$stmtVagas->close();
        }

        // 2. Busca Candidatos que se aplicaram às Vagas
        $sqlCandidatos = '
            SELECT
                c.id_candidatura,
                c.status,
                c.data_candidatura,
                p.nome,
                p.email,
                v.titulo AS vaga,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN pessoa p ON p.id_pessoa = c.id_pessoa
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            WHERE v.id_empresa = ?
            ORDER BY c.data_candidatura DESC, c.id_candidatura DESC
        ';
        $stmtCandidatos = $conn->prepare($sqlCandidatos);
        if ($stmtCandidatos) {
            $stmtCandidatos->bind_param('i',$empresaId);
            $stmtCandidatos->execute();$candidatos = $stmtCandidatos->get_result()->fetch_all(MYSQLI_ASSOC);$stmtCandidatos->close();
        }
    } finally {
        $conn->close();
    }
} catch (Throwable $exception) {
    error_log('Erro ao carregar dados do dashboard empresa: ' . $exception->getMessage());
}

foreach ($empresaPosts as &$post) {$post['resumo']  = 'Publicada em ' . date('d/m/Y', strtotime($post['tempo_vaga']));
    $post['detalhe'] =$post['descricao'];
}
unset($post);

foreach ($candidatos as &$candidato) {$candidato['resumo']  = 'Vaga: ' . $candidato['vaga'] . ' \vert{} Status: ' . ucfirst($candidato['status']);
    $candidato['detalhe'] = 'E-mail: ' .$candidato['email']
        . '. Cursos: ' . ($candidato['cursos'] !== '' ?$candidato['cursos'] : 'não informado')
        . '. Experiência: ' . ($candidato['experiencia'] !== '' ?$candidato['experiencia'] : 'não informada');
}
unset($candidato);

$talentos = [];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Empresa</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>
<body>
    <main class="dashboard-shell empresa-dashboard page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="empresa.php">
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">