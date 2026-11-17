<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/helpers.php
 * Finalidade: helpers.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * Retorna a classe 'ativo' caso a página fornecida seja a página atual.
 */
// Detalhe DevIN: Método ativo: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function ativo(string $paginaAtual, string $pagina): string // Explicação DevIN: Declara função/método ativo, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return $paginaAtual === $pagina ? 'ativo' : ''; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Escapa strings contra XSS (Cross-Site Scripting).
 */
// Detalhe DevIN: Método h: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function h(?string $valor): string // Explicação DevIN: Declara função/método h, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** 
 * Cabeçalho compacto compartilhado pelas listas dos dashboards.
 */
// Detalhe DevIN: Método dashboardListHeader: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dashboardListHeader(string $pagina, string $titulo, string $placeholder, string $contador = '', bool $filtrosCandidatos = false): string // Explicação DevIN: Declara função/método dashboardListHeader, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Escapa textos dinâmicos antes de compor o HTML compartilhado do cabeçalho.
    $countMarkup = $contador === '' // Explicação DevIN: Atribui um valor à variável PHP $countMarkup.
        ? '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : '<span class="result-count">' . h($contador) . '</span>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    $candidateFilters = $filtrosCandidatos // Explicação DevIN: Atribui um valor à variável PHP $candidateFilters.
        ? '<label><span>Status</span><select data-card-filter aria-label="Filtrar candidatos por status"></select></label>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<label><span>Escolaridade</span><select data-candidate-global-education aria-label="Filtrar por escolaridade"><option value="">Todos os níveis</option></select></label>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<label class="candidate-global-curriculum"><span>Currículo</span><input type="search" data-candidate-global-curriculum placeholder="Cursos, habilidades, experiência ou idiomas"></label>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : '<label><span>Filtrar por</span><select data-card-filter aria-label="Filtrar cards"></select></label>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    return '<header class="dashboard-header compact-list-header">' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        . '<h1>' . h($titulo) . '</h1>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<form class="busca" method="get">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<input type="hidden" name="pagina" value="' . h($pagina) . '">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<label class="compact-search-field"><span class="sr-only">Pesquisar</span>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<input type="search" name="q" placeholder="' . h($placeholder) . '"' . ($filtrosCandidatos ? ' data-candidate-list-search' : '') . '></label>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<button class="filter-button" type="button" data-toggle-filters aria-expanded="false">Filtros</button>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '<div class="filter-menu' . ($filtrosCandidatos ? ' candidate-global-filters' : '') . '" data-filter-menu hidden>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . $candidateFilters // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '</div>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '</form>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . $countMarkup // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        . '</header>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** 
 * Transforma os rótulos de um card em etiquetas compactas e seguras.
 */
