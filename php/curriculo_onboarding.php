<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

requireWebAuth('pessoa'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Seu currículo</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
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

        <section class="resume-onboarding__panel" aria-labelledby="onboarding-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <p class="resume-onboarding__eyebrow">PRÓXIMA ETAPA</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <h1 id="onboarding-title">Você já tem um currículo?</h1> <!-- Explicação DevIN: Declara título principal. -->
            <p>Adicione um arquivo que já está pronto ou crie seu currículo com ajuda da IA.</p> <!-- Explicação DevIN: Declara parágrafo. -->

            <div class="resume-onboarding__choices" data-onboarding-existing> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a class="resume-onboarding__choice resume-onboarding__choice--primary" href="curriculo_upload.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                    <span aria-hidden="true">↑</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                    <span><strong>Sim, já tenho</strong><small>Enviar PDF ou documento do Word</small></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
                <button class="resume-onboarding__choice" type="button" data-onboarding-no> <!-- Explicação DevIN: Declara botão de ação. -->
                    <span aria-hidden="true">＋</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                    <span><strong>Ainda não</strong><small>Escolher como continuar</small></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <div class="resume-onboarding__followup" data-onboarding-followup hidden tabindex="-1"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <h2>Quer criar um currículo com ajuda da IA?</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <div class="resume-onboarding__actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <a class="resume-onboarding__button resume-onboarding__button--primary" href="cadastrar_curriculo.php?ia=1">Sim, quero criar</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <a class="resume-onboarding__button" href="pessoa.php?pagina=curriculo&sem_curriculo=1">Não, continuar sem currículo</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <button class="resume-onboarding__back" type="button" data-onboarding-back>Voltar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->
    <script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script>
        (() => { // Explicação DevIN: Continua expressão ou bloco pertencente a marcação script.
            const choices = document.querySelector('[data-onboarding-existing]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const followup = document.querySelector('[data-onboarding-followup]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const noButton = document.querySelector('[data-onboarding-no]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const backButton = document.querySelector('[data-onboarding-back]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (!(choices instanceof HTMLElement) || !(followup instanceof HTMLElement) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || !(noButton instanceof HTMLButtonElement) || !(backButton instanceof HTMLButtonElement)) return; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

            noButton.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                choices.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                followup.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                followup.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a marcação script.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            backButton.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                followup.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                choices.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                noButton.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a marcação script.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        })(); // Explicação DevIN: Continua expressão ou bloco pertencente a marcação script.
    </script>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->