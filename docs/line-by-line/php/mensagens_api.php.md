# Explicação linha a linha — php/mensagens_api.php

**Arquivo original:** [abrir código no GitHub](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php)  
**Linguagem:** PHP  
**Linhas de origem:** 845  
**SHA-256 da origem:** f0facd0ceb3d83baeec8a89bd1fc2df569c80162be7537fb1cada684871d9912

> Descrições iniciais produzidas automaticamente, linha a linha, por regras de sintaxe.
> Explicações de comportamento complexo ou ambíguo exigem revisão técnica humana.
> Os links abrem diretamente a linha correspondente do código real; nenhum
> arquivo executável é alterado pela geração desta documentação.

| Linha do código | Explicação da linha |
| ---: | --- |
| [L1](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L1) | Abre código PHP interpretado no servidor. |
| [L2](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L2) | Linha em branco: separa visualmente trechos do código. |
| [L3](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L3) | Habilita a verificação estrita de tipos no PHP. |
| [L4](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L4) | Linha em branco: separa visualmente trechos do código. |
| [L5](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L5) | Inclui dependência PHP necessária à execução do módulo. |
| [L6](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L6) | Inclui dependência PHP necessária à execução do módulo. |
| [L7](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L7) | Inclui dependência PHP necessária à execução do módulo. |
| [L8](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L8) | Inclui dependência PHP necessária à execução do módulo. |
| [L9](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L9) | Inclui dependência PHP necessária à execução do módulo. |
| [L10](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L10) | Inclui dependência PHP necessária à execução do módulo. |
| [L11](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L11) | Inclui dependência PHP necessária à execução do módulo. |
| [L12](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L12) | Linha em branco: separa visualmente trechos do código. |
| [L13](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L13) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L14](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L14) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L15](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L15) | Linha em branco: separa visualmente trechos do código. |
| [L16](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L16) | Declara função/método responderMensagens, com seus parâmetros e comportamento. |
| [L17](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L17) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L18](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L18) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L19](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L19) | Converte dados entre estrutura interna e representação JSON. |
| [L20](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L20) | Continua expressão ou bloco pertencente a função responderMensagens. |
| [L21](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L21) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L22](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L22) | Linha em branco: separa visualmente trechos do código. |
| [L23](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L23) | Declara função/método validarTipoChat, com seus parâmetros e comportamento. |
| [L24](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L24) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L25](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L25) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L26](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L26) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L27](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L27) | Linha em branco: separa visualmente trechos do código. |
| [L28](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L28) | Declara função/método chaveAtorChat, com seus parâmetros e comportamento. |
| [L29](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L29) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L30](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L30) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L31](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L31) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L32](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L32) | Linha em branco: separa visualmente trechos do código. |
| [L33](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L33) | Declara função/método registrarConversaChat, com seus parâmetros e comportamento. |
| [L34](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L34) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L35](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L35) | Atribui um valor à variável PHP $participantes. |
| [L36](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L36) | Continua expressão ou bloco pertencente a função registrarConversaChat. |
| [L37](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L37) | Atribui um valor à variável PHP $chaveAtor. |
| [L38](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L38) | Linha em branco: separa visualmente trechos do código. |
| [L39](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L39) | Define função anônima usada como operação ou callback. |
| [L40](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L40) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L41](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L41) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L42](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L42) | Controla a continuação ou saída de laço/seleção. |
| [L43](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L43) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L44](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L44) | Linha em branco: separa visualmente trechos do código. |
| [L45](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L45) | Atribui um valor à variável PHP $ocultadaPara. |
| [L46](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L46) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L47](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L47) | Continua expressão ou bloco pertencente a função anônima. |
| [L48](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L48) | Continua expressão ou bloco pertencente a função anônima. |
| [L49](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L49) | Finaliza expressão, lista, função ou bloco sintático. |
| [L50](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L50) | Continua expressão ou bloco pertencente a função anônima. |
| [L51](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L51) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L52](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L52) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L53](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L53) | Continua expressão ou bloco pertencente a função anônima. |
| [L54](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L54) | Linha em branco: separa visualmente trechos do código. |
| [L55](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L55) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L56](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L56) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L57](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L57) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L58](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L58) | Finaliza expressão, lista, função ou bloco sintático. |
| [L59](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L59) | Continua expressão ou bloco pertencente a função anônima. |
| [L60](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L60) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L61](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L61) | Linha em branco: separa visualmente trechos do código. |
| [L62](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L62) | Declara função/método mensagemOcultaParaAtor, com seus parâmetros e comportamento. |
| [L63](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L63) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L64](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L64) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L65](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L65) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L66](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L66) | Linha em branco: separa visualmente trechos do código. |
| [L67](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L67) | Declara função/método traduzirMensagemMyMemory, com seus parâmetros e comportamento. |
| [L68](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L68) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L69](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L69) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L70](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L70) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L71](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L71) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L72](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L72) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L73](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L73) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L74](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L74) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L75](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L75) | Linha em branco: separa visualmente trechos do código. |
| [L76](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L76) | Atribui um valor à variável PHP $idiomasMyMemory. |
| [L77](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L77) | Atribui um valor à variável PHP $idiomaAlvo. |
| [L78](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L78) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L79](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L79) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L80](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L80) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L81](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L81) | Linha em branco: separa visualmente trechos do código. |
| [L82](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L82) | Atribui um valor à variável PHP $restante. |
| [L83](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L83) | Atribui um valor à variável PHP $trechos. |
| [L84](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L84) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L85](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L85) | Atribui um valor à variável PHP $trecho. |
| [L86](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L86) | Atribui um valor à variável PHP $ultimoEspaco. |
| [L87](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L87) | Atribui um valor à variável PHP $tamanhoTrecho. |
| [L88](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L88) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L89](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L89) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L90](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L90) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L91](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L91) | Atribui um valor à variável PHP $restante. |
| [L92](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L92) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L93](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L93) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L94](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L94) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L95](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L95) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L96](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L96) | Linha em branco: separa visualmente trechos do código. |
| [L97](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L97) | Atribui um valor à variável PHP $traducao. |
| [L98](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L98) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L99](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L99) | Atribui um valor à variável PHP $url. |
| [L100](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L100) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L101](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L101) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L102](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L102) | Finaliza expressão, lista, função ou bloco sintático. |
| [L103](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L103) | Atribui um valor à variável PHP $curl. |
| [L104](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L104) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L105](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L105) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L106](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L106) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L107](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L107) | Linha em branco: separa visualmente trechos do código. |
| [L108](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L108) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L109](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L109) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L110](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L110) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L111](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L111) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L112](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L112) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L113](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L113) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L114](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L114) | Finaliza expressão, lista, função ou bloco sintático. |
| [L115](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L115) | Linha em branco: separa visualmente trechos do código. |
| [L116](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L116) | Atribui um valor à variável PHP $resposta. |
| [L117](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L117) | Atribui um valor à variável PHP $status. |
| [L118](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L118) | Atribui um valor à variável PHP $erro. |
| [L119](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L119) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L120](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L120) | Linha em branco: separa visualmente trechos do código. |
| [L121](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L121) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L122](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L122) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L123](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L123) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L124](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L124) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L125](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L125) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L126](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L126) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L127](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L127) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L128](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L128) | Linha em branco: separa visualmente trechos do código. |
| [L129](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L129) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L130](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L130) | Converte dados entre estrutura interna e representação JSON. |
| [L131](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L131) | Captura exceção ou erro para tratar a falha de execução. |
| [L132](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L132) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L133](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L133) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L134](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L134) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L135](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L135) | Linha em branco: separa visualmente trechos do código. |
| [L136](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L136) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L137](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L137) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L138](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L138) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L139](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L139) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L140](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L140) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L141](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L141) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L142](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L142) | Linha em branco: separa visualmente trechos do código. |
| [L143](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L143) | Atribui um valor à variável PHP $statusTraducao. |
| [L144](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L144) | Atribui um valor à variável PHP $textoTraduzido. |
| [L145](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L145) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L146](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L146) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L147](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L147) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L148](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L148) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L149](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L149) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L150](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L150) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L151](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L151) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L152](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L152) | Linha em branco: separa visualmente trechos do código. |
| [L153](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L153) | Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory. |
| [L154](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L154) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L155](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L155) | Linha em branco: separa visualmente trechos do código. |
| [L156](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L156) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L157](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L157) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L158](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L158) | Linha em branco: separa visualmente trechos do código. |
| [L159](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L159) | Declara função/método resumirConversasChat, com seus parâmetros e comportamento. |
| [L160](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L160) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L161](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L161) | Atribui um valor à variável PHP $contatos. |
| [L162](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L162) | Linha em branco: separa visualmente trechos do código. |
| [L163](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L163) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L164](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L164) | Atribui um valor à variável PHP $participantes. |
| [L165](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L165) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L166](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L166) | Controla a continuação ou saída de laço/seleção. |
| [L167](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L167) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L168](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L168) | Linha em branco: separa visualmente trechos do código. |
| [L169](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L169) | Atribui um valor à variável PHP $atorChave. |
| [L170](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L170) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L171](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L171) | Controla a continuação ou saída de laço/seleção. |
| [L172](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L172) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L173](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L173) | Atribui um valor à variável PHP $outroParticipante. |
| [L174](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L174) | Continua expressão ou bloco pertencente a função resumirConversasChat. |
| [L175](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L175) | Continua expressão ou bloco pertencente a função resumirConversasChat. |
| [L176](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L176) | Continua expressão ou bloco pertencente a função resumirConversasChat. |
| [L177](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L177) | Continua expressão ou bloco pertencente a função resumirConversasChat. |
| [L178](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L178) | Atribui um valor à variável PHP $idContatoNumerico. |
| [L179](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L179) | Atribui um valor à variável PHP $contatoSistema. |
| [L180](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L180) | Atribui um valor à variável PHP $contatoUsuario. |
| [L181](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L181) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L182](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L182) | Controla a continuação ou saída de laço/seleção. |
| [L183](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L183) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L184](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L184) | Linha em branco: separa visualmente trechos do código. |
| [L185](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L185) | Atribui um valor à variável PHP $chave. |
| [L186](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L186) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L187](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L187) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L188](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L188) | Linha em branco: separa visualmente trechos do código. |
| [L189](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L189) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L190](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L190) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L191](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L191) | Controla a continuação ou saída de laço/seleção. |
| [L192](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L192) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L193](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L193) | Linha em branco: separa visualmente trechos do código. |
| [L194](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L194) | Atribui um valor à variável PHP $remetenteEhAtor. |
| [L195](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L195) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L196](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L196) | Atribui um valor à variável PHP $destinatarioEhAtor. |
| [L197](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L197) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L198](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L198) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L199](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L199) | Controla a continuação ou saída de laço/seleção. |
| [L200](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L200) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L201](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L201) | Linha em branco: separa visualmente trechos do código. |
| [L202](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L202) | Atribui um valor à variável PHP $tipoContato. |
| [L203](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L203) | Atribui um valor à variável PHP $idContato. |
| [L204](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L204) | Atribui um valor à variável PHP $chave. |
| [L205](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L205) | Atribui um valor à variável PHP $idMensagem. |
| [L206](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L206) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L207](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L207) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L208](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L208) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L209](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L209) | Linha em branco: separa visualmente trechos do código. |
| [L210](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L210) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L211](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L211) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L212](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L212) | Continua expressão ou bloco pertencente a função resumirConversasChat. |
| [L213](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L213) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L214](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L214) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L215](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L215) | Linha em branco: separa visualmente trechos do código. |
| [L216](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L216) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L217](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L217) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L218](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L218) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L219](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L219) | Finaliza expressão, lista, função ou bloco sintático. |
| [L220](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L220) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L221](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L221) | Linha em branco: separa visualmente trechos do código. |
| [L222](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L222) | Declara função/método resumirArmazenamentoChat, com seus parâmetros e comportamento. |
| [L223](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L223) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L224](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L224) | Atribui um valor à variável PHP $mensagens. |
| [L225](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L225) | Atribui um valor à variável PHP $conversas. |
| [L226](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L226) | Continua expressão ou bloco pertencente a função resumirArmazenamentoChat. |
| [L227](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L227) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L228](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L228) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L229](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L229) | Linha em branco: separa visualmente trechos do código. |
| [L230](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L230) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L231](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L231) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L232](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L232) | Linha em branco: separa visualmente trechos do código. |
| [L233](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L233) | Declara função/método buscarUsuarioChat, com seus parâmetros e comportamento. |
| [L234](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L234) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L235](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L235) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L236](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L236) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L237](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L237) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L238](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L238) | Linha em branco: separa visualmente trechos do código. |
| [L239](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L239) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L240](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L240) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L241](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L241) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L242](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L242) | Linha em branco: separa visualmente trechos do código. |
| [L243](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L243) | Atribui um valor à variável PHP $tabela. |
| [L244](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L244) | Atribui um valor à variável PHP $coluna. |
| [L245](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L245) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L246](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L246) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L247](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L247) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L248](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L248) | Executa ou lê resultados de uma consulta ao banco. |
| [L249](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L249) | Continua expressão ou bloco pertencente a função buscarUsuarioChat. |
| [L250](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L250) | Linha em branco: separa visualmente trechos do código. |
| [L251](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L251) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L252](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L252) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L253](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L253) | Linha em branco: separa visualmente trechos do código. |
| [L254](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L254) | Declara função/método destinatarioValido, com seus parâmetros e comportamento. |
| [L255](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L255) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L256](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L256) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L257](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L257) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L258](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L258) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L259](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L259) | Linha em branco: separa visualmente trechos do código. |
| [L260](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L260) | Atribui um valor à variável PHP $destinatario. |
| [L261](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L261) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L262](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L262) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L263](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L263) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L264](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L264) | Linha em branco: separa visualmente trechos do código. |
| [L265](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L265) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L266](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L266) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L267](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L267) | Linha em branco: separa visualmente trechos do código. |
| [L268](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L268) | Declara função/método atorTemConversaChat, com seus parâmetros e comportamento. |
| [L269](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L269) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L270](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L270) | Atribui um valor à variável PHP $resumo. |
| [L271](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L271) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L272](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L272) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L273](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L273) | Linha em branco: separa visualmente trechos do código. |
| [L274](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L274) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L275](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L275) | Atribui um valor à variável PHP $ator. |
| [L276](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L276) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L277](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L277) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L278](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L278) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L279](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L279) | Linha em branco: separa visualmente trechos do código. |
| [L280](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L280) | Atribui um valor à variável PHP $conn. |
| [L281](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L281) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L282](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L282) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L283](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L283) | Atribui um valor à variável PHP $acao. |
| [L284](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L284) | Linha em branco: separa visualmente trechos do código. |
| [L285](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L285) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L286](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L286) | Atribui um valor à variável PHP $tipo. |
| [L287](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L287) | Atribui um valor à variável PHP $id. |
| [L288](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L288) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L289](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L289) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L290](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L290) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L291](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L291) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L292](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L292) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L293](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L293) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L294](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L294) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L295](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L295) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L296](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L296) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L297](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L297) | Linha em branco: separa visualmente trechos do código. |
| [L298](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L298) | Atribui um valor à variável PHP $perfil. |
| [L299](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L299) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L300](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L300) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L301](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L301) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L302](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L302) | Linha em branco: separa visualmente trechos do código. |
| [L303](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L303) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L304](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L304) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L305](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L305) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L306](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L306) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L307](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L307) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L308](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L308) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L309](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L309) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L310](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L310) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L311](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L311) | Finaliza expressão, lista, função ou bloco sintático. |
| [L312](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L312) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L313](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L313) | Linha em branco: separa visualmente trechos do código. |
| [L314](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L314) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L315](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L315) | Atribui um valor à variável PHP $tipo. |
| [L316](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L316) | Atribui um valor à variável PHP $id. |
| [L317](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L317) | Atribui um valor à variável PHP $perfis. |
| [L318](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L318) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L319](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L319) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L320](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L320) | Finaliza expressão, lista, função ou bloco sintático. |
| [L321](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L321) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L322](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L322) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L323](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L323) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L324](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L324) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L325](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L325) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L326](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L326) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L327](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L327) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L328](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L328) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L329](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L329) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L330](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L330) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L331](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L331) | Linha em branco: separa visualmente trechos do código. |
| [L332](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L332) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L333](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L333) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L334](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L334) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L335](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L335) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L336](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L336) | Executa ou lê resultados de uma consulta ao banco. |
| [L337](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L337) | Atribui um valor à variável PHP $foto. |
| [L338](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L338) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L339](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L339) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L340](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L340) | Linha em branco: separa visualmente trechos do código. |
| [L341](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L341) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L342](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L342) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L343](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L343) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L344](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L344) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L345](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L345) | Linha em branco: separa visualmente trechos do código. |
| [L346](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L346) | Atribui um valor à variável PHP $conteudoFoto. |
| [L347](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L347) | Atribui um valor à variável PHP $caminhoFoto. |
| [L348](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L348) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L349](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L349) | Atribui um valor à variável PHP $conteudoFoto. |
| [L350](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L350) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L351](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L351) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L352](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L352) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L353](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L353) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L354](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L354) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L355](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L355) | Linha em branco: separa visualmente trechos do código. |
| [L356](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L356) | Atribui um valor à variável PHP $mime. |
| [L357](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L357) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L358](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L358) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L359](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L359) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L360](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L360) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L361](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L361) | Linha em branco: separa visualmente trechos do código. |
| [L362](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L362) | Atribui um valor à variável PHP $etag. |
| [L363](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L363) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L364](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L364) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L365](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L365) | Atribui um valor à variável PHP $etagsSolicitadas. |
| [L366](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L366) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L367](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L367) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L368](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L368) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L369](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L369) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L370](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L370) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L371](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L371) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L372](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L372) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L373](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L373) | Linha em branco: separa visualmente trechos do código. |
| [L374](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L374) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L375](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L375) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L376](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L376) | Produz saída ou conteúdo da resposta do programa. |
| [L377](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L377) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L378](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L378) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L379](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L379) | Linha em branco: separa visualmente trechos do código. |
| [L380](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L380) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L381](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L381) | Atribui um valor à variável PHP $termo. |
| [L382](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L382) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L383](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L383) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L384](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L384) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L385](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L385) | Linha em branco: separa visualmente trechos do código. |
| [L386](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L386) | Atribui um valor à variável PHP $contatosEncontrados. |
| [L387](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L387) | Atribui um valor à variável PHP $busca. |
| [L388](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L388) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L389](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L389) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L390](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L390) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L391](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L391) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L392](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L392) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L393](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L393) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L394](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L394) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L395](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L395) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L396](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L396) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L397](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L397) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L398](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L398) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L399](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L399) | Prepara, parametriza ou executa operação SQL por interface do banco. |
| [L400](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L400) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L401](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L401) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L402](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L402) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L403](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L403) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L404](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L404) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L405](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L405) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L406](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L406) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L407](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L407) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L408](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L408) | Finaliza expressão, lista, função ou bloco sintático. |
| [L409](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L409) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L410](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L410) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L411](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L411) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L412](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L412) | Linha em branco: separa visualmente trechos do código. |
| [L413](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L413) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L414](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L414) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L415](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L415) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L416](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L416) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L417](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L417) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L418](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L418) | Linha em branco: separa visualmente trechos do código. |
| [L419](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L419) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L420](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L420) | Atribui um valor à variável PHP $resumo. |
| [L421](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L421) | Atribui um valor à variável PHP $contatos. |
| [L422](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L422) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L423](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L423) | Continua expressão ou bloco pertencente a função atorTemConversaChat. |
| [L424](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L424) | Atribui um valor à variável PHP $usuario. |
| [L425](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L425) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L426](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L426) | Controla a continuação ou saída de laço/seleção. |
| [L427](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L427) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L428](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L428) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L429](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L429) | Define um dos caminhos possíveis de uma seleção de casos. |
| [L430](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L430) | Atribui um valor à variável PHP $rotuloContato. |
| [L431](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L431) | Controla a continuação ou saída de laço/seleção. |
| [L432](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L432) | Define um dos caminhos possíveis de uma seleção de casos. |
| [L433](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L433) | Atribui um valor à variável PHP $rotuloContato. |
| [L434](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L434) | Controla a continuação ou saída de laço/seleção. |
| [L435](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L435) | Define um dos caminhos possíveis de uma seleção de casos. |
| [L436](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L436) | Atribui um valor à variável PHP $rotuloContato. |
| [L437](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L437) | Controla a continuação ou saída de laço/seleção. |
| [L438](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L438) | Define um dos caminhos possíveis de uma seleção de casos. |
| [L439](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L439) | Atribui um valor à variável PHP $rotuloContato. |
| [L440](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L440) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L441](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L441) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L442](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L442) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L443](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L443) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L444](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L444) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L445](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L445) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L446](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L446) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L447](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L447) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L448](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L448) | Finaliza expressão, lista, função ou bloco sintático. |
| [L449](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L449) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L450](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L450) | Linha em branco: separa visualmente trechos do código. |
| [L451](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L451) | Define função anônima usada como operação ou callback. |
| [L452](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L452) | Atribui um valor à variável PHP $recencia. |
| [L453](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L453) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L454](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L454) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L455](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L455) | Continua expressão ou bloco pertencente a função anônima. |
| [L456](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L456) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L457](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L457) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L458](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L458) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L459](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L459) | Finaliza expressão, lista, função ou bloco sintático. |
| [L460](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L460) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L461](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L461) | Linha em branco: separa visualmente trechos do código. |
| [L462](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L462) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L463](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L463) | Atribui um valor à variável PHP $resumo. |
| [L464](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L464) | Continua expressão ou bloco pertencente a função anônima. |
| [L465](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L465) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L466](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L466) | Linha em branco: separa visualmente trechos do código. |
| [L467](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L467) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L468](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L468) | Atribui um valor à variável PHP $tipo. |
| [L469](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L469) | Atribui um valor à variável PHP $id. |
| [L470](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L470) | Atribui um valor à variável PHP $depoisDe. |
| [L471](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L471) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L472](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L472) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L473](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L473) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L474](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L474) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L475](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L475) | Continua expressão ou bloco pertencente a função anônima. |
| [L476](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L476) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L477](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L477) | Linha em branco: separa visualmente trechos do código. |
| [L478](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L478) | Define função anônima usada como operação ou callback. |
| [L479](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L479) | Define função anônima usada como operação ou callback. |
| [L480](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L480) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L481](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L481) | Continua expressão ou bloco pertencente a função anônima. |
| [L482](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L482) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L483](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L483) | Continua expressão ou bloco pertencente a função anônima. |
| [L484](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L484) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L485](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L485) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L486](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L486) | Linha em branco: separa visualmente trechos do código. |
| [L487](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L487) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L488](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L488) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L489](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L489) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L490](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L490) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L491](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L491) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L492](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L492) | Continua expressão ou bloco pertencente a função anônima. |
| [L493](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L493) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L494](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L494) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L495](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L495) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L496](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L496) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L497](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L497) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L498](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L498) | Finaliza expressão, lista, função ou bloco sintático. |
| [L499](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L499) | Continua expressão ou bloco pertencente a função anônima. |
| [L500](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L500) | Linha em branco: separa visualmente trechos do código. |
| [L501](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L501) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L502](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L502) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L503](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L503) | Continua expressão ou bloco pertencente a função anônima. |
| [L504](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L504) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L505](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L505) | Continua expressão ou bloco pertencente a função anônima. |
| [L506](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L506) | Controla a continuação ou saída de laço/seleção. |
| [L507](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L507) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L508](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L508) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L509](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L509) | Continua expressão ou bloco pertencente a função anônima. |
| [L510](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L510) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L511](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L511) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L512](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L512) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L513](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L513) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L514](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L514) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L515](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L515) | Continua expressão ou bloco pertencente a função anônima. |
| [L516](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L516) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L517](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L517) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L518](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L518) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L519](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L519) | Continua expressão ou bloco pertencente a função anônima. |
| [L520](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L520) | Linha em branco: separa visualmente trechos do código. |
| [L521](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L521) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L522](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L522) | Atribui um valor à variável PHP $conversa. |
| [L523](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L523) | Define ramo alternativo para condição anterior. |
| [L524](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L524) | Atribui um valor à variável PHP $recentes. |
| [L525](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L525) | Atribui um valor à variável PHP $novas. |
| [L526](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L526) | Continua expressão ou bloco pertencente a função anônima. |
| [L527](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L527) | Continua expressão ou bloco pertencente a função anônima. |
| [L528](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L528) | Finaliza expressão, lista, função ou bloco sintático. |
| [L529](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L529) | Atribui um valor à variável PHP $atualizadas. |
| [L530](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L530) | Continua expressão ou bloco pertencente a função anônima. |
| [L531](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L531) | Continua expressão ou bloco pertencente a função anônima. |
| [L532](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L532) | Finaliza expressão, lista, função ou bloco sintático. |
| [L533](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L533) | Atribui um valor à variável PHP $porId. |
| [L534](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L534) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L535](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L535) | Continua expressão ou bloco pertencente a função anônima. |
| [L536](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L536) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L537](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L537) | Atribui um valor à variável PHP $conversa. |
| [L538](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L538) | Continua expressão ou bloco pertencente a função anônima. |
| [L539](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L539) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L540](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L540) | Linha em branco: separa visualmente trechos do código. |
| [L541](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L541) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L542](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L542) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L543](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L543) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L544](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L544) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L545](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L545) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L546](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L546) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L547](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L547) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L548](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L548) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L549](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L549) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L550](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L550) | Continua expressão ou bloco pertencente a função anônima. |
| [L551](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L551) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L552](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L552) | Linha em branco: separa visualmente trechos do código. |
| [L553](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L553) | Continua expressão ou bloco pertencente a função anônima. |
| [L554](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L554) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L555](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L555) | Linha em branco: separa visualmente trechos do código. |
| [L556](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L556) | Continua expressão ou bloco pertencente a função anônima. |
| [L557](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L557) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L558](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L558) | Linha em branco: separa visualmente trechos do código. |
| [L559](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L559) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L560](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L560) | Ajusta cabeçalho, sessão ou resposta HTTP do servidor. |
| [L561](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L561) | Continua expressão ou bloco pertencente a função anônima. |
| [L562](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L562) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L563](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L563) | Linha em branco: separa visualmente trechos do código. |
| [L564](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L564) | Continua expressão ou bloco pertencente a função anônima. |
| [L565](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L565) | Converte dados entre estrutura interna e representação JSON. |
| [L566](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L566) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L567](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L567) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L568](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L568) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L569](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L569) | Linha em branco: separa visualmente trechos do código. |
| [L570](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L570) | Atribui um valor à variável PHP $acao. |
| [L571](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L571) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L572](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L572) | Atribui um valor à variável PHP $tipo. |
| [L573](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L573) | Atribui um valor à variável PHP $id. |
| [L574](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L574) | Atribui um valor à variável PHP $motivo. |
| [L575](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L575) | Atribui um valor à variável PHP $detalhes. |
| [L576](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L576) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L577](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L577) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L578](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L578) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L579](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L579) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L580](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L580) | Continua expressão ou bloco pertencente a função anônima. |
| [L581](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L581) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L582](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L582) | Continua expressão ou bloco pertencente a função anônima. |
| [L583](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L583) | Continua expressão ou bloco pertencente a função anônima. |
| [L584](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L584) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L585](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L585) | Linha em branco: separa visualmente trechos do código. |
| [L586](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L586) | Continua expressão ou bloco pertencente a função anônima. |
| [L587](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L587) | Atribui um valor à variável PHP $language. |
| [L588](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L588) | Atribui um valor à variável PHP $submittedReport. |
| [L589](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L589) | Atribui um valor à variável PHP $reportId. |
| [L590](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L590) | Atribui um valor à variável PHP $reportedName. |
| [L591](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L591) | Atribui um valor à variável PHP $warnings. |
| [L592](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L592) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L593](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L593) | Continua expressão ou bloco pertencente a função anônima. |
| [L594](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L594) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L595](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L595) | Continua expressão ou bloco pertencente a função anônima. |
| [L596](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L596) | Continua expressão ou bloco pertencente a função anônima. |
| [L597](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L597) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L598](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L598) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L599](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L599) | Captura exceção ou erro para tratar a falha de execução. |
| [L600](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L600) | Continua expressão ou bloco pertencente a função anônima. |
| [L601](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L601) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L602](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L602) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L603](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L603) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L604](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L604) | Continua expressão ou bloco pertencente a função anônima. |
| [L605](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L605) | Continua expressão ou bloco pertencente a função anônima. |
| [L606](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L606) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L607](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L607) | Continua expressão ou bloco pertencente a função anônima. |
| [L608](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L608) | Continua expressão ou bloco pertencente a função anônima. |
| [L609](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L609) | Continua expressão ou bloco pertencente a função anônima. |
| [L610](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L610) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L611](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L611) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L612](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L612) | Atribui um valor à variável PHP $response. |
| [L613](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L613) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L614](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L614) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L615](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L615) | Finaliza expressão, lista, função ou bloco sintático. |
| [L616](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L616) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L617](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L617) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L618](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L618) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L619](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L619) | Continua expressão ou bloco pertencente a função anônima. |
| [L620](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L620) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L621](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L621) | Linha em branco: separa visualmente trechos do código. |
| [L622](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L622) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L623](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L623) | Atribui um valor à variável PHP $tipo. |
| [L624](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L624) | Atribui um valor à variável PHP $id. |
| [L625](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L625) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L626](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L626) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L627](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L627) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L628](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L628) | Linha em branco: separa visualmente trechos do código. |
| [L629](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L629) | Atribui um valor à variável PHP $destinatario. |
| [L630](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L630) | Continua expressão ou bloco pertencente a função anônima. |
| [L631](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L631) | Continua expressão ou bloco pertencente a função anônima. |
| [L632](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L632) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L633](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L633) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L634](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L634) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L635](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L635) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L636](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L636) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L637](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L637) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L638](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L638) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L639](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L639) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L640](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L640) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L641](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L641) | Finaliza expressão, lista, função ou bloco sintático. |
| [L642](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L642) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L643](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L643) | Linha em branco: separa visualmente trechos do código. |
| [L644](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L644) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L645](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L645) | Atribui um valor à variável PHP $texto. |
| [L646](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L646) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L647](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L647) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L648](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L648) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L649](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L649) | Linha em branco: separa visualmente trechos do código. |
| [L650](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L650) | Atribui um valor à variável PHP $idioma. |
| [L651](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L651) | Continua expressão ou bloco pertencente a função anônima. |
| [L652](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L652) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L653](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L653) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L654](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L654) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L655](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L655) | Finaliza expressão, lista, função ou bloco sintático. |
| [L656](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L656) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L657](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L657) | Linha em branco: separa visualmente trechos do código. |
| [L658](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L658) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L659](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L659) | Atribui um valor à variável PHP $idMensagem. |
| [L660](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L660) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L661](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L661) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L662](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L662) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L663](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L663) | Linha em branco: separa visualmente trechos do código. |
| [L664](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L664) | Define função anônima usada como operação ou callback. |
| [L665](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L665) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L666](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L666) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L667](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L667) | Controla a continuação ou saída de laço/seleção. |
| [L668](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L668) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L669](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L669) | Linha em branco: separa visualmente trechos do código. |
| [L670](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L670) | Atribui um valor à variável PHP $atorEhParticipante. |
| [L671](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L671) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L672](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L672) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L673](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L673) | Continua expressão ou bloco pertencente a função anônima. |
| [L674](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L674) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L675](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L675) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L676](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L676) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L677](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L677) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L678](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L678) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L679](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L679) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L680](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L680) | Linha em branco: separa visualmente trechos do código. |
| [L681](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L681) | Atribui um valor à variável PHP $chaveAtor. |
| [L682](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L682) | Atribui um valor à variável PHP $ocultadaPara. |
| [L683](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L683) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L684](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L684) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L685](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L685) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L686](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L686) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L687](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L687) | Linha em branco: separa visualmente trechos do código. |
| [L688](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L688) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L689](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L689) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L690](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L690) | Continua expressão ou bloco pertencente a função anônima. |
| [L691](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L691) | Linha em branco: separa visualmente trechos do código. |
| [L692](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L692) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L693](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L693) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L694](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L694) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L695](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L695) | Continua expressão ou bloco pertencente a função anônima. |
| [L696](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L696) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L697](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L697) | Linha em branco: separa visualmente trechos do código. |
| [L698](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L698) | Continua expressão ou bloco pertencente a função anônima. |
| [L699](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L699) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L700](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L700) | Linha em branco: separa visualmente trechos do código. |
| [L701](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L701) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L702](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L702) | Atribui um valor à variável PHP $idMensagem. |
| [L703](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L703) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L704](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L704) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L705](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L705) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L706](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L706) | Linha em branco: separa visualmente trechos do código. |
| [L707](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L707) | Define função anônima usada como operação ou callback. |
| [L708](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L708) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L709](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L709) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L710](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L710) | Continua expressão ou bloco pertencente a função anônima. |
| [L711](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L711) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L712](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L712) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L713](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L713) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L714](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L714) | Continua expressão ou bloco pertencente a função anônima. |
| [L715](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L715) | Controla a continuação ou saída de laço/seleção. |
| [L716](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L716) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L717](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L717) | Linha em branco: separa visualmente trechos do código. |
| [L718](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L718) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L719](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L719) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L720](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L720) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L721](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L721) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L722](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L722) | Continua expressão ou bloco pertencente a função anônima. |
| [L723](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L723) | Linha em branco: separa visualmente trechos do código. |
| [L724](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L724) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L725](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L725) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L726](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L726) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L727](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L727) | Continua expressão ou bloco pertencente a função anônima. |
| [L728](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L728) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L729](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L729) | Linha em branco: separa visualmente trechos do código. |
| [L730](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L730) | Continua expressão ou bloco pertencente a função anônima. |
| [L731](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L731) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L732](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L732) | Linha em branco: separa visualmente trechos do código. |
| [L733](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L733) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L734](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L734) | Atribui um valor à variável PHP $tipo. |
| [L735](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L735) | Atribui um valor à variável PHP $id. |
| [L736](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L736) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L737](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L737) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L738](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L738) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L739](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L739) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L740](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L740) | Continua expressão ou bloco pertencente a função anônima. |
| [L741](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L741) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L742](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L742) | Linha em branco: separa visualmente trechos do código. |
| [L743](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L743) | Define função anônima usada como operação ou callback. |
| [L744](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L744) | Atribui um valor à variável PHP $chaveAtor. |
| [L745](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L745) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L746](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L746) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L747](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L747) | Continua expressão ou bloco pertencente a função anônima. |
| [L748](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L748) | Continua expressão ou bloco pertencente a função anônima. |
| [L749](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L749) | Controla a continuação ou saída de laço/seleção. |
| [L750](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L750) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L751](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L751) | Atribui um valor à variável PHP $mensagemPertenceAConversa. |
| [L752](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L752) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L753](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L753) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L754](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L754) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L755](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L755) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L756](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L756) | Continua expressão ou bloco pertencente a função anônima. |
| [L757](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L757) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L758](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L758) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L759](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L759) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L760](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L760) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L761](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L761) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L762](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L762) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L763](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L763) | Controla a continuação ou saída de laço/seleção. |
| [L764](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L764) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L765](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L765) | Linha em branco: separa visualmente trechos do código. |
| [L766](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L766) | Atribui um valor à variável PHP $ocultadaPara. |
| [L767](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L767) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L768](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L768) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L769](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L769) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L770](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L770) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L771](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L771) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L772](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L772) | Continua expressão ou bloco pertencente a função anônima. |
| [L773](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L773) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L774](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L774) | Define função anônima usada como operação ou callback. |
| [L775](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L775) | Atribui um valor à variável PHP $participantes. |
| [L776](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L776) | Continua expressão ou bloco pertencente a função anônima. |
| [L777](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L777) | Atribui um valor à variável PHP $chaveAtor. |
| [L778](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L778) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L779](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L779) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L780](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L780) | Controla a continuação ou saída de laço/seleção. |
| [L781](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L781) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L782](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L782) | Atribui um valor à variável PHP $ocultadaPara. |
| [L783](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L783) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L784](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L784) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L785](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L785) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L786](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L786) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L787](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L787) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L788](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L788) | Continua expressão ou bloco pertencente a função anônima. |
| [L789](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L789) | Continua expressão ou bloco pertencente a função anônima. |
| [L790](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L790) | Linha em branco: separa visualmente trechos do código. |
| [L791](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L791) | Continua expressão ou bloco pertencente a função anônima. |
| [L792](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L792) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L793](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L793) | Linha em branco: separa visualmente trechos do código. |
| [L794](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L794) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L795](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L795) | Continua expressão ou bloco pertencente a função anônima. |
| [L796](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L796) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L797](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L797) | Linha em branco: separa visualmente trechos do código. |
| [L798](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L798) | Atribui um valor à variável PHP $tipo. |
| [L799](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L799) | Atribui um valor à variável PHP $id. |
| [L800](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L800) | Atribui um valor à variável PHP $mensagem. |
| [L801](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L801) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L802](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L802) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L803](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L803) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L804](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L804) | Linha em branco: separa visualmente trechos do código. |
| [L805](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L805) | Continua expressão ou bloco pertencente a função anônima. |
| [L806](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L806) | Continua expressão ou bloco pertencente a função anônima. |
| [L807](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L807) | Define função anônima usada como operação ou callback. |
| [L808](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L808) | Atribui um valor à variável PHP $proximoId. |
| [L809](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L809) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L810](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L810) | Atribui um valor à variável PHP $proximoId. |
| [L811](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L811) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L812](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L812) | Linha em branco: separa visualmente trechos do código. |
| [L813](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L813) | Atribui um valor à variável PHP $novaMensagem. |
| [L814](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L814) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L815](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L815) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L816](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L816) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L817](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L817) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L818](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L818) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L819](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L819) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L820](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L820) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L821](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L821) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L822](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L822) | Finaliza expressão, lista, função ou bloco sintático. |
| [L823](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L823) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L824](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L824) | Linha em branco: separa visualmente trechos do código. |
| [L825](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L825) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L826](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L826) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L827](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L827) | Linha em branco: separa visualmente trechos do código. |
| [L828](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L828) | Continua expressão ou bloco pertencente a função anônima. |
| [L829](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L829) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L830](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L830) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L831](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L831) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L832](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L832) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L833](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L833) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L834](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L834) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L835](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L835) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L836](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L836) | Continua expressão ou bloco pertencente a função anônima. |
| [L837](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L837) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L838](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L838) | Continua expressão ou bloco pertencente a função anônima. |
| [L839](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L839) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L840](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L840) | Captura exceção ou erro para tratar a falha de execução. |
| [L841](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L841) | Continua expressão ou bloco pertencente a função anônima. |
| [L842](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L842) | Captura exceção ou erro para tratar a falha de execução. |
| [L843](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L843) | Continua expressão ou bloco pertencente a função anônima. |
| [L844](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L844) | Continua expressão ou bloco pertencente a função anônima. |
| [L845](https://github.com/CauaChiappin/DevIN/blob/main/php/mensagens_api.php#L845) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
