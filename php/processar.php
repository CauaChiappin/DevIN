<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/processar.php
 * Finalidade: processar.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once dirname(__DIR__) . '/vendor/autoload.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$envDirectory = dirname(__DIR__); // Explicação DevIN: Atribui um valor à variável PHP $envDirectory.
if (is_file($envDirectory . '/.env')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    Dotenv\Dotenv::createImmutable($envDirectory)->safeLoad(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: index.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$acao = requestString($_POST, 'acao'); // Explicação DevIN: Atribui um valor à variável PHP $acao.
// Valida CSRF antes de qualquer operação que altere dados ou estado da sessão.
if (!verifyCsrfToken(requestString($_POST, 'csrf_token'))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if ($acao === 'cadastrar_curriculo') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $_SESSION['erro_curriculo'] = 'Sessão inválida ou expirada. Atualize a página e tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        header('Location: cadastrar_curriculo.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        $_SESSION['erro_recuperacao'] = 'Sessão inválida ou expirada. Atualize a página e tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

switch ($acao) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    case 'solicitar_recuperacao': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        // Etapa 1: identifica a conta e envia um código temporário sem armazenar o código em texto puro.
        $email = requestString($_POST, 'email'); // Explicação DevIN: Atribui um valor à variável PHP $email.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_recuperacao'] = 'Informe um e-mail válido.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Limita por IP e por endereço para dificultar abuso sem depender só do navegador.
        $ipRetry = RateLimiter::consume('recovery:ip:' . RateLimiter::clientIp(), 10, 3600); // Explicação DevIN: Atribui um valor à variável PHP $ipRetry.
        $emailRetry = RateLimiter::consume('recovery:email:' . strtolower($email), 3, 3600); // Explicação DevIN: Atribui um valor à variável PHP $emailRetry.
        if ($ipRetry > 0 || $emailRetry > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['sucesso_recuperacao'] = 'Se houver uma conta com esse e-mail, enviaremos as instruções de recuperação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $conn = null; // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            $usuario = null; // Explicação DevIN: Atribui um valor à variável PHP $usuario.
            $tabelas = [ // Explicação DevIN: Atribui um valor à variável PHP $tabelas.
                ['tabela' => 'pessoa', 'id' => 'id_pessoa', 'tipo' => 'pessoa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ['tabela' => 'empresa', 'id' => 'id_empresa', 'tipo' => 'empresa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

            foreach ($tabelas as $item) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $sql = "SELECT {$item['id']} AS id, nome, email FROM {$item['tabela']} WHERE email = ? LIMIT 1"; // Explicação DevIN: Atribui um valor à variável PHP $sql.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare($sql); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('s', $email); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $usuario = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if ($usuario !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $usuario['tipo'] = $item['tipo']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $usuario['tabela'] = $item['tabela']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $usuario['id_coluna'] = $item['id']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($usuario === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $_SESSION['sucesso_recuperacao'] = 'Se houver uma conta com esse e-mail, enviaremos as instruções de recuperação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $idiomaEmail = ProfileLanguagePreference::load($conn, $usuario['tipo'], (int) $usuario['id']); // Explicação DevIN: Atribui um valor à variável PHP $idiomaEmail.

            $codigo = ''; // Explicação DevIN: Atribui um valor à variável PHP $codigo.
            $alfabeto = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Explicação DevIN: Atribui um valor à variável PHP $alfabeto.
            for ($i = 0; $i < 6; $i++) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)]; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Persiste apenas o hash do código e define prazo de validade no banco.
            $codigoHash = hash('sha256', $codigo); // Explicação DevIN: Atribui um valor à variável PHP $codigoHash.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$usuario['id_coluna']} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $idUsuario = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $idUsuario.
            $stmt->bind_param('si', $codigoHash, $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if (!MailerHelper::enviarCodigoRecuperacao6($email, (string) $usuario['nome'], $codigo, $idiomaEmail)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$usuario['id_coluna']} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $_SESSION['erro_recuperacao'] = 'Não foi possível enviar o e-mail. Confira a configuração SMTP e tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['recuperacao_email'] = $email; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_tipo'] = $usuario['tipo']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_id'] = $idUsuario; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_nome'] = (string) $usuario['nome']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['codigo_recuperacao_hash'] = $codigoHash; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['ultimo_envio_codigo'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            unset($_SESSION['recuperacao_verificada']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['sucesso_codigo'] = 'Enviamos um código para o e-mail informado.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log($e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_recuperacao'] = 'Não foi possível processar a recuperação agora. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if ($conn instanceof mysqli) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    case 'validar_codigo': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        // Etapa 2: confere o código na sessão e no banco; ao aceitar, invalida o código e libera a troca.
        // Aplica limites antes de comparar o código para dificultar tentativas automatizadas.
        $ipRetry = RateLimiter::consume('code:ip:' . RateLimiter::clientIp(), 20, 3600); // Explicação DevIN: Atribui um valor à variável PHP $ipRetry.
        $emailSessao = strtolower((string) ($_SESSION['recuperacao_email'] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $emailSessao.
        $emailRetry = RateLimiter::consume('code:email:' . $emailSessao, 10, 3600); // Explicação DevIN: Atribui um valor à variável PHP $emailRetry.
        if ($ipRetry > 0 || $emailRetry > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_codigo'] = 'Muitas tentativas. Aguarde antes de tentar novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $codigoDigitado = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', requestString($_POST, 'codigo'))); // Explicação DevIN: Atribui um valor à variável PHP $codigoDigitado.
        $email = (string) ($_SESSION['recuperacao_email'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $email.
        $hashSessao = (string) ($_SESSION['codigo_recuperacao_hash'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $hashSessao.
        // Compara o hash em tempo constante e exige exatamente seis caracteres válidos.
        $codigoCorrespondeASessao = strlen($codigoDigitado) === 6 // Explicação DevIN: Atribui um valor à variável PHP $codigoCorrespondeASessao.
            && $hashSessao !== '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
            && hash_equals($hashSessao, hash('sha256', $codigoDigitado)); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.

        if ($email === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$codigoCorrespondeASessao) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_codigo'] = 'Código incorreto ou expirado.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $conn = null; // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            $codigoHash = hash('sha256', $codigoDigitado); // Explicação DevIN: Atribui um valor à variável PHP $codigoHash.
            $tabelas = [ // Explicação DevIN: Atribui um valor à variável PHP $tabelas.
                ['tabela' => 'pessoa', 'id' => 'id_pessoa', 'tipo' => 'pessoa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ['tabela' => 'empresa', 'id' => 'id_empresa', 'tipo' => 'empresa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            $usuario = null; // Explicação DevIN: Atribui um valor à variável PHP $usuario.
            foreach ($tabelas as $item) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare("SELECT {$item['id']} AS id, nome, email FROM {$item['tabela']} WHERE email = ? AND token_recuperacao = ? AND token_expiracao > NOW() LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('ss', $email, $codigoHash); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $usuario = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if ($usuario !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $usuario['tipo'] = $item['tipo']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $usuario['tabela'] = $item['tabela']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $usuario['id_coluna'] = $item['id']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($usuario === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                unset($_SESSION['codigo_recuperacao_hash']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $_SESSION['erro_codigo'] = 'Código incorreto ou expirado.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $idUsuario = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $idUsuario.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$usuario['id_coluna']} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            $_SESSION['recuperacao_verificada'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_id'] = $idUsuario; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_tipo'] = $usuario['tipo']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_nome'] = (string) $usuario['nome']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['recuperacao_email'] = (string) $usuario['email']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            unset($_SESSION['codigo_recuperacao_hash']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            header('Location: redefinir.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log($e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_codigo'] = 'Não foi possível validar o código agora. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if ($conn instanceof mysqli) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    case 'reenviar_codigo': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        // Emite novo código apenas para a conta guardada na sessão e respeita limites e intervalo mínimo.
        $email = (string) ($_SESSION['recuperacao_email'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $email.
        $idUsuario = (int) ($_SESSION['recuperacao_id'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $idUsuario.
        $tipo = (string) ($_SESSION['recuperacao_tipo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $tipo.
        $nome = (string) ($_SESSION['recuperacao_nome'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $nome.
        $ultimoEnvio = (int) ($_SESSION['ultimo_envio_codigo'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $ultimoEnvio.

        if ($email === '' || $idUsuario < 1 || !in_array($tipo, ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ipRetry = RateLimiter::consume('resend:ip:' . RateLimiter::clientIp(), 10, 3600); // Explicação DevIN: Atribui um valor à variável PHP $ipRetry.
        $emailRetry = RateLimiter::consume('resend:email:' . strtolower($email), 5, 3600); // Explicação DevIN: Atribui um valor à variável PHP $emailRetry.
        if ($ipRetry > 0 || $emailRetry > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_codigo'] = 'Muitas solicitações. Aguarde antes de pedir outro código.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // O intervalo de 60 segundos complementa o limite por hora configurado acima.
        if (time() - $ultimoEnvio < 60) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $restante = 60 - (time() - $ultimoEnvio); // Explicação DevIN: Atribui um valor à variável PHP $restante.
            $_SESSION['erro_codigo'] = "Aguarde {$restante}s antes de solicitar outro código."; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $tabela = $tipo === 'empresa' ? 'empresa' : 'pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $tabela.
        $idColuna = $tipo === 'empresa' ? 'id_empresa' : 'id_pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $idColuna.
        $codigo = ''; // Explicação DevIN: Atribui um valor à variável PHP $codigo.
        $alfabeto = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Explicação DevIN: Atribui um valor à variável PHP $alfabeto.
        for ($i = 0; $i < 6; $i++) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)]; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $codigoHash = hash('sha256', $codigo); // Explicação DevIN: Atribui um valor à variável PHP $codigoHash.
        $conn = null; // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            $idiomaEmail = ProfileLanguagePreference::load($conn, $tipo, $idUsuario); // Explicação DevIN: Atribui um valor à variável PHP $idiomaEmail.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("UPDATE {$tabela} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$idColuna} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('si', $codigoHash, $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if (!MailerHelper::enviarCodigoRecuperacao6($email, $nome, $codigo, $idiomaEmail)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare("UPDATE {$tabela} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$idColuna} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                unset($_SESSION['codigo_recuperacao_hash']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                $_SESSION['erro_codigo'] = 'Não foi possível reenviar o código. Tente novamente mais tarde.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $_SESSION['codigo_recuperacao_hash'] = $codigoHash; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['ultimo_envio_codigo'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['sucesso_codigo'] = 'Novo código enviado com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log($e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_codigo'] = 'Não foi possível reenviar o código agora. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: codigo-senha.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if ($conn instanceof mysqli) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    case 'redefinir_senha': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        // Etapa final: exige código validado, salva nova senha como hash e inicia a sessão do usuário.
        $ipRetry = RateLimiter::consume('reset:ip:' . RateLimiter::clientIp(), 10, 3600); // Explicação DevIN: Atribui um valor à variável PHP $ipRetry.
        $emailRetry = RateLimiter::consume('reset:email:' . strtolower((string) ($_SESSION['recuperacao_email'] ?? '')), 5, 3600); // Explicação DevIN: Atribui um valor à variável PHP $emailRetry.
        if ($ipRetry > 0 || $emailRetry > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_redefinir'] = 'Muitas tentativas. Aguarde antes de tentar novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: redefinir.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Exige a etapa de verificação concluída antes de aceitar uma nova senha.
        if (empty($_SESSION['recuperacao_verificada'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $novaSenha = requestString($_POST, 'nova_senha'); // Explicação DevIN: Atribui um valor à variável PHP $novaSenha.
        $confirmarSenha = requestString($_POST, 'confirmar_senha'); // Explicação DevIN: Atribui um valor à variável PHP $confirmarSenha.
        $idUsuario = (int) ($_SESSION['recuperacao_id'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $idUsuario.
        $tipo = (string) ($_SESSION['recuperacao_tipo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $tipo.
        if ($idUsuario < 1 || !in_array($tipo, ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            unset($_SESSION['recuperacao_verificada']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($novaSenha !== $confirmarSenha) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_redefinir'] = 'As senhas não coincidem.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: redefinir.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (strlen($novaSenha) < 8|| !preg_match('/[A-Z]/', $novaSenha) || !preg_match('/[^a-zA-Z0-9]/', $novaSenha)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['erro_redefinir'] = 'A senha não preenche todos os requisitos de segurança.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: redefinir.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $tabela = $tipo === 'empresa' ? 'empresa' : 'pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $tabela.
        $idColuna = $tipo === 'empresa' ? 'id_empresa' : 'id_pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $idColuna.
        // Atualiza somente o hash; a sessão autenticada é renovada após a troca.
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
        $conn = null; // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("UPDATE {$tabela} SET senha_hash = ? WHERE {$idColuna} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('si', $senhaHash, $idUsuario); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if ($stmt->affected_rows < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('A senha não foi atualizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            AuditService::tryRecord('password_reset', 'success', $tipo, $idUsuario); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            $nomeUsuario = (string) ($_SESSION['recuperacao_nome'] ?? 'Usuário'); // Explicação DevIN: Atribui um valor à variável PHP $nomeUsuario.
            $emailUsuario = (string) ($_SESSION['recuperacao_email'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $emailUsuario.
            $idiomaEmail = ProfileLanguagePreference::load($conn, $tipo, $idUsuario); // Explicação DevIN: Atribui um valor à variável PHP $idiomaEmail.
            $emailAvisoEnviado = filter_var($emailUsuario, FILTER_VALIDATE_EMAIL) // Explicação DevIN: Atribui um valor à variável PHP $emailAvisoEnviado.
                && MailerHelper::enviarAvisoAlteracaoSenha($emailUsuario, $nomeUsuario, $idiomaEmail); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

            secureSessionRegenerate(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['logado'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_id'] = $idUsuario; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_tipo'] = $tipo; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_nome'] = $nomeUsuario; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['usuario_email'] = $emailUsuario; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $_SESSION['idioma'] = $idiomaEmail; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            unset($_SESSION['recuperacao_verificada'], $_SESSION['recuperacao_id'], $_SESSION['recuperacao_tipo'], $_SESSION['recuperacao_email'], $_SESSION['recuperacao_nome']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['sucesso_login'] = 'Senha alterada com sucesso.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (!$emailAvisoEnviado) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $_SESSION['erro_email_senha'] = 'A senha foi alterada, mas não foi possível enviar o aviso por e-mail.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            header('Location: ' . ($tipo === 'empresa' ? 'empresa.php' : 'pessoa.php')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log($e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_redefinir'] = 'Não foi possível atualizar a senha agora. Tente novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: redefinir.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if ($conn instanceof mysqli) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    case 'cadastrar_curriculo': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        // Salva currículo somente para a pessoa autenticada, atualizando o registro existente quando necessário.
        $conn = null; // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Usa o ID da sessão autenticada, sem aceitar que o formulário escolha outro candidato.
            $usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
            $idPessoa = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $idPessoa.
            $nomePessoa = (string) ($usuario['nome'] ?? 'Candidato'); // Explicação DevIN: Atribui um valor à variável PHP $nomePessoa.
            $emailPessoa = (string) ($usuario['email'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $emailPessoa.
            $nomeSocial = requestString($_POST, 'nome_social'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSocial.
            $grauEscolaridade = requestString($_POST, 'grau_de_escolaridade'); // Explicação DevIN: Atribui um valor à variável PHP $grauEscolaridade.
            $cursos = requestString($_POST, 'cursos'); // Explicação DevIN: Atribui um valor à variável PHP $cursos.
            $experiencia = requestString($_POST, 'experiencia'); // Explicação DevIN: Atribui um valor à variável PHP $experiencia.
            $idiomas = requestString($_POST, 'idiomas'); // Explicação DevIN: Atribui um valor à variável PHP $idiomas.
            $gerarComIa = requestString($_POST, 'gerar_com_ia') === '1'; // Explicação DevIN: Atribui um valor à variável PHP $gerarComIa.

            if ($gerarComIa && requestString($_POST, 'consentimento_ia') !== 'yes') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Autorize o envio dos dados para iniciar a geração com IA.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($nomeSocial === '' || $grauEscolaridade === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Informe o nome social e o grau de escolaridade.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $idPessoa); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $jaExiste = $stmt->get_result()->num_rows > 0; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            // Atualiza o currículo existente ou cria o primeiro registro para esta pessoa.
            if ($jaExiste) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare('UPDATE curriculo SET nome_social = ?, grau_de_escolaridade = ?, cursos = ?, experiencia = ?, idiomas = ? WHERE id_pessoa = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('sssssi', $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas, $idPessoa); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare('INSERT INTO curriculo (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas) VALUES (?, ?, ?, ?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('isssss', $idPessoa, $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmtLembrete = $conn->prepare('UPDATE pessoa SET lembrete_enviado = 0 WHERE id_pessoa = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtLembrete->bind_param('i', $idPessoa); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtLembrete->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtLembrete->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if ($emailPessoa !== '' && filter_var($emailPessoa, FILTER_VALIDATE_EMAIL) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                && MailerHelper::enviarConfirmacaoCadastroCurriculo($emailPessoa, $nomePessoa, $_SESSION['idioma'] ?? 'pt-BR')) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $_SESSION['sucesso_curriculo'] = 'Currículo salvo. Enviamos uma confirmação para seu e-mail.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                $_SESSION['erro_curriculo'] = 'Currículo salvo, mas não foi possível enviar o e-mail de confirmação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($gerarComIa) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $_SESSION['resume_generate_on_dashboard'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: pessoa.php?pagina=curriculo&gerar=1'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                header('Location: pessoa.php?pagina=inicio'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log($e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['erro_curriculo'] = $e instanceof InvalidArgumentException // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? $e->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível salvar o currículo agora. Tente novamente.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            header('Location: cadastrar_curriculo.php' . (requestString($_POST, 'gerar_com_ia') === '1' ? '?ia=1' : '')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if ($conn instanceof mysqli) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    default: // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
        header('Location: index.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
