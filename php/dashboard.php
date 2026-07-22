<?php
session_start();

if (empty($_SESSION['logado'])) {
    header('Location: login.php');
    exit;
}

$tipo = $_SESSION['usuario_tipo'] ?? 'pessoa';
$nome = $_SESSION['usuario_nome'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? 'email@devin.com';
$pagina = $_GET['pagina'] ?? 'inicio';

$paginasPermitidas = [
    'empresa' => ['inicio', 'candidatos', 'sobre', 'perfil'],
    'pessoa' => ['inicio', 'vagas', 'sobre', 'perfil'],
    'adm' => ['inicio', 'candidatos', 'sobre', 'perfil'],
];

if (!in_array($pagina, $paginasPermitidas[$tipo] ?? $paginasPermitidas['pessoa'], true)) {
    $pagina = 'inicio';
}

$empresaPosts = [
    ['titulo' => 'Desenvolvedor Front-end', 'resumo' => 'HTML, CSS, JavaScript e portfolio simples.', 'detalhe' => 'Vaga para criar telas responsivas, manter paginas existentes e colaborar com a equipe de design.'],
    ['titulo' => 'Analista de Suporte', 'resumo' => 'Atendimento, redes basicas e organizacao.', 'detalhe' => 'Buscamos uma pessoa comunicativa para registrar chamados, orientar usuarios e resolver problemas iniciais.'],
];

$talentos = [
    ['nome' => 'Marina Santos', 'resumo' => 'React, CSS e comunicacao clara.', 'detalhe' => 'Marina tem interesse em vagas de front-end junior e disponibilidade para conversar esta semana.'],
    ['nome' => 'Lucas Pereira', 'resumo' => 'PHP, MySQL e logica de programacao.', 'detalhe' => 'Lucas procura primeira oportunidade em desenvolvimento web e ja criou projetos escolares com banco de dados.'],
];

$candidatos = [
    ['nome' => 'Ana Clara', 'vaga' => 'Desenvolvedor Front-end', 'resumo' => 'Boa base de HTML, CSS e Git.', 'detalhe' => 'A candidata se inscreveu para Front-end e enviou portfolio com paginas responsivas.'],
    ['nome' => 'Pedro Lima', 'vaga' => 'Analista de Suporte', 'resumo' => 'Conhecimento em atendimento e manutencao.', 'detalhe' => 'O candidato descreveu experiencia em suporte tecnico escolar e disponibilidade integral.'],
];

$vagasPessoa = [
    ['empresa' => 'Empresa X', 'titulo' => 'Estagio em Desenvolvimento', 'resumo' => 'Logica, HTML, CSS e vontade de aprender.', 'detalhe' => 'A vaga oferece mentoria, atividades de interface e apoio em projetos internos.', 'status' => 'Em analise'],
    ['empresa' => 'Empresa Y', 'titulo' => 'Assistente de TI', 'resumo' => 'Suporte, organizacao e comunicacao.', 'detalhe' => 'Atuacao com chamados, inventario de equipamentos e apoio aos colaboradores.', 'status' => 'Reprovado'],
];

$postsAdmin = [
    ['empresa' => 'Empresa Alfa', 'titulo' => 'Post da vaga', 'detalhe' => 'Revise a descricao da vaga, requisitos, salario informado e aderencia as diretrizes do site.'],
    ['empresa' => 'Empresa Beta', 'titulo' => 'Post da vaga', 'detalhe' => 'Este post foi sinalizado para avaliacao do administrador antes de continuar visivel.'],
];

$usuariosAdmin = [
    ['nome' => 'Carlos Souza', 'resumo' => 'Perfil de candidato em verificacao.', 'detalhe' => 'Verifique foto, e-mail, dados basicos e comportamento do perfil antes de remover a conta.'],
    ['nome' => 'Bianca Rocha', 'resumo' => 'Conta com dados incompletos.', 'detalhe' => 'A conta precisa ser avaliada por suspeita de informacoes falsas.'],
];

$tituloLateral = [
    'empresa' => 'Explicando tudo sobre a pessoa selecionada',
    'pessoa' => 'Explicando sobre a vaga da empresa',
    'adm' => 'Explicando tudo sobre o post da vaga selecionada',
];

function ativo(string $paginaAtual, string $pagina): string
{
    return $paginaAtual === $pagina ? 'ativo' : '';
}

function h(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <main class="dashboard-shell" data-tipo="<?= h($tipo) ?>">
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="dashboard.php">
                    <span class="brand-mark"></span>
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <?php if ($tipo === 'empresa'): ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><span class="icon home"></span><span class="menu-text">Inicio</span></a>
                    <a class="<?= ativo($pagina, 'candidatos') ?>" href="?pagina=candidatos"><span class="icon users"></span><span class="menu-text">Candidatos</span></a>
                <?php elseif ($tipo === 'adm'): ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><span class="icon building"></span><span class="menu-text">Empresas</span></a>
                    <a class="<?= ativo($pagina, 'candidatos') ?>" href="?pagina=candidatos"><span class="icon users"></span><span class="menu-text">Candidatos</span></a>
                <?php else: ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><span class="icon home"></span><span class="menu-text">Inicio</span></a>
                    <a class="<?= ativo($pagina, 'vagas') ?>" href="?pagina=vagas"><span class="icon briefcase"></span><span class="menu-text">Vagas</span></a>
                <?php endif; ?>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="?pagina=sobre"><span class="icon info"></span><span class="menu-text">Sobre nos</span></a>
            </nav>

            <div class="conta">
                <a class="perfil-link <?= ativo($pagina, 'perfil') ?>" href="?pagina=perfil">
                    <span class="avatar-mini"></span>
                    <span class="menu-text">Perfil</span>
                </a>
                <?php if ($pagina === 'perfil'): ?>
                    <div class="perfil-menu">
                        <a href="?pagina=perfil"><span class="avatar-foto"></span><span class="menu-text">Meu perfil</span></a>
                        <button type="button" data-open-settings><span class="gear-icon"></span><span class="menu-text">Configuracoes</span></button>
                    </div>
                <?php endif; ?>
                <a class="sair" href="logout.php"><span class="exit-icon"></span><span class="menu-text">Sair da Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <header class="dashboard-header">
                <div>
                    <span>Painel DevIN</span>
                    <h1>Dashboard</h1>
                </div>
            </header>

            <form class="busca" action="" method="get">
                <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                <label>
                    <span></span>
                    <input type="search" name="q" placeholder="Pesquise">
                </label>
            </form>

            <?php if ($pagina === 'sobre'): ?>
                <section class="sobre-intro">
                    <span class="tag">Nossa historia</span>
                    <h1>Conectando talentos ao futuro da tecnologia</h1>
                    <p>A DevIN nasceu para transformar a forma como desenvolvedores encontram oportunidades: simples, rapido e eficiente.</p>
                </section>
                <div class="sobre-cards">
                    <article>
                        <span class="card-icon"></span>
                        <h2>Visao</h2>
                        <p>Ser referencia na conexao entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovacao.</p>
                    </article>
                    <article>
                        <span class="card-icon"></span>
                        <h2>Missao</h2>
                        <p>Conectar desenvolvedores de todos os niveis a oportunidades de trabalho, tornando o recrutamento mais simples.</p>
                    </article>
                </div>
                <section class="fundadores">
                    <div>
                        <h2>Quem somos nos?</h2>
                        <p>A DevIN nasceu com o proposito de transformar a forma como profissionais de tecnologia encontram oportunidades.</p>
                        <p>Hoje, oferecemos um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos.</p>
                    </div>
                    <div class="time-card">
                        <h2>Time fundador</h2>
                        <p><strong>Caua Chiappin de Lima</strong><br>Co-fundador</p>
                        <p><strong>Enzo Vasconcelos de Camargo</strong><br>Co-fundador</p>
                        <p><strong>Joao Vitor da Silva e Souza</strong><br>Co-fundador</p>
                    </div>
                </section>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="?pagina=inicio">x</a>
                    <div class="perfil-topo">
                        <span class="avatar-grande"></span>
                        <div>
                            <strong><?= h($nome) ?></strong>
                            <small><?= h($email) ?></small>
                        </div>
                    </div>
                    <form class="form-perfil">
                        <label>Nome<input type="text" value="<?= h($nome) ?>"></label>
                        <label>E-mail account<input type="email" value="<?= h($email) ?>"></label>
                        <label>CEP<input type="text" placeholder="00000-000"></label>
                        <label>Celular<input type="tel" placeholder="(00) 00000-0000"></label>
                        <button type="button">save</button>
                    </form>
                </section>
            <?php elseif ($tipo === 'empresa' && $pagina === 'candidatos'): ?>
                <?php foreach ($candidatos as $candidato): ?>
                    <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($candidato['nome']) ?></h2>
                            <p><?= h($candidato['resumo']) ?></p>
                        </div>
                        <div class="acoes-card">
                            <button class="btn danger" type="button">Nao se encaixa</button>
                            <button class="btn success" type="button">Aprovar</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'empresa'): ?>
                <button class="criar-post" type="button">Criar post</button>
                <?php foreach ($empresaPosts as $post): ?>
                    <article class="item-card" data-detail="<?= h($post['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($post['titulo']) ?></h2>
                            <p><?= h($post['resumo']) ?></p>
                        </div>
                        <div class="post-tools">
                            <button class="edit" type="button" aria-label="Editar post"></button>
                            <button class="delete" type="button" aria-label="Excluir post"></button>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php foreach ($talentos as $talento): ?>
                    <article class="item-card" data-detail="<?= h($talento['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($talento['nome']) ?></h2>
                            <p><?= h($talento['resumo']) ?></p>
                        </div>
                        <button class="btn primary" type="button">Conversar</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'adm' && $pagina === 'candidatos'): ?>
                <?php foreach ($usuariosAdmin as $usuario): ?>
                    <article class="item-card" data-detail="<?= h($usuario['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($usuario['nome']) ?></h2>
                            <p><?= h($usuario['resumo']) ?></p>
                        </div>
                        <button class="btn danger" type="button">Excluir perfil</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'adm'): ?>
                <?php foreach ($postsAdmin as $post): ?>
                    <article class="item-card" data-detail="<?= h($post['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($post['empresa']) ?></h2>
                            <p><?= h($post['titulo']) ?></p>
                            <a href="#">Saiba Mais...</a>
                        </div>
                        <button class="btn danger" type="button">Excluir Post</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($pagina === 'vagas'): ?>
                <?php foreach ($vagasPessoa as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['resumo']) ?></p>
                        </div>
                        <span class="status <?= $vaga['status'] === 'Reprovado' ? 'reprovado' : 'analise' ?>"><?= h($vaga['status']) ?></span>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($vagasPessoa as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>">
                        <span class="avatar-mini"></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['resumo']) ?></p>
                        </div>
                        <button class="btn primary" type="button">Candidatar-se</button>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestoes ou pedir suporte.</p>
                <p><strong>E-mail:</strong> contato@devin.com.br</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Explicando tudo sobre a vaga selecionada</h2>
                <p>Use este espaco para visualizar detalhes da vaga, pessoa ou post escolhido no painel.</p>
                <button class="btn primary fixed-action" type="button">Candidatar-se</button>
            <?php else: ?>
                <h2><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : $tituloLateral[$tipo]) ?></h2>
                <p id="detailText"><?= h($tipo === 'adm' ? 'Selecione um post ou candidato para analisar as informacoes.' : 'Selecione um card para ver a explicacao completa aqui.') ?></p>
                <?php if ($tipo === 'empresa' && $pagina === 'candidatos'): ?>
                    <div class="detalhe-botoes">
                        <button class="btn danger" type="button">Nao se encaixa</button>
                        <button class="btn success" type="button">Aprovar</button>
                    </div>
                <?php elseif ($tipo === 'adm'): ?>
                    <button class="btn danger fixed-action" type="button">Excluir Post</button>
                <?php elseif ($tipo === 'pessoa' && $pagina === 'vagas'): ?>
                    <span class="status aprovado fixed-action">Aprovado</span>
                <?php endif; ?>
            <?php endif; ?>
        </aside>
    </main>

    <dialog class="settings-modal" id="settingsModal">
        <form method="dialog">
            <button class="modal-close" value="close" aria-label="Fechar">x</button>
            <h2>Configuracoes</h2>
            <label>Idioma
                <select>
                    <option>Portugues</option>
                    <option>Ingles</option>
                    <option>Espanhol</option>
                </select>
            </label>
            <button class="btn danger" type="button">Excluir conta</button>
        </form>
    </dialog>

    <script src="../js/dashboard.js"></script>
</body>
</html>
