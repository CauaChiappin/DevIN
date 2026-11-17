/**
 * Interface única do Suporte DevIN: formulário do usuário e caixa ADM.
 * Sempre cria nós com textContent; conteúdo vindo do MySQL nunca vira HTML.
 * As mutações usam FormData com CSRF e identidade conferida pelo servidor.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const root = document.querySelector('[data-support-root]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!root) return; // Não executar em outras páginas do portal.
    const admin = root.dataset.role === 'admin'; // Explicação DevIN: Declara variável JavaScript admin para reutilizar valor ou resultado.
    const endpoint = root.dataset.endpoint; // Explicação DevIN: Declara variável JavaScript endpoint para reutilizar valor ou resultado.
    const csrf = root.dataset.csrf; // Explicação DevIN: Declara variável JavaScript csrf para reutilizar valor ou resultado.
    const list = root.querySelector('[data-support-list]'); // Explicação DevIN: Declara variável JavaScript list para reutilizar valor ou resultado.
    const detail = root.querySelector('[data-support-detail]'); // Explicação DevIN: Declara variável JavaScript detail para reutilizar valor ou resultado.
    const feedback = root.querySelector('[data-support-feedback]'); // Explicação DevIN: Declara variável JavaScript feedback para reutilizar valor ou resultado.
    const messageForm = root.querySelector('[data-support-reply]'); // Explicação DevIN: Declara variável JavaScript messageForm para reutilizar valor ou resultado.
    const search = root.querySelector('[data-support-search]'); // Explicação DevIN: Declara variável JavaScript search para reutilizar valor ou resultado.
    const createForm = root.querySelector('[data-support-create]'); // Explicação DevIN: Declara variável JavaScript createForm para reutilizar valor ou resultado.
    const categories = { // Explicação DevIN: Declara variável JavaScript categories para reutilizar valor ou resultado.
        erro:'Erro no site', duvida:'Dúvida', conta:'Conta e acesso', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        vaga:'Vagas e candidaturas', seguranca:'Segurança e privacidade', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        sugestao:'Sugestão', outro:'Outros assuntos' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    let tickets = []; // Explicação DevIN: Declara variável JavaScript tickets para reutilizar valor ou resultado.
    let selectedId = null; // Explicação DevIN: Declara variável JavaScript selectedId para reutilizar valor ou resultado.

    // Consulta JSON autenticada ou FormData CSRF; erro HTTP nunca vira sucesso.
    async function request(query = {}, payload = null) { // Explicação DevIN: Declara função/método request, com seus parâmetros e comportamento.
        const url = new URL(endpoint, location.href); // Explicação DevIN: Declara variável JavaScript url para reutilizar valor ou resultado.
        Object.entries(query).forEach(([key, value]) => url.searchParams.set(key, String(value))); // Explicação DevIN: Continua expressão ou bloco pertencente a função request.
        const options = { credentials:'same-origin', cache:'no-store' }; // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
        if (payload) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            options.method = 'POST'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            options.body = payload; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (!payload.has('csrf_token')) payload.set('csrf_token', csrf); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const response = await fetch(url.toString(), options); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        const data = await response.json().catch(() => null); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if (!response.ok || !data?.sucesso) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Error(data?.mensagem || 'O servidor não conseguiu atender agora.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Cada texto é inserido sem interpolação de HTML, evitando XSS em relatos.
    function node(tag, text = '', className = '') { // Explicação DevIN: Declara função/método node, com seus parâmetros e comportamento.
        const element = document.createElement(tag); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (className) element.className = className; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (text !== '') element.textContent = String(text); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return element; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function status(text, isError = false) { // Explicação DevIN: Declara função/método status, com seus parâmetros e comportamento.
        if (!feedback) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        feedback.textContent = text; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        feedback.classList.toggle('is-error', isError); // Explicação DevIN: Continua expressão ou bloco pertencente a função status.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function label(ticket) { // Explicação DevIN: Declara função/método label, com seus parâmetros e comportamento.
        return categories[ticket.category] || ticket.category; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function button(text, onclick, className = 'support-secondary') { // Explicação DevIN: Declara função/método button, com seus parâmetros e comportamento.
        const b = node('button', text, className); // Explicação DevIN: Declara variável JavaScript b para reutilizar valor ou resultado.
        b.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        // O callback recebe o próprio botão para bloquear duplo clique.
        b.addEventListener('click', () => void onclick(b)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        return b; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Listagem possui nomes e e-mails apenas para administradores.
    function renderList() { // Explicação DevIN: Declara função/método renderList, com seus parâmetros e comportamento.
        if (!list) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        list.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
        const term = (search?.value || '').trim().toLocaleLowerCase('pt-BR'); // Explicação DevIN: Declara variável JavaScript term para reutilizar valor ou resultado.
        const visible = tickets.filter(item => !term || [ // Explicação DevIN: Declara variável JavaScript visible para reutilizar valor ou resultado.
            item.id, item.requester_name || '', item.requester_email || '', // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
            label(item), item.description || '' // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
        ].join(' ').toLocaleLowerCase('pt-BR').includes(term)); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
        const count = root.querySelector('[data-support-stat-open]'); // Explicação DevIN: Declara variável JavaScript count para reutilizar valor ou resultado.
        if (count) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const open = tickets.filter(item => item.status === 'aberto').length; // Explicação DevIN: Declara variável JavaScript open para reutilizar valor ou resultado.
            count.textContent = open + ' aguardando atendimento · ' + tickets.length + ' chamados exibidos'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!visible.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            list.append(node('p', 'Nenhum chamado encontrado.', 'support-hint')); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        visible.forEach(ticket => { // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
            const item = button('', () => loadDetail(Number(ticket.id)), 'support-ticket'); // Explicação DevIN: Declara variável JavaScript item para reutilizar valor ou resultado.
            item.classList.toggle('is-active', Number(ticket.id) === selectedId); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
            item.append( // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
                node('strong', '#' + ticket.id + ' · ' + label(ticket)), // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
                node('small', admin // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
                    ? (ticket.requester_name + ' · ' + ticket.requester_email) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : new Date(ticket.created_at.replace(' ', 'T')).toLocaleDateString('pt-BR')), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                node('span', (ticket.description || '').slice(0,130), 'support-ticket-snippet'), // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
                node('em', ticket.status === 'aberto' ? 'Aguardando resposta' // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
                    : ticket.status === 'respondido' ? 'Respondido' : 'Encerrado', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    'support-ticket-state state-' + ticket.status) // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            list.append(item); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderList.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Reconsulta detalhes com verificação de propriedade no PHP.
    async function loadDetail(id) { // Explicação DevIN: Declara função/método loadDetail, com seus parâmetros e comportamento.
        selectedId = id; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        renderList(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
        detail.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        detail.replaceChildren(node('p','Buscando detalhes...','support-hint')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
        if (messageForm) messageForm.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const { ticket, replies } = await request({action:'detail', id}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            detail.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            detail.append(node('h3','#' + ticket.id + ' · ' + label(ticket))); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            if (admin) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                detail.append( // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    node('p', 'Usuário: ' + ticket.requester_name), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    node('p', 'E-mail: ' + ticket.requester_email), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    node('p','Conta: ' + (ticket.requester_type === 'pessoa' ? 'Candidato' : 'Empresa')) // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            detail.append(node('p', 'Aberto em: ' + ticket.created_at, 'support-hint')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            detail.append(node('h4','Descrição enviada')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            detail.append(node('p', ticket.description, 'support-description')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            if (ticket.attachment_name) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const link = node('a','Baixar anexo: ' + ticket.attachment_name, 'support-download'); // Explicação DevIN: Declara variável JavaScript link para reutilizar valor ou resultado.
                const url = new URL(endpoint, location.href); // Explicação DevIN: Declara variável JavaScript url para reutilizar valor ou resultado.
                url.searchParams.set('action','download'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                url.searchParams.set('id', String(id)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                link.href = url.toString(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                link.rel = 'nofollow'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                detail.append(link); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            detail.append(node('h4','Histórico do atendimento')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            if (!replies.length) detail.append(node('p','Ainda não há respostas da equipe.','support-hint')); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            replies.forEach(reply => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                const box = node('article','','support-answer'); // Explicação DevIN: Declara variável JavaScript box para reutilizar valor ou resultado.
                box.append( // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    node('strong','Suporte DevIN · ' + reply.created_at), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    node('p',reply.message, 'support-description') // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (admin && (!reply.chat_sent_at || !reply.email_sent_at)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    const channels = (!reply.chat_sent_at ? 'mensagem interna ' : '') // Explicação DevIN: Declara variável JavaScript channels para reutilizar valor ou resultado.
                        + (!reply.email_sent_at ? 'e-mail ' : ''); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    box.append(node('small','Entrega pendente: ' + channels, 'support-hint')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                    box.append(button('Tentar entrega novamente', async event => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                        event.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                            const payload = new FormData(); // Explicação DevIN: Declara variável JavaScript payload para reutilizar valor ou resultado.
                            payload.set('action','retry'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                            payload.set('reply_id',String(reply.id)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                            const result = await request({},payload); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
                            status('Mensagens: ' + (result.chat_sent ? 'enviadas' : 'pendentes') // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                                + ' · E-mail: ' + (result.email_sent ? 'enviado' : 'pendente')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                            await loadDetail(id); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
                        } catch(error) { status(error.message, true); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                        finally { event.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                    })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                detail.append(box); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (admin && messageForm) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                messageForm.hidden = ticket.status === 'fechado'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                messageForm.dataset.ticketId = String(id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const close = messageForm.querySelector('[data-support-close]'); // Explicação DevIN: Declara variável JavaScript close para reutilizar valor ou resultado.
                if (close) close.disabled = ticket.status === 'fechado'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            detail.replaceChildren(node('p', error.message, 'support-error')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
            status(error.message, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDetail.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Atualizar a caixa sem solicitar detalhes ou dados de outras contas.
    async function refresh() { // Explicação DevIN: Declara função/método refresh, com seus parâmetros e comportamento.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const response = await request({action:'list'}); // Explicação DevIN: Declara variável JavaScript response para reutilizar valor ou resultado.
            tickets = response.tickets || []; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderList(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            if (selectedId && tickets.some(t=>Number(t.id)===selectedId)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                await loadDetail(selectedId); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            list.replaceChildren(node('p', error.message, 'support-error')); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            status(error.message, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // O formulário requer categoria, descrição e opcionalmente arquivo <=20MiB.
    createForm?.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        if (!createForm.reportValidity()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const file = createForm.querySelector('[name="attachment"]')?.files?.[0]; // Explicação DevIN: Declara variável JavaScript file para reutilizar valor ou resultado.
        if (file && file.size > 20 * 1024 * 1024) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            status('O anexo ultrapassa 20 MB.', true); return; // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const send = createForm.querySelector('[data-support-submit]'); // Explicação DevIN: Declara variável JavaScript send para reutilizar valor ou resultado.
        send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status('Enviando sua solicitação...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = new FormData(createForm); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            data.set('action','create'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            const response = await request({},data); // Explicação DevIN: Declara variável JavaScript response para reutilizar valor ou resultado.
            createForm.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            status('Chamado #' + response.id + ' enviado com sucesso.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            await refresh(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            await loadDetail(response.id); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        } catch (error) { status(error.message, true); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        finally { send.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // O usuário recebe confirmação apenas após a API gravar sua resposta.
    messageForm?.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        if (!messageForm.reportValidity()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const send = messageForm.querySelector('button[type="submit"]'); // Explicação DevIN: Declara variável JavaScript send para reutilizar valor ou resultado.
        send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status('Registrando a resposta...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const payload = new FormData(messageForm); // Explicação DevIN: Declara variável JavaScript payload para reutilizar valor ou resultado.
            payload.set('action','reply'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            payload.set('id',messageForm.dataset.ticketId); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            const result = await request({},payload); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
            messageForm.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            status('Resposta registrada. Mensagem interna: ' + (result.chat_sent ? 'enviada' : 'pendente') // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
                + '. E-mail: ' + (result.email_sent ? 'enviado' : 'pendente') + '.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            await refresh(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        } catch (error) { status(error.message, true); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        finally { send.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Encerrar também exige confirmação do ADM e validação server-side.
    root.querySelector('[data-support-close]')?.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!selectedId || !window.confirm('Encerrar este chamado? As mensagens continuam registradas.')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const payload = new FormData(); // Explicação DevIN: Declara variável JavaScript payload para reutilizar valor ou resultado.
        payload.set('action','close');payload.set('id',String(selectedId)); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        try { await request({},payload);status('Chamado encerrado.');await refresh(); } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        catch(error){status(error.message,true);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    search?.addEventListener('input', renderList); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    root.querySelector('[data-support-refresh]')?.addEventListener('click', refresh); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    refresh(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
