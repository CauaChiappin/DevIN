<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
/**
 * Atalhos acessíveis dentro do perfil de todos os três tipos de conta.
 * A navegação nunca altera permissões; as rotas revalidam a sessão e CSRF.
 */
$profileKind = (string)($tipo ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $profileKind.
if (!in_array($profileKind, ['pessoa','empresa','adm'], true)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
$basePage = $profileKind . '.php'; // Explicação DevIN: Atribui um valor à variável PHP $basePage.
$destinations = [ // Explicação DevIN: Atribui um valor à variável PHP $destinations.
    ['Configurações da conta', $basePage . '?pagina=perfil&configuracoes=1'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['Privacidade e dados', 'politica_privacidade.php'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['Mensagens', $profileKind === 'adm' ? 'adm.php?pagina=suporte' : $basePage . '?pagina=mensagens'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['Suporte DevIN', $profileKind === 'adm' ? 'adm.php?pagina=suporte' : 'suporte.php'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
if ($profileKind === 'pessoa') $destinations[] = ['Minhas candidaturas', 'pessoa.php?pagina=vagas']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
if ($profileKind === 'empresa') $destinations[] = ['Gerenciar candidatos', 'empresa.php?pagina=candidatos']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
if ($profileKind === 'adm') $destinations[] = ['JARVIS Admin', 'adm.php?pagina=inicio#jarvisAdmin']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
if ($profileKind === 'adm') $destinations[] = ['Diagnóstico JARVIS', 'api/jarvis/readiness.php']; // API retorna somente status, sem chaves ou credenciais.
?>
<nav class="profile-tools" aria-label="Ações da conta"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
    <h2>Central da minha conta</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
    <p>Gerencie seus dados, privacidade, atendimento e atividades sem sair do painel.</p> <!-- Explicação DevIN: Declara parágrafo. -->
    <div class="profile-tools__links"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        <?php foreach ($destinations as [$label, $href]): ?>
            <a href="<?= h($href) ?>" class="profile-tools__link"><?= h($label) ?></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
        <?php endforeach; ?>
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
</nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
