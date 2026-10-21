<?php

declare(strict_types=1);

/**
 * Retorna a classe 'ativo' caso a página fornecida seja a página atual.
 */
function ativo(string $paginaAtual, string $pagina): string
{
    return $paginaAtual === $pagina ? 'ativo' : '';
}

/**
 * Escapa strings contra XSS (Cross-Site Scripting).
 */
function h(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** 
 * Cabeçalho compacto compartilhado pelas listas dos dashboards.
 */
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

/** 
 * Transforma os rótulos de um card em etiquetas compactas e seguras.
 */
function dashboardCardTags(string $tags): string
{
    $items = array_values(array_filter(array_map('trim', explode('|', $tags))));
    if ($items === []) {
        return '';
    }

    $markup = array_map(static fn(string $item): string => '<li>' . h($item) . '</li>', $items);
    return '<ul class="card-tags" aria-label="Detalhes do card">' . implode('', $markup) . '</ul>';
}

/** 
 * Prepara os dados do currículo para o painel de detalhes do administrador.
 */
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

/** 
 * Conteúdo institucional compartilhado pelos três dashboards.
 */
function aboutPage(): string
{
    return <<<'HTML'
<div class="sobre-container">
    <div class="sobre-top-section">
        <span class="badge-historia">Nossa história</span>
        <h1 class="sobre-title">Conectando talentos ao <span class="text-blue palavra-rotativa" data-rotating-word aria-live="polite">futuro</span><br>da tecnologia</h1>
        <p class="sobre-subtitle">A DevIN nasceu para transformar a forma como desenvolvedores<br>encontram oportunidades - "simples, rápido e eficiente".</p>
        <div class="sobre-cards">
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true">⌁</span><h3>Visão</h3><p>Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.</p></article>
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true">◎</span><h3>Missão</h3><p>Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.</p></article>
        </div>
    </div>
    <div class="sobre-bottom-section">
        <div class="sobre-about-text"><h2>Quem somos <span class="text-blue">nós?</span></h2><p>A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.</p><p>Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.</p></div>
        <section class="sobre-team-card"><h2 class="team-header">Time fundador</h2><ul class="team-list"><li><img class="team-avatar" src="../img/caua.jpg" alt="Foto de Cauã Chiappin de Lima"><div class="team-info"><strong>Cauã Chiappin de Lima</strong><a href="mailto:caua.lima@scseduca.com.br">Cofundador · caua.lima@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/enzo.jpg" alt="Foto de Enzo Vasconcelos de Camargo"><div class="team-info"><strong>Enzo Vasconcelos de Camargo</strong><a href="mailto:enzo.camargo@scseduca.com.br">Cofundador · enzo.camargo@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/joao.jpg" alt="Foto de João Vitor da Silva e Sousa"><div class="team-info"><strong>João Vitor da Silva e Sousa</strong><a href="mailto:joao.sousa2@scseduca.com.br">Cofundador · joao.sousa2@scseduca.com.br</a></div></li></ul></section>
    </div>
</div>
HTML;
}

/**
 * Resolve fotos armazenadas no diretório de uploads, inclusive nomes legados.
 */
function profilePhotoFilePath(string $foto, ?string $tipo = null, ?int $id = null): ?string
{
    if (str_starts_with($foto, 'uploads/')) {
        $caminhoRelativo = substr($foto, strlen('uploads/'));
    } elseif (preg_match('/^(pessoa|empresa)-(\d+)-[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/D', $foto, $matches) === 1) {
        if (($tipo !== null && $matches[1] !== $tipo) || ($id !== null && (int) $matches[2] !== $id)) {
            return null;
        }
        $caminhoRelativo = $foto;
    } else {
        return null;
    }

    $diretorioUploads = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads');
    if ($diretorioUploads === false) {
        return null;
    }

    $caminhoArquivo = realpath(
        $diretorioUploads . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $caminhoRelativo)
    );
    $prefixoUploads = rtrim($diretorioUploads, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    return $caminhoArquivo !== false
        && is_file($caminhoArquivo)
        && str_starts_with(strtolower($caminhoArquivo), strtolower($prefixoUploads))
        ? $caminhoArquivo
        : null;
}

/**
 * Monta o avatar reutilizado no menu, no modal e no perfil.
 * Se não houver foto válida, exibe as iniciais do nome como avatar padrão.
 */
function profileAvatar(array $perfil, string $classes): string
{
    static $finfo = null;
    $foto = $perfil['foto'] ?? '';
    $caminhoArquivo = is_string($foto) ? profilePhotoFilePath($foto) : null;

    // Trata imagens armazenadas em BLOB (Bytes brutos no banco)
    if (is_string($foto) && $foto !== '' && $caminhoArquivo === null) {
        if ($finfo === null) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }
        $mime = $finfo->buffer($foto);

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $imagem = '<img src="data:' . $mime . ';base64,' . base64_encode($foto) . '" alt="Foto de perfil">';
            return '<span class="' . h($classes) . ' current-user-avatar has-photo">' . $imagem . '</span>';
        }
    }

    $caminhoPublico = $caminhoArquivo === null
        ? ''
        : (str_starts_with($foto, 'uploads/') ? $foto : 'uploads/' . basename($caminhoArquivo));
    $imagem = $caminhoPublico !== ''
        ? '<img src="' . h($caminhoPublico) . '" alt="Foto de perfil">'
        : '';

    $partesNome = preg_split('/\s+/u', trim((string) ($perfil['nome'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $iniciais = '';
    foreach (array_slice($partesNome, 0, 2) as $parte) {
        if (preg_match('/^./u', $parte, $matches) === 1) {
            $iniciais .= $matches[0];
        }
    }
    $conteudo = $imagem !== '' ? $imagem : h($iniciais !== '' ? $iniciais : 'DI');

    return '<span class="' . h($classes) . ' current-user-avatar' . ($imagem !== '' ? ' has-photo' : '') . '">' . $conteudo . '</span>';
}

/** 
 * Painel de detalhes compartilhado pelas áreas de listagem dos dashboards.
 */
function dashboardDetailPanel(): string
{
    return <<<'HTML'
<aside class="detalhe-area profile-detail-panel" aria-label="Detalhes do item selecionado">
    <div class="detail-placeholder" data-detail-placeholder>
        <span class="detail-placeholder-icon" aria-hidden="true">DI</span>
        <h2>Selecione um card</h2>
        <p>As informações do perfil, vaga ou candidatura aparecerão aqui.</p>
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
            <button class="detail-chat-action" type="button" data-start-chat hidden>Conversar</button>
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
            <h3>Experiência</h3>
            <ul class="detail-timeline" data-detail-experience></ul>
        </section>
    </article>
</aside>
HTML;
}

/**
 * Retorna os caminhos SVG dos ícones da interface.
 */
function dashboardIcon(string $name): string
{
    $paths = match ($name) {
        'brand'    => '<path d="M4 5.5h16v13H4z"/><path d="M12 5.5v13"/>',
        'home'     => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>',
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building' => '<path d="M3 21h18M5 21V5h10v16M15 9h4v12M8 9h4M8 13h4M8 17h4"/>',
        'briefcase'=> '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33h-.08A1.65 1.65 0 0 0 14 20.91V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82v-.08A1.65 1.65 0 0 0 3.09 14H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.08A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.08A1.65 1.65 0 0 0 20.91 10H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/>',
        'logout'   => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>',
        default    => '',
    };

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}