<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/pessoa.php
 * Finalidade: pessoa.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/EmploymentWorkflow.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/JobSchema.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ResumeDocument.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/helpers.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuarioAtual = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuarioAtual.

$tipo          = 'pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
$idPessoaAtual = (int) $usuarioAtual['id']; // Explicação DevIN: Atribui um valor à variável PHP $idPessoaAtual.
$nome          = $_SESSION['usuario_nome']  ?? $usuarioAtual['nome']  ?? 'Usuário'; // Explicação DevIN: Atribui um valor à variável PHP $nome.
$email         = $_SESSION['usuario_email'] ?? $usuarioAtual['email'] ?? 'email@devin.com'; // Explicação DevIN: Atribui um valor à variável PHP $email.
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemSenhaAlterada.
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailSenha.
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCadastro.
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailCadastro.
$mensagemCurriculoSalvo = (string) ($_SESSION['sucesso_curriculo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCurriculoSalvo.
$mensagemEmailCurriculo = (string) ($_SESSION['erro_curriculo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailCurriculo.
$mensagemCurriculoEditado = (string) ($_SESSION['resume_content_success'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCurriculoEditado.
$mensagemCurriculoErro = (string) ($_SESSION['resume_content_error'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCurriculoErro.
unset( // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['sucesso_login'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['erro_email_senha'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['sucesso_cadastro'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['erro_email_cadastro'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['sucesso_curriculo'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['erro_curriculo'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['resume_content_success'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['resume_content_error'] // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$pagina        = requestString($_GET, 'pagina'); // Explicação DevIN: Atribui um valor à variável PHP $pagina.

$paginasPermitidas = ['inicio', 'vagas', 'curriculo', 'sobre', 'perfil', 'mensagens', 'suporte']; // Explicação DevIN: Atribui um valor à variável PHP $paginasPermitidas.
if (!in_array($pagina, $paginasPermitidas, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Aceita apenas as seções válidas do painel do candidato.
    $pagina = 'inicio'; // Explicação DevIN: Atribui um valor à variável PHP $pagina.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$gerarCurriculoAutomaticamente = $pagina === 'curriculo' // Explicação DevIN: Atribui um valor à variável PHP $gerarCurriculoAutomaticamente.
    && requestString($_GET, 'gerar') === '1' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && ($_SESSION['resume_generate_on_dashboard'] ?? false) === true; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
if ($gerarCurriculoAutomaticamente) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    unset($_SESSION['resume_generate_on_dashboard']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| PROCESSAMENTO DE AÇÕES (POST) // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Valida CSRF antes de aceitar candidatura ou alteração de perfil.
    requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $action = requestString($_POST, 'action'); // Explicação DevIN: Atribui um valor à variável PHP $action.

        // O próprio candidato confirma a contratação que a empresa já registrou.
        if ($action === 'confirm_hiring') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $applicationId = filter_var($_POST['id_candidatura'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $applicationId.
            $closeOthers = ($_POST['close_others'] ?? '') === '1'; // Explicação DevIN: Atribui um valor à variável PHP $closeOthers.
            if (!$applicationId || $applicationId < 1) throw new InvalidArgumentException('Candidatura inválida.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $closed = EmploymentWorkflow::candidateConfirm($db, $idPessoaAtual, $applicationId, $closeOthers); // Explicação DevIN: Atribui um valor à variável PHP $closed.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $_SESSION['job_success'] = 'Contratação confirmada. Outras candidaturas pendentes encerradas: ' . $closed . '.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=vagas'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'apply_job') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // A candidatura pertence ao ID da sessão; o cliente envia apenas qual vaga deseja solicitar.
            $vagaId = (int) requestString($_POST, 'id_vaga'); // Explicação DevIN: Atribui um valor à variável PHP $vagaId.
            if ($vagaId <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Vaga inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (!ResumeDocument::hasResume($conn, $idPessoaAtual)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('Você precisa adicionar um currículo para se candidatar. Envie um arquivo ou crie um currículo com IA em “Meu currículo”.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $conn->begin_transaction(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                    // Bloqueia a vaga enquanto valida o encerramento e registra a candidatura.
                    $check = $conn->prepare("
                        SELECT id_vaga
                        FROM vagas
                        WHERE id_vaga = ?
                          AND encerrada_em IS NULL
                          AND (
                              criterio_encerramento NOT IN ('prazo', 'qualquer')
                              OR data_encerramento > CURRENT_TIMESTAMP
                          )
                        LIMIT 1
                        FOR UPDATE
                    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    $check->bind_param('i', $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $check->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $exists = $check->get_result()->num_rows === 1; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                    $check->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                    if (!$exists) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        throw new InvalidArgumentException('A vaga não está mais disponível.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    /* A consulta evita uma candidatura repetida antes do INSERT; ambos os IDs vêm
                     * da sessão e do formulário validado, e são enviados como parâmetros inteiros. */
                    $duplicate = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $duplicate->bind_param('ii', $idPessoaAtual, $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $duplicate->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $alreadyApplied = $duplicate->get_result()->num_rows > 0; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                    $duplicate->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                    if ($alreadyApplied) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        throw new InvalidArgumentException('Você já se candidatou a esta vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    // Cria o registro inicial como pendente para análise da empresa responsável.
                    $stmt = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), ?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $status = 'pendente'; // Explicação DevIN: Atribui um valor à variável PHP $status.
                    $stmt->bind_param('sii', $status, $idPessoaAtual, $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    $conn->commit(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                    $conn->rollback(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['job_success'] = 'Candidatura enviada com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'delete_resume') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ResumeDocument::deleteAll($conn, $idPessoaAtual); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $_SESSION['resume_content_success'] = 'Currículo excluído.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=curriculo'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_resume_content') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $submittedResume = is_array($_POST['resume'] ?? null) ? $_POST['resume'] : []; // Explicação DevIN: Atribui um valor à variável PHP $submittedResume.
            $resumeContentToSave = ['nome' => $nome]; // Explicação DevIN: Atribui um valor à variável PHP $resumeContentToSave.
            foreach (['resumo', 'formacao', 'experiencia', 'cursos', 'idiomas'] as $field) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $value = requestString($submittedResume, $field); // Explicação DevIN: Atribui um valor à variável PHP $value.
                if (strlen($value) > 6000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('Uma seção do currículo excede o limite de 6.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $resumeContentToSave[$field] = $value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (count(array_filter($resumeContentToSave, static fn ($value): bool => is_string($value) && trim($value) !== '')) < 2) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Preencha ao menos uma seção antes de salvar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ResumeDocument::saveGenerated($conn, $idPessoaAtual, $resumeContentToSave); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $_SESSION['resume_content_success'] = 'Currículo atualizado.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=curriculo'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            updateProfile($tipo, $idPessoaAtual, $_POST, $_FILES['foto'] ?? null); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['usuario_nome']  = trim(requestString($_POST, 'nome')); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_email'] = trim(requestString($_POST, 'email')); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            updateLanguage($tipo, $idPessoaAtual, requestString($_POST, 'idioma') ?: 'pt-BR'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'delete_account') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            AuditService::tryRecord('account_deletion_requested', 'success', $tipo, $idPessoaAtual); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            deleteProfile($tipo, $idPessoaAtual); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            AuditService::tryRecord('account_deleted', 'success', $tipo, $idPessoaAtual); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            header('Location: logout.php?destino=index'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro no dashboard pessoa: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (in_array($action, ['apply_job','confirm_hiring'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['job_error'] = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível enviar a candidatura. Tente novamente.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: pessoa.php?pagina=vagas'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'update_resume_content' || $action === 'delete_resume') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['resume_content_error'] = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : ($action === 'delete_resume' ? 'Não foi possível excluir o currículo agora.' : 'Não foi possível salvar o currículo agora.'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: pessoa.php?pagina=curriculo'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['profile_error'] = 'Não foi possível atualizar o perfil. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } elseif ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $_SESSION['profile_error'] = 'Não foi possível concluir a operação. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: pessoa.php?pagina=perfil'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| CONSULTA DE DADOS E VAGAS // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

$perfilAtual = findProfile($tipo, $idPessoaAtual); // Explicação DevIN: Atribui um valor à variável PHP $perfilAtual.
if (!$perfilAtual) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: logout.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$idiomaAtual = getProfileLanguage($tipo, $idPessoaAtual); // Explicação DevIN: Atribui um valor à variável PHP $idiomaAtual.

$vagasDisponiveis  = []; // Explicação DevIN: Atribui um valor à variável PHP $vagasDisponiveis.
$minhasCandidaturas = []; // Explicação DevIN: Atribui um valor à variável PHP $minhasCandidaturas.
$employmentReady = false; // Sem migração, a interface de contratação permanece indisponível sem quebrar o site.
$employmentConfirmations = []; // Estado de confirmação por candidatura pertencente à sessão.
$applicationWithdrawals = []; // Retiradas voluntárias preservam histórico.
$vagasCandidatadas = []; // Explicação DevIN: Atribui um valor à variável PHP $vagasCandidatadas.
$curriculoAtual = []; // Explicação DevIN: Atribui um valor à variável PHP $curriculoAtual.
$resumeDocument = []; // Explicação DevIN: Atribui um valor à variável PHP $resumeDocument.
$resumeDocument = []; // Explicação DevIN: Atribui um valor à variável PHP $resumeDocument.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        // Relaciona cada vaga à empresa e restringe candidaturas ao usuário autenticado.
        // Carrega vagas com seus dados de empresa para a lista de oportunidades disponíveis.
        $stmt = $conn->prepare("
            SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao,
                   v.salario_mensal, v.limite_aprovados, v.tempo_vaga,
                   v.criterio_encerramento, v.data_encerramento, v.encerrada_em,
                   e.id_empresa, e.nome AS empresa,
                   COALESCE(contagens.aprovadas, 0) AS candidaturas_aprovadas
            FROM vagas v
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            LEFT JOIN (
                  SELECT id_vaga, COUNT(*) AS aprovadas
                FROM candidatura
                  WHERE status = 'aprovado'
                GROUP BY id_vaga
            ) contagens ON contagens.id_vaga = v.id_vaga
            ORDER BY v.id_vaga DESC
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $vagasDisponiveis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        // Busca apenas candidaturas deste usuário, junto ao status e aos dados da vaga.
        $stmt = $conn->prepare("
            SELECT c.id_candidatura, c.status, c.data_candidatura, v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            WHERE c.id_pessoa = ?
            ORDER BY c.id_candidatura DESC
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('i', $idPessoaAtual); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $minhasCandidaturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        // Consultas complementares só ocorrem quando a migration está presente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            EmploymentWorkflow::ensureInstalled($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $employmentReady = true; // Explicação DevIN: Atribui um valor à variável PHP $employmentReady.
            $stmtConfirm = $conn->prepare('SELECT ec.application_id,ec.employer_confirmed_at,ec.candidate_confirmed_at
                FROM devin_employment_confirmations ec JOIN candidatura c ON c.id_candidatura=ec.application_id
                WHERE c.id_pessoa=?'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $stmtConfirm->bind_param('i',$idPessoaAtual); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtConfirm->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            foreach ($stmtConfirm->get_result()->fetch_all(MYSQLI_ASSOC) as $confirmation) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $employmentConfirmations[(int)$confirmation['application_id']]=$confirmation; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmtConfirm->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $stmtWithdraw = $conn->prepare('SELECT application_id FROM devin_application_withdrawals WHERE candidate_id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtWithdraw->bind_param('i',$idPessoaAtual); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtWithdraw->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            foreach ($stmtWithdraw->get_result()->fetch_all(MYSQLI_ASSOC) as $withdrawn) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $applicationWithdrawals[(int)$withdrawn['application_id']] = true; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmtWithdraw->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } catch (RuntimeException $notInstalled) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $employmentReady = false; // Explicação DevIN: Atribui um valor à variável PHP $employmentReady.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
 // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        foreach ($minhasCandidaturas as $candidatura) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $vagasCandidatadas[(int) $candidatura['id_vaga']] = true; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $stmt = $conn->prepare('SELECT id_curriculo, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $idPessoaAtual); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $curriculoAtual = $stmt->get_result()->fetch_assoc() ?? []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $resumeDocument = ResumeDocument::find($conn, $idPessoaAtual); // Explicação DevIN: Atribui um valor à variável PHP $resumeDocument.

        $stmt = $conn->prepare('SELECT id_curriculo, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $idPessoaAtual); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $curriculoAtual = $stmt->get_result()->fetch_assoc() ?? []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $resumeDocument = ResumeDocument::find($conn, $idPessoaAtual); // Explicação DevIN: Atribui um valor à variável PHP $resumeDocument.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao carregar vagas da pessoa: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_SESSION['job_error'] = 'Não foi possível carregar as vagas agora.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

foreach ($vagasDisponiveis as &$vaga) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $vaga['detalhe'] = $vaga['descricao'] !== '' ? $vaga['descricao'] : 'Esta vaga ainda não possui uma descrição detalhada.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
unset($vaga); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$resumeContent = json_decode((string) ($resumeDocument['conteudo_json'] ?? ''), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
$resumeContent = is_array($resumeContent) ? $resumeContent : []; // Explicação DevIN: Atribui um valor à variável PHP $resumeContent.
$possuiCurriculo = !empty($curriculoAtual['id_curriculo']) // Explicação DevIN: Atribui um valor à variável PHP $possuiCurriculo.
    || !empty($resumeDocument['arquivo_caminho']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || !empty($resumeDocument['conteudo_json']); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
if ($resumeContent === [] && !empty($curriculoAtual['id_curriculo'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $resumeContent = [ // Explicação DevIN: Atribui um valor à variável PHP $resumeContent.
        'nome' => (string) ($curriculoAtual['nome_social'] ?? $nome), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'resumo' => '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'formacao' => (string) ($curriculoAtual['grau_de_escolaridade'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'experiencia' => (string) ($curriculoAtual['experiencia'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'cursos' => (string) ($curriculoAtual['cursos'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'idiomas' => (string) ($curriculoAtual['idiomas'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$resumeContent['nome'] = (string) ($resumeContent['nome'] ?? $nome); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$hasResumeText = count(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $hasResumeText.
    ['resumo', 'formacao', 'experiencia', 'cursos', 'idiomas'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    static fn (string $field): bool => trim((string) ($resumeContent[$field] ?? '')) !== '' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
)) > 0; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$hasResumePreview = $hasResumeText || !empty($resumeDocument['arquivo_nome']); // Explicação DevIN: Atribui um valor à variável PHP $hasResumePreview.
$resumeContent = json_decode((string) ($resumeDocument['conteudo_json'] ?? ''), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
$resumeContent = is_array($resumeContent) ? $resumeContent : []; // Explicação DevIN: Atribui um valor à variável PHP $resumeContent.
$possuiCurriculo = !empty($curriculoAtual['id_curriculo']) // Explicação DevIN: Atribui um valor à variável PHP $possuiCurriculo.
    || !empty($resumeDocument['arquivo_caminho']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || !empty($resumeDocument['conteudo_json']); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= h($idiomaAtual) ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Dashboard Candidato</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php if ($pagina === 'mensagens'): ?>
        <link rel="stylesheet" href="../css/chat.css?v=<?= filemtime(__DIR__ . '/../css/chat.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
    <?php if (in_array($pagina, ['sobre', 'suporte'], true)): ?>
    <link rel="stylesheet" href="../css/support.css?v=<?= filemtime(__DIR__ . '/../css/support.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>"> <!-- Explicação DevIN: Declara conteúdo principal da página. Contém dado dinâmico processado pelo PHP. -->
        <?php if ($mensagemSenhaAlterada !== '' || $mensagemCadastro !== '' || $mensagemEmailSenha !== '' || $mensagemEmailCadastro !== '' || $mensagemCurriculoSalvo !== '' || $mensagemEmailCurriculo !== '' || $mensagemCurriculoEditado !== '' || $mensagemCurriculoErro !== ''): ?>
            <div class="dashboard-toast" data-auto-dismiss-toast role="status" aria-live="polite"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                <?php if ($mensagemSenhaAlterada !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemSenhaAlterada) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCadastro) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemEmailSenha !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailSenha) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemEmailCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailCadastro) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemCurriculoSalvo !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCurriculoSalvo) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemCurriculoEditado !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCurriculoEditado) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemCurriculoErro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemCurriculoErro) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemEmailCurriculo !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailCurriculo) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>
        <aside class="sidebar"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
            <div class="sidebar-topo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a class="brand" href="pessoa.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                    <span class="brand-text">Dev<span>IN</span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                    <span></span><span></span><span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <nav class="menu-principal" aria-label="Menu principal"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                <a class="<?= ativo($pagina, 'inicio') ?>" href="pessoa.php?pagina=inicio"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                    <?= dashboardIcon('home') ?><span class="menu-text">Início</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <a class="<?= ativo($pagina, 'vagas') ?>" href="pessoa.php?pagina=vagas"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                    <?= dashboardIcon('briefcase') ?><span class="menu-text">Minhas Vagas</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <a class="<?= ativo($pagina, 'curriculo') ?>" href="pessoa.php?pagina=curriculo"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                    <?= dashboardIcon('file') ?><span class="menu-text">Meu currículo</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <a class="<?= ativo($pagina, 'mensagens') ?>" href="pessoa.php?pagina=mensagens" data-messages-api="../php/mensagens_api.php"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                    <?= dashboardIcon('users') ?><span class="menu-text">Mensagens</span><span class="message-unread-badge" data-message-unread-badge hidden></span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <!-- A boia identifica o suporte, diferente do ícone de denúncias. -->
                <a class="<?= ativo($pagina, 'suporte') ?>" href="pessoa.php?pagina=suporte" aria-label="Abrir central de suporte do DevIN"><?= dashboardIcon('lifebuoy') ?><span class="menu-text">Suporte</span></a> <!-- Explicação DevIN: Declara link de navegação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'sobre') ?>" href="pessoa.php?pagina=sobre"> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                    <?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
            </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->

            <div class="conta"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <details class="perfil-dropdown"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    <summary class="perfil-link"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                        <?= profileAvatar($perfilAtual, 'avatar-mini') ?>
                        <span class="menu-text">Perfil</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                    </summary> <!-- Explicação DevIN: Fecha elemento summary do documento. -->
                    <div class="perfil-menu"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <button type="button" data-open-profile> <!-- Explicação DevIN: Declara botão de ação. -->
                            <?= profileAvatar($perfilAtual, 'avatar-foto') ?>
                            <span class="menu-text">Meu perfil</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                        <button type="button" data-open-settings> <!-- Explicação DevIN: Declara botão de ação. -->
                            <?= dashboardIcon('settings') ?>
                            <span class="menu-text">Configurações</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?"> <!-- Explicação DevIN: Declara link de navegação. -->
                    <?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </aside> <!-- Explicação DevIN: Fecha elemento aside do documento. -->

        <section class="lista-area"> <!-- Explicação DevIN: Declara seção temática. -->
            <?php if (!in_array($pagina, ['sobre', 'mensagens', 'curriculo', 'suporte'], true)): ?>
                <?= dashboardListHeader( // Explicação DevIN: Combina execução de código com conteúdo HTML gerado dinamicamente.
                    $pagina, // Explicação DevIN: Continua expressão ou bloco pertencente a marcação section.
                    $pagina === 'vagas' ? 'Minhas candidaturas' : ($pagina === 'perfil' ? 'Perfil' : 'Vagas'), // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                    'Pesquisar por vaga ...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    $pagina === 'vagas' // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                        ? count($minhasCandidaturas) . (count($minhasCandidaturas) === 1 ? ' candidatura' : ' candidaturas') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : ($pagina === 'inicio' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            ? count($vagasDisponiveis) . (count($vagasDisponiveis) === 1 ? ' vaga' : ' vagas') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            : '') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'vagas'], true)): ?>
                    <p class="list-section-label">Disponíveis</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'suporte'): ?>
                <?php require __DIR__ . '/components/support-user.php'; // Reutiliza o formulário e o histórico dentro do painel. ?>
            <?php elseif ($pagina === 'mensagens'): ?>
                <section class="chat-app" data-chat-app data-api="../php/mensagens_api.php" data-csrf="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara seção temática. Contém dado dinâmico processado pelo PHP. -->
                    <aside class="chat-contacts"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
                        <div class="chat-contacts-heading"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h1>Mensagens</h1> <!-- Explicação DevIN: Declara título principal. -->
                            <span class="chat-unread-total" data-chat-unread-total hidden></span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <p class="chat-notification" data-chat-notification role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                        <label class="chat-search"> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <span class="sr-only">Buscar pessoas e empresas</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                            <input type="search" data-chat-search placeholder="Buscar pessoas e empresas"> <!-- Explicação DevIN: Declara campo de entrada. -->
                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                        <p class="chat-status" data-chat-contacts-status role="status">Carregando contatos...</p> <!-- Explicação DevIN: Declara parágrafo. -->
                        <nav class="chat-contact-list" data-chat-contacts aria-label="Contatos"></nav> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                    </aside> <!-- Explicação DevIN: Fecha elemento aside do documento. -->
                    <section class="chat-conversation" aria-label="Conversa"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                        <header class="chat-conversation-header" data-chat-header> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
                            <button class="chat-back-to-contacts" type="button" data-chat-back aria-label="Voltar para contatos">←</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                            <div class="chat-header-contact"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <span class="chat-header-avatar" data-chat-avatar aria-hidden="true"></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                                <span class="chat-header-copy"> <!-- Explicação DevIN: Declara trecho em linha. -->
                                    <button class="chat-profile-trigger" type="button" data-chat-profile-open disabled data-chat-title aria-label="Ver perfil">Escolha alguém para conversar</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                                    <span data-chat-subtitle>Mensagens privadas entre usuários da DevIN</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                </span> <!-- Explicação DevIN: Fecha elemento span do documento. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            <details class="chat-conversation-menu"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <summary data-chat-menu-toggle aria-label="Opções da conversa" aria-expanded="false">⋮</summary> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
                                <div class="chat-conversation-menu-items"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                    <button class="chat-delete-conversation" type="button" data-chat-delete-conversation disabled>Excluir conversa</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
                        <div class="chat-messages" data-chat-messages role="log" aria-live="polite" aria-relevant="additions text"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                            <p class="chat-empty">Selecione um contato para iniciar ou continuar uma conversa.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form class="chat-composer" data-chat-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                            <label class="sr-only" for="chat-message">Sua mensagem</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <textarea id="chat-message" name="message" maxlength="2000" placeholder="Escreva uma mensagem..." required disabled data-chat-input></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
                            <button type="submit" disabled data-chat-send>Enviar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                        <p class="chat-status chat-send-status" data-chat-send-status role="status"></p> <!-- Explicação DevIN: Declara parágrafo. -->
                    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                <dialog class="chat-profile-dialog" data-chat-profile-dialog aria-labelledby="chatProfileName"> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
                    <div class="chat-profile-dialog__content"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <button class="chat-profile-dialog__close" type="button" data-chat-profile-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <div class="chat-profile-dialog__identity"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="chat-profile-dialog__avatar" data-chat-profile-avatar aria-hidden="true"></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                            <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <h2 id="chatProfileName" data-chat-profile-name></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                <p data-chat-profile-type></p> <!-- Explicação DevIN: Declara parágrafo. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <p class="chat-profile-dialog__privacy">Este perfil simplificado mostra apenas o nome e o tipo de conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                        <button class="chat-profile-dialog__report-button" type="button" data-chat-profile-report-open>Denunciar perfil</button> <!-- Explicação DevIN: Declara botão de ação. -->
                        <div class="dashboard-feedback-toast chat-profile-feedback" data-feedback-toast hidden role="status" aria-live="polite"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                            <p class="dashboard-toast__msg" data-chat-profile-status data-feedback-message></p> <!-- Explicação DevIN: Declara parágrafo. -->
                            <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <form class="chat-profile-report-form" data-chat-profile-report-form hidden> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                            <label for="chat-profile-report-reason">Motivo</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <select id="chat-profile-report-reason" required data-chat-profile-reason> <!-- Explicação DevIN: Declara seleção de opções. -->
                                <option value="">Selecione um motivo</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                <option value="assedio">Assédio ou comportamento abusivo</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                <option value="fraude">Fraude ou golpe</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                <option value="falsidade">Informações falsas</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                <option value="conteudo_inadequado">Conteúdo inadequado</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                <option value="outro">Outro</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                            <label for="chat-profile-report-details">Detalhes</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <textarea id="chat-profile-report-details" maxlength="1000" required data-chat-profile-details placeholder="Explique por que este perfil deve ser analisado."></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
                            <div class="chat-profile-report-form__actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <button type="button" data-chat-profile-report-cancel>Cancelar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <button type="submit" data-chat-profile-submit>Enviar denúncia</button> <!-- Explicação DevIN: Declara botão de ação. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </dialog> <!-- Explicação DevIN: Fecha elemento dialog do documento. -->
            <?php elseif ($pagina === 'curriculo'): ?>
                <section class="dashboard-resume"> <!-- Explicação DevIN: Declara seção temática. -->
                    <header class="dashboard-resume__header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
                        <div class="dashboard-resume__heading"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h1><?= $possuiCurriculo ? 'Seu currículo' : 'Adicione um currículo para se candidatar' ?></h1> <!-- Explicação DevIN: Declara título principal. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= $possuiCurriculo ? 'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.' : 'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.' ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

                    <?php if (!$possuiCurriculo): ?>
                        <section class="dashboard-resume__missing" aria-labelledby="resume-missing-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                            <span class="dashboard-resume__missing-icon"><?= dashboardIcon('file') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                            <h2 id="resume-missing-title">Ainda não há currículo nesta conta</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                            <p>Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                            <div class="dashboard-resume__options"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                <a class="dashboard-resume__option" href="curriculo_upload.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                                    <span class="dashboard-resume__action-icon"><?= dashboardIcon('upload') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                    <strong>Enviar arquivo pronto</strong> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                    <small>PDF ou Word que você já tem</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                                <a class="dashboard-resume__option" href="cadastrar_curriculo.php?ia=1"> <!-- Explicação DevIN: Declara link de navegação. -->
                                    <span class="dashboard-resume__action-icon"><?= dashboardIcon('sparkle') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                    <strong>Criar com ajuda da IA</strong> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                    <small>Preencha seus dados e a IA monta o texto</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            <ol class="dashboard-resume__steps"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <li><b>1</b><span>Adicione seu currículo</span></li> <!-- Explicação DevIN: Declara item da lista. A linha reúne múltiplos elementos. -->
                                <li><b>2</b><span>Empresas veem seu perfil completo</span></li> <!-- Explicação DevIN: Declara item da lista. A linha reúne múltiplos elementos. -->
                                <li><b>3</b><span>Candidate-se às vagas com um clique</span></li> <!-- Explicação DevIN: Declara item da lista. A linha reúne múltiplos elementos. -->
                            </ol> <!-- Explicação DevIN: Fecha elemento ol do documento. -->
                        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                    <?php else: ?>
                    <div class="dashboard-resume__content"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <div class="dashboard-resume__side"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <section class="dashboard-resume__card" aria-labelledby="resume-actions-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                                <h2 id="resume-actions-title" class="dashboard-resume__card-title">Gerenciar currículo</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                <div class="dashboard-resume__actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                    <?php if ($hasResumeText): ?>
                                        <button class="dashboard-resume__action" type="button" data-print-resume> <!-- Explicação DevIN: Declara botão de ação. -->
                                            <span class="dashboard-resume__action-icon"><?= dashboardIcon('printer') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                            <span>Baixar / Imprimir PDF</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                                        <a class="dashboard-resume__action" href="curriculo_exportar.php?formato=docx"> <!-- Explicação DevIN: Declara link de navegação. -->
                                            <span class="dashboard-resume__action-icon"><?= dashboardIcon('download') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                            <span>Baixar DOCX</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                                    <?php endif; ?>
                                    <?php if (!empty($resumeDocument['arquivo_caminho'])): ?>
                                        <a class="dashboard-resume__action" href="curriculo_baixar.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                                            <span class="dashboard-resume__action-icon"><?= dashboardIcon('download') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                            <span>Baixar arquivo original</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                                    <?php endif; ?>
                                    <a class="dashboard-resume__action" href="curriculo_upload.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                                        <span class="dashboard-resume__action-icon"><?= dashboardIcon('upload') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                        <span>Substituir currículo</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                    </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                                    <a class="dashboard-resume__action" href="cadastrar_curriculo.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                                        <span class="dashboard-resume__action-icon"><?= dashboardIcon('edit') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                        <span>Editar nome e dados</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                    </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

                            <section class="dashboard-resume__card dashboard-resume__card--ai dashboard-resume__generator" aria-labelledby="resume-generator-title" data-resume-csrf="<?= h(csrfToken()) ?>" data-resume-auto-generate="<?= $gerarCurriculoAutomaticamente ? 'true' : 'false' ?>"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
                                <div class="dashboard-resume__card-head"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                    <span class="dashboard-resume__generator-icon" aria-hidden="true"><?= dashboardIcon('sparkle') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
                                    <h2 id="resume-generator-title" class="dashboard-resume__card-title">Editar ou melhorar</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                <p class="dashboard-resume__lead">Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <label class="dashboard-resume__consent"> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                    <input type="checkbox" data-resume-consent> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    <span>Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                <?php if (!empty($curriculoAtual['id_curriculo'])): ?>
                                    <button class="dashboard-resume__button dashboard-resume__button--primary" type="button" data-generate-resume disabled>✦ Gerar com IA</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <?php endif; ?>
                                <p class="dashboard-resume__status" data-resume-status role="status" aria-live="polite">Seus dados só serão enviados quando você confirmar e solicitar a geração.</p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                                <p class="dashboard-resume__privacy">A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

                            <details class="dashboard-resume__card dashboard-resume__editor"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <summary>Editar conteúdo do currículo</summary> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <form method="post"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                    <input type="hidden" name="action" value="update_resume_content"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <?php foreach (['resumo' => 'Resumo profissional', 'formacao' => 'Formação', 'experiencia' => 'Experiência profissional', 'cursos' => 'Cursos e certificações', 'idiomas' => 'Idiomas'] as $field => $label): ?>
                                        <label><?= h($label) ?><textarea name="resume[<?= h($field) ?>]" maxlength="6000"><?= h((string) ($resumeContent[$field] ?? '')) ?></textarea></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                                    <?php endforeach; ?>
                                    <button class="dashboard-resume__button dashboard-resume__button--primary" type="submit">Salvar alterações</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                            </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->

                            <form class="dashboard-resume__danger" method="post" onsubmit="return confirm('Excluir seu currículo? Você precisará adicionar outro para se candidatar.');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                                <input type="hidden" name="action" value="delete_resume"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <button class="dashboard-resume__danger-button" type="submit"><?= dashboardIcon('trash') ?><span>Excluir currículo</span></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                        <article class="dashboard-resume__paper<?= $hasResumePreview ? '' : ' dashboard-resume__paper--empty' ?>" data-resume-output aria-label="Prévia do currículo" aria-live="polite"> <!-- Explicação DevIN: Declara conteúdo independente. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
                            <div class="dashboard-resume__empty-state" data-resume-empty <?= $hasResumePreview ? 'hidden' : '' ?>> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
                                <span aria-hidden="true">▤</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                                <h2>Seu currículo aparecerá aqui</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                <p>Confirme o envio dos dados e clique em <strong>Gerar currículo com IA</strong>.</p> <!-- Explicação DevIN: Declara parágrafo. A linha reúne múltiplos elementos. -->
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                            <div data-resume-content <?= $hasResumePreview ? '' : 'hidden' ?>> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
                                <h2><?= h($resumeContent['nome']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                                <?php foreach (['resumo' => 'Resumo profissional', 'formacao' => 'Formação', 'experiencia' => 'Experiência profissional', 'cursos' => 'Cursos e certificações', 'idiomas' => 'Idiomas'] as $field => $label): ?>
                                    <?php if (trim((string) ($resumeContent[$field] ?? '')) !== ''): ?>
                                        <section class="dashboard-resume__section"> <!-- Explicação DevIN: Declara seção temática. -->
                                            <h3><?= h($label) ?></h3> <!-- Explicação DevIN: Declara subtítulo de terceiro nível. Contém dado dinâmico processado pelo PHP. -->
                                            <p><?= h((string) $resumeContent[$field]) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                                        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!empty($resumeDocument['arquivo_nome'])): ?>
                                    <section class="dashboard-resume__section dashboard-resume__file"> <!-- Explicação DevIN: Declara seção temática. -->
                                        <div class="dashboard-resume__file-bar"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                            <span class="dashboard-resume__file-icon"><?= dashboardIcon('file') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                            <div class="dashboard-resume__file-info"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                                <small>Arquivo enviado</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                                <strong><?= h((string) $resumeDocument['arquivo_nome']) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                            <a href="curriculo_baixar.php">Abrir ou baixar arquivo original</a> <!-- Explicação DevIN: Declara link de navegação. -->
                                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                        <iframe class="dashboard-resume__uploaded-preview" src="curriculo_visualizar.php" title="Prévia do currículo enviado" loading="lazy"></iframe> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                                <?php endif; ?>
                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <?php endif; ?>
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            <?php elseif ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
                <!-- Acesso flutuante rápido na seção Sobre nós; destino exige login. -->
                <a class="support-floating-help" href="pessoa.php?pagina=suporte" aria-label="Pedir ajuda ao Suporte DevIN"> <!-- Explicação DevIN: Declara link de navegação. Inclui atributos de acessibilidade. -->
                    <?= dashboardIcon('lifebuoy') ?><span>Ajuda</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card"> <!-- Explicação DevIN: Declara seção temática. -->
                    <a class="fechar-card" href="pessoa.php?pagina=inicio">×</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <div class="perfil-topo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <?= profileAvatar($perfilAtual, 'avatar-grande') ?>
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <strong><?= h($nome) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                            <small><?= h($email) ?></small> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <button class="btn primary" type="button" data-open-profile>Editar perfil</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    <!-- Ferramentas de conta compartilhadas e autorizadas por tipo. -->
                    <?php require __DIR__ . '/components/profile-tools.php'; ?>
 <!-- Explicação DevIN: Declara botão de ação. -->
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            <?php elseif ($pagina === 'vagas'): ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?> <a href="pessoa.php?pagina=curriculo">Abrir Meu currículo</a></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if (!$minhasCandidaturas): ?>
                    <p class="empty-state">Você ainda não se candidatou a nenhuma vaga.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php endif; ?>
                <?php foreach ($minhasCandidaturas as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Candidatura para ' . $vaga['empresa']) ?>" data-detail-tags="Candidatura|<?= h(ucfirst($vaga['status'])) ?>|<?= h($vaga['empresa']) ?>" data-detail-experience="<?= h('Candidatura::Status ' . ucfirst($vaga['status'])) ?>"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($vaga['empresa']) ?> · <?= h($vaga['titulo']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <?= dashboardCardTags('Candidatura|' . ucfirst($vaga['status']) . '|' . $vaga['empresa']) ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <?php $applicationId = (int)$vaga['id_candidatura']; ?>
                        <?php $employment = $employmentConfirmations[$applicationId] ?? null; ?>
                        <?php if (isset($applicationWithdrawals[$applicationId])): ?>
                            <span class="status">Candidatura retirada após contratação</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        <?php elseif (!empty($employment['candidate_confirmed_at'])): ?>
                            <span class="status aprovado">Contratação confirmada</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        <?php else: ?>
                            <span class="status <?= $vaga['status'] === 'aprovado' ? 'aprovado' : ($vaga['status'] === 'recusado' ? 'reprovado' : 'analise') ?>"><?= h(ucfirst($vaga['status'])) ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <?php endif; ?>
                        <?php if ($employmentReady && $vaga['status'] === 'aprovado' // Explicação DevIN: Abre código PHP interpretado no servidor.
                            && !empty($employment['employer_confirmed_at']) && empty($employment['candidate_confirmed_at'])): ?>
                            <form method="post" onsubmit="return confirm('Confirmar que você foi contratado(a)? Suas outras candidaturas pendentes só serão encerradas se selecionar a opção abaixo.');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                                <input type="hidden" name="action" value="confirm_hiring"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="id_candidatura" value="<?= $applicationId ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <label><input type="checkbox" name="close_others" value="1"> Encerrar minhas outras candidaturas pendentes (opcional)</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                                <button class="btn success" type="submit">Confirmar minha contratação</button> <!-- Explicação DevIN: Declara botão de ação. -->
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                        <?php endif; ?>
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if (!$vagasDisponiveis): ?>
                    <p class="empty-state">Ainda não há vagas publicadas.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php endif; ?>
                <?php foreach ($vagasDisponiveis as $vaga): ?>
                    <?php $jaSeCandidatou = isset($vagasCandidatadas[(int) $vaga['id_vaga']]); ?>
                    <?php $vagaEncerrada = !empty($vaga['encerrada_em']); ?>
                    <?php $statusVaga = $vagaEncerrada ? 'Vaga encerrada' : ($jaSeCandidatou ? 'Candidatura enviada' : 'Vaga disponível'); ?>
                    <?php $salarioExibido = (float) $vaga['salario_mensal'] > 0 ? 'R$ ' . number_format((float) $vaga['salario_mensal'], 2, ',', '.') : 'A combinar'; ?>
                    <?php $limiteExibido = (int) $vaga['limite_aprovados'] > 0 ? (string) $vaga['limite_aprovados'] : 'Sem limite'; ?>
                    <?php $prazoVagaExibido = !empty($vaga['data_encerramento']) ? date('d/m/Y H:i', strtotime($vaga['data_encerramento'])) : ''; ?>
                    <?php $vagasRestantes = (int) $vaga['limite_aprovados'] > 0 ? (string) max((int) $vaga['limite_aprovados'] - (int) $vaga['candidaturas_aprovadas'], 0) : 'Sem limite'; ?>
                    <?php $detalheExperienciaItens = [ // Explicação DevIN: Abre código PHP interpretado no servidor.
                        $vagaEncerrada ? 'Vaga encerrada' : ($jaSeCandidatou ? 'Candidatura enviada::Você já se candidatou a esta vaga' : 'Vaga publicada::Confira os requisitos e envie sua candidatura'), // Explicação DevIN: Continua expressão ou bloco pertencente a marcação p.
                        'Salário mensal::' . $salarioExibido, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'Limite de vagas::' . $limiteExibido, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'Aprovadas::' . (int) $vaga['candidaturas_aprovadas'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'Recusadas::' . (int) $vaga['candidaturas_recusadas'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'Em análise::' . (int) $vaga['candidaturas_em_analise'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'Vagas restantes::' . $vagasRestantes, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ]; ?>
                    <?php if ($prazoVagaExibido !== ''): ?>
                        <?php $detalheExperienciaItens[] = 'Data de encerramento::' . $prazoVagaExibido; ?>
                    <?php endif; ?>
                    <?php $detalheExperiencia = implode('|', $detalheExperienciaItens); ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h($vaga['empresa']) ?>" data-detail-tags="<?= h($statusVaga) ?>|<?= h($vaga['empresa']) ?>" data-detail-experience="<?= h($detalheExperiencia) ?>" data-application-summary="true" data-report-job-id="<?= (int) $vaga['id_vaga'] ?>" data-detail-action-label="Candidatar-se" data-chat-type="empresa" data-chat-id="<?= (int) $vaga['id_empresa'] ?>"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($vaga['empresa']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <p class="job-compensation"><span>Salário mensal:</span> <strong><?= h($salarioExibido) ?></strong> · <span>Limite de vagas:</span> <strong><?= h($limiteExibido) ?></strong> · <span>Vagas restantes:</span> <strong><?= h($vagasRestantes) ?></strong></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            <?php if ($prazoVagaExibido !== ''): ?><p class="job-deadline">Candidaturas até: <?= h($prazoVagaExibido) ?></p><?php endif; ?>
                            <?= dashboardCardTags($statusVaga . '|' . $vaga['empresa']) ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="card-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <form method="post"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                <input type="hidden" name="action" value="apply_job"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="id_vaga" value="<?= (int) $vaga['id_vaga'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <?php if ($vagaEncerrada): ?>
                                    <button class="btn applied" type="button" disabled>Vaga encerrada</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <?php elseif ($jaSeCandidatou): ?>
                                    <button class="btn applied" type="button" disabled>Candidatou-se</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <?php elseif (!$possuiCurriculo): ?>
                                    <button class="btn applied" type="button" disabled>Adicione um currículo</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <?php else: ?>
                                    <button class="btn primary" type="submit" data-detail-action-target>Candidatar-se</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                <?php endif; ?>
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                            <button class="btn primary" type="button" data-start-chat data-chat-type="empresa" data-chat-id="<?= (int) $vaga['id_empresa'] ?>">Conversar</button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($pagina === 'sobre'): ?>
                <?php require __DIR__ . '/components/dashboard-footer.php'; ?>
            <?php endif; ?>
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

        <?php if (!in_array($pagina, ['sobre', 'perfil', 'mensagens', 'curriculo', 'suporte'], true)): ?>
            <?= dashboardDetailPanel() ?>
        <?php elseif ($pagina !== 'mensagens' && $pagina !== 'suporte'): ?>
            <aside class="detalhe-area"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
                <?php if ($pagina === 'sobre'): ?>
                    <h2>Contato</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                    <p>Fale com a equipe DevIN para conhecer melhor o projeto.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php elseif ($pagina === 'perfil'): ?>
                    <h2 id="detailTitle">Detalhes da vaga</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                    <p>Use este espaço para visualizar detalhes da vaga.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                    <button class="btn primary fixed-action" type="button">Candidatar-se</button> <!-- Explicação DevIN: Declara botão de ação. -->
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
                <label class="profile-photo" aria-label="Alterar foto de perfil"> <!-- Explicação DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. -->
                    <?= profileAvatar($perfilAtual, 'profile-photo-preview') ?>
                    <span class="photo-edit" aria-hidden="true">✎</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                    <input name="foto" type="file" accept="image/png,image/jpeg,image/webp"> <!-- Explicação DevIN: Declara campo de entrada. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <strong><?= h($perfilAtual['nome']) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                    <small><?= h($perfilAtual['email']) ?></small> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <div class="profile-fields"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <label>Nome<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>E-mail da conta<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep'] ?? '') ?>" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <button class="profile-save" type="submit">Salvar</button> <!-- Explicação DevIN: Declara botão de ação. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
    </dialog> <!-- Explicação DevIN: Fecha elemento dialog do documento. -->

    <!-- Modal Configurações -->
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
                <small>Escolha o idioma usado na sua experiência na DevIN.</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
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

    <script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script src="../js/dashboard-menu.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/dashboard.js?v=<?= filemtime(__DIR__ . '/../js/dashboard.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/konami-arcade.js?v=<?= filemtime(__DIR__ . '/../js/konami-arcade.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/mobile-arcade-secret.js?v=<?= filemtime(__DIR__ . '/../js/mobile-arcade-secret.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <?php if ($pagina === 'mensagens'): ?>
        <script src="../js/chat.js?v=<?= filemtime(__DIR__ . '/../js/chat.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
    <?php if ($pagina === 'curriculo'): ?>
        <script src="../js/curriculo.js?v=<?= filemtime(__DIR__ . '/../js/curriculo.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
    <?php if ($pagina === 'suporte'): ?>
        <script src="../js/support-ui.js?v=<?= filemtime(__DIR__ . '/../js/support-ui.js') ?>" defer></script> <!-- Inicializa o suporte sem sair do dashboard. -->
    <?php endif; ?>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->

</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
