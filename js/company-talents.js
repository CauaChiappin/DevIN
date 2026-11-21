/* DevIN Empresa: perfil profissional seguro, com ações já existentes de conversa e denúncia. */
(() => { // Explicação DevIN: Inicializa a integração da lista de talentos.
  'use strict'; // Explicação DevIN: Aplica a verificação estrita de JavaScript.
  const list = document.querySelector('[data-company-talents]'); // Explicação DevIN: Localiza o catálogo de candidatos.
  const dialog = document.querySelector('[data-talent-dialog]'); // Explicação DevIN: Localiza o painel de detalhes.
  if (!list || !dialog) return; // Explicação DevIN: Não executa fora do Início da empresa.
  const field = (key) => dialog.querySelector('[data-talent-' + key + ']'); // Explicação DevIN: Localiza campos controlados.
  const photo = field('avatar'); // Explicação DevIN: Obtém a região da foto de perfil.
  const open = (button) => { // Explicação DevIN: Apresenta o perfil profissional selecionado.
    const { talentId, talentName, talentEducation, talentCourses, talentExperience, talentLanguages } = button.dataset; // Explicação DevIN: Lê somente dados do cartão gerado no PHP.
    const id = Number(talentId); // Explicação DevIN: Converte o identificador em número inteiro.
    if (!Number.isSafeInteger(id) || id < 1) return; // Explicação DevIN: Rejeita identificadores inválidos.
    field('name').textContent = talentName || 'Candidato'; // Explicação DevIN: Exibe o nome de forma segura.
    for (const [key, value] of [['education',talentEducation],['courses',talentCourses],['experience',talentExperience],['languages',talentLanguages]]) { // Explicação DevIN: Preenche os campos profissionais visíveis.
      field(key).textContent = (value || '').trim() || 'Não informado'; // Explicação DevIN: Evita inserir HTML fornecido por usuários.
    } // Explicação DevIN: Encerra a renderização dos campos.
    const avatar = button.querySelector('.company-talent-avatar'); // Explicação DevIN: Recupera a foto previamente validada.
    photo.replaceChildren(...(avatar ? [avatar.cloneNode(true)] : [])); // Explicação DevIN: Replica a foto ou iniciais sem expor URL privada.
    const target = new URL('empresa.php', window.location.href); // Explicação DevIN: Prepara navegação na mesma origem.
    target.searchParams.set('pagina', 'mensagens'); // Explicação DevIN: Reutiliza a central de conversas do site.
    target.searchParams.set('contato_tipo', 'pessoa'); // Explicação DevIN: Limita o destino aos candidatos.
    target.searchParams.set('contato_id', String(id)); // Explicação DevIN: Seleciona a pessoa consultada.
    field('chat').href = target.toString(); // Explicação DevIN: Abre o chat sem enviar mensagem automaticamente.
    target.searchParams.set('reportar', '1'); // Explicação DevIN: Indica abertura do formulário de denúncia.
    field('report').href = target.toString(); // Explicação DevIN: Encaminha ao fluxo de denúncias existente.
    if (!dialog.open) dialog.showModal(); // Explicação DevIN: Exibe opções somente após seleção humana.
  }; // Explicação DevIN: Finaliza o controlador do painel.
  list.addEventListener('click', (event) => { // Explicação DevIN: Captura seleção de qualquer cartão.
    const button = event.target.closest('[data-talent-open]'); // Explicação DevIN: Localiza o cartão escolhido.
    if (button) open(button); // Explicação DevIN: Abre o perfil correspondente.
  }); // Explicação DevIN: Finaliza a ação de escolha.
  field('close').addEventListener('click', () => dialog.close()); // Explicação DevIN: Permite fechar sem alterar os dados.
  dialog.addEventListener('click', (event) => { // Explicação DevIN: Permite fechar ao tocar fora do painel.
    if (event.target === dialog) dialog.close(); // Explicação DevIN: Fecha apenas quando o fundo é selecionado.
  }); // Explicação DevIN: Finaliza o tratamento de toque externo.
})(); // Explicação DevIN: Finaliza o módulo isolado.
