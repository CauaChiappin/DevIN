<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ResumeDocument.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
$idPessoa = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $idPessoa.
$erro = ''; // Explicação DevIN: Atribui um valor à variável PHP $erro.
$sucesso = (string) ($_SESSION['resume_upload_success'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $sucesso.
unset($_SESSION['resume_upload_success']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $action = requestString($_POST, 'action'); // Explicação DevIN: Atribui um valor à variável PHP $action.

    if ($action === 'review_choice') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (requestString($_POST, 'choice') === 'yes') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (($_POST['consent'] ?? '') !== 'yes') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $erro = 'Confirme o envio do documento ao Google Gemini para solicitar a revisão.'; // Explicação DevIN: Atribui um valor à variável PHP $erro.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                $_SESSION['resume_abnt_consent'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: curriculo_abnt.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            header('Location: pessoa.php?pagina=curriculo'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } elseif ($action === 'upload') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $newRelativePath = null; // Explicação DevIN: Atribui um valor à variável PHP $newRelativePath.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $file = $_FILES['curriculo'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $file.
            if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Selecione um arquivo válido para continuar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 10 * 1024 * 1024) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('O arquivo deve ter no máximo 10 MB.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $originalName = basename((string) ($file['name'] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $originalName.
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)); // Explicação DevIN: Atribui um valor à variável PHP $extension.
            $allowedTypes = [ // Explicação DevIN: Atribui um valor à variável PHP $allowedTypes.
                'pdf' => ['application/pdf'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'docx' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'application/zip', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'application/x-zip', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            if (!isset($allowedTypes[$extension])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Envie um arquivo PDF, DOC ou DOCX.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $fileInfo = new finfo(FILEINFO_MIME_TYPE); // Explicação DevIN: Atribui um valor à variável PHP $fileInfo.
            $mimeType = $fileInfo->file((string) $file['tmp_name']); // Explicação DevIN: Atribui um valor à variável PHP $mimeType.
            if (!is_string($mimeType) || !in_array($mimeType, $allowedTypes[$extension], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('O conteúdo do arquivo não corresponde ao formato informado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $originalName.
            $originalName = mb_substr($originalName, 0, 255, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $originalName.
            $token = bin2hex(random_bytes(16)); // Explicação DevIN: Atribui um valor à variável PHP $token.
            $newRelativePath = 'storage/resumes/' . $token . '.' . $extension; // Explicação DevIN: Atribui um valor à variável PHP $newRelativePath.
            $destination = ResumeDocument::storageDirectory() . DIRECTORY_SEPARATOR . $token . '.' . $extension; // Explicação DevIN: Atribui um valor à variável PHP $destination.
            if (!move_uploaded_file((string) $file['tmp_name'], $destination)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível guardar o arquivo enviado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $connection = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $connection.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $oldPath = ResumeDocument::replaceUpload($connection, $idPessoa, $originalName, $newRelativePath, $mimeType); // Explicação DevIN: Atribui um valor à variável PHP $oldPath.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $connection->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            ResumeDocument::removeFile($oldPath); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $_SESSION['resume_upload_success'] = 'Currículo enviado com segurança.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            header('Location: curriculo_upload.php?etapa=abnt'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            ResumeDocument::removeFile($newRelativePath); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            error_log('Erro ao enviar currículo: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $erro = $exception instanceof InvalidArgumentException // Explicação DevIN: Atribui um valor à variável PHP $erro.
                ? $exception->getMessage() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Não foi possível enviar o currículo. Tente novamente.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$documento = []; // Explicação DevIN: Atribui um valor à variável PHP $documento.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $connection = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $connection.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $documento = ResumeDocument::find($connection, $idPessoa); // Explicação DevIN: Atribui um valor à variável PHP $documento.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $connection->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao carregar documento do currículo: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$mostrarEtapaAbnt = requestString($_GET, 'etapa') === 'abnt' && !empty($documento['arquivo_caminho']); // Explicação DevIN: Atribui um valor à variável PHP $mostrarEtapaAbnt.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | <?= $mostrarEtapaAbnt ? 'Revisão ABNT' : 'Enviar currículo' ?></title> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/cadastrostyle.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/curriculo.css?v=<?= filemtime(__DIR__ . '/../css/curriculo.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body class="curriculo-page resume-onboarding-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="resume-onboarding"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
        <header class="cadastro-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <div class="brand-logo"><a href="index.php">Dev<span>IN</span></a></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
            <a class="header-action" href="logout.php">Sair</a> <!-- Explicação DevIN: Declara link de navegação. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

        <section class="resume-onboarding__panel" aria-labelledby="upload-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <?php if ($erro !== ''): ?><p class="resume-upload-message" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <?php if ($sucesso !== ''): ?><p class="resume-upload-message resume-upload-message--success" role="status"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

            <?php if ($mostrarEtapaAbnt): ?>
                <p class="resume-onboarding__eyebrow">ARQUIVO RECEBIDO</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <h1 id="upload-title">Quer uma revisão de apresentação?</h1> <!-- Explicação DevIN: Declara título principal. -->
                <p>Podemos analisar a organização do documento com referências gerais da ABNT. Currículos não têm um formato obrigatório definido por essa norma; a análise é orientativa, não um certificado de conformidade.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <form method="post" class="resume-onboarding__actions"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    <input type="hidden" name="action" value="review_choice"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <input type="hidden" name="choice" value="yes"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <label class="resume-onboarding__consent"> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="checkbox" name="consent" value="yes" required> <!-- Explicação DevIN: Declara campo de entrada. -->
                        <span>Autorizo o envio do documento ao Google Gemini para gerar a revisão. O arquivo pode conter dados pessoais.</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                    <button class="resume-onboarding__button resume-onboarding__button--primary" type="submit">Sim, revisar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                <form method="post" class="resume-onboarding__actions"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    <input type="hidden" name="action" value="review_choice"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <input type="hidden" name="choice" value="no"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <button class="resume-onboarding__button" type="submit">Não, ir para meu currículo</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
            <?php else: ?>
                <p class="resume-onboarding__eyebrow">MEU CURRÍCULO</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <h1 id="upload-title"><?= !empty($documento['arquivo_nome']) ? 'Substituir currículo' : 'Enviar currículo pronto' ?></h1> <!-- Explicação DevIN: Declara título principal. Contém dado dinâmico processado pelo PHP. -->
                <p>Aceitamos PDF, Word DOC ou DOCX de até 10 MB. O arquivo fica privado e só pode ser acessado pela sua conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <?php if (!empty($documento['arquivo_nome'])): ?>
                    <p class="resume-upload-current">Arquivo atual: <strong><?= htmlspecialchars((string) $documento['arquivo_nome'], ENT_QUOTES, 'UTF-8') ?></strong></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="resume-upload-form" data-upload-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    <input type="hidden" name="action" value="upload"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <label class="resume-dropzone" for="resume-file" data-dropzone> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <span class="resume-dropzone__icon" aria-hidden="true"> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 20h14"/></svg> <!-- Explicação DevIN: Declara gráfico vetorial. A linha reúne múltiplos elementos. -->
                        </span> <!-- Explicação DevIN: Fecha elemento span do documento. -->
                        <strong data-dropzone-title>Arraste seu currículo aqui</strong> <!-- Explicação DevIN: Declara elemento de marcação. -->
                        <small data-dropzone-hint>ou clique para escolher um arquivo · PDF, DOC ou DOCX · até 10 MB</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                        <input id="resume-file" name="curriculo" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required> <!-- Explicação DevIN: Declara campo de entrada. -->
                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                    <button class="resume-onboarding__button resume-onboarding__button--primary" type="submit">Enviar currículo</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
                <ul class="resume-upload-tips"> <!-- Explicação DevIN: Declara lista sem numeração. -->
                    <li>Arquivo privado, acessível apenas pela sua conta.</li> <!-- Explicação DevIN: Declara item da lista. -->
                    <li>Você poderá substituir o arquivo quando quiser.</li> <!-- Explicação DevIN: Declara item da lista. -->
                </ul> <!-- Explicação DevIN: Fecha elemento ul do documento. -->
                <a class="resume-onboarding__back" href="pessoa.php?pagina=curriculo">← Voltar para meu currículo</a> <!-- Explicação DevIN: Declara link de navegação. -->
            <?php endif; ?>
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->
    <script>
        (function () { // Explicação DevIN: Define função anônima usada como operação ou callback.
            var form = document.querySelector('[data-upload-form]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (!form) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            var zone = form.querySelector('[data-dropzone]'); // Explicação DevIN: Declara variável JavaScript zone para reutilizar valor ou resultado.
            var input = form.querySelector('input[type="file"]'); // Explicação DevIN: Declara variável JavaScript input para reutilizar valor ou resultado.
            var title = form.querySelector('[data-dropzone-title]'); // Explicação DevIN: Declara variável JavaScript title para reutilizar valor ou resultado.
            var hint = form.querySelector('[data-dropzone-hint]'); // Explicação DevIN: Declara variável JavaScript hint para reutilizar valor ou resultado.
            var defaults = [title.textContent, hint.textContent]; // Explicação DevIN: Declara variável JavaScript defaults para reutilizar valor ou resultado.
            function update() { // Explicação DevIN: Declara função/método update, com seus parâmetros e comportamento.
                var file = input.files && input.files[0]; // Explicação DevIN: Declara variável JavaScript file para reutilizar valor ou resultado.
                zone.classList.toggle('has-file', !!file); // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
                title.textContent = file ? file.name : defaults[0]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                hint.textContent = file ? (file.size / 1048576).toFixed(2) + ' MB · clique para trocar' : defaults[1]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            input.addEventListener('change', update); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            ['dragenter', 'dragover'].forEach(function (name) { // Explicação DevIN: Define função anônima usada como operação ou callback.
                zone.addEventListener(name, function (event) { event.preventDefault(); zone.classList.add('is-dragging'); }); // Explicação DevIN: Define função anônima usada como operação ou callback.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            ['dragleave', 'drop'].forEach(function (name) { // Explicação DevIN: Define função anônima usada como operação ou callback.
                zone.addEventListener(name, function () { zone.classList.remove('is-dragging'); }); // Explicação DevIN: Define função anônima usada como operação ou callback.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            zone.addEventListener('drop', function (event) { // Explicação DevIN: Define função anônima usada como operação ou callback.
                event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                if (event.dataTransfer && event.dataTransfer.files.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    input.files = event.dataTransfer.files; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    update(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        })(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    </script>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->