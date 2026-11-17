<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/admin_token2.php
 * Finalidade: admin token2.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Rota legada sem cadastro de credenciais: redireciona apenas acessos GET.
// Todas as mudanças de Token 2 acontecem exclusivamente em JarvisT.php.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(405); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Allow: GET'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
header('Cache-Control: no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Location: JarvisT.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
