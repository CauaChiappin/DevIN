/* DevIN Empresa: perfil profissional seguro, com ações já existentes de conversa e denúncia. */
(() => { // Detalhe DevIN: Inicializa a integração da lista de talentos.
  'use strict'; // Detalhe DevIN: Aplica a verificação estrita de JavaScript.
  const list = document.querySelector('[data-company-talents]'); // Detalhe DevIN: Localiza o catálogo de candidatos.
  const dialog = document.querySelector('[data-talent-dialog]'); // Detalhe DevIN: Localiza o painel de detalhes.
  if (!list || !dialog) return; // Detalhe DevIN: Não executa fora do Início da empresa.
  const field = (key) => dialog.querySelector('[data-talent-' + key + ']'); // Detalhe DevIN: Localiza campos controlados.
  const photo = field('avatar'); // Detalhe DevIN: Obtém a região da foto de perfil.
  const open = (button) => { // Detalhe DevIN: Apresenta o perfil profissional selecionado.
    const { talentId, talentName, talentEducation, talentCourses, talentExperience, talentLanguages } = button.dataset; // Detalhe DevIN: Lê somente dados do cartão gerado no PHP.
    const id = Number(talentId); // Detalhe DevIN: Converte o identificador em número inteiro.
    if (!Number.isSafeInteger(id) || id < 1) return; // Detalhe DevIN: Rejeita identificadores inválidos.
    field('name').textContent = talentName || 'Candidato'; // Detalhe DevIN: Exibe o nome de forma segura.
    for (const [key, value] of [['education',talentEducation],['courses',talentCourses],['experience',talentExperience],['languages',talentLanguages]]) { // Detalhe DevIN: Preenche os campos profissionais visíveis.
      field(key).textContent = (value || '').trim() || 'Não informado'; // Detalhe DevIN: Evita inserir HTML fornecido por usuários.
    } // Detalhe DevIN: Encerra a renderização dos campos.
    const avatar = button.querySelector('.company-talent-avatar'); // Detalhe DevIN: Recupera a foto previamente validada.
    photo.replaceChildren(...(avatar ? [avatar.cloneNode(true)] : [])); // Detalhe DevIN: Replica a foto ou iniciais sem expor URL privada.
    const target = new URL('empresa.php', window.location.href); // Detalhe DevIN: Prepara navegação na mesma origem.
    target.searchParams.set('pagina', 'mensagens'); // Detalhe DevIN: Reutiliza a central de conversas do site.
    target.searchParams.set('contato_tipo', 'pessoa'); // Detalhe DevIN: Limita o destino aos candidatos.
    target.searchParams.set('contato_id', String(id)); // Detalhe DevIN: Seleciona a pessoa consultada.
    field('chat').href = target.toString(); // Detalhe DevIN: Abre o chat sem enviar mensagem automaticamente.
    target.searchParams.set('reportar', '1'); // Detalhe DevIN: Indica abertura do formulário de denúncia.
    field('report').href = target.toString(); // Detalhe DevIN: Encaminha ao fluxo de denúncias existente.
    if (!dialog.open) dialog.showModal(); // Detalhe DevIN: Exibe opções somente após seleção humana.
  }; // Detalhe DevIN: Finaliza o controlador do painel.
  list.addEventListener('click', (event) => { // Detalhe DevIN: Captura seleção de qualquer cartão.
    const button = event.target.closest('[data-talent-open]'); // Detalhe DevIN: Localiza o cartão escolhido.
    if (button) open(button); // Detalhe DevIN: Abre o perfil correspondente.
  }); // Detalhe DevIN: Finaliza a ação de escolha.
  field('close').addEventListener('click', () => dialog.close()); // Detalhe DevIN: Permite fechar sem alterar os dados.
  dialog.addEventListener('click', (event) => { // Detalhe DevIN: Permite fechar ao tocar fora do painel.
    if (event.target === dialog) dialog.close(); // Detalhe DevIN: Fecha apenas quando o fundo é selecionado.
  }); // Detalhe DevIN: Finaliza o tratamento de toque externo.
})(); // Detalhe DevIN: Finaliza o módulo isolado.
