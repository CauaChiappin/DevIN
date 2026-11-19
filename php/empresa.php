<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/empresa.php
 * Finalidade: empresa.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

require_once __DIR__ . '/controllers/ProfileController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/EmploymentWorkflow.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/JobSchema.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/helpers.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuarioAtual = requireWebAuth('empresa'); // Explicação DevIN: Atribui um valor à variável PHP $usuarioAtual.

$tipo   = 'empresa'; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
$nome   = $_SESSION['usuario_nome']  ?? 'Empresa'; // Explicação DevIN: Atribui um valor à variável PHP $nome.
$email  = $_SESSION['usuario_email'] ?? 'empresa@devin.com'; // Explicação DevIN: Atribui um valor à variável PHP $email.
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemSenhaAlterada.
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailSenha.
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCadastro.
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailCadastro.
unset($_SESSION['sucesso_login'], $_SESSION['erro_email_senha'], $_SESSION['sucesso_cadastro'], $_SESSION['erro_email_cadastro']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$pagina = requestString($_GET, 'pagina'); // Explicação DevIN: Atribui um valor à variável PHP $pagina.

$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil', 'mensagens', 'suporte']; // Explicação DevIN: Atribui um valor à variável PHP $paginasPermitidas.
if (!in_array($pagina, $paginasPermitidas, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Limita a navegação a seções implementadas no painel da empresa.
    $pagina = 'inicio'; // Explicação DevIN: Atribui um valor à variável PHP $pagina.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| PROCESSAMENTO DE AÇÕES (POST) // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Toda ação que altera vaga, candidatura ou perfil exige token CSRF válido.
    requireValidCsrf(false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    $action = requestString($_POST, 'action'); // Explicação DevIN: Atribui um valor à variável PHP $action.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Empresa só pode confirmar contratação depois de aprovar a candidatura.
        if ($action === 'confirm_employment') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                EmploymentWorkflow::employerConfirm($db, (int)$usuarioAtual['id'], (int)requestString($_POST,'id_candidatura')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $_SESSION['candidate_success'] = 'Contratação registrada. Aguardando confirmação da pessoa.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=candidatos'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_application_status') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $idCandidatura = (int) requestString($_POST, 'id_candidatura'); // Explicação DevIN: Atribui um valor à variável PHP $idCandidatura.
            $status        = requestString($_POST, 'status'); // Explicação DevIN: Atribui um valor à variável PHP $status.

            if ($idCandidatura <= 0 || !in_array($status, ['aprovado', 'recusado'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Dados da candidatura inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $conn->begin_transaction(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                /* Obtém primeiro a vaga autorizada para serializar aprovações pelo limite. */
                $empresaId = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $empresaId.
                $stmt = $conn->prepare('
                    SELECT c.id_vaga
                    FROM candidatura c
                    INNER JOIN vagas v ON v.id_vaga = c.id_vaga
                    WHERE c.id_candidatura = ? AND v.id_empresa = ?
                '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                $stmt->bind_param('ii', $idCandidatura, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $candidaturaVaga = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                if (!$candidaturaVaga) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Candidatura não encontrada ou sem permissão para alterá-la.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $vagaId = (int) $candidaturaVaga['id_vaga']; // Explicação DevIN: Atribui um valor à variável PHP $vagaId.
                $stmt = $conn->prepare('SELECT limite_aprovados FROM vagas WHERE id_vaga = ? AND id_empresa = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('ii', $vagaId, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $vaga = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (!$vaga) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para alterá-la.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $stmt = $conn->prepare('SELECT status FROM candidatura WHERE id_candidatura = ? AND id_vaga = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('ii', $idCandidatura, $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $candidatura = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (!$candidatura) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Candidatura não encontrada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                if ($status === 'aprovado' && $candidatura['status'] !== 'aprovado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $stmt = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_vaga = ? AND status = "aprovado" FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $stmt->bind_param('i', $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $aprovados = $stmt->get_result()->num_rows; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                    $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                    $limiteAprovados = (int) $vaga['limite_aprovados']; // Explicação DevIN: Atribui um valor à variável PHP $limiteAprovados.
                    if ($limiteAprovados > 0 && $aprovados >= $limiteAprovados) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        throw new RuntimeException('O limite de vagas desta oportunidade já foi atingido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $stmt = $conn->prepare('UPDATE candidatura SET status = ? WHERE id_candidatura = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('si', $status, $idCandidatura); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                if ($stmt->affected_rows !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível atualizar a candidatura.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                atualizarVagasEncerradas($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $conn->commit(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                $conn->rollback(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['candidate_success'] = 'Candidatura ' . ($status === 'aprovado' ? 'aprovada' : 'recusada') . ' com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=candidatos'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'create_job') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $titulo    = trim(requestString($_POST, 'titulo')); // Explicação DevIN: Atribui um valor à variável PHP $titulo.
            $descricao = trim(requestString($_POST, 'descricao')); // Explicação DevIN: Atribui um valor à variável PHP $descricao.
            $salarioMensal = validarSalarioMensal(requestString($_POST, 'salario_mensal')); // Explicação DevIN: Atribui um valor à variável PHP $salarioMensal.
            $limiteAprovados = validarLimiteAprovados(requestString($_POST, 'limite_aprovados'), true); // Explicação DevIN: Atribui um valor à variável PHP $limiteAprovados.
            $criterioEncerramento = requestString($_POST, 'criterio_encerramento') ?: 'nenhum'; // Explicação DevIN: Atribui um valor à variável PHP $criterioEncerramento.
            $dataEncerramento = validarConfiguracaoEncerramento( // Explicação DevIN: Atribui um valor à variável PHP $dataEncerramento.
                $criterioEncerramento, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                requestString($_POST, 'data_encerramento'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $limiteAprovados // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($titulo === '' || $descricao === '' || mb_strlen($titulo) > 50 || mb_strlen($descricao) > 1000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Informe título (até 50 caracteres) e descrição (até 1000 caracteres).'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                // O ID da empresa vem da sessão autenticada, não de um campo do formulário.
                $stmt = $conn->prepare('INSERT INTO vagas (titulo, descricao, salario_mensal, limite_aprovados, criterio_encerramento, data_encerramento, tempo_vaga, id_empresa) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível criar a vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $empresaId = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $empresaId.
                $stmt->bind_param('sssissi', $titulo, $descricao, $salarioMensal, $limiteAprovados, $criterioEncerramento, $dataEncerramento, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['job_success'] = 'Vaga publicada com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_job') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $vagaId    = (int) requestString($_POST, 'id_vaga'); // Explicação DevIN: Atribui um valor à variável PHP $vagaId.
            $titulo    = trim(requestString($_POST, 'titulo')); // Explicação DevIN: Atribui um valor à variável PHP $titulo.
            $descricao = trim(requestString($_POST, 'descricao')); // Explicação DevIN: Atribui um valor à variável PHP $descricao.
            $salarioMensal = validarSalarioMensal(requestString($_POST, 'salario_mensal')); // Explicação DevIN: Atribui um valor à variável PHP $salarioMensal.
            $limiteAprovados = validarLimiteAprovados(requestString($_POST, 'limite_aprovados'), true); // Explicação DevIN: Atribui um valor à variável PHP $limiteAprovados.
            $criterioEncerramento = requestString($_POST, 'criterio_encerramento') ?: 'nenhum'; // Explicação DevIN: Atribui um valor à variável PHP $criterioEncerramento.
            $dataEncerramento = validarConfiguracaoEncerramento( // Explicação DevIN: Atribui um valor à variável PHP $dataEncerramento.
                $criterioEncerramento, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                requestString($_POST, 'data_encerramento'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $limiteAprovados // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($vagaId <= 0 || $titulo === '' || $descricao === '' || mb_strlen($titulo) > 50 || mb_strlen($descricao) > 1000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Dados da vaga inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $conn->begin_transaction(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $stmt = $conn->prepare('SELECT id_vaga FROM vagas WHERE id_vaga = ? AND id_empresa = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $empresaId = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $empresaId.
                $stmt->bind_param('ii', $vagaId, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $vagaEncontrada = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (!$vagaEncontrada) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para editá-la.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM candidatura WHERE id_vaga = ? AND status = "aprovado"'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $vagaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $aprovados = (int) $stmt->get_result()->fetch_assoc()['total']; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if ($limiteAprovados > 0 && $limiteAprovados < $aprovados) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('O limite não pode ser menor que a quantidade de candidatos já aprovados (' . $aprovados . ').'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                // Inclui o proprietário no WHERE para impedir edição de vaga de outra empresa.
                $stmt = $conn->prepare('UPDATE vagas
                    SET titulo = ?, descricao = ?, salario_mensal = ?, limite_aprovados = ?,
                        criterio_encerramento = ?, data_encerramento = ?, encerrada_em = NULL
                    WHERE id_vaga = ? AND id_empresa = ?'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível editar a vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $stmt->bind_param('sssissii', $titulo, $descricao, $salarioMensal, $limiteAprovados, $criterioEncerramento, $dataEncerramento, $vagaId, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                atualizarVagasEncerradas($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $conn->commit(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                $conn->rollback(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['job_success'] = 'Vaga atualizada com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'delete_job') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $vagaId = (int) requestString($_POST, 'id_vaga'); // Explicação DevIN: Atribui um valor à variável PHP $vagaId.
            if ($vagaId <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Vaga inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                // A condição de proprietário impede que uma empresa exclua vagas de outra.
                $stmt = $conn->prepare('DELETE FROM vagas WHERE id_vaga = ? AND id_empresa = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível excluir a vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $empresaId = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $empresaId.
                $stmt->bind_param('ii', $vagaId, $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                if ($stmt->affected_rows !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para excluí-la.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['job_success'] = 'Vaga excluída com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Operações de perfil reutilizam validações comuns e mantêm os dados da sessão atualizados.
        if ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $savedProfile = updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null); // Explicação DevIN: Atribui um valor à variável PHP $savedProfile.
            $_SESSION['usuario_nome'] = $savedProfile['nome']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_email'] = $savedProfile['email']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['profile_success'] = !empty($savedProfile['email_alterado']) // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'E-mail alterado com sucesso. No próximo acesso, entre com o novo endereço.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Perfil atualizado com sucesso.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&perfil=meu'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'delete_account') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            AuditService::tryRecord('account_deletion_requested', 'success', $tipo, (int) $_SESSION['usuario_id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            deleteProfile($tipo, (int) $_SESSION['usuario_id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            AuditService::tryRecord('account_deleted', 'success', $tipo, (int) $_SESSION['usuario_id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            header('Location: logout.php?destino=index'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro no dashboard empresa: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if ($action === 'update_profile') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['profile_error'] = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível atualizar o perfil. Seus dados foram mantidos.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&perfil=meu'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($action === 'update_settings') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (in_array($action, ['update_application_status','confirm_employment'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['candidate_error'] = $exception instanceof mysqli_sql_exception // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'Não foi possível concluir a ação. Tente novamente.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : $exception->getMessage(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: empresa.php?pagina=candidatos'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (in_array($action, ['create_job', 'update_job', 'delete_job'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['job_error'] = $exception->getMessage(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: empresa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $_SESSION['profile_error'] = $exception->getMessage(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        header('Location: empresa.php?pagina=perfil'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| CARREGAMENTO DE DADOS E VAGAS // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

$empresaId   = (int) $_SESSION['usuario_id']; // Explicação DevIN: Atribui um valor à variável PHP $empresaId.
$perfilAtual = findProfile($tipo, $empresaId); // Explicação DevIN: Atribui um valor à variável PHP $perfilAtual.

if (!$perfilAtual) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: logout.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$idiomaAtual = getProfileLanguage($tipo, $empresaId); // Explicação DevIN: Atribui um valor à variável PHP $idiomaAtual.

$empresaPosts = []; // Explicação DevIN: Atribui um valor à variável PHP $empresaPosts.
$candidatos   = []; // Explicação DevIN: Atribui um valor à variável PHP $candidatos.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        ensureJobCompensationSchema($conn); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        // 1. Busca Vagas Criadas pela Empresa
        // Carrega somente vagas da empresa autenticada para a área de gerenciamento.
        $stmtVagas = $conn->prepare("
            SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao,
                   v.salario_mensal, v.limite_aprovados, v.tempo_vaga,
                   v.criterio_encerramento, v.data_encerramento, v.encerrada_em,
                   COALESCE(contagens.aprovadas, 0) AS candidaturas_aprovadas
            FROM vagas v
            LEFT JOIN (
                SELECT id_vaga, COUNT(*) AS aprovadas
                FROM candidatura
                WHERE status = 'aprovado'
                GROUP BY id_vaga
            ) contagens ON contagens.id_vaga = v.id_vaga
            WHERE v.id_empresa = ?
            ORDER BY v.id_vaga DESC
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        if ($stmtVagas) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmtVagas->bind_param('i', $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtVagas->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $empresaPosts = $stmtVagas->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmtVagas->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // 2. Busca Candidatos que se aplicaram às Vagas
        $sqlCandidatos = '
            SELECT
                c.id_candidatura,
                p.id_pessoa,
                v.id_vaga,
                c.status,
                c.data_candidatura,
                p.nome,
                p.email,
                v.titulo AS vaga,
                COALESCE(cu.nome_social, "") AS nome_social,
                COALESCE(cu.grau_de_escolaridade, "") AS grau_de_escolaridade,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia,
                COALESCE(cu.idiomas, "") AS idiomas
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN pessoa p ON p.id_pessoa = c.id_pessoa
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            WHERE v.id_empresa = ?
            ORDER BY c.data_candidatura DESC, c.id_candidatura DESC
        '; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        // Monta candidatos relacionados às vagas da empresa usando consultas preparadas.
        $stmtCandidatos = $conn->prepare($sqlCandidatos); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        if ($stmtCandidatos) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmtCandidatos->bind_param('i', $empresaId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtCandidatos->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $candidatos = $stmtCandidatos->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmtCandidatos->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao carregar dados do dashboard empresa: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

foreach ($empresaPosts as &$post) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $post['resumo']  = 'Publicada em ' . date('d/m/Y', strtotime($post['tempo_vaga'])); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    $post['detalhe'] = $post['descricao']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
unset($post); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

foreach ($candidatos as &$candidato) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $candidato['resumo']  = 'Vaga: ' . $candidato['vaga'] . ' | Status: ' . ucfirst($candidato['status']); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    $candidato['detalhe'] = 'E-mail: ' . $candidato['email'] // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        . '. Cursos: ' . ($candidato['cursos'] !== '' ? $candidato['cursos'] : 'não informado') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '. Experiência: ' . ($candidato['experiencia'] !== '' ? $candidato['experiencia'] : 'não informada'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
unset($candidato); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

$candidatosPorVaga = []; // Explicação DevIN: Atribui um valor à variável PHP $candidatosPorVaga.
foreach ($candidatos as $candidato) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $candidatosPorVaga[(int) $candidato['id_vaga']][] = $candidato; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= h($idiomaAtual) ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Dashboard Empresa</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php if ($pagina === 'mensagens'): ?>
        <link rel="stylesheet" href="../css/chat.css?v=<?= filemtime(__DIR__ . '/../css/chat.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
    <?php if (in_array($pagina, ['sobre', 'suporte'], true)): ?>
    <link rel="stylesheet" href="../css/support.css?v=<?= filemtime(__DIR__ . '/../css/support.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
    <link rel="stylesheet" href="../css/ui-consistency.css?v=<?= filemtime(__DIR__ . '/../css/ui-consistency.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="dashboard-shell empresa-dashboard page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>"> <!-- Explicação DevIN: Declara conteúdo principal da página. Contém dado dinâmico processado pelo PHP. -->
        <?php if ($mensagemSenhaAlterada !== '' || $mensagemCadastro !== '' || $mensagemEmailSenha !== '' || $mensagemEmailCadastro !== ''): ?>
            <div class="dashboard-toast" data-auto-dismiss-toast role="status" aria-live="polite"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                <?php if ($mensagemSenhaAlterada !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemSenhaAlterada) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCadastro) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemEmailSenha !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error"><?= h($mensagemEmailSenha) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if ($mensagemEmailCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error"><?= h($mensagemEmailCadastro) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>
        
        <aside class="sidebar"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
            <div class="sidebar-topo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a class="brand" href="empresa.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                    <span class="brand-text">Dev<span>IN</span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                    <span></span><span></span><span></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <nav class="menu-principal" aria-label="Menu principal"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                <a class="<?= ativo($pagina, 'inicio') ?>" href="empresa.php?pagina=inicio"><?= dashboardIcon('home') ?><span class="menu-text">Início</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="empresa.php?pagina=candidatos"><?= dashboardIcon('user') ?><span class="menu-text">Candidatos</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'mensagens') ?>" href="empresa.php?pagina=mensagens" data-messages-api="../php/mensagens_api.php"><?= dashboardIcon('users') ?><span class="menu-text">Mensagens</span><span class="message-unread-badge" data-message-unread-badge hidden></span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <!-- A boia identifica o suporte, diferente do ícone de denúncias. -->
                <a class="<?= ativo($pagina, 'suporte') ?>" href="empresa.php?pagina=suporte" aria-label="Abrir central de suporte do DevIN"><?= dashboardIcon('lifebuoy') ?><span class="menu-text">Suporte</span></a> <!-- Explicação DevIN: Declara link de navegação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <a class="<?= ativo($pagina, 'sobre') ?>" href="empresa.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->

            <div class="conta"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <details class="perfil-dropdown"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                    <div class="perfil-menu"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configurações</span></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                <a class="sair" href="logout.php"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </aside> <!-- Explicação DevIN: Fecha elemento aside do documento. -->

        <section class="lista-area"> <!-- Explicação DevIN: Declara seção temática. -->
            <?php if (!in_array($pagina, ['sobre', 'mensagens', 'suporte'], true)): ?>
                <?= dashboardListHeader( // Explicação DevIN: Combina execução de código com conteúdo HTML gerado dinamicamente.
                    $pagina, // Explicação DevIN: Continua expressão ou bloco pertencente a marcação section.
                    $pagina === 'candidatos' ? 'Candidatos' : ($pagina === 'perfil' ? 'Perfil' : 'Vagas publicadas'), // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                    $pagina === 'candidatos' ? 'Pesquisar por nome ou e-mail...' : 'Pesquisar por nome ou habilidade...', // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                    $pagina === 'candidatos' ? count($candidatos) . ' candidatos' : ($pagina === 'inicio' ? count($empresaPosts) . ' vagas' : ''), // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                    $pagina === 'candidatos' // Explicação DevIN: Atribui um valor à variável PHP $pagina.
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'candidatos'], true)): ?>
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
            <?php elseif ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
                <!-- Acesso flutuante rápido na seção Sobre nós; destino exige login. -->
                <a class="support-floating-help" href="empresa.php?pagina=suporte" aria-label="Pedir ajuda ao Suporte DevIN"> <!-- Explicação DevIN: Declara link de navegação. Inclui atributos de acessibilidade. -->
                    <?= dashboardIcon('lifebuoy') ?><span>Ajuda</span>
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->

            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card"> <!-- Explicação DevIN: Declara seção temática. -->
                    <a class="fechar-card" href="empresa.php?pagina=inicio">×</a> <!-- Explicação DevIN: Declara link de navegação. -->
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

            <?php elseif ($pagina === 'candidatos'): ?>
                <?php if (!empty($_SESSION['candidate_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['candidate_error']); unset($_SESSION['candidate_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if (!empty($_SESSION['candidate_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['candidate_success']); unset($_SESSION['candidate_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Você ainda não publicou nenhuma vaga.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php endif; ?>
                <?php foreach ($empresaPosts as $post): ?>
                    <?php $candidatosDaVaga = $candidatosPorVaga[(int) $post['id_vaga']] ?? []; ?>
                    <?php // Explicação DevIN: Abre código PHP interpretado no servidor.
                    $escolaridades = array_values(array_unique(array_filter(array_column($candidatosDaVaga, 'grau_de_escolaridade')))); // Explicação DevIN: Atribui um valor à variável PHP $escolaridades.
                    sort($escolaridades, SORT_NATURAL | SORT_FLAG_CASE); // Explicação DevIN: Continua expressão ou bloco pertencente a marcação p.
                    ?>
                    <details class="candidate-vacancy-panel"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                        <summary> <!-- Explicação DevIN: Declara elemento de marcação. -->
                            <span class="candidate-vacancy-info"> <!-- Explicação DevIN: Declara trecho em linha. -->
                                <strong><?= h($post['titulo']) ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                                <small><?= h($post['resumo']) ?></small> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                            </span> <!-- Explicação DevIN: Fecha elemento span do documento. -->
                            <span class="candidate-vacancy-count"> <!-- Explicação DevIN: Declara trecho em linha. -->
                                <?= count($candidatosDaVaga) ?> <?= count($candidatosDaVaga) === 1 ? 'candidato' : 'candidatos' ?>
                            </span> <!-- Explicação DevIN: Fecha elemento span do documento. -->
                        </summary> <!-- Explicação DevIN: Fecha elemento summary do documento. -->
                        <div class="candidate-vacancy-list"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <?php if ($candidatosDaVaga): ?>
                                <div class="candidate-filters"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                    <label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <span>Pesquisar por nome ou e-mail</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        <input type="search" data-candidate-search placeholder="Nome ou e-mail"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <span>Escolaridade</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        <select data-candidate-education> <!-- Explicação DevIN: Declara seleção de opções. -->
                                            <option value="">Todos os níveis</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                            <?php foreach ($escolaridades as $escolaridade): ?>
                                                <option value="<?= h($escolaridade) ?>"><?= h($escolaridade) ?></option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                                            <?php endforeach; ?>
                                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <span>Cursos</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        <select data-candidate-course aria-label="Filtrar por curso"> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. -->
                                            <option value="">Todos os cursos</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <span>Habilidades</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        <select data-candidate-skill aria-label="Filtrar por habilidade"> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. -->
                                            <option value="">Todas as habilidades</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <span>Experiência</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                                        <select data-candidate-experience aria-label="Filtrar por experiência"> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. -->
                                            <option value="">Todas as experiências</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                <p class="candidate-filter-empty" data-candidate-filter-empty role="status" hidden>Nenhum candidato corresponde aos filtros.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                            <?php endif; ?>
                            <?php if (!$candidatosDaVaga): ?>
                                <p class="empty-state">Ainda não há candidatos para esta vaga.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                            <?php endif; ?>
                            <?php foreach ($candidatosDaVaga as $candidato): ?>
                                <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>" data-detail-role="<?= h('Candidato para ' . $candidato['vaga']) ?>" data-detail-tags="Candidatura|<?= h(ucfirst($candidato['status'])) ?>" data-detail-experience="<?= h('Candidatura recebida::' . date('d/m/Y', strtotime($candidato['data_candidatura']))) ?>" data-candidate-name-email="<?= h($candidato['nome'] . ' ' . $candidato['email'] . ' ' . $candidato['nome_social']) ?>" data-candidate-education="<?= h($candidato['grau_de_escolaridade']) ?>" data-candidate-courses="<?= h($candidato['cursos']) ?>" data-candidate-skills="<?= h(implode('|', array_filter([$candidato['cursos'], $candidato['idiomas']]))) ?>" data-candidate-experiences="<?= h($candidato['experiencia']) ?>" data-candidate-curriculum="<?= h(implode(' ', [$candidato['grau_de_escolaridade'], $candidato['cursos'], $candidato['experiencia'], $candidato['idiomas']])) ?>" data-detail-action-label="Aprovar candidato" data-chat-type="pessoa" data-chat-id="<?= (int) $candidato['id_pessoa'] ?>"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                                    <span class="card-avatar"><?= dashboardIcon('user') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                    <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                        <h2><?= h($candidato['nome']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                                        <p><?= h($candidato['resumo']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                                        <?= dashboardCardTags('Candidatura|' . ucfirst($candidato['status'])) ?>
                                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                    <div class="acoes-card"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                        <button class="btn primary" type="button" data-start-chat data-chat-type="pessoa" data-chat-id="<?= (int) $candidato['id_pessoa'] ?>">Conversar</button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. -->
                                        <?php if ($candidato['status'] === 'pendente'): ?>
                                            <div class="candidate-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                                                <form method="post"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                                    <input type="hidden" name="action" value="update_application_status"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <input type="hidden" name="status" value="recusado"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                    <button class="btn danger" type="submit">Não se encaixa</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                                                <form method="post"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                                    <input type="hidden" name="action" value="update_application_status"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <input type="hidden" name="status" value="aprovado"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                    <button class="btn success" type="submit" data-detail-action-target>Aprovar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                                            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                        <?php else: ?>
                                            <?php if ($candidato['status'] === 'aprovado'): ?>
                                                <!-- Empresa registra contratação real; candidato confirma depois. -->
                                                <form method="post" onsubmit="return confirm('A contratação já ocorreu? A pessoa precisará confirmar também.');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <input type="hidden" name="action" value="confirm_employment"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                    <input type="hidden" name="id_candidatura" value="<?= (int)$candidato['id_candidatura'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                                    <button class="btn success" type="submit">Confirmar contratação</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                                            <?php endif; ?>
                                            <span class="status <?= $candidato['status'] === 'aprovado' ? 'aprovado' : 'reprovado' ?>"> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                                                <?= h(ucfirst($candidato['status'])) ?>
                                            </span> <!-- Explicação DevIN: Fecha elemento span do documento. -->
                                        <?php endif; ?>
                                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                                </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                            <?php endforeach; ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                <?php endforeach; ?>

            <?php else: ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>

                <details class="create-job-panel"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    <summary class="criar-post">Publicar nova vaga</summary> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    <form method="post" class="create-job-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                        <input type="hidden" name="action" value="create_job"> <!-- Explicação DevIN: Declara campo de entrada. -->
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                        <label>Título da vaga <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <input name="titulo" type="text" maxlength="50" placeholder="Ex.: Desenvolvedor PHP" required> <!-- Explicação DevIN: Declara campo de entrada. -->
                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                        <label>Descrição <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                            <textarea name="descricao" maxlength="1000" placeholder="Descreva as atividades e requisitos da vaga." required></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                        <label>Salário mensal (R$) <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                            <input name="salario_mensal" type="number" min="0" max="99999999.99" step="0.01" value="0.00" required> <!-- Explicação DevIN: Declara campo de entrada. -->
                                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                        <label>Limite de vagas <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                            <input name="limite_aprovados" type="number" min="0" max="2147483647" step="1" value="0" required data-job-approved-limit> <!-- Explicação DevIN: Declara campo de entrada. -->
                                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                                        <label>Encerrar a vaga quando <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                                            <select name="criterio_encerramento" data-job-closure-mode> <!-- Explicação DevIN: Declara seleção de opções. -->
                                                                <option value="nenhum" selected>Não encerrar automaticamente</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                                                <option value="limite">Atingir o limite de aprovados</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                                                <option value="prazo">Chegar à data e hora de encerramento</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                                                <option value="qualquer">Atingir o limite ou a data, o que vier primeiro</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                                                            </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                                        <label>Data e hora de encerramento <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                                            <input name="data_encerramento" type="datetime-local" data-job-closure-date> <!-- Explicação DevIN: Declara campo de entrada. -->
                                                        </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                                        <p class="job-form-hint">No modo “qualquer”, a vaga encerra quando atingir o limite de aprovados ou o prazo.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                        <p class="job-form-hint">Informe 0 no salário para exibir "A combinar".</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                        <button class="btn primary" type="submit">Publicar vaga</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Você ainda não publicou nenhuma vaga.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php endif; ?>

                <?php foreach ($empresaPosts as $post): ?>
                    <?php $salarioExibido = (float) $post['salario_mensal'] > 0 ? 'R$ ' . number_format((float) $post['salario_mensal'], 2, ',', '.') : 'A combinar'; ?>
                    <?php $limiteExibido = (int) $post['limite_aprovados'] > 0 ? (string) $post['limite_aprovados'] : 'Sem limite'; ?>
                    <?php $vagaEncerrada = !empty($post['encerrada_em']); ?>
                    <?php $encerramentoExibido = !empty($post['data_encerramento']) ? date('d/m/Y H:i', strtotime($post['data_encerramento'])) : 'Sem prazo'; ?>
                    <article class="item-card job-card" data-detail="<?= h($post['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($post['titulo']) ?>" data-detail-role="Vaga publicada" data-detail-tags="<?= $vagaEncerrada ? 'Vaga|Encerrada' : 'Vaga|Publicada' ?>" data-detail-experience="<?= h('Publicação::' . date('d/m/Y', strtotime($post['tempo_vaga'])) . '|Salário mensal::' . $salarioExibido . '|Limite de vagas::' . $limiteExibido . '|Encerramento::' . $encerramentoExibido) ?>"> <!-- Explicação DevIN: Declara conteúdo independente. Contém dado dinâmico processado pelo PHP. -->
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                        <div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <h2><?= h($post['titulo']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                            <p><?= h($post['descricao'] ?: 'Sem descrição informada.') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
                            <p class="job-compensation"><span>Salário mensal:</span> <strong><?= h($salarioExibido) ?></strong> · <span>Limite de vagas:</span> <strong><?= h($limiteExibido) ?></strong></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                            <?= dashboardCardTags($vagaEncerrada ? 'Vaga|Encerrada' : 'Vaga|Publicada') ?>
                            <?php if ($vagaEncerrada): ?><p class="job-closed-label">Vaga encerrada</p><?php endif; ?>
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="post-tools"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <details class="job-editor"> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <summary class="edit" aria-label="Editar vaga" title="Editar vaga"> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
                                    <?= dashboardIcon('edit') ?>
                                </summary> <!-- Explicação DevIN: Fecha elemento summary do documento. -->
                                <form method="post" class="edit-job-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                                    <input type="hidden" name="action" value="update_job"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    <label>Título <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <input name="titulo" type="text" maxlength="50" value="<?= h($post['titulo']) ?>" required> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label>Descrição <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <textarea name="descricao" maxlength="1000" required><?= h($post['descricao']) ?></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. Contém dado dinâmico processado pelo PHP. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label>Salário mensal (R$) <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <input name="salario_mensal" type="number" min="0" max="99999999.99" step="0.01" value="<?= h(number_format((float) $post['salario_mensal'], 2, '.', '')) ?>" required> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label>Limite de vagas <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <input name="limite_aprovados" type="number" min="0" max="2147483647" step="1" value="<?= (int) $post['limite_aprovados'] > 0 ? max((int) $post['candidaturas_aprovadas'], (int) $post['limite_aprovados']) : 0 ?>" required data-job-approved-limit> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label>Encerrar a vaga quando <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <select name="criterio_encerramento" data-job-closure-mode> <!-- Explicação DevIN: Declara seleção de opções. -->
                                            <option value="nenhum" <?= $post['criterio_encerramento'] === 'nenhum' ? 'selected' : '' ?>>Não encerrar automaticamente</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                                            <option value="limite" <?= $post['criterio_encerramento'] === 'limite' ? 'selected' : '' ?>>Atingir o limite de aprovados</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                                            <option value="prazo" <?= $post['criterio_encerramento'] === 'prazo' ? 'selected' : '' ?>>Chegar à data e hora de encerramento</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                                            <option value="qualquer" <?= $post['criterio_encerramento'] === 'qualquer' ? 'selected' : '' ?>>Atingir o limite ou a data, o que vier primeiro</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <label>Data e hora de encerramento <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                                        <input name="data_encerramento" type="datetime-local" data-job-closure-date value="<?= !empty($post['data_encerramento']) ? h(date('Y-m-d\TH:i', strtotime($post['data_encerramento']))) : '' ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                                    <p class="job-form-hint">0 deixa a vaga sem limite de vagas.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                    <button class="btn primary" type="submit">Salvar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                            </details> <!-- Explicação DevIN: Fecha elemento details do documento. -->
                            <form method="post" onsubmit="return confirm('Excluir esta vaga e as candidaturas dela?');"> <!-- Explicação DevIN: Declara formulário de entrada de dados. Vincula um evento da interface. -->
                                <input type="hidden" name="action" value="delete_job"> <!-- Explicação DevIN: Declara campo de entrada. -->
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                                <button class="delete" type="submit" aria-label="Excluir vaga" title="Excluir vaga"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                                    <?= dashboardIcon('trash') ?>
                                </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
                <?php endforeach; ?>

            <?php endif; ?>
            <?php if ($pagina === 'sobre'): ?>
                <?php require __DIR__ . '/components/dashboard-footer.php'; ?>
            <?php endif; ?>
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

        <?php if (!in_array($pagina, ['sobre', 'perfil', 'mensagens', 'suporte'], true)): ?>
            <?= dashboardDetailPanel() ?>
        <?php elseif ($pagina !== 'mensagens' && $pagina !== 'suporte'): ?>
        <aside class="detalhe-area"> <!-- Explicação DevIN: Declara conteúdo auxiliar. -->
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p>Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestões ou pedir suporte.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <p><strong>E-mail:</strong> contato@devin.com.br</p> <!-- Explicação DevIN: Declara parágrafo. A linha reúne múltiplos elementos. -->
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Explicando tudo sobre a vaga selecionada</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p>Use este espaço para visualizar detalhes da vaga, pessoa ou post escolhido no painel.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <?php else: ?>
                <h2 id="detailTitle"><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : 'Detalhes da vaga') ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
                <p id="detailText">Selecione uma vaga para ver sua descrição completa aqui.</p> <!-- Explicação DevIN: Declara parágrafo. -->
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
                <label>Nome da empresa<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" readonly aria-readonly="true"></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>E-mail da conta<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" maxlength="254" autocomplete="email" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <label>Senha atual (somente para alterar e-mail)<input name="senha_atual" type="password" autocomplete="current-password" maxlength="256" placeholder="Confirme sua senha se mudar o e-mail"></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
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
    <?php if ($pagina === 'suporte'): ?>
        <script src="../js/support-locale.js?v=<?= filemtime(__DIR__ . '/../js/support-locale.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
        <script src="../js/support-ui.js?v=<?= filemtime(__DIR__ . '/../js/support-ui.js') ?>" defer></script> <!-- Inicializa o suporte sem sair do dashboard. -->
    <?php endif; ?>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <?php if ($pagina === 'mensagens'): ?>
        <script src="../js/chat.js?v=<?= filemtime(__DIR__ . '/../js/chat.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <?php endif; ?>
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
