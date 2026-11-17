/**
 * Interface única do Suporte DevIN: formulário do usuário e caixa ADM.
 * Sempre cria nós com textContent; conteúdo vindo do MySQL nunca vira HTML.
 * As mutações usam FormData com CSRF e identidade conferida pelo servidor.
 */
(() => {
    'use strict';
    const root = document.querySelector('[data-support-root]');
    if (!root) return; // Não executar em outras páginas do portal.
    const admin = root.dataset.role === 'admin';
    const endpoint = root.dataset.endpoint;
    const csrf = root.dataset.csrf;
    const list = root.querySelector('[data-support-list]');
    const detail = root.querySelector('[data-support-detail]');
    const feedback = root.querySelector('[data-support-feedback]');
    const messageForm = root.querySelector('[data-support-reply]');
    const search = root.querySelector('[data-support-search]');
    const createForm = root.querySelector('[data-support-create]');
    const categories = {
        erro:'Erro no site', duvida:'Dúvida', conta:'Conta e acesso',
        vaga:'Vagas e candidaturas', seguranca:'Segurança e privacidade',
        sugestao:'Sugestão', outro:'Outros assuntos'
    };
    let tickets = [];
    let selectedId = null;

    // Consulta JSON autenticada ou FormData CSRF; erro HTTP nunca vira sucesso.
    async function request(query = {}, payload = null) {
        const url = new URL(endpoint, location.href);
        Object.entries(query).forEach(([key, value]) => url.searchParams.set(key, String(value)));
        const options = { credentials:'same-origin', cache:'no-store' };
        if (payload) {
            options.method = 'POST';
            options.body = payload;
            if (!payload.has('csrf_token')) payload.set('csrf_token', csrf);
        }
        const response = await fetch(url.toString(), options);
        const data = await response.json().catch(() => null);
        if (!response.ok || !data?.sucesso) {
            throw new Error(data?.mensagem || 'O servidor não conseguiu atender agora.');
        }
        return data;
    }

    // Cada texto é inserido sem interpolação de HTML, evitando XSS em relatos.
    function node(tag, text = '', className = '') {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== '') element.textContent = String(text);
        return element;
    }

    function status(text, isError = false) {
        if (!feedback) return;
        feedback.textContent = text;
        feedback.classList.toggle('is-error', isError);
    }

    function label(ticket) {
        return categories[ticket.category] || ticket.category;
    }

    function button(text, onclick, className = 'support-secondary') {
        const b = node('button', text, className);
        b.type = 'button';
        b.addEventListener('click', onclick);
        return b;
    }

    // Listagem possui nomes e e-mails apenas para administradores.
    function renderList() {
        if (!list) return;
        list.replaceChildren();
        const term = (search?.value || '').trim().toLocaleLowerCase('pt-BR');
        const visible = tickets.filter(item => !term || [
            item.id, item.requester_name || '', item.requester_email || '',
            label(item), item.description || ''
        ].join(' ').toLocaleLowerCase('pt-BR').includes(term));
        const count = root.querySelector('[data-support-stat-open]');
        if (count) {
            const open = tickets.filter(item => item.status === 'aberto').length;
            count.textContent = open + ' aguardando atendimento · ' + tickets.length + ' chamados exibidos';
        }
        if (!visible.length) {
            list.append(node('p', 'Nenhum chamado encontrado.', 'support-hint'));
            return;
        }
        visible.forEach(ticket => {
            const item = button('', () => loadDetail(Number(ticket.id)), 'support-ticket');
            item.classList.toggle('is-active', Number(ticket.id) === selectedId);
            item.append(
                node('strong', '#' + ticket.id + ' · ' + label(ticket)),
                node('small', admin
                    ? (ticket.requester_name + ' · ' + ticket.requester_email)
                    : new Date(ticket.created_at.replace(' ', 'T')).toLocaleDateString('pt-BR')),
                node('span', (ticket.description || '').slice(0,130), 'support-ticket-snippet'),
                node('em', ticket.status === 'aberto' ? 'Aguardando resposta'
                    : ticket.status === 'respondido' ? 'Respondido' : 'Encerrado',
                    'support-ticket-state state-' + ticket.status)
            );
            list.append(item);
        });
    }

    // Reconsulta detalhes com verificação de propriedade no PHP.
    async function loadDetail(id) {
        selectedId = id;
        renderList();
        detail.hidden = false;
        detail.replaceChildren(node('p','Buscando detalhes...','support-hint'));
        if (messageForm) messageForm.hidden = true;
        try {
            const { ticket, replies } = await request({action:'detail', id});
            detail.replaceChildren();
            detail.append(node('h3','#' + ticket.id + ' · ' + label(ticket)));
            if (admin) {
                detail.append(
                    node('p', 'Usuário: ' + ticket.requester_name),
                    node('p', 'E-mail: ' + ticket.requester_email),
                    node('p','Conta: ' + (ticket.requester_type === 'pessoa' ? 'Candidato' : 'Empresa'))
                );
            }
            detail.append(node('p', 'Aberto em: ' + ticket.created_at, 'support-hint'));
            detail.append(node('h4','Descrição enviada'));
            detail.append(node('p', ticket.description, 'support-description'));
            if (ticket.attachment_name) {
                const link = node('a','Baixar anexo: ' + ticket.attachment_name, 'support-download');
                const url = new URL(endpoint, location.href);
                url.searchParams.set('action','download');
                url.searchParams.set('id', String(id));
                link.href = url.toString();
                link.rel = 'nofollow';
                detail.append(link);
            }
            detail.append(node('h4','Histórico do atendimento'));
            if (!replies.length) detail.append(node('p','Ainda não há respostas da equipe.','support-hint'));
            replies.forEach(reply => {
                const box = node('article','','support-answer');
                box.append(
                    node('strong','Suporte DevIN · ' + reply.created_at),
                    node('p',reply.message, 'support-description')
                );
                if (admin && (!reply.chat_sent_at || !reply.email_sent_at)) {
                    const channels = (!reply.chat_sent_at ? 'mensagem interna ' : '')
                        + (!reply.email_sent_at ? 'e-mail ' : '');
                    box.append(node('small','Entrega pendente: ' + channels, 'support-hint'));
                    box.append(button('Tentar entrega novamente', async event => {
                        event.disabled = true;
                        try {
                            const payload = new FormData();
                            payload.set('action','retry');
                            payload.set('reply_id',String(reply.id));
                            const result = await request({},payload);
                            status('Mensagens: ' + (result.chat_sent ? 'enviadas' : 'pendentes')
                                + ' · E-mail: ' + (result.email_sent ? 'enviado' : 'pendente'));
                            await loadDetail(id);
                        } catch(error) { status(error.message, true); }
                        finally { event.disabled = false; }
                    }));
                }
                detail.append(box);
            });
            if (admin && messageForm) {
                messageForm.hidden = ticket.status === 'fechado';
                messageForm.dataset.ticketId = String(id);
                const close = messageForm.querySelector('[data-support-close]');
                if (close) close.disabled = ticket.status === 'fechado';
            }
        } catch (error) {
            detail.replaceChildren(node('p', error.message, 'support-error'));
            status(error.message, true);
        }
    }

    // Atualizar a caixa sem solicitar detalhes ou dados de outras contas.
    async function refresh() {
        try {
            const response = await request({action:'list'});
            tickets = response.tickets || [];
            renderList();
            if (selectedId && tickets.some(t=>Number(t.id)===selectedId)) {
                await loadDetail(selectedId);
            }
        } catch (error) {
            list.replaceChildren(node('p', error.message, 'support-error'));
            status(error.message, true);
        }
    }

    // O formulário requer categoria, descrição e opcionalmente arquivo <=20MiB.
    createForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!createForm.reportValidity()) return;
        const file = createForm.querySelector('[name="attachment"]')?.files?.[0];
        if (file && file.size > 20 * 1024 * 1024) {
            status('O anexo ultrapassa 20 MB.', true); return;
        }
        const send = createForm.querySelector('[data-support-submit]');
        send.disabled = true;
        status('Enviando sua solicitação...');
        try {
            const data = new FormData(createForm);
            data.set('action','create');
            const response = await request({},data);
            createForm.reset();
            status('Chamado #' + response.id + ' enviado com sucesso.');
            await refresh();
            await loadDetail(response.id);
        } catch (error) { status(error.message, true); }
        finally { send.disabled = false; }
    });

    // O usuário recebe confirmação apenas após a API gravar sua resposta.
    messageForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!messageForm.reportValidity()) return;
        const send = messageForm.querySelector('button[type="submit"]');
        send.disabled = true;
        status('Registrando a resposta...');
        try {
            const payload = new FormData(messageForm);
            payload.set('action','reply');
            payload.set('id',messageForm.dataset.ticketId);
            const result = await request({},payload);
            messageForm.reset();
            status('Resposta registrada. Mensagem interna: ' + (result.chat_sent ? 'enviada' : 'pendente')
                + '. E-mail: ' + (result.email_sent ? 'enviado' : 'pendente') + '.');
            await refresh();
        } catch (error) { status(error.message, true); }
        finally { send.disabled = false; }
    });

    // Encerrar também exige confirmação do ADM e validação server-side.
    root.querySelector('[data-support-close]')?.addEventListener('click', async () => {
        if (!selectedId || !window.confirm('Encerrar este chamado? As mensagens continuam registradas.')) return;
        const payload = new FormData();
        payload.set('action','close');payload.set('id',String(selectedId));
        try { await request({},payload);status('Chamado encerrado.');await refresh(); }
        catch(error){status(error.message,true);}
    });
    search?.addEventListener('input', renderList);
    root.querySelector('[data-support-refresh]')?.addEventListener('click', refresh);
    refresh();
})();
