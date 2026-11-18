<?php // Compatibilidade para links antigos da Central de Ajuda.
declare(strict_types=1); // Mantém a validação estrita do DevIN.
require_once __DIR__ . '/middlewares/auth.php'; // Protege o redirecionamento com login.
$actor = requireWebAuth(); // Consulta o tipo real da conta autenticada.
if ($actor['tipo'] === 'adm') { // Administradores usam o atendimento do painel ADM.
    header('Location: adm.php?pagina=suporte', true, 303); // Mantém a sidebar administrativa.
    exit; // Encerra o redirecionamento.
} // Conclui o fluxo administrativo.
if (!in_array($actor['tipo'], ['pessoa', 'empresa'], true)) { // Somente usuários reconhecidos podem usar a Central.
    http_response_code(403); // Bloqueia tipos de conta inválidos.
    exit('Tipo de conta não autorizado.'); // Não expõe a área de atendimento.
} // Conclui a checagem de identidade.
$dashboard = $actor['tipo'] === 'pessoa' ? 'pessoa.php' : 'empresa.php'; // Seleciona o painel correto.
header('Location: ' . $dashboard . '?pagina=suporte', true, 303); // Preserva menu e navegação do painel.
exit; // Encerra a resposta após o redirecionamento.
