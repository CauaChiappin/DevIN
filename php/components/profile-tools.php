<?php
/**
 * Atalhos acessíveis dentro do perfil de todos os três tipos de conta.
 * A navegação nunca altera permissões; as rotas revalidam a sessão e CSRF.
 */
$profileKind = (string)($tipo ?? '');
if (!in_array($profileKind, ['pessoa','empresa','adm'], true)) return;
$basePage = $profileKind . '.php';
$destinations = [
    ['Configurações da conta', $basePage . '?pagina=perfil&configuracoes=1'],
    ['Privacidade e dados', 'politica_privacidade.php'],
    ['Mensagens', $profileKind === 'adm' ? 'adm.php?pagina=suporte' : $basePage . '?pagina=mensagens'],
    ['Suporte DevIN', $profileKind === 'adm' ? 'adm.php?pagina=suporte' : 'suporte.php'],
];
if ($profileKind === 'pessoa') $destinations[] = ['Minhas candidaturas', 'pessoa.php?pagina=vagas'];
if ($profileKind === 'empresa') $destinations[] = ['Gerenciar candidatos', 'empresa.php?pagina=candidatos'];
if ($profileKind === 'adm') $destinations[] = ['JARVIS Admin', 'adm.php?pagina=inicio#jarvisAdmin'];
if ($profileKind === 'adm') $destinations[] = ['Diagnóstico JARVIS', 'api/jarvis/readiness.php']; // API retorna somente status, sem chaves ou credenciais.
?>
<nav class="profile-tools" aria-label="Ações da conta">
    <h2>Central da minha conta</h2>
    <p>Gerencie seus dados, privacidade, atendimento e atividades sem sair do painel.</p>
    <div class="profile-tools__links">
        <?php foreach ($destinations as [$label, $href]): ?>
            <a href="<?= h($href) ?>" class="profile-tools__link"><?= h($label) ?></a>
        <?php endforeach; ?>
    </div>
</nav>
