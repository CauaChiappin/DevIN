<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';

requireWebAuth('pessoa');

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Seu currículo</title>
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

        <section class="resume-onboarding__panel" aria-labelledby="onboarding-title">
            <p class="resume-onboarding__eyebrow">PRÓXIMA ETAPA</p>
            <h1 id="onboarding-title">Você já tem um currículo?</h1>
            <p>Adicione um arquivo que já está pronto ou crie seu currículo com ajuda da IA.</p>

            <div class="resume-onboarding__choices" data-onboarding-existing>
                <a class="resume-onboarding__choice resume-onboarding__choice--primary" href="curriculo_upload.php">
                    <span aria-hidden="true">↑</span>
                    <span><strong>Sim, já tenho</strong><small>Enviar PDF ou documento do Word</small></span>
                </a>
                <button class="resume-onboarding__choice" type="button" data-onboarding-no>
                    <span aria-hidden="true">＋</span>
                    <span><strong>Ainda não</strong><small>Escolher como continuar</small></span>
                </button>
            </div>

            <div class="resume-onboarding__followup" data-onboarding-followup hidden tabindex="-1">
                <h2>Quer criar um currículo com ajuda da IA?</h2>
                <div class="resume-onboarding__actions">
                    <a class="resume-onboarding__button resume-onboarding__button--primary" href="cadastrar_curriculo.php?ia=1">Sim, quero criar</a>
                    <a class="resume-onboarding__button" href="pessoa.php?pagina=curriculo&sem_curriculo=1">Não, continuar sem currículo</a>
                    <button class="resume-onboarding__back" type="button" data-onboarding-back>Voltar</button>
                </div>
            </div>
        </section>
    </main>
    <script src="../js/site-navigation.js"></script>
    <script>
        (() => {
            const choices = document.querySelector('[data-onboarding-existing]');
            const followup = document.querySelector('[data-onboarding-followup]');
            const noButton = document.querySelector('[data-onboarding-no]');
            const backButton = document.querySelector('[data-onboarding-back]');
            if (!(choices instanceof HTMLElement) || !(followup instanceof HTMLElement)
                || !(noButton instanceof HTMLButtonElement) || !(backButton instanceof HTMLButtonElement)) return;

            noButton.addEventListener('click', () => {
                choices.hidden = true;
                followup.hidden = false;
                followup.focus();
            });
            backButton.addEventListener('click', () => {
                followup.hidden = true;
                choices.hidden = false;
                noButton.focus();
            });
        })();
    </script>
</body>
</html>