<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
/**
 * REVISÃO DETALHADA DEVIN — php/politica_privacidade.php
 * Finalidade: politica privacidade.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

// Serve a versão HTML estática da política através da rota amigável em PHP.
readfile(__DIR__ . '/../html/politica_privacidade.html'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
