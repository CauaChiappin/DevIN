<?php
declare(strict_types=1); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.

/** Catálogo de talentos: somente dados profissionais e fotos, com paginação. */
$talentTerm = is_string($_GET['talento_q'] ?? null) ? trim($_GET['talento_q']) : ''; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentTerm = mb_substr($talentTerm, 0, 100, 'UTF-8'); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentPage = filter_var($_GET['talento_pagina'] ?? 1, FILTER_VALIDATE_INT); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentPage = $talentPage !== false ? min(1000, max(1, $talentPage)) : 1; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentLimit = 24; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentTotal = 0; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talents = []; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
$talentError = ''; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
try { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    $talentDb = getDatabaseConnection(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    try { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentJoin = ' FROM pessoa p LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa'; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentWhere = $talentTerm === '' ? '' : ' WHERE (p.nome LIKE ? OR cu.cursos LIKE ? OR cu.experiencia LIKE ? OR cu.grau_de_escolaridade LIKE ?)'; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentCountStmt = $talentDb->prepare('SELECT COUNT(DISTINCT p.id_pessoa)' . $talentJoin . $talentWhere); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentPattern = '%' . $talentTerm . '%'; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        if ($talentWhere !== '') { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            $talentCountStmt->bind_param('ssss', $talentPattern, $talentPattern, $talentPattern, $talentPattern); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        } // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentCountStmt->execute(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentTotal = (int) $talentCountStmt->get_result()->fetch_row()[0]; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentCountStmt->close(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentMaxPage = max(1, (int) ceil($talentTotal / $talentLimit)); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentPage = min($talentPage, $talentMaxPage); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentOffset = ($talentPage - 1) * $talentLimit; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentSql = 'SELECT p.id_pessoa AS id, p.nome, p.foto, ' // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            . 'COALESCE(cu.grau_de_escolaridade, "") AS escolaridade, ' // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            . 'COALESCE(cu.cursos, "") AS cursos, ' // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            . 'COALESCE(cu.experiencia, "") AS experiencia, ' // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            . 'COALESCE(cu.idiomas, "") AS idiomas' // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            . $talentJoin . $talentWhere . ' ORDER BY p.id_pessoa DESC LIMIT ? OFFSET ?'; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentStmt = $talentDb->prepare($talentSql); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        if ($talentWhere !== '') { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            $talentStmt->bind_param('ssssii', $talentPattern, $talentPattern, $talentPattern, $talentPattern, $talentLimit, $talentOffset); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        } else { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
            $talentStmt->bind_param('ii', $talentLimit, $talentOffset); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        } // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentStmt->execute(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talents = $talentStmt->get_result()->fetch_all(MYSQLI_ASSOC); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentStmt->close(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    } finally { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
        $talentDb->close(); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    } // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
} catch (Throwable $exception) { // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    error_log('Catálogo de talentos DevIN: ' . get_class($exception)); // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
    $talentError = 'Não foi possível carregar os candidatos agora. Tente novamente.'; // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
} // Detalhe DevIN: Executa a consulta e organiza os perfis profissionais.
?>
<section class="company-talents" aria-labelledby="companyTalentsHeading" data-company-talents> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <header class="company-talents__header"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <h2 id="companyTalentsHeading">Encontrar candidatos</h2> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <p>Conheça os perfis profissionais cadastrados no DevIN, mesmo sem inscrição em suas vagas.</p> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <strong><?= number_format($talentTotal, 0, ',', '.') ?> perfis</strong> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    </header> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <form method="get" action="empresa.php" class="company-talents__search"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <input type="hidden" name="pagina" value="inicio"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <label for="companyTalentQuery">Pesquisar por nome, formação ou habilidade</label> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <div class="company-talents__search-row"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <input id="companyTalentQuery" type="search" name="talento_q" value="<?= h($talentTerm) ?>" maxlength="100" placeholder="Ex.: PHP, design, João" autocomplete="off"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <button class="btn primary" type="submit">Pesquisar</button> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <?php if ($talentTerm !== ''): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                <a class="btn secondary" href="empresa.php?pagina=inicio#companyTalentsHeading">Limpar</a> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <?php endif; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
        </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    </form> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <?php if ($talentError !== ''): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
        <p class="form-error" role="alert"><?= h($talentError) ?></p> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <?php elseif (!$talents): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
        <p class="empty-state">Nenhum candidato encontrado.</p> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <?php else: ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
        <div class="company-talents__grid"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <?php foreach ($talents as $talent): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                <article class="company-talent-card"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                    <button type="button" class="company-talent-card__open" data-talent-open <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                        data-talent-id="<?= (int) $talent['id'] ?>"
                        data-talent-name="<?= h((string) $talent['nome']) ?>"
                        data-talent-education="<?= h((string) $talent['escolaridade']) ?>"
                        data-talent-courses="<?= h((string) $talent['cursos']) ?>"
                        data-talent-experience="<?= h((string) $talent['experiencia']) ?>"
                        data-talent-languages="<?= h((string) $talent['idiomas']) ?>"
                        aria-label="Abrir perfil de <?= h((string) $talent['nome']) ?>">
                        <?= profileAvatar($talent, 'company-talent-avatar') ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                        <span class="company-talent-card__copy"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                            <strong><?= h((string) $talent['nome']) ?></strong> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                            <small><?= h((string) ($talent['escolaridade'] ?: 'Formação não informada')) ?></small> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                            <span><?= h(mb_strimwidth((string) ($talent['cursos'] ?: 'Clique para conhecer o perfil profissional.'), 0, 110, '…', 'UTF-8')) ?></span> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                        </span> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                        <span class="company-talent-card__cta">Ver perfil</span> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                    </button> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                </article> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <?php endforeach; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
        </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <?php if ($talentMaxPage > 1): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
            <nav class="company-talents__pages" aria-label="Páginas de candidatos"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <?php if ($talentPage > 1): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                    <a href="empresa.php?<?= h(http_build_query(['pagina'=>'inicio','talento_q'=>$talentTerm,'talento_pagina'=>$talentPage - 1])) ?>#companyTalentsHeading">Anterior</a> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <?php endif; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                <span>Página <?= (int) $talentPage ?> de <?= (int) $talentMaxPage ?></span> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <?php if ($talentPage < $talentMaxPage): ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
                    <a href="empresa.php?<?= h(http_build_query(['pagina'=>'inicio','talento_q'=>$talentTerm,'talento_pagina'=>$talentPage + 1])) ?>#companyTalentsHeading">Próxima</a> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <?php endif; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
            </nav> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <?php endif; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
    <?php endif; ?> <!-- Detalhe DevIN: Apresenta dados somente na interface autorizada. -->
