<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
/**
 * REVISÃO DETALHADA DEVIN — php/index.php
 * Finalidade: index.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

// Reutiliza o HTML estático como página inicial servida pela rota PHP.
readfile(__DIR__ . '/../html/index.html'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
