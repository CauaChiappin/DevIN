<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

requireWebAuth('pessoa');
if (($_SESSION['resume_abnt_consent'] ?? false) !== true) {
    header('Location: curriculo_upload.php?etapa=abnt');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Revisão do currículo</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
    <link rel="stylesheet" href="../css/curriculo.css?v=<?= filemtime(__DIR__ . '/../css/curriculo.css') ?>">
</head>

<body class="curriculo-page resume-onboarding-page">
    <main class="resume-onboarding">
        <header class="cadastro-header">
            <div class="brand-logo"><a href="index.php">Dev<span>IN</span></a></div>
            <a class="header-action" href="logout.php">Sair</a>
        </header>
        <section class="resume-onboarding__panel resume-review" data-abnt-review data-csrf="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <p class="resume-onboarding__eyebrow">REVISÃO ORIENTATIVA</p>
            <h1>Revisar meu currículo</h1>
            <p>A análise avalia clareza, organização e apresentação. A ABNT não tem uma norma obrigatória específica para currículos, então o resultado não é um certificado de conformidade.</p>
            <button class="resume-onboarding__button resume-onboarding__button--primary" type="button" data-abnt-run>Iniciar revisão</button>
            <p class="resume-review__status" data-abnt-status role="status" aria-live="polite">Seu arquivo será enviado ao Google Gemini para análise.</p>
            <div class="resume-review__result" data-abnt-result hidden></div>
            <a class="resume-onboarding__back" href="pessoa.php?pagina=curriculo">Ir para meu currículo</a>
        </section>
    </main>
    <script src="../js/curriculo-abnt.js?v=<?= filemtime(__DIR__ . '/../js/curriculo-abnt.js') ?>" defer></script>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script>
</body>
</html>