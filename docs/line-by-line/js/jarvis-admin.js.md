# Explicação linha a linha — js/jarvis-admin.js

**Arquivo original:** [abrir código no GitHub](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js)  
**Linguagem:** JavaScript  
**Linhas de origem:** 625  
**SHA-256 da origem:** 73e3e76851de678f2b5ba607ce11b3992c39a22fe81898e825dc94ecf76d5273

> Descrições iniciais produzidas automaticamente, linha a linha, por regras de sintaxe.
> Explicações de comportamento complexo ou ambíguo exigem revisão técnica humana.
> Os links abrem diretamente a linha correspondente do código real; nenhum
> arquivo executável é alterado pela geração desta documentação.

| Linha do código | Explicação da linha |
| ---: | --- |
| [L1](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L1) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L2](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L2) | Participa da instrução atual, ajustando cálculo, condição, dados ou resultado. |
| [L3](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L3) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L4](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L4) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L5](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L5) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L6](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L6) | Participa da instrução atual, ajustando cálculo, condição, dados ou resultado. |
| [L7](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L7) | Declara variável JavaScript panel para reutilizar valor ou resultado. |
| [L8](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L8) | Declara variável JavaScript launcher para reutilizar valor ou resultado. |
| [L9](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L9) | Declara variável JavaScript input para reutilizar valor ou resultado. |
| [L10](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L10) | Declara variável JavaScript messages para reutilizar valor ou resultado. |
| [L11](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L11) | Declara variável JavaScript status para reutilizar valor ou resultado. |
| [L12](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L12) | Declara variável JavaScript taskStatus para reutilizar valor ou resultado. |
| [L13](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L13) | Declara variável JavaScript send para reutilizar valor ou resultado. |
| [L14](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L14) | Declara variável JavaScript conversationSelect para reutilizar valor ou resultado. |
| [L15](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L15) | Declara variável JavaScript taskList para reutilizar valor ou resultado. |
| [L16](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L16) | Declara variável JavaScript conversationId para reutilizar valor ou resultado. |
| [L17](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L17) | Declara variável JavaScript busy para reutilizar valor ou resultado. |
| [L18](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L18) | Declara variável JavaScript activeTab para reutilizar valor ou resultado. |
| [L19](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L19) | Declara variável JavaScript searchTimer para reutilizar valor ou resultado. |
| [L20](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L20) | Linha em branco: separa visualmente trechos do código. |
| [L21](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L21) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L22](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L22) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L23](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L23) | Declara variável JavaScript dockStorageKey para reutilizar valor ou resultado. |
| [L24](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L24) | Declara variável JavaScript savedDock para reutilizar valor ou resultado. |
| [L25](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L25) | Declara variável JavaScript dockSide para reutilizar valor ou resultado. |
| [L26](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L26) | Declara variável JavaScript pointerDrag para reutilizar valor ou resultado. |
| [L27](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L27) | Declara variável JavaScript suppressDragClick para reutilizar valor ou resultado. |
| [L28](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L28) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L29](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L29) | Converte dados entre estrutura interna e representação JSON. |
| [L30](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L30) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L31](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L31) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L32](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L32) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L33](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L33) | Captura exceção ou erro para tratar a falha de execução. |
| [L34](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L34) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L35](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L35) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L36](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L36) | Linha em branco: separa visualmente trechos do código. |
| [L37](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L37) | Declara função/método positionPanelNearLauncher, com seus parâmetros e comportamento. |
| [L38](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L38) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L39](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L39) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L40](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L40) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L41](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L41) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L42](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L42) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L43](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L43) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L44](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L44) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L45](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L45) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L46](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L46) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L47](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L47) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L48](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L48) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L49](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L49) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L50](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L50) | Declara variável JavaScript rect para reutilizar valor ou resultado. |
| [L51](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L51) | Declara variável JavaScript width para reutilizar valor ou resultado. |
| [L52](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L52) | Declara variável JavaScript height para reutilizar valor ou resultado. |
| [L53](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L53) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L54](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L54) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L55](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L55) | Declara variável JavaScript desiredX para reutilizar valor ou resultado. |
| [L56](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L56) | Declara variável JavaScript desiredY para reutilizar valor ou resultado. |
| [L57](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L57) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L58](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L58) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L59](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L59) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L60](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L60) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L61](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L61) | Linha em branco: separa visualmente trechos do código. |
| [L62](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L62) | Declara função/método placeLauncher, com seus parâmetros e comportamento. |
| [L63](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L63) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L64](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L64) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L65](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L65) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L66](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L66) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L67](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L67) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L68](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L68) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L69](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L69) | Continua expressão ou bloco pertencente a função placeLauncher. |
| [L70](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L70) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L71](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L71) | Linha em branco: separa visualmente trechos do código. |
| [L72](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L72) | Declara função/método restoreLauncher, com seus parâmetros e comportamento. |
| [L73](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L73) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L74](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L74) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L75](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L75) | Continua expressão ou bloco pertencente a função restoreLauncher. |
| [L76](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L76) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L77](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L77) | Linha em branco: separa visualmente trechos do código. |
| [L78](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L78) | Declara função/método persistLauncher, com seus parâmetros e comportamento. |
| [L79](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L79) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L80](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L80) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L81](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L81) | Captura exceção ou erro para tratar a falha de execução. |
| [L82](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L82) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L83](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L83) | Linha em branco: separa visualmente trechos do código. |
| [L84](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L84) | Declara função/método initLauncherDocking, com seus parâmetros e comportamento. |
| [L85](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L85) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L86](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L86) | Continua expressão ou bloco pertencente a função initLauncherDocking. |
| [L87](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L87) | Registra callback para responder a uma interação ou evento do navegador. |
| [L88](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L88) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L89](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L89) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L90](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L90) | Declara variável JavaScript rect para reutilizar valor ou resultado. |
| [L91](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L91) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L92](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L92) | Continua expressão ou bloco pertencente a função initLauncherDocking. |
| [L93](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L93) | Continua expressão ou bloco pertencente a função initLauncherDocking. |
| [L94](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L94) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L95](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L95) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L96](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L96) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L97](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L97) | Registra callback para responder a uma interação ou evento do navegador. |
| [L98](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L98) | Declara variável JavaScript state para reutilizar valor ou resultado. |
| [L99](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L99) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L100](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L100) | Declara variável JavaScript dx para reutilizar valor ou resultado. |
| [L101](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L101) | Declara variável JavaScript dy para reutilizar valor ou resultado. |
| [L102](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L102) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L103](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L103) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L104](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L104) | Continua expressão ou bloco pertencente a função initLauncherDocking. |
| [L105](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L105) | Declara variável JavaScript width para reutilizar valor ou resultado. |
| [L106](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L106) | Declara variável JavaScript height para reutilizar valor ou resultado. |
| [L107](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L107) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L108](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L108) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L109](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L109) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L110](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L110) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L111](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L111) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L112](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L112) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L113](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L113) | Continua expressão ou bloco pertencente a função initLauncherDocking. |
| [L114](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L114) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L115](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L115) | Declara função/método finishDrag, com seus parâmetros e comportamento. |
| [L116](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L116) | Declara variável JavaScript state para reutilizar valor ou resultado. |
| [L117](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L117) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L118](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L118) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L119](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L119) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L120](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L120) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L121](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L121) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L122](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L122) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L123](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L123) | Declara variável JavaScript rect para reutilizar valor ou resultado. |
| [L124](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L124) | Declara variável JavaScript target para reutilizar valor ou resultado. |
| [L125](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L125) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L126](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L126) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L127](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L127) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L128](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L128) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L129](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L129) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L130](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L130) | Registra callback para responder a uma interação ou evento do navegador. |
| [L131](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L131) | Registra callback para responder a uma interação ou evento do navegador. |
| [L132](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L132) | Registra callback para responder a uma interação ou evento do navegador. |
| [L133](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L133) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L134](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L134) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L135](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L135) | Declara variável JavaScript targetSide para reutilizar valor ou resultado. |
| [L136](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L136) | Declara variável JavaScript rect para reutilizar valor ou resultado. |
| [L137](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L137) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L138](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L138) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L139](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L139) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L140](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L140) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L141](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L141) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L142](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L142) | Registra callback para responder a uma interação ou evento do navegador. |
| [L143](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L143) | Registra callback para responder a uma interação ou evento do navegador. |
| [L144](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L144) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L145](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L145) | Continua expressão ou bloco pertencente a função finishDrag. |
| [L146](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L146) | Linha em branco: separa visualmente trechos do código. |
| [L147](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L147) | Declara função/método api, com seus parâmetros e comportamento. |
| [L148](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L148) | Declara variável JavaScript options para reutilizar valor ou resultado. |
| [L149](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L149) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L150](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L150) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L151](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L151) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L152](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L152) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L153](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L153) | Converte dados entre estrutura interna e representação JSON. |
| [L154](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L154) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L155](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L155) | Inicia solicitação HTTP assíncrona a um endereço configurado. |
| [L156](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L156) | Captura exceção ou erro para tratar a falha de execução. |
| [L157](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L157) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L158](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L158) | Interrompe o fluxo atual levantando exceção ou erro. |
| [L159](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L159) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L160](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L160) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L161](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L161) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L162](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L162) | Declara função/método open, com seus parâmetros e comportamento. |
| [L163](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L163) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L164](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L164) | Continua expressão ou bloco pertencente a função open. |
| [L165](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L165) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L166](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L166) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L167](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L167) | Captura exceção ou erro para tratar a falha de execução. |
| [L168](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L168) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L169](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L169) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L170](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L170) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L171](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L171) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L172](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L172) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L173](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L173) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L174](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L174) | Define ramo alternativo para condição anterior. |
| [L175](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L175) | Define ramo alternativo para condição anterior. |
| [L176](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L176) | Continua expressão ou bloco pertencente a função open. |
| [L177](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L177) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L178](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L178) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L179](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L179) | Declara função/método tab, com seus parâmetros e comportamento. |
| [L180](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L180) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L181](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L181) | Continua expressão ou bloco pertencente a função tab. |
| [L182](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L182) | Declara variável JavaScript selected para reutilizar valor ou resultado. |
| [L183](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L183) | Continua expressão ou bloco pertencente a função tab. |
| [L184](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L184) | Continua expressão ou bloco pertencente a função tab. |
| [L185](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L185) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L186](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L186) | Continua expressão ou bloco pertencente a função tab. |
| [L187](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L187) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L188](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L188) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L189](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L189) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L190](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L190) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L191](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L191) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L192](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L192) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L193](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L193) | Define ramo alternativo para condição anterior. |
| [L194](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L194) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L195](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L195) | Declara função/método addMessage, com seus parâmetros e comportamento. |
| [L196](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L196) | Continua expressão ou bloco pertencente a função addMessage. |
| [L197](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L197) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L198](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L198) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L199](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L199) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L200](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L200) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L201](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L201) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L202](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L202) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L203](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L203) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L204](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L204) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L205](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L205) | Continua expressão ou bloco pertencente a função addMessage. |
| [L206](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L206) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L207](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L207) | Declara variável JavaScript details para reutilizar valor ou resultado. |
| [L208](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L208) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L209](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L209) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L210](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L210) | Continua expressão ou bloco pertencente a função addMessage. |
| [L211](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L211) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L212](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L212) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L213](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L213) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L214](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L214) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L215](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L215) | Continua expressão ou bloco pertencente a função addMessage. |
| [L216](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L216) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L217](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L217) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L218](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L218) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L219](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L219) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L220](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L220) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L221](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L221) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L222](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L222) | Continua expressão ou bloco pertencente a função addMessage. |
| [L223](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L223) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L224](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L224) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L225](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L225) | Continua expressão ou bloco pertencente a função addMessage. |
| [L226](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L226) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L227](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L227) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L228](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L228) | Declara função/método clearMessages, com seus parâmetros e comportamento. |
| [L229](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L229) | Continua expressão ou bloco pertencente a função clearMessages. |
| [L230](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L230) | Continua expressão ou bloco pertencente a função clearMessages. |
| [L231](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L231) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L232](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L232) | Declara função/método resize, com seus parâmetros e comportamento. |
| [L233](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L233) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L234](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L234) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L235](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L235) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L236](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L236) | Declara função/método loadConversations, com seus parâmetros e comportamento. |
| [L237](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L237) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L238](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L238) | Declara variável JavaScript previous para reutilizar valor ou resultado. |
| [L239](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L239) | Continua expressão ou bloco pertencente a função loadConversations. |
| [L240](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L240) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L241](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L241) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L242](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L242) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L243](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L243) | Continua expressão ou bloco pertencente a função loadConversations. |
| [L244](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L244) | Continua expressão ou bloco pertencente a função loadConversations. |
| [L245](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L245) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L246](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L246) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L247](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L247) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L248](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L248) | Continua expressão ou bloco pertencente a função loadConversations. |
| [L249](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L249) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L250](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L250) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L251](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L251) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L252](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L252) | Declara função/método loadConversation, com seus parâmetros e comportamento. |
| [L253](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L253) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L254](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L254) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L255](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L255) | Continua expressão ou bloco pertencente a função loadConversation. |
| [L256](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L256) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L257](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L257) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L258](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L258) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L259](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L259) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L260](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L260) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L261](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L261) | Continua expressão ou bloco pertencente a função loadConversation. |
| [L262](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L262) | Continua expressão ou bloco pertencente a função loadConversation. |
| [L263](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L263) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L264](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L264) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L265](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L265) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L266](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L266) | Declara função/método chat, com seus parâmetros e comportamento. |
| [L267](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L267) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L268](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L268) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L269](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L269) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L270](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L270) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L271](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L271) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L272](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L272) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L273](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L273) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L274](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L274) | Continua expressão ou bloco pertencente a função chat. |
| [L275](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L275) | Continua expressão ou bloco pertencente a função chat. |
| [L276](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L276) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L277](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L277) | Continua expressão ou bloco pertencente a função chat. |
| [L278](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L278) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L279](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L279) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L280](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L280) | Captura exceção ou erro para tratar a falha de execução. |
| [L281](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L281) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L282](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L282) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L283](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L283) | Captura exceção ou erro para tratar a falha de execução. |
| [L284](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L284) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L285](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L285) | Continua expressão ou bloco pertencente a função chat. |
| [L286](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L286) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L287](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L287) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L288](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L288) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L289](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L289) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L290](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L290) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L291](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L291) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L292](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L292) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L293](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L293) | Declara variável JavaScript labels para reutilizar valor ou resultado. |
| [L294](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L294) | Declara função/método taskButton, com seus parâmetros e comportamento. |
| [L295](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L295) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L296](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L296) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L297](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L297) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L298](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L298) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L299](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L299) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L300](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L300) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L301](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L301) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L302](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L302) | Declara função/método renderTasks, com seus parâmetros e comportamento. |
| [L303](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L303) | Continua expressão ou bloco pertencente a função renderTasks. |
| [L304](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L304) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L305](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L305) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L306](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L306) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L307](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L307) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L308](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L308) | Continua expressão ou bloco pertencente a função renderTasks. |
| [L309](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L309) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L310](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L310) | Continua expressão ou bloco pertencente a função renderTasks. |
| [L311](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L311) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L312](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L312) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L313](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L313) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L314](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L314) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L315](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L315) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L316](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L316) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L317](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L317) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L318](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L318) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L319](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L319) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L320](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L320) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L321](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L321) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L322](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L322) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L323](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L323) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L324](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L324) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L325](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L325) | Continua expressão ou bloco pertencente a função renderTasks. |
| [L326](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L326) | Continua expressão ou bloco pertencente a função renderTasks. |
| [L327](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L327) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L328](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L328) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L329](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L329) | Declara função/método loadTasks, com seus parâmetros e comportamento. |
| [L330](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L330) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L331](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L331) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L332](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L332) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L333](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L333) | Continua expressão ou bloco pertencente a função loadTasks. |
| [L334](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L334) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L335](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L335) | Captura exceção ou erro para tratar a falha de execução. |
| [L336](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L336) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L337](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L337) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L338](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L338) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L339](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L339) | Linha em branco: separa visualmente trechos do código. |
| [L340](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L340) | Linha em branco: separa visualmente trechos do código. |
| [L341](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L341) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L342](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L342) | Declara variável JavaScript toolUrl para reutilizar valor ou resultado. |
| [L343](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L343) | Declara variável JavaScript lookupStatus para reutilizar valor ou resultado. |
| [L344](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L344) | Declara variável JavaScript recordResults para reutilizar valor ou resultado. |
| [L345](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L345) | Declara variável JavaScript analysisArea para reutilizar valor ou resultado. |
| [L346](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L346) | Declara variável JavaScript outboxStatus para reutilizar valor ou resultado. |
| [L347](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L347) | Declara variável JavaScript outboxList para reutilizar valor ou resultado. |
| [L348](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L348) | Declara variável JavaScript auditStatus para reutilizar valor ou resultado. |
| [L349](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L349) | Declara variável JavaScript auditList para reutilizar valor ou resultado. |
| [L350](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L350) | Linha em branco: separa visualmente trechos do código. |
| [L351](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L351) | Declara função/método toolNotice, com seus parâmetros e comportamento. |
| [L352](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L352) | Linha em branco: separa visualmente trechos do código. |
| [L353](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L353) | Declara função/método loadDrafts, com seus parâmetros e comportamento. |
| [L354](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L354) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L355](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L355) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L356](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L356) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L357](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L357) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L358](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L358) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L359](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L359) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L360](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L360) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L361](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L361) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L362](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L362) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L363](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L363) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L364](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L364) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L365](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L365) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L366](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L366) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L367](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L367) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L368](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L368) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L369](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L369) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L370](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L370) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L371](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L371) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L372](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L372) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L373](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L373) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L374](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L374) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L375](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L375) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L376](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L376) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L377](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L377) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L378](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L378) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L379](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L379) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L380](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L380) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L381](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L381) | Registra callback para responder a uma interação ou evento do navegador. |
| [L382](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L382) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L383](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L383) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L384](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L384) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L385](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L385) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L386](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L386) | Captura exceção ou erro para tratar a falha de execução. |
| [L387](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L387) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L388](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L388) | Declara variável JavaScript recipient para reutilizar valor ou resultado. |
| [L389](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L389) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L390](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L390) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L391](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L391) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L392](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L392) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L393](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L393) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L394](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L394) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L395](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L395) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L396](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L396) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L397](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L397) | Captura exceção ou erro para tratar a falha de execução. |
| [L398](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L398) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L399](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L399) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L400](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L400) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L401](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L401) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L402](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L402) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L403](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L403) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L404](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L404) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L405](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L405) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L406](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L406) | Registra callback para responder a uma interação ou evento do navegador. |
| [L407](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L407) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L408](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L408) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L409](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L409) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L410](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L410) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L411](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L411) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L412](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L412) | Captura exceção ou erro para tratar a falha de execução. |
| [L413](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L413) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L414](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L414) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L415](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L415) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L416](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L416) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L417](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L417) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L418](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L418) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L419](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L419) | Continua expressão ou bloco pertencente a função loadDrafts. |
| [L420](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L420) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L421](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L421) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L422](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L422) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L423](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L423) | Captura exceção ou erro para tratar a falha de execução. |
| [L424](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L424) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L425](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L425) | Linha em branco: separa visualmente trechos do código. |
| [L426](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L426) | Declara função/método loadAudit, com seus parâmetros e comportamento. |
| [L427](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L427) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L428](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L428) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L429](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L429) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L430](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L430) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L431](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L431) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L432](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L432) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L433](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L433) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L434](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L434) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L435](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L435) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L436](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L436) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L437](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L437) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L438](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L438) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L439](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L439) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L440](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L440) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L441](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L441) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L442](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L442) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L443](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L443) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L444](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L444) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L445](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L445) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L446](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L446) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L447](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L447) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L448](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L448) | Captura exceção ou erro para tratar a falha de execução. |
| [L449](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L449) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L450](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L450) | Linha em branco: separa visualmente trechos do código. |
| [L451](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L451) | Registra callback para responder a uma interação ou evento do navegador. |
| [L452](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L452) | Registra callback para responder a uma interação ou evento do navegador. |
| [L453](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L453) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L454](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L454) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L455](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L455) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L456](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L456) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L457](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L457) | Declara variável JavaScript kind para reutilizar valor ou resultado. |
| [L458](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L458) | Declara variável JavaScript query para reutilizar valor ou resultado. |
| [L459](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L459) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L460](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L460) | Declara variável JavaScript data para reutilizar valor ou resultado. |
| [L461](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L461) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L462](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L462) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L463](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L463) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L464](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L464) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L465](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L465) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L466](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L466) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L467](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L467) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L468](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L468) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L469](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L469) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L470](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L470) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L471](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L471) | Registra callback para responder a uma interação ou evento do navegador. |
| [L472](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L472) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L473](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L473) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L474](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L474) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L475](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L475) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L476](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L476) | Declara variável JavaScript result para reutilizar valor ou resultado. |
| [L477](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L477) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L478](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L478) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L479](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L479) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L480](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L480) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L481](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L481) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L482](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L482) | Captura exceção ou erro para tratar a falha de execução. |
| [L483](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L483) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L484](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L484) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L485](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L485) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L486](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L486) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L487](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L487) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L488](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L488) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L489](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L489) | Define ramo alternativo para condição anterior. |
| [L490](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L490) | Captura exceção ou erro para tratar a falha de execução. |
| [L491](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L491) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L492](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L492) | Linha em branco: separa visualmente trechos do código. |
| [L493](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L493) | Registra callback para responder a uma interação ou evento do navegador. |
| [L494](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L494) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L495](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L495) | Declara variável JavaScript submit para reutilizar valor ou resultado. |
| [L496](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L496) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L497](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L497) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L498](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L498) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L499](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L499) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L500](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L500) | Declara variável JavaScript result para reutilizar valor ou resultado. |
| [L501](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L501) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L502](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L502) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L503](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L503) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L504](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L504) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L505](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L505) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L506](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L506) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L507](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L507) | Declara ou continua um valor literal usado pela instrução em andamento. |
| [L508](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L508) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L509](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L509) | Captura exceção ou erro para tratar a falha de execução. |
| [L510](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L510) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L511](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L511) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L512](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L512) | Linha em branco: separa visualmente trechos do código. |
| [L513](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L513) | Registra callback para responder a uma interação ou evento do navegador. |
| [L514](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L514) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L515](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L515) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L516](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L516) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L517](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L517) | Retorna ou produz resultado da função, conforme a expressão informada. |
| [L518](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L518) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L519](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L519) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L520](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L520) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L521](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L521) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L522](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L522) | Registra callback para responder a uma interação ou evento do navegador. |
| [L523](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L523) | Registra callback para responder a uma interação ou evento do navegador. |
| [L524](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L524) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L525](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L525) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L526](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L526) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L527](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L527) | Registra callback para responder a uma interação ou evento do navegador. |
| [L528](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L528) | Registra callback para responder a uma interação ou evento do navegador. |
| [L529](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L529) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L530](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L530) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L531](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L531) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L532](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L532) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L533](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L533) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L534](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L534) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L535](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L535) | Registra callback para responder a uma interação ou evento do navegador. |
| [L536](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L536) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L537](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L537) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L538](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L538) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L539](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L539) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L540](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L540) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L541](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L541) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L542](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L542) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L543](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L543) | Captura exceção ou erro para tratar a falha de execução. |
| [L544](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L544) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L545](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L545) | Registra callback para responder a uma interação ou evento do navegador. |
| [L546](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L546) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L547](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L547) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L548](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L548) | Captura exceção ou erro para tratar a falha de execução. |
| [L549](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L549) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L550](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L550) | Registra callback para responder a uma interação ou evento do navegador. |
| [L551](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L551) | Interage com a página, elementos visíveis ou eventos do navegador. |
| [L552](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L552) | Declara variável JavaScript query para reutilizar valor ou resultado. |
| [L553](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L553) | Captura exceção ou erro para tratar a falha de execução. |
| [L554](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L554) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L555](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L555) | Registra callback para responder a uma interação ou evento do navegador. |
| [L556](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L556) | Registra callback para responder a uma interação ou evento do navegador. |
| [L557](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L557) | Registra callback para responder a uma interação ou evento do navegador. |
| [L558](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L558) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L559](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L559) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L560](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L560) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L561](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L561) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L562](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L562) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L563](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L563) | Registra callback para responder a uma interação ou evento do navegador. |
| [L564](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L564) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L565](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L565) | Declara variável JavaScript value para reutilizar valor ou resultado. |
| [L566](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L566) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L567](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L567) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L568](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L568) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L569](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L569) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L570](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L570) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L571](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L571) | Declara variável JavaScript taskExamples para reutilizar valor ou resultado. |
| [L572](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L572) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L573](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L573) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L574](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L574) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L575](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L575) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L576](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L576) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L577](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L577) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L578](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L578) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L579](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L579) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L580](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L580) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L581](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L581) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L582](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L582) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L583](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L583) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L584](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L584) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L585](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L585) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L586](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L586) | Registra callback para responder a uma interação ou evento do navegador. |
| [L587](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L587) | Declara variável JavaScript example para reutilizar valor ou resultado. |
| [L588](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L588) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L589](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L589) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L590](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L590) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L591](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L591) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L592](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L592) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L593](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L593) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L594](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L594) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L595](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L595) | Registra callback para responder a uma interação ou evento do navegador. |
| [L596](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L596) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L597](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L597) | Declara variável JavaScript title para reutilizar valor ou resultado. |
| [L598](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L598) | Declara variável JavaScript description para reutilizar valor ou resultado. |
| [L599](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L599) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L600](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L600) | Declara variável JavaScript button para reutilizar valor ou resultado. |
| [L601](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L601) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L602](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L602) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L603](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L603) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L604](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L604) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L605](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L605) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L606](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L606) | Captura exceção ou erro para tratar a falha de execução. |
| [L607](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L607) | Declara limpeza ou finalização executada ao encerrar a tentativa. |
| [L608](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L608) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L609](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L609) | Registra callback para responder a uma interação ou evento do navegador. |
| [L610](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L610) | Declara variável JavaScript button para reutilizar valor ou resultado. |
| [L611](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L611) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L612](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L612) | Declara variável JavaScript action para reutilizar valor ou resultado. |
| [L613](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L613) | Declara variável JavaScript actionLabel para reutilizar valor ou resultado. |
| [L614](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L614) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L615](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L615) | Atribui propriedade, resultado ou associação de chave e valor. |
| [L616](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L616) | Inicia bloco de operação com tratamento de possíveis falhas. |
| [L617](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L617) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L618](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L618) | Continua expressão ou bloco pertencente a função loadAudit. |
| [L619](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L619) | Captura exceção ou erro para tratar a falha de execução. |
| [L620](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L620) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L621](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L621) | Registra callback para responder a uma interação ou evento do navegador. |
| [L622](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L622) | Inicia desvio condicional: executa ramo somente quando a condição for satisfeita. |
| [L623](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L623) | Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente. |
| [L624](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L624) | Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho. |
| [L625](https://github.com/CauaChiappin/DevIN/blob/main/js/jarvis-admin.js#L625) | Continua expressão ou bloco pertencente a função loadAudit. |
