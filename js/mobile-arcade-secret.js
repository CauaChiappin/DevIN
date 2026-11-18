/**
 * Entrada secreta do Arcade por cinco toques no título "Time fundador".
 * Não depende de long-press, do tipo de dispositivo ou de um botão de jogos.
 * O servidor permanece responsável por sessão, CSRF e desbloqueio temporário.
 */
(() => { // Explicação DevIN: Isola o segredo para não afetar os demais módulos.
    'use strict'; // Explicação DevIN: Habilita execução JavaScript estrita.
    const target = document.querySelector('[data-mobile-arcade-secret]'); // Explicação DevIN: Usa título já existente na página Sobre nós.
    if (!target) return; // Explicação DevIN: Não ativa em páginas sem o título.
    const requiredTaps = 5; // Explicação DevIN: Define a sequência secreta de toques.
    const maxGap = 1700; // Explicação DevIN: Tolera o intervalo entre toques em celulares.
    let taps = 0; // Explicação DevIN: Conta somente toques sucessivos no título.
    let lastTap = 0; // Explicação DevIN: Registra quando o último toque ocorreu.
    let unlocking = false; // Explicação DevIN: Evita liberações simultâneas.

    async function unlock() { // Explicação DevIN: Solicita o desbloqueio autenticado.
        if (unlocking) return; // Explicação DevIN: Impede múltiplas requisições simultâneas.
        unlocking = true; // Explicação DevIN: Bloqueia repetição enquanto aguarda.
        try { // Explicação DevIN: Converte falhas da rede em uma mensagem legível.
            const csrfResponse = await fetch(new URL('csrf.php', window.location.href).toString(), { credentials: 'same-origin', cache: 'no-store' }); // Explicação DevIN: Consulta token de uma sessão autenticada.
            const csrfData = await csrfResponse.json(); // Explicação DevIN: Lê a resposta autenticada.
            if (!csrfResponse.ok || csrfData.sucesso !== true || typeof csrfData.token !== 'string') throw new Error('Não foi possível validar a sessão.'); // Explicação DevIN: Exige token CSRF válido.
            const response = await fetch(new URL('arcade_unlock.php', window.location.href).toString(), { // Explicação DevIN: Libera o Arcade sem expor um link público.
                method: 'POST', // Explicação DevIN: Solicita uma alteração autorizada da sessão.
                credentials: 'same-origin', // Explicação DevIN: Usa os cookies de autenticação atuais.
                cache: 'no-store', // Explicação DevIN: Evita reaproveitar uma liberação antiga.
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, // Explicação DevIN: Envia formulário de forma compatível com PHP.
                body: new URLSearchParams({ csrf_token: csrfData.token, modo: 'toque' }) // Explicação DevIN: Reaproveita o modo secreto já validado no backend.
            }); // Explicação DevIN: Finaliza requisição de desbloqueio.
            const result = await response.json(); // Explicação DevIN: Lê a resposta do servidor.
            if (!response.ok || result.sucesso !== true || typeof result.url !== 'string') throw new Error(result.mensagem || 'Não foi possível liberar os jogos.'); // Explicação DevIN: Não navega sem confirmação do servidor.
            window.location.assign(result.url); // Explicação DevIN: Entra no Arcade após a liberação temporária.
        } catch (error) { // Explicação DevIN: Permite nova tentativa quando a autenticação falhar.
            unlocking = false; // Explicação DevIN: Libera uma nova sequência de toques.
            if (typeof window.alert === 'function') window.alert(error.message || 'Não foi possível abrir o Arcade.'); // Explicação DevIN: Expõe erro compreensível no celular.
        } // Explicação DevIN: Conclui o desbloqueio.
    } // Explicação DevIN: Finaliza a função de entrada secreta.

    target.addEventListener('click', () => { // Explicação DevIN: Conta toques nativos, sem temporizador de pressão.
        if (unlocking) return; // Explicação DevIN: Ignora novos toques durante a navegação.
        const now = Date.now(); // Explicação DevIN: Usa o relógio do navegador.
        if (!lastTap || now - lastTap > maxGap) taps = 0; // Explicação DevIN: Reinicia sequências interrompidas.
        lastTap = now; // Explicação DevIN: Atualiza o tempo do toque mais recente.
        taps += 1; // Explicação DevIN: Conta o toque efetivo.
        if (taps < requiredTaps) return; // Explicação DevIN: Não expõe o Arcade antes de cinco toques.
        taps = 0; // Explicação DevIN: Zera a sequência antes de solicitar liberação.
        void unlock(); // Explicação DevIN: Dispara fluxo assíncrono com proteção contra duplicidade.
    }); // Explicação DevIN: Finaliza a detecção do segredo.
    document.addEventListener('visibilitychange', () => { // Explicação DevIN: Cancela sequência ao sair da página.
        if (document.hidden) { taps = 0; lastTap = 0; } // Explicação DevIN: Evita toques acumulados após retornar.
    }); // Explicação DevIN: Finaliza o tratamento da visibilidade.
})(); // Explicação DevIN: Encerra o módulo independente do Arcade.