</section> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
<dialog class="company-talent-dialog" data-talent-dialog aria-labelledby="companyTalentProfileName"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    <div class="company-talent-dialog__content"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <button type="button" class="company-talent-dialog__close" data-talent-close aria-label="Fechar perfil">×</button> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <div class="company-talent-dialog__identity"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <span data-talent-avatar></span> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <h2 id="companyTalentProfileName" data-talent-name></h2> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
                <p>Candidato DevIN</p> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <dl class="company-talent-dialog__details"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <div><dt>Formação</dt><dd data-talent-education></dd></div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <div><dt>Cursos e habilidades</dt><dd data-talent-courses></dd></div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <div><dt>Experiência profissional</dt><dd data-talent-experience></dd></div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <div><dt>Idiomas</dt><dd data-talent-languages></dd></div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        </dl> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <div class="company-talent-dialog__actions"> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <a class="btn primary" data-talent-chat href="empresa.php?pagina=mensagens">Conversar</a> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
            <a class="btn secondary" data-talent-report href="empresa.php?pagina=mensagens">Reportar perfil</a> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
        <p class="company-talent-dialog__privacy">O contato ocorre pelo chat do DevIN; dados pessoais privados não são divulgados aqui.</p> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
    </div> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
</dialog> <!-- Detalhe DevIN: Estrutura o catálogo e o perfil dos candidatos. -->
