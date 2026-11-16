# Explicação linha a linha — php/controllers/JobReports.php

**Arquivo original:** [abrir código no GitHub](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php)  
**Linguagem:** PHP  
**Linhas de origem:** 259  
**SHA-256 da origem:** 98923368043ff411e8b8361ce2627143e37fe95cd784af0392b9da5c10353fab

> Descrições iniciais produzidas automaticamente, linha a linha, por regras de sintaxe.
> Explicações de comportamento complexo ou ambíguo exigem revisão técnica humana.
> Os links abrem diretamente a linha correspondente do código real; nenhum
> arquivo executável é alterado pela geração desta documentação.

| Linha do código | Explicação da linha |
| ---: | --- |
| [L1](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L1) | Abre código PHP interpretado no servidor. |
| [L2](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L2) | Linha em branco: separa visualmente trechos do código. |
| [L3](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L3) | Habilita a verificação estrita de tipos no PHP. |
| [L4](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L4) | Linha em branco: separa visualmente trechos do código. |
| [L5](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L5) | Inclui dependência PHP necessária à execução do módulo. |
| [L6](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L6) | Inclui dependência PHP necessária à execução do módulo. |
| [L7](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L7) | Linha em branco: separa visualmente trechos do código. |
| [L8](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L8) | Declara função/método ensureJobReportsSchema, com seus parâmetros e comportamento. |
| [L9](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L9) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L10](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L10) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L11](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L11) | Linha em branco: separa visualmente trechos do código. |
| [L12](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L12) | Executa ou lê resultados de uma consulta ao banco. |
| [L13](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L13) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L14](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L14) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L15](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L15) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L16](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L16) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L17](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L17) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L18](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L18) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L19](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L19) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L20](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L20) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L21](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L21) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L22](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L22) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L23](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L23) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L24](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L24) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L25](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L25) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L26](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L26) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L27](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L27) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L28](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L28) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L29](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L29) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L30](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L30) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L31](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L31) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L32](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L32) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L33](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L33) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L34](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L34) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L35](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L35) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L36](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L36) | Linha em branco: separa visualmente trechos do código. |
| [L37](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L37) | Atribui um valor à variável PHP $columns. |
| [L38](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L38) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L39](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L39) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L40](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L40) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L41](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L41) | Finaliza expressão, lista, função ou bloco sintático. |
| [L42](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L42) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L43](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L43) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L44](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L44) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L45](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L45) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L46](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L46) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L47](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L47) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L48](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L48) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L49](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L49) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L50](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L50) | Executa ou lê resultados de uma consulta ao banco. |
| [L51](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L51) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L52](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L52) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L53](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L53) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L54](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L54) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L55](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L55) | Executa ou lê resultados de uma consulta ao banco. |
| [L56](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L56) | Captura exceção ou erro para tratar a falha de execução. |
| [L57](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L57) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L58](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L58) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L59](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L59) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L60](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L60) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L61](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L61) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L62](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L62) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L63](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L63) | Linha em branco: separa visualmente trechos do código. |
| [L64](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L64) | Executa ou lê resultados de uma consulta ao banco. |
| [L65](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L65) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L66](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L66) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L67](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L67) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L68](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L68) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L69](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L69) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L70](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L70) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L71](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L71) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L72](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L72) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L73](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L73) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L74](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L74) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L75](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L75) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L76](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L76) | Linha em branco: separa visualmente trechos do código. |
| [L77](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L77) | Executa ou lê resultados de uma consulta ao banco. |
| [L78](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L78) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L79](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L79) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L80](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L80) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L81](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L81) | Continua expressão ou bloco pertencente a função ensureJobReportsSchema. |
| [L82](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L82) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L83](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L83) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L84](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L84) | Linha em branco: separa visualmente trechos do código. |
| [L85](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L85) | Declara função/método submitJobReport, com seus parâmetros e comportamento. |
| [L86](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L86) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L87](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L87) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L88](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L88) | Atribui um valor à variável PHP $allowedReasons. |
| [L89](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L89) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L90](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L90) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L91](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L91) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L92](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L92) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L93](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L93) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L94](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L94) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L95](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L95) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L96](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L96) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L97](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L97) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L98](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L98) | Linha em branco: separa visualmente trechos do código. |
| [L99](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L99) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L100](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L100) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L101](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L101) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L102](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L102) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L103](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L103) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L104](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L104) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L105](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L105) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L106](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L106) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L107](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L107) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L108](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L108) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L109](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L109) | Executa ou lê resultados de uma consulta ao banco. |
| [L110](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L110) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L111](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L111) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L112](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L112) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L113](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L113) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L114](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L114) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L115](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L115) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L116](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L116) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L117](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L117) | Linha em branco: separa visualmente trechos do código. |
| [L118](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L118) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L119](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L119) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L120](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L120) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L121](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L121) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L122](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L122) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L123](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L123) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L124](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L124) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L125](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L125) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L126](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L126) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L127](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L127) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L128](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L128) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L129](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L129) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L130](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L130) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L131](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L131) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L132](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L132) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L133](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L133) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L134](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L134) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L135](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L135) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L136](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L136) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L137](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L137) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L138](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L138) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L139](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L139) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L140](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L140) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L141](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L141) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L142](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L142) | Atribui um valor à variável PHP $reportId. |
| [L143](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L143) | Captura exceção ou erro para tratar a falha de execução. |
| [L144](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L144) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L145](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L145) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L146](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L146) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L147](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L147) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L148](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L148) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L149](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L149) | Continua expressão ou bloco pertencente a função submitJobReport. |
| [L150](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L150) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L151](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L151) | Linha em branco: separa visualmente trechos do código. |
| [L152](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L152) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L153](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L153) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L154](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L154) | Linha em branco: separa visualmente trechos do código. |
| [L155](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L155) | Declara função/método dispatchJobReportNotifications, com seus parâmetros e comportamento. |
| [L156](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L156) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L157](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L157) | Executa ou lê resultados de uma consulta ao banco. |
| [L158](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L158) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L159](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L159) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L160](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L160) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L161](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L161) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L162](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L162) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L163](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L163) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L164](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L164) | Executa ou lê resultados de uma consulta ao banco. |
| [L165](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L165) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L166](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L166) | Atribui um valor à variável PHP $allDelivered. |
| [L167](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L167) | Linha em branco: separa visualmente trechos do código. |
| [L168](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L168) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L169](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L169) | Atribui um valor à variável PHP $notificationId. |
| [L170](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L170) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L171](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L171) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L172](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L172) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L173](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L173) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L174](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L174) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L175](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L175) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L176](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L176) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L177](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L177) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L178](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L178) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L179](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L179) | Captura exceção ou erro para tratar a falha de execução. |
| [L180](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L180) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L181](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L181) | Atribui um valor à variável PHP $allDelivered. |
| [L182](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L182) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L183](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L183) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L184](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L184) | Continua expressão ou bloco pertencente a função dispatchJobReportNotifications. |
| [L185](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L185) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L186](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L186) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L187](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L187) | Linha em branco: separa visualmente trechos do código. |
| [L188](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L188) | Declara função/método decideJobReport, com seus parâmetros e comportamento. |
| [L189](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L189) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L190](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L190) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L191](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L191) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L192](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L192) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L193](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L193) | Linha em branco: separa visualmente trechos do código. |
| [L194](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L194) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L195](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L195) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L196](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L196) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L197](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L197) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L198](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L198) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L199](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L199) | Executa ou lê resultados de uma consulta ao banco. |
| [L200](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L200) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L201](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L201) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L202](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L202) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L203](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L203) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L204](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L204) | Linha em branco: separa visualmente trechos do código. |
| [L205](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L205) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L206](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L206) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L207](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L207) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L208](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L208) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L209](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L209) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L210](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L210) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L211](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L211) | Linha em branco: separa visualmente trechos do código. |
| [L212](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L212) | Atribui um valor à variável PHP $status. |
| [L213](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L213) | Atribui um valor à variável PHP $parecer. |
| [L214](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L214) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L215](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L215) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L216](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L216) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L217](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L217) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L218](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L218) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L219](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L219) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L220](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L220) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L221](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L221) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L222](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L222) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L223](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L223) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L224](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L224) | Linha em branco: separa visualmente trechos do código. |
| [L225](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L225) | Atribui um valor à variável PHP $title. |
| [L226](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L226) | Atribui um valor à variável PHP $reporterMessage. |
| [L227](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L227) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L228](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L228) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L229](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L229) | Atribui um valor à variável PHP $companyMessage. |
| [L230](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L230) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L231](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L231) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L232](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L232) | Atribui um valor à variável PHP $notifications. |
| [L233](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L233) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L234](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L234) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L235](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L235) | Finaliza expressão, lista, função ou bloco sintático. |
| [L236](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L236) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L237](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L237) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L238](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L238) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L239](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L239) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L240](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L240) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L241](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L241) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L242](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L242) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L243](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L243) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L244](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L244) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L245](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L245) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L246](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L246) | Linha em branco: separa visualmente trechos do código. |
| [L247](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L247) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L248](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L248) | Captura exceção ou erro para tratar a falha de execução. |
| [L249](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L249) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L250](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L250) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L251](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L251) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L252](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L252) | Linha em branco: separa visualmente trechos do código. |
| [L253](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L253) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L254](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L254) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L255](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L255) | Captura exceção ou erro para tratar a falha de execução. |
| [L256](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L256) | Continua expressão ou bloco pertencente a função decideJobReport. |
| [L257](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L257) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L258](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L258) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L259](https://github.com/CauaChiappin/DevIN/blob/main/php/controllers/JobReports.php#L259) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
