<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ResumeDocument.php';

$usuario = requireWebAuth('pessoa');
$idPessoa = (int) $usuario['id'];
$erro = '';
$sucesso = (string) ($_SESSION['resume_upload_success'] ?? '');
unset($_SESSION['resume_upload_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();
    $action = requestString($_POST, 'action');

    if ($action === 'review_choice') {
        if (requestString($_POST, 'choice') === 'yes') {
            if (($_POST['consent'] ?? '') !== 'yes') {
                $erro = 'Confirme o envio do documento ao Google Gemini para solicitar a revisão.';
            } else {
                $_SESSION['resume_abnt_consent'] = true;
                header('Location: curriculo_abnt.php');
                exit;
            }
        } else {
            header('Location: pessoa.php?pagina=curriculo');
            exit;
        }
    } elseif ($action === 'upload') {
        $newRelativePath = null;
        try {
            $file = $_FILES['curriculo'] ?? null;
            if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('Selecione um arquivo válido para continuar.');
            }
            if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 10 * 1024 * 1024) {
                throw new InvalidArgumentException('O arquivo deve ter no máximo 10 MB.');
            }

            $originalName = basename((string) ($file['name'] ?? ''));
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedTypes = [
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
                'docx' => [
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip',
                    'application/x-zip',
                ],
            ];
            if (!isset($allowedTypes[$extension])) {
                throw new InvalidArgumentException('Envie um arquivo PDF, DOC ou DOCX.');
            }
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file((string) $file['tmp_name']);
            if (!is_string($mimeType) || !in_array($mimeType, $allowedTypes[$extension], true)) {
                throw new InvalidArgumentException('O conteúdo do arquivo não corresponde ao formato informado.');
            }

            $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? '';
            $originalName = mb_substr($originalName, 0, 255, 'UTF-8');
            $token = bin2hex(random_bytes(16));
            $newRelativePath = 'storage/resumes/' . $token . '.' . $extension;
            $destination = ResumeDocument::storageDirectory() . DIRECTORY_SEPARATOR . $token . '.' . $extension;
            if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
                throw new RuntimeException('Não foi possível guardar o arquivo enviado.');
            }

            $connection = getDatabaseConnection();
            try {
                $oldPath = ResumeDocument::replaceUpload($connection, $idPessoa, $originalName, $newRelativePath, $mimeType);
            } finally {
                $connection->close();
            }
            ResumeDocument::removeFile($oldPath);
            $_SESSION['resume_upload_success'] = 'Currículo enviado com segurança.';
            header('Location: curriculo_upload.php?etapa=abnt');
            exit;
        } catch (Throwable $exception) {
            ResumeDocument::removeFile($newRelativePath);
            error_log('Erro ao enviar currículo: ' . $exception->getMessage());
            $erro = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível enviar o currículo. Tente novamente.';
        }
    }
}

$documento = [];
try {
    $connection = getDatabaseConnection();
    try {
        $documento = ResumeDocument::find($connection, $idPessoa);
    } finally {
        $connection->close();
    }
} catch (Throwable $exception) {
    error_log('Erro ao carregar documento do currículo: ' . $exception->getMessage());
}
$mostrarEtapaAbnt = requestString($_GET, 'etapa') === 'abnt' && !empty($documento['arquivo_caminho']);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | <?= $mostrarEtapaAbnt ? 'Revisão ABNT' : 'Enviar currículo' ?></title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
    <link rel="stylesheet" href="../css/site-navigation.css">
    <link rel="stylesheet" href="../css/curriculo.css?v=<?= filemtime(__DIR__ . '/../css/curriculo.css') ?>">
</head>

<body class="curriculo-page resume-onboarding-page">
    <main class="resume-onboarding">
        <header class="cadastro-header">
            <div class="brand-logo"><a href="index.php">Dev<span>IN</span></a></div>
            <a class="header-action" href="logout.php">Sair</a>
        </header>

        <section class="resume-onboarding__panel" aria-labelledby="upload-title">
            <?php if ($erro !== ''): ?><p class="resume-upload-message" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <?php if ($sucesso !== ''): ?><p class="resume-upload-message resume-upload-message--success" role="status"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

            <?php if ($mostrarEtapaAbnt): ?>
                <p class="resume-onboarding__eyebrow">ARQUIVO RECEBIDO</p>
                <h1 id="upload-title">Quer uma revisão de apresentação?</h1>
                <p>Podemos analisar a organização do documento com referências gerais da ABNT. Currículos não têm um formato obrigatório definido por essa norma; a análise é orientativa, não um certificado de conformidade.</p>
                <form method="post" class="resume-onboarding__actions">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="review_choice">
                    <input type="hidden" name="choice" value="yes">
                    <label class="resume-onboarding__consent">
                        <input type="checkbox" name="consent" value="yes" required>
                        <span>Autorizo o envio do documento ao Google Gemini para gerar a revisão. O arquivo pode conter dados pessoais.</span>
                    </label>
                    <button class="resume-onboarding__button resume-onboarding__button--primary" type="submit">Sim, revisar</button>
                </form>
                <form method="post" class="resume-onboarding__actions">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="review_choice">
                    <input type="hidden" name="choice" value="no">
                    <button class="resume-onboarding__button" type="submit">Não, ir para meu currículo</button>
                </form>
            <?php else: ?>
                <p class="resume-onboarding__eyebrow">MEU CURRÍCULO</p>
                <h1 id="upload-title"><?= !empty($documento['arquivo_nome']) ? 'Substituir currículo' : 'Enviar currículo pronto' ?></h1>
                <p>Aceitamos PDF, Word DOC ou DOCX de até 10 MB. O arquivo fica privado e só pode ser acessado pela sua conta.</p>
                <?php if (!empty($documento['arquivo_nome'])): ?>
                    <p class="resume-upload-current">Arquivo atual: <strong><?= htmlspecialchars((string) $documento['arquivo_nome'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="resume-upload-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="upload">
                    <label for="resume-file">Arquivo do currículo</label>
                    <input id="resume-file" name="curriculo" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button class="resume-onboarding__button resume-onboarding__button--primary" type="submit">Enviar currículo</button>
                </form>
                <a class="resume-onboarding__back" href="pessoa.php?pagina=curriculo">Voltar para meu currículo</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>