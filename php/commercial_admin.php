<?php
declare(strict_types=1);

/**
 * Console restrito aos ADMs do DevIN comercial: edita rascunhos dos seis
 * planos e consulta quem assinou. Não publica planos nem executa cobranças.
 */
require_once __DIR__.'/middlewares/auth.php';
require_once __DIR__.'/config/security.php';
require_once __DIR__.'/helpers.php';
$actor=requireWebAuth('adm');
?>
<!doctype html>
<html lang="<?= h($_SESSION['idioma'] ?? 'pt-BR') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Gestão comercial | DevIN</title>
    <link rel="stylesheet" href="../css/commercial-admin.css?v=<?= (int)filemtime(__DIR__.'/../css/commercial-admin.css') ?>">
</head>
<body>
<main class="commercial-shell" data-commercial-admin
      data-endpoint="api/commercial/plans.php" data-csrf="<?= h(csrfToken()) ?>">
    <header class="commercial-head">
        <a href="adm.php?pagina=perfil" aria-label="Voltar ao painel de administrador">← Voltar ao DevIN</a>
        <span>DEVIN · ADMINISTRAÇÃO COMERCIAL</span>
        <h1>Planos e assinaturas</h1>
        <p>Área de preparação. Preços e estoques são rascunhos até homologar o gateway, contratos e pagamentos.</p>
    </header>
    <div class="commercial-tabs" role="group" aria-label="Seções comerciais">
        <button type="button" data-commercial-tab="plans" aria-pressed="true">Planos</button>
        <button type="button" data-commercial-tab="subscribers" aria-pressed="false">Assinantes</button>
    </div>
    <section data-commercial-pane="plans" aria-label="Planos de pessoa e empresa">
        <p class="commercial-warning">Não há checkout ativo. Alterar preços aqui não cobra ninguém.</p>
        <div data-commercial-list class="commercial-grid" aria-live="polite">Carregando planos...</div>
    </section>
    <section data-commercial-pane="subscribers" aria-label="Assinaturas cadastradas" hidden>
        <p class="commercial-warning">Apenas assinaturas confirmadas por provedor devem conceder direitos.</p>
        <div data-commercial-subscribers aria-live="polite">Consultando assinantes...</div>
    </section>
    <section class="commercial-editor" data-commercial-editor hidden aria-labelledby="commercial-edit-title">
        <h2 id="commercial-edit-title">Editar rascunho do plano</h2>
        <p data-commercial-edit-target></p>
        <form data-commercial-form>
            <div class="commercial-form-grid">
                <label>Nome do plano<input name="title" maxlength="100" required></label>
                <label>Mensalidade em reais (somente rascunho)<input name="price_brl" type="number" min="0.01" step="0.01" required></label>
                <label>Limite de vagas ativas<input name="max_active_jobs" type="number" min="0" step="1" placeholder="Sem limite específico"></label>
                <label>Inscrições por mês<input name="max_applications_month" type="number" min="0" step="1" placeholder="Sem limite específico"></label>
                <label>Taxa B2B (%)<input name="placement_percent" type="number" min="0" max="5" step="0.01" required></label>
                <label>Estoque de assinaturas<input name="stock" type="number" min="0" step="1" placeholder="Sem limite específico"></label>
            </div>
            <div class="commercial-editor-actions">
                <button type="submit" data-commercial-save>Salvar rascunho e auditar</button>
                <button type="button" data-commercial-cancel>Cancelar</button>
            </div>
            <p data-commercial-feedback role="status" aria-live="polite"></p>
        </form>
    </section>
    <p data-commercial-status role="status" aria-live="polite"></p>
</main>
<script src="../js/commercial-admin.js?v=<?= (int)filemtime(__DIR__.'/../js/commercial-admin.js') ?>" defer></script>
</body>
</html>