// Detalhe DevIN: Método dashboardCardTags: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dashboardCardTags(string $tags): string // Explicação DevIN: Declara função/método dashboardCardTags, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $items = array_values(array_filter(array_map('trim', explode('|', $tags)))); // Explicação DevIN: Atribui um valor à variável PHP $items.
    if ($items === []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return ''; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $markup = array_map(static fn(string $item): string => '<li>' . h($item) . '</li>', $items); // Explicação DevIN: Atribui um valor à variável PHP $markup.
    return '<ul class="card-tags" aria-label="Detalhes do card">' . implode('', $markup) . '</ul>'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** 
 * Prepara os dados do currículo para o painel de detalhes do administrador.
 */
// Detalhe DevIN: Método dashboardCandidateDetails: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dashboardCandidateDetails(array $candidate): array // Explicação DevIN: Declara função/método dashboardCandidateDetails, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Divide habilidades escritas em linhas ou separadas por vírgula, ponto e vírgula ou barra.
    $text = static fn(string $key): string => trim((string) ($candidate[$key] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $text.
    $splitSkills = static function (string $value): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $items = preg_split('/[\r\n,;|]+/u', $value) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $items.
        $items = array_filter(array_map('trim', $items), static fn(string $item): bool => $item !== ''); // Explicação DevIN: Atribui um valor à variável PHP $items.
        return array_values(array_unique($items)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $accountName = $text('nome'); // Explicação DevIN: Atribui um valor à variável PHP $accountName.
    $socialName = $text('nome_social'); // Explicação DevIN: Atribui um valor à variável PHP $socialName.
    $education = $text('grau_de_escolaridade') ?: 'Não informada'; // Explicação DevIN: Atribui um valor à variável PHP $education.
    $skills = array_merge($splitSkills($text('cursos')), $splitSkills($text('idiomas'))); // Explicação DevIN: Atribui um valor à variável PHP $skills.
    $skills = array_values(array_unique($skills)); // Explicação DevIN: Atribui um valor à variável PHP $skills.
    $experience = $text('experiencia') ?: 'Nenhuma experiência informada.'; // Explicação DevIN: Atribui um valor à variável PHP $experience.

    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        'name' => $socialName ?: ($accountName ?: 'Candidato'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'summary' => 'E-mail: ' . $text('email') // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . ' | CPF: ' . $text('cpf') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . ' | Escolaridade: ' . $education, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        'tags' => implode('|', $skills ?: ['Nenhuma habilidade informada']), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'experience' => 'Experiência profissional::' . str_replace(['|', '::'], '—', $experience), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** 
 * Conteúdo institucional compartilhado pelos três dashboards.
 */
// Detalhe DevIN: Método aboutPage: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function aboutPage(): string // Explicação DevIN: Declara função/método aboutPage, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return <<<'HTML'
<div class="sobre-container">
    <div class="sobre-top-section">
        <span class="badge-historia">Nossa história</span>
        <h1 class="sobre-title">Conectando talentos ao <span class="text-blue palavra-rotativa" data-rotating-word aria-live="polite">futuro</span><br>da tecnologia</h1>
        <p class="sobre-subtitle">A DevIN nasceu para transformar a forma como desenvolvedores<br>encontram oportunidades - "simples, rápido e eficiente".</p>
        <div class="sobre-cards">
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></span><h3>Visão</h3><p>Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.</p></article>
            <article class="sobre-card"><span class="icon-placeholder" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg></span><h3>Missão</h3><p>Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.</p></article>
        </div>
    </div>
    <div class="sobre-bottom-section">
        <div class="sobre-about-text"><h2>Quem somos <span class="text-blue">nós?</span></h2><p>A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.</p><p>Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.</p></div>
        <section class="sobre-team-card"><h2 class="team-header">Time fundador</h2><ul class="team-list"><li><img class="team-avatar" src="../img/caua.jpg" alt="Foto de Cauã Chiappin de Lima"><div class="team-info"><strong>Cauã Chiappin de Lima</strong><a href="mailto:caua.lima@scseduca.com.br">Cofundador · caua.lima@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/enzo.jpg" alt="Foto de Enzo Vasconcelos de Camargo"><div class="team-info"><strong>Enzo Vasconcelos de Camargo</strong><a href="mailto:enzo.camargo@scseduca.com.br">Cofundador · enzo.camargo@scseduca.com.br</a></div></li><li><img class="team-avatar" src="../img/joao.jpg" alt="Foto de João Vitor da Silva e Sousa"><div class="team-info"><strong>João Vitor da Silva e Sousa</strong><a href="mailto:joao.sousa2@scseduca.com.br">Cofundador · joao.sousa2@scseduca.com.br</a></div></li></ul></section>
    </div>
</div>
HTML;
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Resolve fotos armazenadas no diretório de uploads, inclusive nomes legados.
 */
// Detalhe DevIN: Método profilePhotoFilePath: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function profilePhotoFilePath(string $foto, ?string $tipo = null, ?int $id = null): ?string // Explicação DevIN: Declara função/método profilePhotoFilePath, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (str_starts_with($foto, 'uploads/')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $caminhoRelativo = substr($foto, strlen('uploads/')); // Explicação DevIN: Atribui um valor à variável PHP $caminhoRelativo.
    } elseif (preg_match('/^(pessoa|empresa)-(\d+)-[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/D', $foto, $matches) === 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (($tipo !== null && $matches[1] !== $tipo) || ($id !== null && (int) $matches[2] !== $id)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $caminhoRelativo = $foto; // Explicação DevIN: Atribui um valor à variável PHP $caminhoRelativo.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $diretorioUploads = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads'); // Explicação DevIN: Atribui um valor à variável PHP $diretorioUploads.
    if ($diretorioUploads === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $caminhoArquivo = realpath( // Explicação DevIN: Atribui um valor à variável PHP $caminhoArquivo.
        $diretorioUploads . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $caminhoRelativo) // Explicação DevIN: Continua expressão ou bloco pertencente a função profilePhotoFilePath.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $prefixoUploads = rtrim($diretorioUploads, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR; // Explicação DevIN: Atribui um valor à variável PHP $prefixoUploads.

    return $caminhoArquivo !== false // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        && is_file($caminhoArquivo) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        && str_starts_with(strtolower($caminhoArquivo), strtolower($prefixoUploads)) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ? $caminhoArquivo // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Monta o avatar reutilizado no menu, no modal e no perfil.
 * Se não houver foto válida, exibe as iniciais do nome como avatar padrão.
 */
// Detalhe DevIN: Método profileAvatar: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function profileAvatar(array $perfil, string $classes): string // Explicação DevIN: Declara função/método profileAvatar, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    static $finfo = null; // Explicação DevIN: Continua expressão ou bloco pertencente a função profileAvatar.
    $foto = $perfil['foto'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $foto.
    $caminhoArquivo = is_string($foto) ? profilePhotoFilePath($foto) : null; // Explicação DevIN: Atribui um valor à variável PHP $caminhoArquivo.

    // Trata imagens armazenadas em BLOB (Bytes brutos no banco)
    if (is_string($foto) && $foto !== '' && $caminhoArquivo === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($finfo === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $finfo = new finfo(FILEINFO_MIME_TYPE); // Explicação DevIN: Atribui um valor à variável PHP $finfo.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Só embute conteúdo binário quando o MIME detectado corresponde a uma imagem aceita.
        $mime = $finfo->buffer($foto); // Explicação DevIN: Atribui um valor à variável PHP $mime.

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $imagem = '<img src="data:' . $mime . ';base64,' . base64_encode($foto) . '" alt="Foto de perfil">'; // Explicação DevIN: Atribui um valor à variável PHP $imagem.
            return '<span class="' . h($classes) . ' current-user-avatar has-photo">' . $imagem . '</span>'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $caminhoPublico = $caminhoArquivo === null // Explicação DevIN: Atribui um valor à variável PHP $caminhoPublico.
        ? '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : (str_starts_with($foto, 'uploads/') ? $foto : 'uploads/' . basename($caminhoArquivo)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    $imagem = $caminhoPublico !== '' // Explicação DevIN: Atribui um valor à variável PHP $imagem.
        ? '<img src="' . h($caminhoPublico) . '" alt="Foto de perfil">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    $partesNome = preg_split('/\s+/u', trim((string) ($perfil['nome'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $partesNome.
    $iniciais = ''; // Explicação DevIN: Atribui um valor à variável PHP $iniciais.
    foreach (array_slice($partesNome, 0, 2) as $parte) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        if (preg_match('/^./u', $parte, $matches) === 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $iniciais .= $matches[0]; // Explicação DevIN: Continua expressão ou bloco pertencente a função profileAvatar.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $conteudo = $imagem !== '' ? $imagem : h($iniciais !== '' ? $iniciais : 'DI'); // Explicação DevIN: Atribui um valor à variável PHP $conteudo.

    return '<span class="' . h($classes) . ' current-user-avatar' . ($imagem !== '' ? ' has-photo' : '') . '">' . $conteudo . '</span>'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** 
 * Painel de detalhes compartilhado pelas áreas de listagem dos dashboards.
 */
// Detalhe DevIN: Método dashboardDetailPanel: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dashboardDetailPanel(): string // Explicação DevIN: Declara função/método dashboardDetailPanel, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return <<<'HTML'
<aside class="detalhe-area profile-detail-panel" aria-label="Detalhes do item selecionado">
    <div class="detail-placeholder" data-detail-placeholder>
        <span class="detail-placeholder-icon" aria-hidden="true">DI</span>
        <h2>Selecione um card</h2>
        <p>As informações do perfil, vaga ou candidatura aparecerão aqui.</p>
    </div>
    <article class="detail-profile" data-detail-content hidden>
        <button class="detail-close" type="button" data-close-detail aria-label="Fechar detalhes" title="Fechar detalhes">×</button>
        <header class="detail-profile-header">
            <span class="detail-avatar" data-detail-avatar aria-hidden="true">DI</span>
            <div class="detail-identity">
                <h2 data-detail-name>Detalhes</h2>
                <p data-detail-role></p>
                <p class="detail-meta" data-detail-meta>DevIN</p>
            </div>
            <div class="detail-profile-actions">
                <button class="detail-action" type="button" data-detail-action hidden></button>
                <button class="detail-chat-action" type="button" data-start-chat hidden>Conversar</button>
            </div>
        </header>
        <section class="detail-section">
            <h3 data-detail-summary-label>Sobre</h3>
            <p data-detail-summary></p>
            <div class="detail-job-facts" data-report-job-facts hidden>
                <div>
                    <span>Salário mensal</span>
                    <strong data-report-job-salary></strong>
                </div>
                <div>
                    <span>Limite de vagas</span>
                    <strong data-report-job-limit></strong>
                </div>
            </div>
        </section>
        <section class="detail-section" data-detail-tags-section>
            <h3>Habilidades</h3>
            <ul class="detail-tags" data-detail-tags></ul>
        </section>
        <section class="detail-section">
            <h3 data-detail-experience-heading>Experiência</h3>
            <ul class="detail-timeline" data-detail-experience></ul>
        </section>
        <section class="detail-report-decision" data-report-decision-block hidden>
            <h3>Decisão da análise</h3>
            <p>Revise os detalhes da denúncia e escolha se o item precisa continuar disponível ou ser removido.</p>
            <div class="report-decision-actions">
                <button class="btn primary" type="button" data-report-decision="manter">Manter</button>
                <button class="btn danger" type="button" data-report-decision="excluir">Excluir</button>
            </div>
        </section>
        <section class="detail-report-section" data-report-job-section hidden>
            <div class="detail-report-copy">
                <h3>Encontrou algo errado?</h3>
                <p>Ajude a manter as vagas confiáveis. Nossa equipe vai analisar seu relato.</p>
            </div>
            <button class="detail-report-action" type="button" data-report-job>Denunciar esta vaga</button>
        </section>
    </article>
</aside>
<dialog class="settings-modal job-report-modal" data-job-report-dialog aria-labelledby="jobReportTitle">
    <form class="modal-form" data-job-report-form>
        <button class="modal-close" type="button" data-close-job-report aria-label="Fechar">×</button>
        <h2 id="jobReportTitle">Denunciar vaga</h2>
        <p class="settings-description">A equipe DevIN analisará a denúncia. A empresa responsável e você receberão o resultado na aba de conversas.</p>
        <input type="hidden" data-job-report-id>
        <label for="jobReportReason">Motivo
            <select id="jobReportReason" name="motivo" required>
                <option value="">Selecione um motivo</option>
                <option value="fraude">Fraude ou golpe</option>
                <option value="informacao_falsa">Informações falsas ou enganosas</option>
                <option value="discriminacao">Discriminação</option>
                <option value="conteudo_inadequado">Conteúdo inadequado</option>
                <option value="vaga_expirada">Vaga expirada ou indisponível</option>
                <option value="outro">Outro</option>
            </select>
        </label>
        <label for="jobReportDetails">Detalhes
            <textarea id="jobReportDetails" name="detalhes" rows="4" maxlength="1000" required placeholder="Explique por que esta vaga deve ser analisada."></textarea>
        </label>
        <div class="dashboard-feedback-toast" data-feedback-toast hidden role="status" aria-live="polite">
            <p class="dashboard-toast__msg" data-job-report-status data-feedback-message></p>
            <button class="dashboard-toast__close" type="button" data-toast-close aria-label="Fechar">×</button>
        </div>
        <button class="btn primary" type="submit" data-job-report-submit>Enviar denúncia</button>
    </form>
</dialog>
HTML;
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Retorna os caminhos SVG dos ícones da interface.
 */
// Detalhe DevIN: Método dashboardIcon: encapsula uma responsabilidade reutilizável do módulo no contexto de helpers. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dashboardIcon(string $name): string // Explicação DevIN: Declara função/método dashboardIcon, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $paths = [ // Explicação DevIN: Atribui um valor à variável PHP $paths.
        'brand'    => '<path d="M4 5.5h16v13H4z"/><path d="M12 5.5v13"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'home'     => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'building' => '<path d="M3 21h18M5 21V5h10v16M15 9h4v12M8 9h4M8 13h4M8 17h4"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'briefcase'=> '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        // Ícone exclusivo do suporte: boia circular, diferente dos ícones atuais.
        'lifebuoy' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="m5.6 5.6 3.6 3.6m5.6 5.6 3.6 3.6m0-12.8-3.6 3.6m-5.6 5.6-3.6 3.6"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        // Reports recebe bandeira própria em vez do ícone "info" usado em Sobre nós.
        'flag' => '<path d="M5 22V4m0 0c4-3 7 3 11 0v12c-4 3-7-3-11 0"/><path d="M5 4v12"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33h-.08A1.65 1.65 0 0 0 14 20.91V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82v-.08A1.65 1.65 0 0 0 3.09 14H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.08A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.08A1.65 1.65 0 0 0 20.91 10H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'logout'   => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'trash'    => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'sparkle'  => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'check'    => '<path d="M20 6 9 17l-5-5"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'printer'  => '<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    $paths = $paths[$name] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $paths.

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
