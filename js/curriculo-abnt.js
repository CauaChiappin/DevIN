(() => {
    'use strict';

    const root = document.querySelector('[data-abnt-review]');
    const button = document.querySelector('[data-abnt-run]');
    const status = document.querySelector('[data-abnt-status]');
    const result = document.querySelector('[data-abnt-result]');
    if (!(root instanceof HTMLElement) || !(button instanceof HTMLButtonElement)
        || !(status instanceof HTMLElement) || !(result instanceof HTMLElement)) return;

    button.addEventListener('click', async () => {
        button.disabled = true;
        status.textContent = 'Analisando seu currículo...';
        status.dataset.state = 'loading';
        result.hidden = true;

        try {
            const response = await fetch('../php/analisar_curriculo_abnt_api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': root.dataset.csrf || '',
                },
                body: JSON.stringify({ consent: true }),
            });
            const payload = await response.json();
            if (!response.ok || payload.sucesso !== true || !payload.revisao) {
                throw new Error(payload.mensagem || 'Não foi possível revisar o currículo.');
            }

            result.replaceChildren();
            const summary = document.createElement('p');
            summary.className = 'resume-review__summary';
            summary.textContent = payload.revisao.resumo;
            const conclusion = document.createElement('p');
            conclusion.className = 'resume-review__conclusion';
            conclusion.textContent = payload.revisao.conclusao;
            result.append(summary, conclusion);

            if (Array.isArray(payload.revisao.pontos) && payload.revisao.pontos.length > 0) {
                const heading = document.createElement('h2');
                heading.textContent = 'Pontos para revisar';
                const list = document.createElement('ul');
                payload.revisao.pontos.forEach((point) => {
                    if (typeof point?.titulo !== 'string' || typeof point?.orientacao !== 'string') return;
                    const item = document.createElement('li');
                    const title = document.createElement('strong');
                    title.textContent = point.titulo;
                    const advice = document.createElement('p');
                    advice.textContent = point.orientacao;
                    item.append(title, advice);
                    list.appendChild(item);
                });
                result.append(heading, list);
            }

            result.hidden = false;
            status.textContent = 'Revisão concluída. Confira as sugestões e valide os dados do documento.';
            status.dataset.state = 'success';
        } catch (error) {
            status.textContent = error instanceof Error ? error.message : 'Não foi possível revisar o currículo.';
            status.dataset.state = 'error';
            button.disabled = false;
        }
    });
})();