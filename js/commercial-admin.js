/**
 * Painel comercial do ADM. Todo texto externo usa textContent para prevenir XSS;
 * alterações passam por sessão, CSRF e validação de permissão no PHP.
 * Nenhuma interação ativa plano, assinante ou pagamento.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const root=document.querySelector('[data-commercial-admin]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if(!root) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const endpoint=root.dataset.endpoint; // Explicação DevIN: Declara variável JavaScript endpoint para reutilizar valor ou resultado.
    const csrf=root.dataset.csrf; // Explicação DevIN: Declara variável JavaScript csrf para reutilizar valor ou resultado.
    const list=root.querySelector('[data-commercial-list]'); // Explicação DevIN: Declara variável JavaScript list para reutilizar valor ou resultado.
    const subscribers=root.querySelector('[data-commercial-subscribers]'); // Explicação DevIN: Declara variável JavaScript subscribers para reutilizar valor ou resultado.
    const editor=root.querySelector('[data-commercial-editor]'); // Explicação DevIN: Declara variável JavaScript editor para reutilizar valor ou resultado.
    const form=root.querySelector('[data-commercial-form]'); // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
    const target=root.querySelector('[data-commercial-edit-target]'); // Explicação DevIN: Declara variável JavaScript target para reutilizar valor ou resultado.
    const feedback=root.querySelector('[data-commercial-feedback]'); // Explicação DevIN: Declara variável JavaScript feedback para reutilizar valor ou resultado.
    const status=root.querySelector('[data-commercial-status]'); // Explicação DevIN: Declara variável JavaScript status para reutilizar valor ou resultado.
    let selected=null; // Explicação DevIN: Declara variável JavaScript selected para reutilizar valor ou resultado.

    function el(tag,value='',className='') { // Explicação DevIN: Declara função/método el, com seus parâmetros e comportamento.
        const element=document.createElement(tag); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        element.textContent=String(value); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if(className) element.className=className; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return element; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function fmtCents(cents) { // Explicação DevIN: Declara função/método fmtCents, com seus parâmetros e comportamento.
        return cents===null ? 'Preço não definido' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            : new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(cents)/100); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function labelLimit(value) { // Explicação DevIN: Declara função/método labelLimit, com seus parâmetros e comportamento.
        return value===null ? 'Sem limite específico' : String(value); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function setStatus(message,error=false) { // Explicação DevIN: Declara função/método setStatus, com seus parâmetros e comportamento.
        status.textContent=message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.classList.toggle('commercial-error',error); // Explicação DevIN: Continua expressão ou bloco pertencente a função setStatus.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    async function request(action='plans',payload=null) { // Explicação DevIN: Declara função/método request, com seus parâmetros e comportamento.
        const url=new URL(endpoint,location.href); // Explicação DevIN: Declara variável JavaScript url para reutilizar valor ou resultado.
        if(!payload)url.searchParams.set('action',action); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const options={credentials:'same-origin',cache:'no-store'}; // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
        if(payload) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            options.method='POST'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            payload.set('csrf_token',csrf); // Explicação DevIN: Continua expressão ou bloco pertencente a função request.
            options.body=payload; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const response=await fetch(url.toString(),options); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        const data=await response.json().catch(()=>null); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if(!response.ok || !data?.success)throw new Error(data?.message||'Serviço comercial indisponível.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    async function loadPlans() { // Explicação DevIN: Declara função/método loadPlans, com seus parâmetros e comportamento.
        const response=await request(); // Explicação DevIN: Declara variável JavaScript response para reutilizar valor ou resultado.
        list.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
        response.rows.forEach(plan=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            const card=el('article','','commercial-card'); // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
            card.append(el('small',(plan.audience==='empresa'?'Empresa':'Pessoa')+' · '+plan.tier)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('h2',plan.title)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('strong',fmtCents(plan.monthly_price_cents))); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('p','Vagas ativas: '+labelLimit(plan.max_active_jobs))); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('p','Inscrições/mês: '+labelLimit(plan.max_applications_month))); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('p','Comissão de serviço B2B: '+(Number(plan.placement_fee_bps)/100).toFixed(2)+'%')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            card.append(el('p','Estoque: '+labelLimit(plan.stock))); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            const state=plan.is_published ? 'Publicado' : 'Rascunho não vendido'; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
            card.append(el('p','Estado: '+state)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            const edit=el('button','Editar rascunho'); // Explicação DevIN: Declara variável JavaScript edit para reutilizar valor ou resultado.
            edit.type='button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            edit.disabled=Number(plan.is_published)!==0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            edit.addEventListener('click',()=>openEditor(plan)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            card.append(edit); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
            list.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadPlans.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function openEditor(plan) { // Explicação DevIN: Declara função/método openEditor, com seus parâmetros e comportamento.
        selected=plan; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        editor.hidden=false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        target.textContent=(plan.audience==='empresa'?'Empresa':'Pessoa')+' / '+plan.tier; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.title.value=plan.title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.price_brl.value=plan.monthly_price_cents===null?'':(Number(plan.monthly_price_cents)/100).toFixed(2); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.max_active_jobs.value=plan.max_active_jobs??''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.max_applications_month.value=plan.max_applications_month??''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.placement_percent.value=(Number(plan.placement_fee_bps)/100).toFixed(2); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.elements.stock.value=plan.stock??''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        feedback.textContent=''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        editor.scrollIntoView({behavior:'smooth',block:'start'}); // Explicação DevIN: Continua expressão ou bloco pertencente a função openEditor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function nullableInt(name) { // Explicação DevIN: Declara função/método nullableInt, com seus parâmetros e comportamento.
        const raw=form.elements[name].value.trim(); // Explicação DevIN: Declara variável JavaScript raw para reutilizar valor ou resultado.
        if(raw==='')return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const number=Number(raw); // Explicação DevIN: Declara variável JavaScript number para reutilizar valor ou resultado.
        if(!Number.isSafeInteger(number)||number<0)throw new Error('Informe um limite inteiro não negativo.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return number; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    async function save(event) { // Explicação DevIN: Declara função/método save, com seus parâmetros e comportamento.
        event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        if(!selected||!form.reportValidity())return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const button=form.querySelector('[data-commercial-save]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const price=Number(form.elements.price_brl.value); // Explicação DevIN: Declara variável JavaScript price para reutilizar valor ou resultado.
            const percent=Number(form.elements.placement_percent.value); // Explicação DevIN: Declara variável JavaScript percent para reutilizar valor ou resultado.
            if(!Number.isFinite(price)||price<=0||price>100000)throw new Error('Preço de rascunho inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if(!Number.isFinite(percent)||percent<0||percent>5)throw new Error('A taxa B2B não pode ultrapassar 5%.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const changes={ // Explicação DevIN: Declara variável JavaScript changes para reutilizar valor ou resultado.
                title:form.elements.title.value.trim(), // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                monthly_price_cents:Math.round(price*100), // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                max_active_jobs:nullableInt('max_active_jobs'), // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                max_applications_month:nullableInt('max_applications_month'), // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                placement_fee_bps:Math.round(percent*100), // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                stock:nullableInt('stock') // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if(!window.confirm('Salvar alterações no plano rascunho? A operação será auditada, mas não haverá cobrança.'))return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const payload=new FormData(); // Explicação DevIN: Declara variável JavaScript payload para reutilizar valor ou resultado.
            payload.set('plan_id',String(selected.id)); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            payload.set('revision',String(selected.revision)); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            payload.set('changes',JSON.stringify(changes)); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            await request('',payload); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            editor.hidden=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            selected=null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            setStatus('Rascunho salvo com registro de auditoria. Nenhum pagamento foi realizado.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            await loadPlans(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        }catch(error){ // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            feedback.textContent=error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            feedback.classList.add('commercial-error'); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        }finally {button.disabled=false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    async function loadSubscribers() { // Explicação DevIN: Declara função/método loadSubscribers, com seus parâmetros e comportamento.
        const data=await request('subscribers'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        subscribers.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
        if(!data.rows.length){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            subscribers.append(el('p','Nenhuma assinatura registrada. Nenhum plano é vendido automaticamente.')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const table=el('table'); // Explicação DevIN: Declara variável JavaScript table para reutilizar valor ou resultado.
        const head=el('tr'); // Explicação DevIN: Declara variável JavaScript head para reutilizar valor ou resultado.
        for(const name of ['ID','Plano','Tipo','Conta ID','Status','Vencimento']) // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            head.append(el('th',name)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
        table.append(head); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
        data.rows.forEach(sub=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
            const row=el('tr'); // Explicação DevIN: Declara variável JavaScript row para reutilizar valor ou resultado.
            for(const value of [sub.id,sub.plan,sub.owner_type,sub.owner_id,sub.status,sub.ends_at??'—']) // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                row.append(el('td',value)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
            table.append(row); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        subscribers.append(table); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    root.querySelectorAll('[data-commercial-tab]').forEach(button=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
        button.addEventListener('click',async()=>{ // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const selectedTab=button.dataset.commercialTab; // Explicação DevIN: Declara variável JavaScript selectedTab para reutilizar valor ou resultado.
            root.querySelectorAll('[data-commercial-tab]').forEach(tab=> // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
                tab.setAttribute('aria-pressed',tab===button?'true':'false')); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
            root.querySelectorAll('[data-commercial-pane]').forEach(pane=> // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
                pane.hidden=pane.dataset.commercialPane!==selectedTab); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                if(selectedTab==='subscribers')await loadSubscribers(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                else await loadPlans(); // Explicação DevIN: Define ramo alternativo para condição anterior.
            }catch(error){setStatus(error.message,true);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    form.addEventListener('submit',save); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    root.querySelector('[data-commercial-cancel]').addEventListener('click',()=>{ // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        selected=null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        editor.hidden=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        feedback.textContent=''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    loadPlans().catch(error=>setStatus(error.message,true)); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadSubscribers.
