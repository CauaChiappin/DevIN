<?php

declare(strict_types=1);

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

session_save_path(__DIR__ . '/../storage/sessions');
session_id('admin-posts-dashboard-test');
session_start();
$_SESSION = [
    'logado' => true,
    'usuario_id' => 7,
    'usuario_tipo' => 'adm',
    'usuario_nome' => 'João',
    'usuario_email' => 'joao.sousa2@scseduca.com.br',
    'csrf_token' => str_repeat('a', 64),
];

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['pagina' => 'posts'];

ob_start();
require __DIR__ . '/../php/adm.php';
$page = ob_get_clean();

expect(
    str_contains($page, 'href="adm.php?pagina=posts"'),
    'A navegação do ADM precisa oferecer a aba Posts das empresas.'
);
expect(
    str_contains($page, '<h1>Posts das empresas</h1>'),
    'A aba posts precisa renderizar o título Posts das empresas.'
);
expect(
    !str_contains($page, 'Excluir perfil'),
    'A aba posts não pode listar perfis de empresas ou candidatos.'
);

expect(
    str_contains($page, 'class="detalhe-area profile-detail-panel"'),
    'A aba posts precisa renderizar o painel direito no formato de perfil detalhado.'
);
expect(
    str_contains($page, 'data-detail-content'),
    'O painel direito precisa ter uma area de detalhes preenchida ao selecionar um card.'
);
expect(
    str_contains($page, 'class="dashboard-header compact-list-header"'),
    'A lista administrativa precisa renderizar o cabecalho compacto do novo layout.'
);

session_destroy();

echo "PASS: aba Posts das empresas renderizada para o ADM.\n";
