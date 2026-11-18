/**
 * Painel comercial do ADM. Todo texto externo usa textContent para prevenir XSS;
 * alterações passam por sessão, CSRF e validação de permissão no PHP.
 * Nenhuma interação ativa plano, assinante ou pagamento.
 */
(() => {
    'use strict';
    const root=document.querySelector('[data-commercial-admin]');
    if(!root) return;
    const endpoint=root.dataset.endpoint;
    const csrf=root.dataset.csrf;
    const list=root.querySelector('[data-commercial-list]');
    const subscribers=root.querySelector('[data-commercial-subscribers]');
    const editor=root.querySelector('[data-commercial-editor]');
    const form=root.querySelector('[data-commercial-form]');
    const target=root.querySelector('[data-commercial-edit-target]');
    const feedback=root.querySelector('[data-commercial-feedback]');
    const status=root.querySelector('[data-commercial-status]');
    let selected=null;

    function el(tag,value='',className='') {
        const element=document.createElement(tag);
        element.textContent=String(value);
        if(className) element.className=className;
        return element;
    }
    function fmtCents(cents) {
        return cents===null ? 'Preço não definido'
            : new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(cents)/100);
    }
    function labelLimit(value) {
        return value===null ? 'Sem limite específico' : String(value);
    }
    function setStatus(message,error=false) {
        status.textContent=message;
        status.classList.toggle('commercial-error',error);
    }
    async function request(action='plans',payload=null) {
        const url=new URL(endpoint,location.href);
        if(!payload)url.searchParams.set('action',action);
        const options={credentials:'same-origin',cache:'no-store'};
        if(payload) {
            options.method='POST';
            payload.set('csrf_token',csrf);
            options.body=payload;
        }
        const response=await fetch(url.toString(),options);
        const data=await response.json().catch(()=>null);
        if(!response.ok || !data?.success)throw new Error(data?.message||'Serviço comercial indisponível.');
        return data;
    }
    async function loadPlans() {
        const response=await request();
        list.replaceChildren();
        response.rows.forEach(plan=>{
            const card=el('article','','commercial-card');
            card.append(el('small',(plan.audience==='empresa'?'Empresa':'Pessoa')+' · '+plan.tier));
            card.append(el('h2',plan.title));
            card.append(el('strong',fmtCents(plan.monthly_price_cents)));
            card.append(el('p','Vagas ativas: '+labelLimit(plan.max_active_jobs)));
            card.append(el('p','Inscrições/mês: '+labelLimit(plan.max_applications_month)));
            card.append(el('p','Comissão de serviço B2B: '+(Number(plan.placement_fee_bps)/100).toFixed(2)+'%'));
            card.append(el('p','Estoque: '+labelLimit(plan.stock)));
            const state=plan.is_published ? 'Publicado' : 'Rascunho não vendido';
            card.append(el('p','Estado: '+state));
            const edit=el('button','Editar rascunho');
            edit.type='button';
            edit.disabled=Number(plan.is_published)!==0;
            edit.addEventListener('click',()=>openEditor(plan));
            card.append(edit);
            list.append(card);
        });
    }
    function openEditor(plan) {
        selected=plan;
        editor.hidden=false;
        target.textContent=(plan.audience==='empresa'?'Empresa':'Pessoa')+' / '+plan.tier;
        form.elements.title.value=plan.title;
        form.elements.price_brl.value=plan.monthly_price_cents===null?'':(Number(plan.monthly_price_cents)/100).toFixed(2);
        form.elements.max_active_jobs.value=plan.max_active_jobs??'';
        form.elements.max_applications_month.value=plan.max_applications_month??'';
        form.elements.placement_percent.value=(Number(plan.placement_fee_bps)/100).toFixed(2);
        form.elements.stock.value=plan.stock??'';
        feedback.textContent='';
        editor.scrollIntoView({behavior:'smooth',block:'start'});
    }
    function nullableInt(name) {
        const raw=form.elements[name].value.trim();
        if(raw==='')return null;
        const number=Number(raw);
        if(!Number.isSafeInteger(number)||number<0)throw new Error('Informe um limite inteiro não negativo.');
        return number;
    }
    async function save(event) {
        event.preventDefault();
        if(!selected||!form.reportValidity())return;
        const button=form.querySelector('[data-commercial-save]');
        button.disabled=true;
        try {
            const price=Number(form.elements.price_brl.value);
            const percent=Number(form.elements.placement_percent.value);
            if(!Number.isFinite(price)||price<=0||price>100000)throw new Error('Preço de rascunho inválido.');
            if(!Number.isFinite(percent)||percent<0||percent>5)throw new Error('A taxa B2B não pode ultrapassar 5%.');
            const changes={
                title:form.elements.title.value.trim(),
                monthly_price_cents:Math.round(price*100),
                max_active_jobs:nullableInt('max_active_jobs'),
                max_applications_month:nullableInt('max_applications_month'),
                placement_fee_bps:Math.round(percent*100),
                stock:nullableInt('stock')
            };
            if(!window.confirm('Salvar alterações no plano rascunho? A operação será auditada, mas não haverá cobrança.'))return;
            const payload=new FormData();
            payload.set('plan_id',String(selected.id));
            payload.set('revision',String(selected.revision));
            payload.set('changes',JSON.stringify(changes));
            await request('',payload);
            editor.hidden=true;
            selected=null;
            setStatus('Rascunho salvo com registro de auditoria. Nenhum pagamento foi realizado.');
            await loadPlans();
        }catch(error){
            feedback.textContent=error.message;
            feedback.classList.add('commercial-error');
        }finally {button.disabled=false;}
    }
    async function loadSubscribers() {
        const data=await request('subscribers');
        subscribers.replaceChildren();
        if(!data.rows.length){
            subscribers.append(el('p','Nenhuma assinatura registrada. Nenhum plano é vendido automaticamente.'));
            return;
        }
        const table=el('table');
        const head=el('tr');
        for(const name of ['ID','Plano','Tipo','Conta ID','Status','Vencimento'])
            head.append(el('th',name));
        table.append(head);
        data.rows.forEach(sub=>{
            const row=el('tr');
            for(const value of [sub.id,sub.plan,sub.owner_type,sub.owner_id,sub.status,sub.ends_at??'—'])
                row.append(el('td',value));
            table.append(row);
        });
        subscribers.append(table);
    }
    root.querySelectorAll('[data-commercial-tab]').forEach(button=>{
        button.addEventListener('click',async()=>{
            const selectedTab=button.dataset.commercialTab;
            root.querySelectorAll('[data-commercial-tab]').forEach(tab=>
                tab.setAttribute('aria-pressed',tab===button?'true':'false'));
            root.querySelectorAll('[data-commercial-pane]').forEach(pane=>
                pane.hidden=pane.dataset.commercialPane!==selectedTab);
            try {
                if(selectedTab==='subscribers')await loadSubscribers();
                else await loadPlans();
            }catch(error){setStatus(error.message,true);}
        });
    });
    form.addEventListener('submit',save);
    root.querySelector('[data-commercial-cancel]').addEventListener('click',()=>{
        selected=null;
        editor.hidden=true;
        feedback.textContent='';
    });
    loadPlans().catch(error=>setStatus(error.message,true));
})();
