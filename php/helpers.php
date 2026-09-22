<?php
function ativo(string $paginaAtual, string $pagina): string
{
    return $paginaAtual === $pagina ? 'ativo' : '';
}

function h(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Cabeçalho compacto compartilhado pelas listas dos dashboards. */
function dashboardListHeader(string $pagina, string $titulo, string $placeholder, string $contador = ''): string
{
    $countMarkup = $contador === ''
        ? ''
        : '<span class="result-count">' . h($contador) . '</span>';

    return '<header class="dashboard-header compact-list-header">'
        . '<h1>' . h($titulo) . '</h1>'
        . '<form class="busca" method="get">'
        . '<input type="hidden" name="pagina" value="' . h($pagina) . '">'
        . '<label class="compact-search-field"><span class="sr-only">Pesquisar</span>'
        . '<input type="search" name="q" placeholder="' . h($placeholder) . '"></label>'
        . '<button class="filter-button" type="button" data-toggle-filters aria-expanded="false">Filtros</button>'
        . '<label class="filter-menu" data-filter-menu hidden><span>Filtrar por</span>'
        . '<select data-card-filter aria-label="Filtrar cards"></select></label>'
        . '</form>'
        . $countMarkup
        . '</header>';
}

/** Transforma os rótulos de um card em etiquetas compactas e seguras. */
function dashboardCardTags(string $tags): string
{
    $items = array_values(array_filter(array_map('trim', explode('|', $tags))));
    if ($items === []) {
        return '';
    }

    $markup = array_map(static fn(string $item): string => '<li>' . h($item) . '</li>', $items);
    return '<ul class="card-tags" aria-label="Detalhes do card">' . implode('', $markup) . '</ul>';
}

/** Prepara os dados do currículo para o painel de detalhes do administrador. */
function dashboardCandidateDetails(array $candidate): array
{
    $text = static fn(string $key): string => trim((string) ($candidate[$key] ?? ''));
    $splitSkills = static function (string $value): array {
        $items = preg_split('/[\r\n,;|]+/u', $value) ?: [];
        $items = array_filter(array_map('trim', $items), static fn(string $item): bool => $item !== '');
        return array_values(array_unique($items));
    };

    $accountName = $text('nome');
    $socialName = $text('nome_social');
    $education = $text('grau_de_escolaridade') ?: 'Não informada';
    $skills = array_merge($splitSkills($text('cursos')), $splitSkills($text('idiomas')));
    $skills = array_values(array_unique($skills));
    $experience = $text('experiencia') ?: 'Nenhuma experiência informada.';

    return [
        'name' => $socialName ?: ($accountName ?: 'Candidato'),
        'summary' => 'E-mail: ' . $text('email')
            . ' | CPF: ' . $text('cpf')
            . ' | Escolaridade: ' . $education,
        'tags' => implode('|', $skills ?: ['Nenhuma habilidade informada']),
        'experience' => 'Experiência profissional::' . str_replace(['|', '::'], '—', $experience),
    ];
}

/** Conteúdo institucional compartilhado pelos três dashboards. */
function aboutPage(): string
{
    return <<<'HTML'
<div class="sobre-container">
    <div class="sobre-top-section">
        <span class="badge-historia">Nossa historia</span>
        <h1 class="sobre-title">Conectando talentos ao <span class="text-blue palavra-rotativa" data-rotating-word aria-live="polite">futuro</span><br>da tecnologia</h1>
        <p class="sobre-subtitle">A DevIN nasceu para transformar a forma como desenvolvedores<br>encontram oportunidades - "simples, rapido e eficiente".</p>
        <div class="sobre-cards">
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true">⌁</span><h3>Visão</h3><p>Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.</p></article>
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true">◎</span><h3>Missão</h3><p>Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.</p></article>
        </div>
    </div>
    <div class="sobre-bottom-section">
        <div class="sobre-about-text"><h2>Quem somos <span class="text-blue">nós?</span></h2><p>A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.</p><p>Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.</p></div>
        <section class="sobre-team-card"><h2 class="team-header">Time fundador</h2><ul class="team-list"><li><img class="team-avatar" src="../img/caua.jpg" alt="Foto temporária de Cauã Chiappin de Lima"><div class="team-info"><strong>Cauã Chiappin de Lima</strong><a href="mailto:caua.lima@scseduca.com.br">Cofundador · caua.lima@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/enzo.jpg" alt="Foto temporária de Enzo Vasconcelos de Camargo"><div class="team-info"><strong>Enzo Vasconcelos de Camargo</strong><a href="mailto:enzo.camargo@scseduca.com.br">Cofundador · enzo.camargo@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/joao.jpg" alt="Foto temporária de João Vitor da Silva e Sousa"><div class="team-info"><strong>João Vitor da Silva e Sousa</strong><a href="mailto:joao.sousa2@scseduca.com.br">Cofundador · joao.sousa2@scseduca.com.br</a></div></li></ul></section>
    </div>
</div>
HTML;
}

/*
 * Monta o avatar reutilizado no menu, no modal e no perfil.
 * Se nÃ£o houver foto vÃ¡lida, retorna somente o span com o fundo padrÃ£o do CSS.
 */
function profileAvatar(array $perfil, string $classes): string
{
    // Pega o caminho salvo no banco; string vazia Ã© usada quando nÃ£o existe foto.
    $foto = $perfil['foto'] ?? '';

    // A foto da empresa agora fica no MEDIUMBLOB; transforma os bytes em uma imagem visivel no navegador.
    if (is_string($foto) && $foto !== '' && !str_starts_with($foto, 'uploads/')) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($foto);
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $imagem = '<img src="data:' . $mime . ';base64,' . base64_encode($foto) . '" alt="Foto de perfil">';
            return '<span class="' . h($classes) . ' current-user-avatar has-photo">' . $imagem . '</span>';
        }
    }
    // SÃ³ renderiza a tag img para caminhos de upload existentes; caso contrÃ¡rio, mantÃ©m o avatar padrÃ£o.
    $imagem = is_string($foto) && str_starts_with($foto, 'uploads/') && is_file(__DIR__ . '/' . $foto)
        // h() escapa o caminho antes de colocÃ¡-lo no HTML, evitando injeÃ§Ã£o de cÃ³digo.
        ? '<img src="' . h($foto) . '" alt="Foto de perfil">'
        : '';

    // Junta as classes visuais e a imagem (quando vÃ¡lida) dentro de um Ãºnico avatar.
    return '<span class="' . h($classes) . ' current-user-avatar' . ($imagem !== '' ? ' has-photo' : '') . '">' . $imagem . '</span>';
}

