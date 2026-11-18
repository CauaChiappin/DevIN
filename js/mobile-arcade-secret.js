/**
 * Segredo do Arcade para celulares na seção "Sobre nós" de todos os dashboards.
 * Um toque contínuo de três segundos no pequeno controle abre php/arcade.php.
 * O desktop conserva o código Konami em konami-arcade.js, sem alterações.
 * Este gesto é somente uma surpresa visual; o servidor continua exigindo login.
 */
(() => { // Explicação DevIN: Isola variáveis do easter egg para evitar conflitos com os dashboards.
    'use strict'; // Explicação DevIN: Rejeita atribuições acidentais a variáveis globais.
    const button = document.querySelector('[data-mobile-arcade-secret]'); // Explicação DevIN: Procura o ícone no card Time fundador, exibido somente em Sobre nós.
    if (!button) return; // Explicação DevIN: Não registra eventos em outras páginas ou módulos.
    const coarseTouch = window.matchMedia('(hover: none) and (pointer: coarse)'); // Explicação DevIN: Restringe o gesto a aparelhos de toque, não ao mouse do computador.
    const holdMilliseconds = 3000; // Explicação DevIN: Exige três segundos de pressão contínua para evitar descobertas acidentais.
    const moveTolerancePixels = 14; // Explicação DevIN: Permite uma pequena oscilação natural do dedo sem aceitar rolagem.
    let timer = null; // Explicação DevIN: Armazena o agendamento da pressão para cancelá-lo quando o dedo sair.
    let trackingPointer = null; // Explicação DevIN: Identifica qual dedo iniciou a pressão, ignorando toques adicionais.
    let startX = 0; // Explicação DevIN: Registra a posição horizontal inicial para detectar movimentos de rolagem.
    let startY = 0; // Explicação DevIN: Registra a posição vertical inicial para detectar movimentos de rolagem.
    let opening = false; // Explicação DevIN: Bloqueia navegação duplicada por múltiplos eventos.

    function cancel() { // Explicação DevIN: Encerra uma tentativa incompleta, inclusive ao trocar de aba ou começar a rolar.
        if (timer !== null) window.clearTimeout(timer); // Explicação DevIN: Cancela a navegação programada antes dos três segundos.
        timer = null; // Explicação DevIN: Informa que não há uma pressão ativa.
        trackingPointer = null; // Explicação DevIN: Libera o identificador para uma nova tentativa.
        button.classList.remove('is-holding'); // Explicação DevIN: Interrompe o anel de progresso do ícone.
    } // Explicação DevIN: Finaliza o cancelamento seguro da tentativa.

    function openArcade() { // Explicação DevIN: Conclui a descoberta do segredo e abre o Arcade protegido por sessão.
        if (opening || !coarseTouch.matches) return; // Explicação DevIN: Impede repetição e preserva uso exclusivo em touch.
        opening = true; // Explicação DevIN: Bloqueia pressões posteriores enquanto o navegador navega.
        cancel(); // Explicação DevIN: Remove temporizador e classe de progresso da pressão.
        button.classList.add('is-discovered'); // Explicação DevIN: Exibe brevemente o estado visual de descoberta.
        button.setAttribute('aria-label', 'Arcade encontrado! Abrindo jogos DevIN'); // Explicação DevIN: Anuncia a descoberta para tecnologia assistiva.
        if (typeof navigator.vibrate === 'function') navigator.vibrate(45); // Explicação DevIN: Vibra discretamente quando permitido; não é requisito para funcionar.
        window.setTimeout(() => { // Explicação DevIN: Mantém o feedback por um instante antes de trocar de página.
            window.location.assign(new URL('arcade.php', window.location.href).toString()); // Explicação DevIN: Entra na página autenticada do Arcade sem abrir recurso externo.
        }, 260); // Explicação DevIN: Usa espera curta, sem exigir animações em aparelhos com movimento reduzido.
    } // Explicação DevIN: Finaliza o acionamento do easter egg.

    button.addEventListener('pointerdown', (event) => { // Explicação DevIN: Inicia contagem somente com contato de dedo ou caneta.
        if (!coarseTouch.matches || opening || event.button !== 0 || event.pointerType === 'mouse') return; // Explicação DevIN: Evita ativação de cliques e contextos de desktop.
        if (trackingPointer !== null) return; // Explicação DevIN: Um segundo dedo não reinicia nem acelera o segredo.
        trackingPointer = event.pointerId; // Explicação DevIN: Associa a contagem ao contato que começou no ícone.
        startX = event.clientX; // Explicação DevIN: Guarda origem horizontal do toque para controlar deslocamento.
        startY = event.clientY; // Explicação DevIN: Guarda origem vertical do toque para controlar deslocamento.
        button.classList.add('is-holding'); // Explicação DevIN: Mostra o anel de progresso durante a pressão.
        timer = window.setTimeout(openArcade, holdMilliseconds); // Explicação DevIN: Só abre o Arcade após três segundos ininterruptos.
    }); // Explicação DevIN: Finaliza o registro do início do toque.

    button.addEventListener('pointermove', (event) => { // Explicação DevIN: Diferencia pressão estável de intenção de rolar a página.
        if (event.pointerId !== trackingPointer) return; // Explicação DevIN: Ignora deslocamento de outros dedos.
        if (Math.hypot(event.clientX - startX, event.clientY - startY) > moveTolerancePixels) cancel(); // Explicação DevIN: Cancela ao mover o dedo além da tolerância definida.
    }); // Explicação DevIN: Finaliza a detecção de arrasto.

    for (const type of ['pointerup', 'pointercancel', 'pointerleave']) { // Explicação DevIN: Abrange soltar o dedo, gesto interrompido e sair do botão.
        button.addEventListener(type, (event) => { // Explicação DevIN: Associa cada encerramento ao contato ativo.
            if (event.pointerId === trackingPointer) cancel(); // Explicação DevIN: Pressões curtas nunca abrem o Arcade.
        }); // Explicação DevIN: Finaliza o tratamento de um tipo de encerramento.
    } // Explicação DevIN: Completa a lista de eventos canceláveis.

    button.addEventListener('contextmenu', (event) => { // Explicação DevIN: Impede menu de contexto sobre o controle durante a pressão.
        if (coarseTouch.matches) event.preventDefault(); // Explicação DevIN: Evita que o menu do navegador interrompa o segurar por três segundos.
    }); // Explicação DevIN: Finaliza a proteção contra menu de contexto do ícone.

    window.addEventListener('scroll', cancel, { passive: true }); // Explicação DevIN: Rolagem de página cancela a tentativa sem bloquear o scroll.
    document.addEventListener('visibilitychange', () => { // Explicação DevIN: Cancela quando o navegador vai para segundo plano.
        if (document.hidden) cancel(); // Explicação DevIN: Evita abertura inesperada após mudar de aba.
    }); // Explicação DevIN: Finaliza controle de visibilidade.
    window.addEventListener('blur', cancel); // Explicação DevIN: Interrompe o gesto quando a janela perde foco.
    button.addEventListener('click', (event) => { // Explicação DevIN: Oferece ativação alternativa para tecnologia assistiva.
        event.preventDefault(); // Explicação DevIN: Não propaga um clique acidental nem submete formulários.
        if (event.detail === 0) openArcade(); // Explicação DevIN: Permite acionamento por teclado/leitor de tela sem exigir gesto físico impossível.
    }); // Explicação DevIN: Finaliza o tratamento acessível do botão.
})(); // Explicação DevIN: Encerra a rotina isolada de inicialização.
