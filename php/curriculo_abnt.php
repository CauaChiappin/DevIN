<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

requireWebAuth('pessoa'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
if (($_SESSION['resume_abnt_consent'] ?? false) !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: curriculo_upload.php?etapa=abnt'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Revisão do currículo</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/cadastrostyle.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/curriculo.css?v=<?= filemtime(__DIR__ . '/../css/curriculo.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body class="curriculo-page resume-onboarding-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="resume-onboarding"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
        <header class="cadastro-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <div class="brand-logo"><a href="index.php">Dev<span>IN</span></a></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
            <a class="header-action" href="logout.php">Sair</a> <!-- Explicação DevIN: Declara link de navegação. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
        <section class="resume-onboarding__panel resume-review" data-abnt-review data-csrf="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara seção temática. Contém dado dinâmico processado pelo PHP. -->
            <p class="resume-onboarding__eyebrow">REVISÃO ORIENTATIVA</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <h1>Revisar meu currículo</h1> <!-- Explicação DevIN: Declara título principal. -->
            <p>A análise avalia clareza, organização e apresentação. A ABNT não tem uma norma obrigatória específica para currículos, então o resultado não é um certificado de conformidade.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <button class="resume-onboarding__button resume-onboarding__button--primary" type="button" data-abnt-run>Iniciar revisão</button> <!-- Explicação DevIN: Declara botão de ação. -->
            <p class="resume-review__status" data-abnt-status role="status" aria-live="polite">Seu arquivo será enviado ao Google Gemini para análise.</p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
            <div class="resume-review__result" data-abnt-result hidden></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <a class="resume-onboarding__back" href="pessoa.php?pagina=curriculo">Ir para meu currículo</a> <!-- Explicação DevIN: Declara link de navegação. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->
    <script src="../js/curriculo-abnt.js?v=<?= filemtime(__DIR__ . '/../js/curriculo-abnt.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->