<?php

declare(strict_types=1);

require_once __DIR__ . '/../php/helpers.php';

function expectHeader(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$header = dashboardListHeader(
    'candidatos',
    'Candidatos',
    'Pesquisar por nome ou habilidade...',
    '3 candidatos'
);

expectHeader(
    str_contains($header, 'class="dashboard-header compact-list-header"'),
    'O cabecalho precisa usar o layout compacto.'
);
expectHeader(
    str_contains($header, '<h1>Candidatos</h1>'),
    'O cabecalho precisa exibir o titulo da lista.'
);
expectHeader(
    str_contains($header, 'name="pagina" value="candidatos"'),
    'A busca precisa preservar a pagina atual.'
);
expectHeader(
    str_contains($header, 'placeholder="Pesquisar por nome ou habilidade..."'),
    'A busca precisa exibir a orientacao informada pela tela.'
);
expectHeader(
    str_contains($header, '<span class="filter-button" aria-hidden="true">Filtros</span>'),
    'O cabecalho precisa oferecer o indicador visual de filtros sem sugerir uma acao inexistente.'
);
expectHeader(
    str_contains($header, '>3 candidatos</span>'),
    'O cabecalho precisa apresentar o contador da lista.'
);

$tags = dashboardCardTags('React|Node.js|PostgreSQL');

expectHeader(
    str_contains($tags, '<ul class="card-tags"'),
    'As habilidades do card precisam ser agrupadas em uma lista visual.'
);
expectHeader(
    str_contains($tags, '<li>React</li>') && str_contains($tags, '<li>Node.js</li>') && str_contains($tags, '<li>PostgreSQL</li>'),
    'As habilidades separadas por barra precisam aparecer como etiquetas individuais.'
);

echo "PASS: cabecalho compacto do dashboard renderizado.\n";
