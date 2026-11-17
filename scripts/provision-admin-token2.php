<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — scripts/provision-admin-token2.php
 * Finalidade: provision admin token2.
 * Domínio: componentes compartilhados.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Atribuição automática do Token 2 foi substituída pela configuração feita
// individualmente pelo ADM; impedimos atribuição acidental pelo operador.
if (PHP_SAPI !== 'cli') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
fwrite(STDERR, "Comando descontinuado: cada ADM escolhe o Token 2 permanente em JarvisT.php.\n"); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
fwrite(STDERR, "Para emitir um código individual temporário, use:\n"); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
fwrite(STDERR, "Acesse a pagina autenticada php/JarvisT.php para cadastrar ou alterar o Token 2.\n"); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
exit(2); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
