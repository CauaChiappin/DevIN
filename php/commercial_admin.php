<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Console restrito aos ADMs do DevIN comercial: edita rascunhos dos seis
 * planos e consulta quem assinou. Não publica planos nem executa cobranças.
 */
require_once __DIR__.'/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__.'/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__.'/helpers.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
$actor=requireWebAuth('adm'); // Explicação DevIN: Atribui um valor à variável PHP $actor.
?>
<!doctype html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= h($_SESSION['idioma'] ?? 'pt-BR') ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="utf-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="robots" content="noindex,nofollow"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>Gestão comercial | DevIN</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="stylesheet" href="../css/commercial-admin.css?v=<?= (int)filemtime(__DIR__.'/../css/commercial-admin.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
<main class="commercial-shell" data-commercial-admin
      data-endpoint="api/commercial/plans.php" data-csrf="<?= h(csrfToken()) ?>">
    <header class="commercial-head"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
        <a href="adm.php?pagina=perfil" aria-label="Voltar ao painel de administrador">← Voltar ao DevIN</a> <!-- Explicação DevIN: Declara link de navegação. Inclui atributos de acessibilidade. -->
        <span>DEVIN · ADMINISTRAÇÃO COMERCIAL</span> <!-- Explicação DevIN: Declara trecho em linha. -->
        <h1>Planos e assinaturas</h1> <!-- Explicação DevIN: Declara título principal. -->
        <p>Área de preparação. Preços e estoques são rascunhos até homologar o gateway, contratos e pagamentos.</p> <!-- Explicação DevIN: Declara parágrafo. -->
    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
    <div class="commercial-tabs" role="group" aria-label="Seções comerciais"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
        <button type="button" data-commercial-tab="plans" aria-pressed="true">Planos</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
        <button type="button" data-commercial-tab="subscribers" aria-pressed="false">Assinantes</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    <section data-commercial-pane="plans" aria-label="Planos de pessoa e empresa"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
        <p class="commercial-warning">Não há checkout ativo. Alterar preços aqui não cobra ninguém.</p> <!-- Explicação DevIN: Declara parágrafo. -->
        <div data-commercial-list class="commercial-grid" aria-live="polite">Carregando planos...</div> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    <section data-commercial-pane="subscribers" aria-label="Assinaturas cadastradas" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
        <p class="commercial-warning">Apenas assinaturas confirmadas por provedor devem conceder direitos.</p> <!-- Explicação DevIN: Declara parágrafo. -->
        <div data-commercial-subscribers aria-live="polite">Consultando assinantes...</div> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    <section class="commercial-editor" data-commercial-editor hidden aria-labelledby="commercial-edit-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
        <h2 id="commercial-edit-title">Editar rascunho do plano</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
        <p data-commercial-edit-target></p> <!-- Explicação DevIN: Declara parágrafo. -->
        <form data-commercial-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <div class="commercial-form-grid"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <label>Nome do plano<input name="title" maxlength="100" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Mensalidade em reais (somente rascunho)<input name="price_brl" type="number" min="0.01" step="0.01" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Limite de vagas ativas<input name="max_active_jobs" type="number" min="0" step="1" placeholder="Sem limite específico"></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Inscrições por mês<input name="max_applications_month" type="number" min="0" step="1" placeholder="Sem limite específico"></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Taxa B2B (%)<input name="placement_percent" type="number" min="0" max="5" step="0.01" required></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Estoque de assinaturas<input name="stock" type="number" min="0" step="1" placeholder="Sem limite específico"></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <div class="commercial-editor-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <button type="submit" data-commercial-save>Salvar rascunho e auditar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                <button type="button" data-commercial-cancel>Cancelar</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <p data-commercial-feedback role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    <p data-commercial-status role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
</main> <!-- Explicação DevIN: Fecha elemento main do documento. -->
<script src="../js/commercial-admin.js?v=<?= (int)filemtime(__DIR__.'/../js/commercial-admin.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
