(() => {
    'use strict';

    const translate = (value) => typeof window.DevINTranslate === 'function'
        ? window.DevINTranslate(value)
        : value;
    const consent = document.querySelector('[data-resume-consent]');
    const generateButton = document.querySelector('[data-generate-resume]');
    const printButton = document.querySelector('[data-print-resume]');
    const status = document.querySelector('[data-resume-status]');
    const output = document.querySelector('[data-resume-output]');
    const emptyState = document.querySelector('[data-resume-empty]');
    const content = document.querySelector('[data-resume-content]');
    if (printButton instanceof HTMLButtonElement) {
        printButton.addEventListener('click', () => window.print());
    }

    if (!(consent instanceof HTMLInputElement)
        || !(status instanceof HTMLElement)
        || !(output instanceof HTMLElement)
        || !(emptyState instanceof HTMLElement)
        || !(content instanceof HTMLElement)) {
        return;
    }

    if (!(generateButton instanceof HTMLButtonElement)) return;

    const sections = [
        ['resumo', translate('Resumo profissional')],
        ['formacao', translate('Formação')],
        ['experiencia', translate('Experiência profissional')],
        ['cursos', translate('Cursos e certificações')],
        ['idiomas', translate('Idiomas')],
    ];

    consent.addEventListener('change', () => {
        generateButton.disabled = !consent.checked;
    });
    generateButton.addEventListener('click', async () => {
        if (!consent.checked || generateButton.disabled) return;

        generateButton.disabled = true;
        consent.disabled = true;
        generateButton.textContent = translate('Gerando seu currículo...');
        status.textContent = translate('A IA está organizando seus dados. Isso pode levar alguns segundos.');
        status.dataset.state = 'loading';

        try {
            const response = await fetch('../php/gerar_curriculo_api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('[data-resume-csrf]')?.getAttribute('data-resume-csrf')
                        || '',
                },
                body: JSON.stringify({ consent: true }),
            });
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error(response.redirected
                    ? translate('Sua sessão expirou. Entre novamente e tente gerar o currículo.')
                    : translate('O serviço de geração retornou uma resposta inválida. Tente novamente.'));
            }
            if (!response.ok || result.sucesso !== true || !result.curriculo) {
                throw new Error(translate(result.mensagem || 'Não foi possível gerar o currículo.'));
            }

            content.replaceChildren();
            const name = document.createElement('h2');
            name.textContent = result.curriculo.nome;
            content.appendChild(name);

            sections.forEach(([key, title]) => {
                const value = result.curriculo[key];
                const editorField = document.querySelector(`[name="resume[${key}]"]`);
                if (editorField instanceof HTMLTextAreaElement) editorField.value = value;
                if (typeof value !== 'string' || value.trim() === '') return;
                const section = document.createElement('section');
                section.className = 'dashboard-resume__section';
                const heading = document.createElement('h3');
                heading.textContent = title;
                const paragraph = document.createElement('p');
                paragraph.textContent = value;
                section.append(heading, paragraph);
                content.appendChild(section);
            });

            emptyState.hidden = true;
            content.hidden = false;
            output.classList.remove('dashboard-resume__paper--empty');
            if (printButton) printButton.hidden = false;
            status.textContent = translate('Currículo gerado. Revise todas as informações antes de imprimir ou salvar em PDF.');
            status.dataset.state = 'success';
        } catch (error) {
            status.textContent = error instanceof Error
                ? translate(error.message)
                : translate('Não foi possível gerar o currículo. Tente novamente.');
            status.dataset.state = 'error';
        } finally {
            consent.disabled = false;
            generateButton.textContent = translate(content.hidden
                ? '✦ Tentar gerar novamente'
                : '✦ Gerar novamente');
            generateButton.disabled = !consent.checked;
        }
    });

    const generatorPanel = generateButton.closest('[data-resume-auto-generate]');
    if (generatorPanel?.getAttribute('data-resume-auto-generate') === 'true') {
        consent.checked = true;
        generateButton.disabled = false;
        generateButton.click();
    }
})();
