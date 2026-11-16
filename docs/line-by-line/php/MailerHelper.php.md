# Explicação linha a linha — php/MailerHelper.php

**Arquivo original:** [abrir código no GitHub](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php)  
**Linguagem:** PHP  
**Linhas de origem:** 370  
**SHA-256 da origem:** 4ffa02472c08c716e7da525c3753887a8e229a99ac113d461cb837ded7882dce

> Descrições iniciais produzidas automaticamente, linha a linha, por regras de sintaxe.
> Explicações de comportamento complexo ou ambíguo exigem revisão técnica humana.
> Os links abrem diretamente a linha correspondente do código real; nenhum
> arquivo executável é alterado pela geração desta documentação.

| Linha do código | Explicação da linha |
| ---: | --- |
| [L1](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L1) | Abre código PHP interpretado no servidor. |
| [L2](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L2) | Linha em branco: separa visualmente trechos do código. |
| [L3](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L3) | Habilita a verificação estrita de tipos no PHP. |
| [L4](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L4) | Linha em branco: separa visualmente trechos do código. |
| [L5](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L5) | Importa ou exporta dependência, nome, classe ou módulo. |
| [L6](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L6) | Importa ou exporta dependência, nome, classe ou módulo. |
| [L7](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L7) | Linha em branco: separa visualmente trechos do código. |
| [L8](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L8) | Inclui dependência PHP necessária à execução do módulo. |
| [L9](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L9) | Inclui dependência PHP necessária à execução do módulo. |
| [L10](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L10) | Linha em branco: separa visualmente trechos do código. |
| [L11](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L11) | Declara o tipo MailerHelper e inicia sua estrutura. |
| [L12](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L12) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L13](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L13) | Define membro, visibilidade ou método de uma classe. |
| [L14](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L14) | Define membro, visibilidade ou método de uma classe. |
| [L15](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L15) | Linha em branco: separa visualmente trechos do código. |
| [L16](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L16) | Declara função/método mensagem, com seus parâmetros e comportamento. |
| [L17](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L17) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L18](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L18) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L19](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L19) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L20](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L20) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L21](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L21) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L22](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L22) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L23](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L23) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L24](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L24) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L25](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L25) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L26](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L26) | Linha em branco: separa visualmente trechos do código. |
| [L27](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L27) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L28](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L28) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L29](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L29) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L30](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L30) | Declara função/método getMailer, com seus parâmetros e comportamento. |
| [L31](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L31) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L32](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L32) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L33](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L33) | Atribui um valor à variável PHP $mail. |
| [L34](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L34) | Linha em branco: separa visualmente trechos do código. |
| [L35](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L35) | Define função anônima usada como operação ou callback. |
| [L36](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L36) | Atribui um valor à variável PHP $value. |
| [L37](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L37) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L38](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L38) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L39](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L39) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L40](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L40) | Linha em branco: separa visualmente trechos do código. |
| [L41](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L41) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L42](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L42) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L43](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L43) | Linha em branco: separa visualmente trechos do código. |
| [L44](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L44) | Atribui um valor à variável PHP $host. |
| [L45](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L45) | Atribui um valor à variável PHP $port. |
| [L46](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L46) | Atribui um valor à variável PHP $encryption. |
| [L47](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L47) | Atribui um valor à variável PHP $username. |
| [L48](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L48) | Atribui um valor à variável PHP $password. |
| [L49](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L49) | Atribui um valor à variável PHP $fromEmail. |
| [L50](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L50) | Atribui um valor à variável PHP $fromName. |
| [L51](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L51) | Linha em branco: separa visualmente trechos do código. |
| [L52](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L52) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L53](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L53) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L54](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L54) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L55](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L55) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L56](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L56) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L57](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L57) | Continua expressão ou bloco pertencente a função anônima. |
| [L58](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L58) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L59](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L59) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L60](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L60) | Linha em branco: separa visualmente trechos do código. |
| [L61](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L61) | Continua expressão ou bloco pertencente a função anônima. |
| [L62](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L62) | Continua expressão ou bloco pertencente a função anônima. |
| [L63](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L63) | Continua expressão ou bloco pertencente a função anônima. |
| [L64](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L64) | Continua expressão ou bloco pertencente a função anônima. |
| [L65](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L65) | Continua expressão ou bloco pertencente a função anônima. |
| [L66](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L66) | Linha em branco: separa visualmente trechos do código. |
| [L67](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L67) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L68](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L68) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L69](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L69) | Continua expressão ou bloco pertencente a função anônima. |
| [L70](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L70) | Define ramo alternativo para condição anterior. |
| [L71](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L71) | Continua expressão ou bloco pertencente a função anônima. |
| [L72](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L72) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L73](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L73) | Linha em branco: separa visualmente trechos do código. |
| [L74](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L74) | Continua expressão ou bloco pertencente a função anônima. |
| [L75](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L75) | Continua expressão ou bloco pertencente a função anônima. |
| [L76](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L76) | Continua expressão ou bloco pertencente a função anônima. |
| [L77](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L77) | Continua expressão ou bloco pertencente a função anônima. |
| [L78](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L78) | Linha em branco: separa visualmente trechos do código. |
| [L79](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L79) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L80](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L80) | Continua expressão ou bloco pertencente a função anônima. |
| [L81](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L81) | Linha em branco: separa visualmente trechos do código. |
| [L82](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L82) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L83](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L83) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L84](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L84) | Linha em branco: separa visualmente trechos do código. |
| [L85](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L85) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L86](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L86) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L87](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L87) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L88](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L88) | Declara função/método enviar, com seus parâmetros e comportamento. |
| [L89](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L89) | Continua expressão ou bloco pertencente a função enviar. |
| [L90](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L90) | Continua expressão ou bloco pertencente a função enviar. |
| [L91](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L91) | Continua expressão ou bloco pertencente a função enviar. |
| [L92](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L92) | Continua expressão ou bloco pertencente a função enviar. |
| [L93](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L93) | Continua expressão ou bloco pertencente a função enviar. |
| [L94](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L94) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L95](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L95) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L96](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L96) | Continua expressão ou bloco pertencente a função enviar. |
| [L97](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L97) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L98](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L98) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L99](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L99) | Linha em branco: separa visualmente trechos do código. |
| [L100](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L100) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L101](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L101) | Atribui um valor à variável PHP $mail. |
| [L102](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L102) | Linha em branco: separa visualmente trechos do código. |
| [L103](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L103) | Continua expressão ou bloco pertencente a função enviar. |
| [L104](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L104) | Continua expressão ou bloco pertencente a função enviar. |
| [L105](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L105) | Continua expressão ou bloco pertencente a função enviar. |
| [L106](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L106) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L107](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L107) | Linha em branco: separa visualmente trechos do código. |
| [L108](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L108) | Continua expressão ou bloco pertencente a função enviar. |
| [L109](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L109) | Continua expressão ou bloco pertencente a função enviar. |
| [L110](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L110) | Continua expressão ou bloco pertencente a função enviar. |
| [L111](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L111) | Linha em branco: separa visualmente trechos do código. |
| [L112](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L112) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L113](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L113) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L114](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L114) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L115](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L115) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L116](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L116) | Continua expressão ou bloco pertencente a função enviar. |
| [L117](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L117) | Linha em branco: separa visualmente trechos do código. |
| [L118](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L118) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L119](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L119) | Linha em branco: separa visualmente trechos do código. |
| [L120](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L120) | Captura exceção ou erro para tratar a falha de execução. |
| [L121](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L121) | Continua expressão ou bloco pertencente a função enviar. |
| [L122](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L122) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L123](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L123) | Captura exceção ou erro para tratar a falha de execução. |
| [L124](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L124) | Continua expressão ou bloco pertencente a função enviar. |
| [L125](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L125) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L126](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L126) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L127](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L127) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L128](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L128) | Linha em branco: separa visualmente trechos do código. |
| [L129](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L129) | Declara função/método enviarConfirmacaoDenuncia, com seus parâmetros e comportamento. |
| [L130](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L130) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L131](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L131) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L132](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L132) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L133](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L133) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L134](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L134) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L135](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L135) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L136](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L136) | Atribui um valor à variável PHP $nomeSeguro. |
| [L137](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L137) | Atribui um valor à variável PHP $alvoSeguro. |
| [L138](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L138) | Atribui um valor à variável PHP $tipoPt. |
| [L139](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L139) | Atribui um valor à variável PHP $tipoEn. |
| [L140](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L140) | Atribui um valor à variável PHP $tipoEs. |
| [L141](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L141) | Atribui um valor à variável PHP $assunto. |
| [L142](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L142) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L143](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L143) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L144](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L144) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L145](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L145) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L146](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L146) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L147](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L147) | Atribui um valor à variável PHP $corpo. |
| [L148](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L148) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L149](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L149) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L150](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L150) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L151](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L151) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L152](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L152) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L153](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L153) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L154](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L154) | Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia. |
| [L155](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L155) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L156](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L156) | Linha em branco: separa visualmente trechos do código. |
| [L157](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L157) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L158](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L158) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L159](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L159) | Linha em branco: separa visualmente trechos do código. |
| [L160](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L160) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L161](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L161) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L162](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L162) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L163](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L163) | Declara função/método enviarConfirmacaoCadastroCurriculo, com seus parâmetros e comportamento. |
| [L164](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L164) | Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo. |
| [L165](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L165) | Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo. |
| [L166](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L166) | Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo. |
| [L167](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L167) | Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo. |
| [L168](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L168) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L169](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L169) | Atribui um valor à variável PHP $nomeSeguro. |
| [L170](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L170) | Atribui um valor à variável PHP $assunto. |
| [L171](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L171) | Linha em branco: separa visualmente trechos do código. |
| [L172](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L172) | Atribui um valor à variável PHP $corpo. |
| [L173](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L173) | Declara agrupamento de elementos. |
| [L174](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L174) | Declara agrupamento de elementos. |
| [L175](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L175) | Declara subtítulo de segundo nível. |
| [L176](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L176) | Declara parágrafo. A linha reúne múltiplos elementos. |
| [L177](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L177) | Declara parágrafo. |
| [L178](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L178) | Declara parágrafo. |
| [L179](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L179) | Declara parágrafo. |
| [L180](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L180) | Declara link de navegação. |
| [L181](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L181) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L182](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L182) | Fecha elemento a do documento. |
| [L183](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L183) | Fecha elemento p do documento. |
| [L184](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L184) | Fecha elemento div do documento. |
| [L185](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L185) | Fecha elemento div do documento. |
| [L186](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L186) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L187](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L187) | Linha em branco: separa visualmente trechos do código. |
| [L188](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L188) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L189](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L189) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L190](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L190) | Linha em branco: separa visualmente trechos do código. |
| [L191](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L191) | Declara função/método enviarConfirmacaoCadastroEmpresa, com seus parâmetros e comportamento. |
| [L192](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L192) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L193](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L193) | Atribui um valor à variável PHP $nomeSeguro. |
| [L194](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L194) | Atribui um valor à variável PHP $corpo. |
| [L195](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L195) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L196](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L196) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L197](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L197) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L198](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L198) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L199](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L199) | Linha em branco: separa visualmente trechos do código. |
| [L200](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L200) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L201](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L201) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L202](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L202) | Linha em branco: separa visualmente trechos do código. |
| [L203](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L203) | Declara função/método enviarConfirmacaoCadastro, com seus parâmetros e comportamento. |
| [L204](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L204) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L205](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L205) | Atribui um valor à variável PHP $nomeSeguro. |
| [L206](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L206) | Atribui um valor à variável PHP $corpo. |
| [L207](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L207) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L208](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L208) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L209](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L209) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L210](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L210) | Linha em branco: separa visualmente trechos do código. |
| [L211](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L211) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L212](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L212) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L213](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L213) | Linha em branco: separa visualmente trechos do código. |
| [L214](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L214) | Declara função/método enviarConfirmacaoExclusao, com seus parâmetros e comportamento. |
| [L215](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L215) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L216](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L216) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L217](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L217) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L218](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L218) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L219](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L219) | Linha em branco: separa visualmente trechos do código. |
| [L220](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L220) | Atribui um valor à variável PHP $nomeSeguro. |
| [L221](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L221) | Atribui um valor à variável PHP $corpo. |
| [L222](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L222) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L223](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L223) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L224](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L224) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L225](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L225) | Linha em branco: separa visualmente trechos do código. |
| [L226](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L226) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L227](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L227) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L228](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L228) | Linha em branco: separa visualmente trechos do código. |
| [L229](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L229) | Declara função/método enviarAvisoAlteracaoSenha, com seus parâmetros e comportamento. |
| [L230](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L230) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L231](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L231) | Atribui um valor à variável PHP $nomeSeguro. |
| [L232](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L232) | Atribui um valor à variável PHP $corpo. |
| [L233](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L233) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L234](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L234) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L235](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L235) | Continua a expressão lógica, encadeamento ou associação do comando anterior. |
| [L236](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L236) | Linha em branco: separa visualmente trechos do código. |
| [L237](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L237) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L238](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L238) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L239](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L239) | Linha em branco: separa visualmente trechos do código. |
| [L240](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L240) | Declara função/método enviarCodigoRecuperacao6, com seus parâmetros e comportamento. |
| [L241](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L241) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L242](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L242) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L243](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L243) | Atribui um valor à variável PHP $codigo. |
| [L244](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L244) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L245](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L245) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L246](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L246) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L247](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L247) | Linha em branco: separa visualmente trechos do código. |
| [L248](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L248) | Atribui um valor à variável PHP $codigoSeguro. |
| [L249](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L249) | Atribui um valor à variável PHP $nomeSeguro. |
| [L250](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L250) | Linha em branco: separa visualmente trechos do código. |
| [L251](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L251) | Atribui um valor à variável PHP $corpo. |
| [L252](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L252) | Declara agrupamento de elementos. |
| [L253](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L253) | Declara agrupamento de elementos. |
| [L254](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L254) | Linha em branco: separa visualmente trechos do código. |
| [L255](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L255) | Declara subtítulo de segundo nível. |
| [L256](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L256) | Declara parágrafo. |
| [L257](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L257) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L258](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L258) | Fecha elemento p do documento. |
| [L259](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L259) | Linha em branco: separa visualmente trechos do código. |
| [L260](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L260) | Declara parágrafo. |
| [L261](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L261) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L262](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L262) | Declara trecho em linha. |
| [L263](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L263) | Continua expressão ou bloco pertencente a marcação span. |
| [L264](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L264) | Fecha elemento span do documento. |
| [L265](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L265) | Fecha elemento p do documento. |
| [L266](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L266) | Linha em branco: separa visualmente trechos do código. |
| [L267](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L267) | Declara parágrafo. |
| [L268](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L268) | Continua expressão ou bloco pertencente a marcação p. |
| [L269](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L269) | Fecha elemento p do documento. |
| [L270](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L270) | Linha em branco: separa visualmente trechos do código. |
| [L271](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L271) | Declara elemento de marcação. |
| [L272](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L272) | Linha em branco: separa visualmente trechos do código. |
| [L273](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L273) | Declara parágrafo. |
| [L274](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L274) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L275](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L275) | Fecha elemento p do documento. |
| [L276](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L276) | Fecha elemento div do documento. |
| [L277](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L277) | Fecha elemento div do documento. |
| [L278](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L278) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L279](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L279) | Linha em branco: separa visualmente trechos do código. |
| [L280](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L280) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L281](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L281) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L282](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L282) | Linha em branco: separa visualmente trechos do código. |
| [L283](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L283) | Linha em branco: separa visualmente trechos do código. |
| [L284](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L284) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L285](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L285) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L286](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L286) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L287](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L287) | Declara função/método enviarLembreteCurriculoPendente, com seus parâmetros e comportamento. |
| [L288](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L288) | Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente. |
| [L289](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L289) | Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente. |
| [L290](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L290) | Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente. |
| [L291](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L291) | Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente. |
| [L292](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L292) | Atribui um valor à variável PHP $nomeSeguro. |
| [L293](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L293) | Atribui um valor à variável PHP $assunto. |
| [L294](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L294) | Linha em branco: separa visualmente trechos do código. |
| [L295](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L295) | Atribui um valor à variável PHP $corpo. |
| [L296](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L296) | Declara agrupamento de elementos. |
| [L297](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L297) | Declara agrupamento de elementos. |
| [L298](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L298) | Declara subtítulo de segundo nível. |
| [L299](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L299) | Declara parágrafo. |
| [L300](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L300) | Declara parágrafo. |
| [L301](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L301) | Declara parágrafo. |
| [L302](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L302) | Declara parágrafo. A linha reúne múltiplos elementos. |
| [L303](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L303) | Declara parágrafo. |
| [L304](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L304) | Declara link de navegação. |
| [L305](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L305) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L306](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L306) | Fecha elemento a do documento. |
| [L307](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L307) | Fecha elemento p do documento. |
| [L308](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L308) | Fecha elemento div do documento. |
| [L309](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L309) | Fecha elemento div do documento. |
| [L310](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L310) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L311](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L311) | Linha em branco: separa visualmente trechos do código. |
| [L312](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L312) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L313](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L313) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L314](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L314) | Linha em branco: separa visualmente trechos do código. |
| [L315](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L315) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L316](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L316) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L317](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L317) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L318](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L318) | Declara função/método notificarEmpresasNovoCandidato, com seus parâmetros e comportamento. |
| [L319](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L319) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L320](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L320) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L321](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L321) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L322](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L322) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L323](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L323) | Atribui um valor à variável PHP $query. |
| [L324](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L324) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L325](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L325) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L326](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L326) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L327](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L327) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L328](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L328) | Linha em branco: separa visualmente trechos do código. |
| [L329](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L329) | Executa ou lê resultados de uma consulta ao banco. |
| [L330](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L330) | Linha em branco: separa visualmente trechos do código. |
| [L331](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L331) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L332](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L332) | Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato. |
| [L333](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L333) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L334](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L334) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L335](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L335) | Linha em branco: separa visualmente trechos do código. |
| [L336](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L336) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L337](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L337) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L338](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L338) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L339](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L339) | Linha em branco: separa visualmente trechos do código. |
| [L340](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L340) | Atribui um valor à variável PHP $nomeSeguro. |
| [L341](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L341) | Linha em branco: separa visualmente trechos do código. |
| [L342](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L342) | Inicia repetição ou seleção de casos a partir de expressão de controle. |
| [L343](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L343) | Atribui um valor à variável PHP $nomeEmpresaOriginal. |
| [L344](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L344) | Atribui um valor à variável PHP $nomeEmpresa. |
| [L345](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L345) | Atribui um valor à variável PHP $emailEmpresa. |
| [L346](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L346) | Atribui um valor à variável PHP $idioma. |
| [L347](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L347) | Linha em branco: separa visualmente trechos do código. |
| [L348](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L348) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L349](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L349) | Controla a continuação ou saída de laço/seleção. |
| [L350](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L350) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L351](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L351) | Linha em branco: separa visualmente trechos do código. |
| [L352](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L352) | Atribui um valor à variável PHP $assunto. |
| [L353](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L353) | Linha em branco: separa visualmente trechos do código. |
| [L354](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L354) | Atribui um valor à variável PHP $corpo. |
| [L355](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L355) | Declara agrupamento de elementos. |
| [L356](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L356) | Declara agrupamento de elementos. |
| [L357](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L357) | Declara subtítulo de segundo nível. |
| [L358](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L358) | Declara parágrafo. A linha reúne múltiplos elementos. |
| [L359](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L359) | Declara parágrafo. A linha reúne múltiplos elementos. |
| [L360](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L360) | Declara parágrafo. |
| [L361](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L361) | Fecha elemento div do documento. |
| [L362](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L362) | Fecha elemento div do documento. |
| [L363](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L363) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L364](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L364) | Linha em branco: separa visualmente trechos do código. |
| [L365](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L365) | Continua expressão ou bloco pertencente a marcação p. |
| [L366](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L366) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L367](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L367) | Linha em branco: separa visualmente trechos do código. |
| [L368](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L368) | Continua expressão ou bloco pertencente a marcação p. |
| [L369](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L369) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L370](https://github.com/CauaChiappin/DevIN/blob/main/php/MailerHelper.php#L370) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