/** Painel de detalhes compartilhado pelas areas de listagem dos dashboards. */
function dashboardDetailPanel(): string
{
    return <<<'HTML'
<aside class="detalhe-area profile-detail-panel" aria-label="Detalhes do item selecionado">
    <div class="detail-placeholder" data-detail-placeholder>
        <span class="detail-placeholder-icon" aria-hidden="true">DI</span>
        <h2>Selecione um card</h2>
        <p>As informacoes do perfil, vaga ou candidatura aparecerao aqui.</p>
    </div>
    <article class="detail-profile" data-detail-content hidden>
        <header class="detail-profile-header">
            <span class="detail-avatar" data-detail-avatar aria-hidden="true">DI</span>
            <div class="detail-identity">
                <h2 data-detail-name>Detalhes</h2>
                <p data-detail-role></p>
                <p class="detail-meta" data-detail-meta>DevIN</p>
            </div>
            <button class="detail-action" type="button" data-detail-action hidden></button>
        </header>
        <section class="detail-section">
            <h3>Sobre</h3>
            <p data-detail-summary></p>
        </section>
        <section class="detail-section">
            <h3>Habilidades</h3>
            <ul class="detail-tags" data-detail-tags></ul>
        </section>
        <section class="detail-section">
            <h3>Experiencia</h3>
            <ul class="detail-timeline" data-detail-experience></ul>
        </section>
    </article>
</aside>
HTML;
}

function dashboardIcon(string $name): string
{
    $paths = match ($name) {
        'brand' => '<path d="M4 5.5h16v13H4z"/><path d="M12 5.5v13"/>',
        'home' => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building' => '<path d="M3 21h18M5 21V5h10v16M15 9h4v12M8 9h4M8 13h4M8 17h4"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-3v-.08A1.7 1.7 0 0 0 10.68 18.36a1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.02 14.7 1.7 1.7 0 0 0 5.46 13.7H5v-3h.08a1.7 1.7 0 0 0 1.56-1.03A1.7 1.7 0 0 0 6.3 7.8l-.06-.06 2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.3 4.46V4h3v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.56 1.03H20v3h-.08A1.7 1.7 0 0 0 18.36 14.36z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>',
        default => '',
    };

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}
?>

