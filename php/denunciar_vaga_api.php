<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/JobReports.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

function responderDenunciaVaga(array $data, int $status = 200): void // Explicação DevIN: Declara função/método responderDenunciaVaga, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        header('Allow: POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $reporter = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $reporter.
    if (!in_array($reporter['tipo'], ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Este perfil não pode denunciar vagas.'], 403); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    requireValidCsrf(); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    $input = json_decode((string) file_get_contents('php://input'), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    if (!is_array($input)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Não foi possível ler os dados da denúncia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $jobId = filter_var($input['id_vaga'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $jobId.
    if ($jobId === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Vaga inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        ensureJobReportsSchema($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        $language = ProfileLanguagePreference::load($conn, $reporter['tipo'], (int) $reporter['id']); // Explicação DevIN: Atribui um valor à variável PHP $language.
        $submittedReport = submitJobReport( // Explicação DevIN: Atribui um valor à variável PHP $submittedReport.
            $conn, // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            $reporter, // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            $jobId, // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            is_string($input['motivo'] ?? null) ? trim($input['motivo']) : '', // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            is_string($input['detalhes'] ?? null) ? trim($input['detalhes']) : '' // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $reportId = $submittedReport['id']; // Explicação DevIN: Atribui um valor à variável PHP $reportId.
        $jobTitle = $submittedReport['titulo']; // Explicação DevIN: Atribui um valor à variável PHP $jobTitle.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $warnings = []; // Explicação DevIN: Atribui um valor à variável PHP $warnings.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            'job-report-submitted:' . $reportId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            (string) $reporter['tipo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            (int) $reporter['id'], // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
            'Support DevIN: Recebemos sua denúncia sobre a vaga "' . $jobTitle . '". Ela está sob análise da nossa equipe. Você receberá outra mensagem quando a análise for concluída.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Falha ao enviar confirmação interna da denúncia de vaga: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        $warnings[] = 'Não foi possível enviar a confirmação na aba de mensagens.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!MailerHelper::enviarConfirmacaoDenuncia( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        (string) $reporter['email'], // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        (string) $reporter['nome'], // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        'vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $jobTitle, // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        $language // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    )) { // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
        $warnings[] = 'Não foi possível enviar o e-mail de confirmação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $response = [ // Explicação DevIN: Atribui um valor à variável PHP $response.
        'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'mensagem' => 'Denúncia enviada para análise da equipe DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    if ($warnings !== []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $response['avisos'] = $warnings; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    responderDenunciaVaga($response, 201); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
} catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => $exception->getMessage()], 400); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
} catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Falha ao salvar denúncia de vaga: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Não foi possível registrar a denúncia agora. Tente novamente.'], 500); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao receber denúncia de vaga: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Não foi possível registrar a denúncia agora. Tente novamente.'], 500); // Explicação DevIN: Continua expressão ou bloco pertencente a função responderDenunciaVaga.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
