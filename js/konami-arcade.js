(() => {
    'use strict';

    const sequence = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'KeyB', 'KeyA', 'Enter'
    ];
    let position = 0;
    let unlocking = false;

    const isTyping = (target) => target instanceof HTMLElement
        && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName));

    const notify = (message) => {
        let status = document.querySelector('[data-arcade-unlock-status]');
        if (!status) {
            status = document.createElement('div');
            status.dataset.arcadeUnlockStatus = '';
            status.setAttribute('role', 'status');
            status.className = 'arcade-unlock-status';
            document.body.appendChild(status);
        }
        status.textContent = message;
        status.hidden = false;
    };

    const unlock = async () => {
        unlocking = true;
        try {
            const csrfResponse = await fetch('../php/csrf.php', { credentials: 'same-origin' });
            const csrfData = await csrfResponse.json();
            if (!csrfResponse.ok || csrfData.sucesso !== true || typeof csrfData.token !== 'string') {
                throw new Error('Não foi possível validar a sessão. Atualize o dashboard e tente novamente.');
            }

            const response = await fetch('../php/arcade_unlock.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: new URLSearchParams({
                    csrf_token: csrfData.token,
                    sequencia: JSON.stringify(sequence)
                })
            });
            const result = await response.json();
            if (!response.ok || result.sucesso !== true || typeof result.url !== 'string') {
                throw new Error(result.mensagem || 'Não foi possível liberar os jogos.');
            }
            window.location.assign(result.url);
        } catch (error) {
            position = 0;
            notify(error.message || 'Não foi possível liberar os jogos. Atualize o dashboard e tente novamente.');
        } finally {
            unlocking = false;
        }
    };

    document.addEventListener('keydown', (event) => {
        if (unlocking || event.repeat || isTyping(event.target)) return;

        const key = event.code;
        if (key === sequence[position]) {
            position += 1;
            if (position === sequence.length) {
                event.preventDefault();
                position = 0;
                unlock();
            } else if (position < 8) {
                event.preventDefault();
            }
            return;
        }

        position = key === sequence[0] ? 1 : 0;
    });
})();
