/*
 * REVISÃO DETALHADA DEVIN — js/dashboard-menu.js
 * Finalidade: dashboard menu.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const MOBILE_BREAKPOINT = 720; // Explicação DevIN: Declara variável JavaScript MOBILE_BREAKPOINT para reutilizar valor ou resultado.

    const init = ({ shell, toggle, windowRef = window }) => { // Explicação DevIN: Declara variável JavaScript init para reutilizar valor ou resultado.
        /* Recebe elementos explicitamente para manter a lógica reutilizável e fácil de conectar ao painel.
         * O mesmo limite de 720px é usado pelo CSS para mudar entre navegação móvel e desktop. */
        if (!shell || !toggle) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const isMobile = () => { // Explicação DevIN: Declara variável JavaScript isMobile para reutilizar valor ou resultado.
            // matchMedia acompanha a regra CSS; o fallback cobre ambientes sem essa API.
            if (typeof windowRef.matchMedia === 'function') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return windowRef.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            return Number(windowRef.innerWidth || 0) <= MOBILE_BREAKPOINT; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Atualiza classe e estado ARIA juntos para manter interface e leitor de tela alinhados.
        const setOpen = (open) => { // Explicação DevIN: Declara variável JavaScript setOpen para reutilizar valor ou resultado.
            // Em desktop o menu não pode ficar neste modo expansível, mesmo se receber open=true.
            const nextOpen = isMobile() && open; // Explicação DevIN: Declara variável JavaScript nextOpen para reutilizar valor ou resultado.
            shell.classList.toggle('mobile-menu-open', nextOpen); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            toggle.setAttribute('aria-expanded', String(nextOpen)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const syncViewport = () => { // Explicação DevIN: Declara variável JavaScript syncViewport para reutilizar valor ou resultado.
            // Fecha o menu ao sair do modo móvel e preserva o estado atual ao redimensionar dentro dele.
            if (!isMobile()) setOpen(false); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            else toggle.setAttribute('aria-expanded', String(shell.classList.contains('mobile-menu-open'))); // Explicação DevIN: Define ramo alternativo para condição anterior.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        toggle.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            // No desktop, o layout lateral é controlado por dashboard.js; aqui o clique só alterna no celular.
            if (isMobile()) setOpen(!shell.classList.contains('mobile-menu-open')); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        windowRef.addEventListener?.('resize', syncViewport); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        // Reavalia imediatamente para que o estado inicial reflita a largura atual da janela.
        syncViewport(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        return { isMobile, setOpen, syncViewport }; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (typeof window !== 'undefined') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Expõe a inicialização para dashboard.js sem executar o módulo em ambientes sem navegador.
        window.DevINDashboardMenu = { init }; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
