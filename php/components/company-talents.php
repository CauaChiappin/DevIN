<?php
declare(strict_types=1);

/** Catálogo de talentos: somente dados profissionais e fotos, com paginação. */
$talentTerm = is_string($_GET['talento_q'] ?? null) ? trim($_GET['talento_q']) : '';
$talentTerm = mb_substr($talentTerm, 0, 100, 'UTF-8');
$talentPage = filter_var($_GET['talento_pagina'] ?? 1, FILTER_VALIDATE_INT);
$talentPage = $talentPage !== false ? min(1000, max(1, $talentPage)) : 1;
$talentLimit = 24;
$talentTotal = 0;
$talents = [];
$talentError = '';
try {
    $talentDb = getDatabaseConnection();
    try {
        $talentJoin = ' FROM pessoa p LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa';
        $talentWhere = $talentTerm === '' ? '' : ' WHERE (p.nome LIKE ? OR cu.cursos LIKE ? OR cu.experiencia LIKE ? OR cu.grau_de_escolaridade LIKE ?)';
        $talentCountStmt = $talentDb->prepare('SELECT COUNT(DISTINCT p.id_pessoa)' . $talentJoin . $talentWhere);
        $talentPattern = '%' . $talentTerm . '%';
        if ($talentWhere !== '') {
            $talentCountStmt->bind_param('ssss', $talentPattern, $talentPattern, $talentPattern, $talentPattern);
        }
        $talentCountStmt->execute();
        $talentTotal = (int) $talentCountStmt->get_result()->fetch_row()[0];
        $talentCountStmt->close();
        $talentMaxPage = max(1, (int) ceil($talentTotal / $talentLimit));
        $talentPage = min($talentPage, $talentMaxPage);
        $talentOffset = ($talentPage - 1) * $talentLimit;
        $talentSql = 'SELECT p.id_pessoa AS id, p.nome, p.foto, '
            . 'COALESCE(cu.grau_de_escolaridade, "") AS escolaridade, '
            . 'COALESCE(cu.cursos, "") AS cursos, '
            . 'COALESCE(cu.experiencia, "") AS experiencia, '
            . 'COALESCE(cu.idiomas, "") AS idiomas'
            . $talentJoin . $talentWhere . ' ORDER BY p.id_pessoa DESC LIMIT ? OFFSET ?';
        $talentStmt = $talentDb->prepare($talentSql);
        if ($talentWhere !== '') {
            $talentStmt->bind_param('ssssii', $talentPattern, $talentPattern, $talentPattern, $talentPattern, $talentLimit, $talentOffset);
        } else {
            $talentStmt->bind_param('ii', $talentLimit, $talentOffset);
        }
        $talentStmt->execute();
        $talents = $talentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $talentStmt->close();
    } finally {
        $talentDb->close();
    }
} catch (Throwable $exception) {
    error_log('Catálogo de talentos DevIN: ' . get_class($exception));
    $talentError = 'Não foi possível carregar os candidatos agora. Tente novamente.';
}
?>
<section class="company-talents" aria-labelledby="companyTalentsHeading" data-company-talents>
    <header class="company-talents__header">
        <div>
            <h2 id="companyTalentsHeading">Encontrar candidatos</h2>
            <p>Conheça os perfis profissionais cadastrados no DevIN, mesmo sem inscrição em suas vagas.</p>
        </div>
        <strong><?= number_format($talentTotal, 0, ',', '.') ?> perfis</strong>
    </header>
    <form method="get" action="empresa.php" class="company-talents__search">
        <input type="hidden" name="pagina" value="inicio">
        <label for="companyTalentQuery">Pesquisar por nome, formação ou habilidade</label>
        <div class="company-talents__search-row">
            <input id="companyTalentQuery" type="search" name="talento_q" value="<?= h($talentTerm) ?>" maxlength="100" placeholder="Ex.: PHP, design, João" autocomplete="off">
            <button class="btn primary" type="submit">Pesquisar</button>
            <?php if ($talentTerm !== ''): ?>
                <a class="btn secondary" href="empresa.php?pagina=inicio#companyTalentsHeading">Limpar</a>
            <?php endif; ?>
        </div>
    </form>
    <?php if ($talentError !== ''): ?>
        <p class="form-error" role="alert"><?= h($talentError) ?></p>
    <?php elseif (!$talents): ?>
        <p class="empty-state">Nenhum candidato encontrado.</p>
    <?php else: ?>
        <div class="company-talents__grid">
            <?php foreach ($talents as $talent): ?>
                <article class="company-talent-card">
                    <button type="button" class="company-talent-card__open" data-talent-open
                        data-talent-id="<?= (int) $talent['id'] ?>"
                        data-talent-name="<?= h((string) $talent['nome']) ?>"
                        data-talent-education="<?= h((string) $talent['escolaridade']) ?>"
                        data-talent-courses="<?= h((string) $talent['cursos']) ?>"
                        data-talent-experience="<?= h((string) $talent['experiencia']) ?>"
                        data-talent-languages="<?= h((string) $talent['idiomas']) ?>"
                        aria-label="Abrir perfil de <?= h((string) $talent['nome']) ?>">
                        <?= profileAvatar($talent, 'company-talent-avatar') ?>
                        <span class="company-talent-card__copy">
                            <strong><?= h((string) $talent['nome']) ?></strong>
                            <small><?= h((string) ($talent['escolaridade'] ?: 'Formação não informada')) ?></small>
                            <span><?= h(mb_strimwidth((string) ($talent['cursos'] ?: 'Clique para conhecer o perfil profissional.'), 0, 110, '…', 'UTF-8')) ?></span>
                        </span>
                        <span class="company-talent-card__cta">Ver perfil</span>
                    </button>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($talentMaxPage > 1): ?>
            <nav class="company-talents__pages" aria-label="Páginas de candidatos">
                <?php if ($talentPage > 1): ?>
                    <a href="empresa.php?<?= h(http_build_query(['pagina'=>'inicio','talento_q'=>$talentTerm,'talento_pagina'=>$talentPage - 1])) ?>#companyTalentsHeading">Anterior</a>
                <?php endif; ?>
                <span>Página <?= (int) $talentPage ?> de <?= (int) $talentMaxPage ?></span>
                <?php if ($talentPage < $talentMaxPage): ?>
                    <a href="empresa.php?<?= h(http_build_query(['pagina'=>'inicio','talento_q'=>$talentTerm,'talento_pagina'=>$talentPage + 1])) ?>#companyTalentsHeading">Próxima</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<dialog class="company-talent-dialog" data-talent-dialog aria-labelledby="companyTalentProfileName">
    <div class="company-talent-dialog__content">
        <button type="button" class="company-talent-dialog__close" data-talent-close aria-label="Fechar perfil">×</button>
        <div class="company-talent-dialog__identity">
            <span data-talent-avatar></span>
            <div>
                <h2 id="companyTalentProfileName" data-talent-name></h2>
                <p>Candidato DevIN</p>
            </div>
        </div>
        <dl class="company-talent-dialog__details">
            <div><dt>Formação</dt><dd data-talent-education></dd></div>
            <div><dt>Cursos e habilidades</dt><dd data-talent-courses></dd></div>
            <div><dt>Experiência profissional</dt><dd data-talent-experience></dd></div>
            <div><dt>Idiomas</dt><dd data-talent-languages></dd></div>
        </dl>
        <div class="company-talent-dialog__actions">
            <a class="btn primary" data-talent-chat href="empresa.php?pagina=mensagens">Conversar</a>
            <a class="btn secondary" data-talent-report href="empresa.php?pagina=mensagens">Reportar perfil</a>
        </div>
        <p class="company-talent-dialog__privacy">O contato ocorre pelo chat do DevIN; dados pessoais privados não são divulgados aqui.</p>
    </div>
</dialog>
