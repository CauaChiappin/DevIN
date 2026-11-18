/*
 * REVISÃO DETALHADA DEVIN — js/dashboard-i18n.js
 * Finalidade: exibir traduções e variações de idioma.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const language = ['en', 'es'].includes(document.documentElement.lang) ? document.documentElement.lang : 'pt-BR'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const translations = { // Explicação DevIN: Declara variável JavaScript translations para reutilizar valor ou resultado.
        'pt-BR': { // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Início': 'Início', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas Vagas': 'Minhas Vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'Minhas candidaturas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas': 'Vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens': 'Mensagens', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre nós': 'Sobre nós', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil': 'Perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Meu perfil': 'Meu perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Configurações': 'Configurações', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sair da Conta': 'Sair da Conta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou habilidade...': 'Pesquisar por nome ou habilidade...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtros': 'Filtros', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponíveis': 'Disponíveis', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você ainda não se candidatou a nenhuma vaga.': 'Você ainda não se candidatou a nenhuma vaga.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há vagas publicadas.': 'Ainda não há vagas publicadas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo': 'Adicione um currículo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum resultado encontrado.': 'Nenhum resultado encontrado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga disponível': 'Vaga disponível', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura enviada': 'Candidatura enviada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar esta vaga': 'Denunciar esta vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar vaga': 'Denunciar vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar denúncia': 'Enviar denúncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada': 'Denúncia enviada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada para análise da equipe DevIN.': 'Denúncia enviada para análise da equipe DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar a denúncia.': 'Não foi possível enviar a denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar perfil': 'Denunciar perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil simplificado mostra apenas o nome e o tipo de conta.': 'Este perfil simplificado mostra apenas o nome e o tipo de conta.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Assédio ou comportamento abusivo': 'Assédio ou comportamento abusivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas': 'Informações falsas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que este perfil deve ser analisado.': 'Explique por que este perfil deve ser analisado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil inválido para denúncia.': 'Perfil inválido para denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não é possível denunciar seu próprio perfil.': 'Não é possível denunciar seu próprio perfil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo válido para a denúncia.': 'Selecione um motivo válido para a denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descreva o motivo em até 1.000 caracteres.': 'Descreva o motivo em até 1.000 caracteres.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está mais disponível.': 'Este perfil não está mais disponível.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já denunciou este perfil.': 'Você já denunciou este perfil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível para denúncia.': 'Este perfil não está disponível para denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível.': 'Este perfil não está disponível.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo': 'Motivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Outro': 'Outro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ver perfil': 'Ver perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas': 'Empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoas': 'Pessoas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Categorias de denúncias': 'Categorias de denúncias', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de vagas': 'Reports de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de empresas': 'Reports de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de pessoas': 'Reports de pessoas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de vagas': 'Denúncias de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de empresas': 'Denúncias de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de pessoas': 'Denúncias de pessoas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum perfil foi denunciado.': 'Nenhum perfil foi denunciado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluída': 'Concluída', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando análise': 'Aguardando análise', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluir análise': 'Concluir análise', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia concluída. Ela permanecerá disponível por mais 1 dia.': 'Denúncia concluída. Ela permanecerá disponível por mais 1 dia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviando...': 'Enviando...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificação da plataforma': 'Notificação da plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificações da plataforma não aceitam respostas.': 'Notificações da plataforma não aceitam respostas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A equipe DevIN analisará a denúncia. A empresa responsável e você receberão o resultado na aba de conversas.': 'A equipe DevIN analisará a denúncia. A empresa responsável e você receberão o resultado na aba de conversas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo': 'Selecione um motivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fraude ou golpe': 'Fraude ou golpe', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas ou enganosas': 'Informações falsas ou enganosas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conteúdo inadequado': 'Conteúdo inadequado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga expirada ou indisponível': 'Vaga expirada ou indisponível', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que esta vaga deve ser analisada.': 'Explique por que esta vaga deve ser analisada.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatou-se': 'Candidatou-se', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatar-se': 'Candidatar-se', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já se candidatou a esta vaga': 'Você já se candidatou a esta vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal (R$)': 'Salário mensal (R$)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal': 'Salário mensal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal:': 'Salário mensal:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de candidatos aprovados': 'Limite de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados': 'Limite de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados:': 'Limite de vagas:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de aprovados': 'Limite de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas': 'Limite de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas:': 'Limite de vagas:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes': 'Vagas restantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes:': 'Vagas restantes:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações da vaga': 'Informações da vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da denúncia': 'Detalhes da denúncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência': 'Experiência', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontrou algo errado?': 'Encontrou algo errado?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ajude a manter as vagas confiáveis. Nossa equipe vai analisar seu relato.': 'Ajude a manter as vagas confiáveis. Nossa equipe vai analisar seu relato.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A combinar': 'A combinar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem limite': 'Sem limite', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe 0 no salário para exibir "A combinar".': 'Informe 0 no salário para exibir "A combinar".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de aprovados.': '0 deixa a vaga sem limite de vagas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de vagas.': '0 deixa a vaga sem limite de vagas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descrição': 'Descrição', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem descrição informada.': 'Sem descrição informada.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da vaga denunciada': 'Detalhes da vaga denunciada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagem da denúncia': 'Mensagem da denúncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Análise': 'Análise', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa:': 'Empresa:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada por': 'Denúncia enviada por', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo:': 'Motivo:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando decisão': 'Aguardando decisão', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi removida após análise da denúncia.': 'A vaga foi removida após análise da denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi mantida após análise da denúncia.': 'A vaga foi mantida após análise da denúncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga denunciada?': 'Excluir vaga denunciada?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.': 'A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cancelar': 'Cancelar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga': 'Excluir vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idioma': 'Idioma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Português': 'Português', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Inglês': 'Inglês', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Espanhol': 'Espanhol', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar alterações': 'Salvar alterações', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir conta': 'Excluir conta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fechar': 'Fechar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome': 'Nome', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail': 'E-mail', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da conta': 'E-mail da conta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Celular': 'Celular', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CEP': 'CEP', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar': 'Salvar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contatos': 'Contatos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversa': 'Conversa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversar': 'Conversar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoa': 'Pessoa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa': 'Empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscar pessoas e empresas': 'Buscar pessoas e empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha alguém para conversar': 'Escolha alguém para conversar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escreva uma mensagem...': 'Escreva uma mensagem...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar': 'Enviar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Processando...': 'Processando...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum contato encontrado.': 'Nenhum contato encontrado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum usuário encontrado.': 'Nenhum usuário encontrado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma conversa por aqui. Busque alguém para começar.': 'Nenhuma conversa por aqui. Busque alguém para começar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscando pessoas e empresas...': 'Buscando pessoas e empresas...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Digite pelo menos 2 caracteres para buscar.': 'Digite pelo menos 2 caracteres para buscar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atualização automática': 'Atualização automática', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'Esta conversa ainda não tem mensagens. Envie a primeira!', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta conversa só para você?': 'Excluir esta conversa só para você?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem para você e para o outro participante?': 'Excluir esta mensagem para você e para o outro participante?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem só para você?': 'Excluir esta mensagem só para você?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você apagou esta mensagem': 'Você apagou esta mensagem', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta mensagem foi apagada': 'Esta mensagem foi apagada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ações da mensagem': 'Ações da mensagem', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para mim': 'Excluir para mim', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para todos': 'Excluir para todos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nova mensagem de': 'Nova mensagem de', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagens não lidas.': 'mensagens não lidas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza que deseja sair da sua conta?': 'Tem certeza que deseja sair da sua conta?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza? Esta ação exclui sua conta permanentemente.': 'Tem certeza? Esta ação exclui sua conta permanentemente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        en: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            'Início': 'Home', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas Vagas': 'My Jobs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'My applications', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas': 'Jobs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens': 'Messages', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre nós': 'About us', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil': 'Profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Meu perfil': 'My profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Configurações': 'Settings', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sair da Conta': 'Log out', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou habilidade...': 'Search by name or skill...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou e-mail...': 'Search by name or email...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtros': 'Filters', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponíveis': 'Available', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você ainda não se candidatou a nenhuma vaga.': 'You have not applied to any jobs yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há vagas publicadas.': 'There are no jobs posted yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo': 'Add a resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum resultado encontrado.': 'No results found.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga disponível': 'Job available', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura enviada': 'Application sent', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar esta vaga': 'Report this job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar vaga': 'Report job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar denúncia': 'Submit report', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada': 'Report submitted', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada para análise da equipe DevIN.': 'Your report was sent to the DevIN team for review.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar a denúncia.': 'Could not submit the report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar a confirmação na aba de mensagens.': 'The confirmation could not be sent to your messages.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar o e-mail de confirmação.': 'The confirmation email could not be sent.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar perfil': 'Report profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil simplificado mostra apenas o nome e o tipo de conta.': 'This simplified profile only shows the name and account type.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Assédio ou comportamento abusivo': 'Harassment or abusive behavior', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas': 'False information', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que este perfil deve ser analisado.': 'Explain why this profile should be reviewed.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil inválido para denúncia.': 'Invalid profile to report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não é possível denunciar seu próprio perfil.': 'You cannot report your own profile.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo válido para a denúncia.': 'Select a valid reason for this report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descreva o motivo em até 1.000 caracteres.': 'Describe the reason in up to 1,000 characters.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está mais disponível.': 'This profile is no longer available.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já denunciou este perfil.': 'You have already reported this profile.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível para denúncia.': 'This profile is unavailable for reporting.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível.': 'This profile is unavailable.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo': 'Reason', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Outro': 'Other', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ver perfil': 'View profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas': 'Companies', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoas': 'People', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Categorias de denúncias': 'Report categories', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de vagas': 'Job reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de empresas': 'Company reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de pessoas': 'Person reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de vagas': 'Job reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de empresas': 'Company reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de pessoas': 'Person reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum perfil foi denunciado.': 'No profiles have been reported.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluída': 'Completed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando análise': 'Awaiting review', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluir análise': 'Complete review', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia concluída. Ela permanecerá disponível por mais 1 dia.': 'Report completed. It will remain available for one more day.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviando...': 'Submitting...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificação da plataforma': 'Platform notification', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificações da plataforma não aceitam respostas.': 'Platform notifications cannot be replied to.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A equipe DevIN analisará a denúncia. A empresa responsável e você receberão o resultado na aba de conversas.': 'The DevIN team will review this report. You and the responsible company will receive the result in Messages.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo': 'Select a reason', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fraude ou golpe': 'Fraud or scam', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas ou enganosas': 'False or misleading information', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Discriminação': 'Discrimination', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conteúdo inadequado': 'Inappropriate content', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga expirada ou indisponível': 'Expired or unavailable job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que esta vaga deve ser analisada.': 'Explain why this job should be reviewed.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatou-se': 'Applied', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatar-se': 'Apply', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já se candidatou a esta vaga': 'You have already applied for this job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal (R$)': 'Monthly salary (BRL)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal': 'Monthly salary', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal:': 'Monthly salary:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de candidatos aprovados': 'Job vacancy limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados': 'Job vacancy limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados:': 'Job vacancy limit:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de aprovados': 'Job vacancy limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas': 'Job vacancy limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas:': 'Job vacancy limit:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes': 'Vacancies remaining', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes:': 'Vacancies remaining:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações da vaga': 'Job information', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes': 'Details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes do item selecionado': 'Selected item details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um card': 'Select an item', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'As informações do perfil, vaga ou candidatura aparecerão aqui.': 'Details about the selected profile, job, or application will appear here.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fechar detalhes': 'Close details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da denúncia': 'Report details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência': 'Experience', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontrou algo errado?': 'Something wrong with this job?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ajude a manter as vagas confiáveis. Nossa equipe vai analisar seu relato.': 'Help keep job listings trustworthy. Our team will review your report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A combinar': 'Negotiable', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem limite': 'No limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe 0 no salário para exibir "A combinar".': 'Set salary to 0 to display "Negotiable".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de aprovados.': '0 means there is no vacancy limit for this job.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de vagas.': '0 means there is no vacancy limit for this job.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descrição': 'Description', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem descrição informada.': 'No description provided.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da vaga denunciada': 'Reported job details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagem da denúncia': 'Report message', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Análise': 'Review', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa:': 'Company:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada por': 'Reported by', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo:': 'Reason:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando decisão': 'Awaiting decision', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi removida após análise da denúncia.': 'The job was removed after review of the report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi mantida após análise da denúncia.': 'The job was kept after review of the report.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga denunciada?': 'Delete reported job?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.': 'The job will be removed, and the company and reporter will receive a closing message.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cancelar': 'Cancel', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga': 'Delete job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idioma': 'Language', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Personalize o idioma da interface da sua conta.': 'Personalize your account interface language.', // Explicação DevIN: Declara o tipo da e inicia sua estrutura.
            'Idioma salvo com sucesso.': 'Language saved successfully.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o idioma. Tente novamente.': 'Could not save the language. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha o idioma usado na sua experiência na DevIN.': 'Choose the language used in your DevIN experience.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Essa ação é permanente e não pode ser desfeita.': 'This action is permanent and cannot be undone.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Português': 'Portuguese', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Inglês': 'English', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Espanhol': 'Spanish', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar alterações': 'Save changes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir conta': 'Delete account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fechar': 'Close', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome': 'Name', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail': 'Email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da conta': 'Account email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Celular': 'Mobile phone', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CEP': 'ZIP code', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar': 'Save', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contatos': 'Contacts', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversa': 'Conversation', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversar': 'Chat', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoa': 'Person', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa': 'Company', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscar pessoas e empresas': 'Search people and companies', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha alguém para conversar': 'Choose someone to chat with', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um contato para iniciar ou continuar uma conversa.': 'Select a contact to start or continue a conversation.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando conversa...': 'Loading conversation...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escreva uma mensagem...': 'Write a message...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar': 'Send', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Processando...': 'Processing...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum contato encontrado.': 'No contacts found.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum usuário encontrado.': 'No users found.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma conversa por aqui. Busque alguém para começar.': 'No conversations yet. Search for someone to get started.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscando pessoas e empresas...': 'Searching people and companies...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Digite pelo menos 2 caracteres para buscar.': 'Enter at least 2 characters to search.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atualização automática': 'Live updates', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Hoje': 'Today', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ontem': 'Yesterday', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir conversa': 'Delete conversation', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Opções da conversa': 'Conversation options', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'This conversation has no messages yet. Send the first one!', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta conversa só para você?': 'Delete this conversation just for you?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem para você e para o outro participante?': 'Delete this message for you and the other participant?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem só para você?': 'Delete this message just for you?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você apagou esta mensagem': 'You deleted this message', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta mensagem foi apagada': 'This message was deleted', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ações da mensagem': 'Message actions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzir mensagem': 'Translate message', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzindo...': 'Translating...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tradução': 'Translation', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Falha na tradução': 'Translation failed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzir novamente': 'Translate again', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A extensão cURL do PHP é necessária para traduzir mensagens.': 'The PHP cURL extension is required to translate messages.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível traduzir a mensagem agora. Tente novamente.': 'The message could not be translated right now. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite gratuito diário de tradução atingido. Tente novamente amanhã.': 'The free daily translation limit has been reached. Please try again tomorrow.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para mim': 'Delete for me', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para todos': 'Delete for everyone', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nova mensagem de': 'New message from', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagens não lidas.': 'unread messages.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza que deseja sair da sua conta?': 'Are you sure you want to log out?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza? Esta ação exclui sua conta permanentemente.': 'Are you sure? This will permanently delete your account.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Choose a JPG, PNG, or WEBP image up to 5 MB.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Meu currículo': 'My resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir Meu currículo': 'Open My resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'MEU CURRÍCULO': 'MY RESUME', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo': 'Your resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo para se candidatar': 'Add a resume to apply', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.': 'Review, edit, and download your document. You can replace the uploaded file whenever you want.', // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.': 'You can upload an existing file or create a resume with AI assistance.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há currículo nesta conta': 'There is no resume on this account yet', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.': 'To apply for jobs, upload a PDF/Word file or fill in your details to create a resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolher como continuar': 'Choose how to continue', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar ou melhorar': 'Edit or improve', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.': 'We will use your preferred name, education, courses, experience, and languages to create a clear, professional resume. AI should not invent details; review the text before using it.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.': 'I understand that generating a resume sends my resume data to Google Gemini to create the text. The API key is protected on the server.', // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
            '✦ Gerar com IA': '✦ Generate with AI', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar / Imprimir PDF': 'Download / Print PDF', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar DOCX': 'Download DOCX', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar arquivo original': 'Download original file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Substituir currículo': 'Replace resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar nome e dados': 'Edit name and details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus dados só serão enviados quando você confirmar e solicitar a geração.': 'Your data will only be sent when you confirm and request generation.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar conteúdo do currículo': 'Edit resume content', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo profissional': 'Professional summary', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Formação': 'Education', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência profissional': 'Work experience', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos e certificações': 'Courses and certifications', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.': 'Data is sent to Google Gemini only after you consent. Generation may use the quota of the API key configured by DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia do currículo': 'Resume preview', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo aparecerá aqui': 'Your resume will appear here', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados e clique em': 'Confirm sending your data and click', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerar currículo com IA': 'Generate resume with AI', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Arquivo enviado': 'Uploaded file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir ou baixar arquivo original': 'Open or download original file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerando seu currículo...': 'Generating your resume...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA está organizando seus dados. Isso pode levar alguns segundos.': 'AI is organizing your details. This may take a few seconds.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua sessão expirou. Entre novamente e tente gerar o currículo.': 'Your session has expired. Sign in again and try generating your resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O serviço de geração retornou uma resposta inválida. Tente novamente.': 'The generation service returned an invalid response. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar o currículo.': 'Could not generate the resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo gerado. Revise todas as informações antes de imprimir ou salvar em PDF.': 'Resume generated. Review all information before printing or saving as PDF.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '✦ Tentar gerar novamente': '✦ Try generating again', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '✦ Gerar novamente': '✦ Generate again', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar o currículo. Tente novamente.': 'Could not generate the resume. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Método não permitido.': 'Method not allowed.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sessão inválida. Atualize a página e tente novamente.': 'Invalid session. Refresh the page and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Solicitação inválida. Atualize a página e tente novamente.': 'Invalid request. Refresh the page and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados para gerar o currículo.': 'Confirm sending your data to generate the resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração por IA ainda não está configurada. Adicione a chave Gemini no ambiente do servidor.': 'AI generation is not configured yet. Add the Gemini key to the server environment.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A hospedagem precisa ter a extensão cURL do PHP habilitada para usar a IA.': 'The hosting server must enable the PHP cURL extension to use AI.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você atingiu o limite de 4 gerações por hora. Tente novamente mais tarde.': 'You have reached the limit of 4 generations per hour. Please try again later.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível iniciar a geração agora. Tente novamente.': 'Could not start generation right now. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível carregar seus dados. Tente novamente.': 'Could not load your details. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha seu currículo antes de solicitar a geração.': 'Fill in your resume before requesting generation.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Um dos campos do currículo é longo demais. Edite-o e tente novamente.': 'One of the resume fields is too long. Edit it and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Complete o nome social e a escolaridade antes de gerar o currículo.': 'Complete your preferred name and education before generating your resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A configuração do modelo de IA está inválida.': 'The AI model configuration is invalid.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível iniciar a conexão com a IA.': 'Could not connect to the AI service.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O limite de uso da IA foi atingido. Aguarde e tente novamente.': 'The AI usage limit has been reached. Wait and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar seu currículo agora. Tente novamente mais tarde.': 'Could not generate your resume right now. Please try again later.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA retornou uma resposta inválida. Tente gerar novamente.': 'The AI returned an invalid response. Please try generating again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA retornou um formato inesperado. Tente gerar novamente.': 'The AI returned an unexpected format. Please try generating again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A resposta da IA ficou longa demais. Tente novamente.': 'The AI response was too long. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O currículo foi gerado, mas não pôde ser salvo. Tente novamente.': 'The resume was generated but could not be saved. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Uma seção do currículo excede o limite de 6.000 caracteres.': 'A resume section exceeds the 6,000-character limit.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha ao menos uma seção antes de salvar.': 'Fill in at least one section before saving.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar arquivo pronto': 'Upload a ready file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'PDF ou Word que você já tem': 'A PDF or Word file you already have', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar com ajuda da IA': 'Create with AI help', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha seus dados e a IA monta o texto': 'Fill in your details and the AI writes the text', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione seu currículo': 'Add your resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas veem seu perfil completo': 'Companies see your full profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidate-se às vagas com um clique': 'Apply to jobs with one click', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerenciar currículo': 'Manage resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pronto para candidaturas': 'Ready to apply', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir currículo': 'Delete resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo excluído.': 'Resume deleted.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir seu currículo? Você precisará adicionar outro para se candidatar.': 'Delete your resume? You will need to add another one to apply.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível excluir o currículo agora.': 'Could not delete the resume right now.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo atualizado.': 'Resume updated.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o currículo agora.': 'Could not save the resume right now.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nossa história': 'Our story', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectando talentos ao': 'Connecting tech talent to', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'da tecnologia': 'in technology', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A DevIN nasceu para transformar a forma como desenvolvedores': 'DevIN was created to transform the way developers', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'encontram oportunidades - "simples, rápido e eficiente".': 'find opportunities — "simple, fast, and efficient."', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Visão': 'Vision', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.': 'To be a leading platform connecting tech talent with companies, fostering professional growth and innovation in the digital market.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Missão': 'Mission', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.': 'To connect developers at every level with job opportunities, making recruitment simpler and more efficient.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quem somos': 'Who are', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'nós?': 'we?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.': 'DevIN was created to transform how technology professionals find opportunities. It was developed in the educational environment of Escola Profª Alcina Dantas Feijão.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.': 'Today, DevIN offers a modern platform where companies can post jobs and manage candidates, while users create profiles and look for jobs, internships, and apprenticeships.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Time fundador': 'Founding team', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · caua.lima@scseduca.com.br': 'Co-founder · caua.lima@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · enzo.camargo@scseduca.com.br': 'Co-founder · enzo.camargo@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · joao.sousa2@scseduca.com.br': 'Co-founder · joao.sousa2@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de Cauã Chiappin de Lima': 'Photo of Cauã Chiappin de Lima', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de Enzo Vasconcelos de Camargo': 'Photo of Enzo Vasconcelos de Camargo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de João Vitor da Silva e Sousa': 'Photo of João Vitor da Silva e Sousa' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        es: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Início': 'Inicio', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas Vagas': 'Mis empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'Mis solicitudes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas': 'Vacantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens': 'Mensajes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre nós': 'Sobre nosotros', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil': 'Perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Meu perfil': 'Mi perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Configurações': 'Configuración', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sair da Conta': 'Cerrar sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou habilidade...': 'Buscar por nombre o habilidad...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou e-mail...': 'Buscar por nombre o correo electrónico...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtros': 'Filtros', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum resultado encontrado.': 'No se encontraron resultados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponíveis': 'Disponibles', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você ainda não se candidatou a nenhuma vaga.': 'Aún no te has postulado a ninguna vacante.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há vagas publicadas.': 'Todavía no hay vacantes publicadas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo': 'Añade un currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga disponível': 'Vacante disponible', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura enviada': 'Solicitud enviada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar esta vaga': 'Denunciar este empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar vaga': 'Denunciar empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar denúncia': 'Enviar denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada': 'Denuncia enviada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada para análise da equipe DevIN.': 'La denuncia se envió al equipo de DevIN para su revisión.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar a denúncia.': 'No se pudo enviar la denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar a confirmação na aba de mensagens.': 'No se pudo enviar la confirmación a tus mensajes.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível enviar o e-mail de confirmação.': 'No se pudo enviar el correo de confirmación.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciar perfil': 'Denunciar perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil simplificado mostra apenas o nome e o tipo de conta.': 'Este perfil simplificado solo muestra el nombre y el tipo de cuenta.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Assédio ou comportamento abusivo': 'Acoso o comportamiento abusivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas': 'Información falsa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que este perfil deve ser analisado.': 'Explica por qué debe revisarse este perfil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil inválido para denúncia.': 'Perfil no válido para denunciar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não é possível denunciar seu próprio perfil.': 'No puedes denunciar tu propio perfil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo válido para a denúncia.': 'Selecciona un motivo válido para esta denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descreva o motivo em até 1.000 caracteres.': 'Describe el motivo en hasta 1.000 caracteres.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está mais disponível.': 'Este perfil ya no está disponible.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já denunciou este perfil.': 'Ya has denunciado este perfil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível para denúncia.': 'Este perfil no está disponible para denunciarlo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este perfil não está disponível.': 'Este perfil no está disponible.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo': 'Motivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Outro': 'Otro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ver perfil': 'Ver perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas': 'Empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoas': 'Personas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Categorias de denúncias': 'Categorías de denuncias', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de vagas': 'Denuncias de empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de empresas': 'Denuncias de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de pessoas': 'Denuncias de personas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de vagas': 'Denuncias de empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de empresas': 'Denuncias de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias de pessoas': 'Denuncias de personas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum perfil foi denunciado.': 'No se han denunciado perfiles.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluída': 'Completada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando análise': 'Pendiente de revisión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Concluir análise': 'Completar revisión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia concluída. Ela permanecerá disponível por mais 1 dia.': 'Denuncia completada. Permanecerá disponible un día más.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviando...': 'Enviando...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificação da plataforma': 'Notificación de la plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Notificações da plataforma não aceitam respostas.': 'No se puede responder a las notificaciones de la plataforma.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A equipe DevIN analisará a denúncia. A empresa responsável e você receberão o resultado na aba de conversas.': 'El equipo de DevIN revisará la denuncia. Tú y la empresa recibirán el resultado en Mensajes.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um motivo': 'Selecciona un motivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fraude ou golpe': 'Fraude o estafa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações falsas ou enganosas': 'Información falsa o engañosa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Discriminação': 'Discriminación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conteúdo inadequado': 'Contenido inapropiado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga expirada ou indisponível': 'Empleo vencido o no disponible', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explique por que esta vaga deve ser analisada.': 'Explica por qué debe revisarse este empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatou-se': 'Te postulaste', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatar-se': 'Postularse', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você já se candidatou a esta vaga': 'Ya te postulaste a esta vacante', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal (R$)': 'Salario mensual (BRL)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal': 'Salario mensual', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salário mensal:': 'Salario mensual:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de candidatos aprovados': 'Límite de vacantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados': 'Límite de vacantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Máximo de aprovados:': 'Límite de vacantes:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de aprovados': 'Límite de vacantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas': 'Límite de vacantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite de vagas:': 'Límite de vacantes:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes': 'Vacantes restantes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas restantes:': 'Vacantes restantes:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informações da vaga': 'Información de la vacante', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes': 'Detalles', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes do item selecionado': 'Detalles del elemento seleccionado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um card': 'Selecciona un elemento', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'As informações do perfil, vaga ou candidatura aparecerão aqui.': 'Aquí aparecerá la información del perfil, la vacante o la candidatura seleccionada.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fechar detalhes': 'Cerrar detalles', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da denúncia': 'Detalles de la denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência': 'Experiencia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontrou algo errado?': '¿Encontraste algo extraño?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ajude a manter as vagas confiáveis. Nossa equipe vai analisar seu relato.': 'Ayuda a mantener las ofertas confiables. Nuestro equipo revisará tu denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A combinar': 'A convenir', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem limite': 'Sin límite', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe 0 no salário para exibir "A combinar".': 'Indica 0 para mostrar "A convenir".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de aprovados.': '0 significa que no hay límite de vacantes para este empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '0 deixa a vaga sem limite de vagas.': '0 significa que no hay límite de vacantes para este empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descrição': 'Descripción', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem descrição informada.': 'Sin descripción.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da vaga denunciada': 'Detalles del empleo denunciado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagem da denúncia': 'Mensaje de la denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Análise': 'Análisis', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa:': 'Empresa:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia enviada por': 'Denunciado por', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo:': 'Motivo:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aguardando decisão': 'Pendiente de decisión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi removida após análise da denúncia.': 'El empleo se eliminó tras revisar la denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga foi mantida após análise da denúncia.': 'El empleo se mantuvo tras revisar la denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga denunciada?': '¿Eliminar el empleo denunciado?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.': 'El empleo se eliminará y la empresa y quien denunció recibirán un mensaje de cierre.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cancelar': 'Cancelar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir vaga': 'Eliminar empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idioma': 'Idioma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Personalize o idioma da interface da sua conta.': 'Personaliza el idioma de la interfaz de tu cuenta.', // Explicação DevIN: Declara o tipo da e inicia sua estrutura.
            'Idioma salvo com sucesso.': 'Idioma guardado correctamente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o idioma. Tente novamente.': 'No se pudo guardar el idioma. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha o idioma usado na sua experiência na DevIN.': 'Elige el idioma para tu experiencia en DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Essa ação é permanente e não pode ser desfeita.': 'Esta acción es permanente y no se puede deshacer.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Português': 'Portugués', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Inglês': 'Inglés', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Espanhol': 'Español', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar alterações': 'Guardar cambios', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir conta': 'Eliminar cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fechar': 'Cerrar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome': 'Nombre', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail': 'Correo electrónico', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da conta': 'Correo electrónico de la cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Celular': 'Teléfono móvil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CEP': 'Código postal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar': 'Guardar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contatos': 'Contactos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversa': 'Conversación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversar': 'Chatear', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoa': 'Persona', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa': 'Empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscar pessoas e empresas': 'Buscar personas y empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha alguém para conversar': 'Elige a alguien para conversar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um contato para iniciar ou continuar uma conversa.': 'Selecciona un contacto para iniciar o continuar una conversación.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando conversa...': 'Cargando conversación...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escreva uma mensagem...': 'Escribe un mensaje...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar': 'Enviar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Processando...': 'Procesando...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum contato encontrado.': 'No se encontraron contactos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum usuário encontrado.': 'No se encontraron usuarios.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma conversa por aqui. Busque alguém para começar.': 'Aún no hay conversaciones. Busca a alguien para empezar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Buscando pessoas e empresas...': 'Buscando personas y empresas...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Digite pelo menos 2 caracteres para buscar.': 'Escribe al menos 2 caracteres para buscar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atualização automática': 'Actualización automática', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Hoje': 'Hoy', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ontem': 'Ayer', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir conversa': 'Eliminar conversación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Opções da conversa': 'Opciones de conversación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'Esta conversación aún no tiene mensajes. ¡Envía el primero!', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta conversa só para você?': '¿Eliminar esta conversación solo para ti?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem para você e para o outro participante?': '¿Eliminar este mensaje para ti y para la otra persona?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta mensagem só para você?': '¿Eliminar este mensaje solo para ti?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você apagou esta mensagem': 'Eliminaste este mensaje', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta mensagem foi apagada': 'Este mensaje fue eliminado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ações da mensagem': 'Acciones del mensaje', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzir mensagem': 'Traducir mensaje', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzindo...': 'Traduciendo...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tradução': 'Traducción', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Falha na tradução': 'Error de traducción', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Traduzir novamente': 'Traducir de nuevo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A extensão cURL do PHP é necessária para traduzir mensagens.': 'Se necesita la extensión cURL de PHP para traducir mensajes.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível traduzir a mensagem agora. Tente novamente.': 'No se pudo traducir el mensaje ahora. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Limite gratuito diário de tradução atingido. Tente novamente amanhã.': 'Se alcanzó el límite diario gratuito de traducción. Inténtalo de nuevo mañana.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para mim': 'Eliminar para mí', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir para todos': 'Eliminar para todos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nova mensagem de': 'Nuevo mensaje de', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagens não lidas.': 'mensajes sin leer.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza que deseja sair da sua conta?': '¿Seguro que quieres cerrar sesión?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza? Esta ação exclui sua conta permanentemente.': '¿Estás seguro? Esta acción eliminará tu cuenta permanentemente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Elige una imagen JPG, PNG o WEBP de hasta 5 MB.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Meu currículo': 'Mi currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir Meu currículo': 'Abrir Mi currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'MEU CURRÍCULO': 'MI CURRÍCULUM', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo': 'Tu currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo para se candidatar': 'Añade un currículum para postularte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.': 'Revisa, edita y descarga tu documento. Puedes reemplazar el archivo enviado cuando quieras.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.': 'Puedes subir un archivo existente o crear un currículum con ayuda de la IA.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há currículo nesta conta': 'Esta cuenta aún no tiene un currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.': 'Para postularte a empleos, sube un archivo PDF/Word o completa tus datos para crear un currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolher como continuar': 'Elegir cómo continuar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar ou melhorar': 'Editar o mejorar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.': 'Usaremos tu nombre preferido, estudios, cursos, experiencia e idiomas para crear un currículum claro y profesional. La IA no debe inventar datos; revisa el texto antes de usarlo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.': 'Entiendo que, al generar el currículum, mis datos se enviarán a Google Gemini para crear el texto. La clave de API está protegida en el servidor.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '✦ Gerar com IA': '✦ Generar con IA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar / Imprimir PDF': 'Descargar / Imprimir PDF', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar DOCX': 'Descargar DOCX', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar arquivo original': 'Descargar archivo original', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Substituir currículo': 'Reemplazar currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar nome e dados': 'Editar nombre y datos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus dados só serão enviados quando você confirmar e solicitar a geração.': 'Tus datos solo se enviarán cuando confirmes y solicites la generación.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar conteúdo do currículo': 'Editar contenido del currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo profissional': 'Resumen profesional', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Formação': 'Educación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência profissional': 'Experiencia profesional', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos e certificações': 'Cursos y certificaciones', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.': 'Los datos solo se envían a Google Gemini después de tu consentimiento. La generación puede consumir la cuota de la clave configurada por DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia do currículo': 'Vista previa del currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo aparecerá aqui': 'Tu currículum aparecerá aquí', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados e clique em': 'Confirma el envío de tus datos y haz clic en', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerar currículo com IA': 'Generar currículum con IA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Arquivo enviado': 'Archivo subido', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir ou baixar arquivo original': 'Abrir o descargar el archivo original', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerando seu currículo...': 'Generando tu currículum...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA está organizando seus dados. Isso pode levar alguns segundos.': 'La IA está organizando tus datos. Esto puede tardar unos segundos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua sessão expirou. Entre novamente e tente gerar o currículo.': 'Tu sesión ha caducado. Inicia sesión de nuevo e intenta generar tu currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O serviço de geração retornou uma resposta inválida. Tente novamente.': 'El servicio de generación devolvió una respuesta no válida. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar o currículo.': 'No se pudo generar el currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo gerado. Revise todas as informações antes de imprimir ou salvar em PDF.': 'Currículum generado. Revisa toda la información antes de imprimirlo o guardarlo como PDF.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '✦ Tentar gerar novamente': '✦ Intentar generar de nuevo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '✦ Gerar novamente': '✦ Generar de nuevo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar o currículo. Tente novamente.': 'No se pudo generar el currículum. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Método não permitido.': 'Método no permitido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sessão inválida. Atualize a página e tente novamente.': 'Sesión no válida. Actualiza la página e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Solicitação inválida. Atualize a página e tente novamente.': 'Solicitud no válida. Actualiza la página e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados para gerar o currículo.': 'Confirma el envío de tus datos para generar el currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração por IA ainda não está configurada. Adicione a chave Gemini no ambiente do servidor.': 'La generación con IA aún no está configurada. Añade la clave de Gemini al entorno del servidor.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A hospedagem precisa ter a extensão cURL do PHP habilitada para usar a IA.': 'El servidor debe tener habilitada la extensión cURL de PHP para usar la IA.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você atingiu o limite de 4 gerações por hora. Tente novamente mais tarde.': 'Has alcanzado el límite de 4 generaciones por hora. Inténtalo más tarde.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível iniciar a geração agora. Tente novamente.': 'No se pudo iniciar la generación. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível carregar seus dados. Tente novamente.': 'No se pudieron cargar tus datos. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha seu currículo antes de solicitar a geração.': 'Completa tu currículum antes de solicitar la generación.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Um dos campos do currículo é longo demais. Edite-o e tente novamente.': 'Uno de los campos del currículum es demasiado largo. Edítalo e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Complete o nome social e a escolaridade antes de gerar o currículo.': 'Completa tu nombre preferido y tus estudios antes de generar el currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A configuração do modelo de IA está inválida.': 'La configuración del modelo de IA no es válida.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível iniciar a conexão com a IA.': 'No se pudo conectar con el servicio de IA.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O limite de uso da IA foi atingido. Aguarde e tente novamente.': 'Se alcanzó el límite de uso de la IA. Espera e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível gerar seu currículo agora. Tente novamente mais tarde.': 'No se pudo generar tu currículum ahora. Inténtalo más tarde.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA retornou uma resposta inválida. Tente gerar novamente.': 'La IA devolvió una respuesta no válida. Intenta generar el currículum de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A IA retornou um formato inesperado. Tente gerar novamente.': 'La IA devolvió un formato inesperado. Intenta generar el currículum de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A resposta da IA ficou longa demais. Tente novamente.': 'La respuesta de la IA fue demasiado larga. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'O currículo foi gerado, mas não pôde ser salvo. Tente novamente.': 'Se generó el currículum, pero no se pudo guardar. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Uma seção do currículo excede o limite de 6.000 caracteres.': 'Una sección del currículum supera el límite de 6.000 caracteres.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha ao menos uma seção antes de salvar.': 'Completa al menos una sección antes de guardar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviar arquivo pronto': 'Subir un archivo listo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'PDF ou Word que você já tem': 'Un PDF o Word que ya tienes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar com ajuda da IA': 'Crear con ayuda de la IA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha seus dados e a IA monta o texto': 'Completa tus datos y la IA arma el texto', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione seu currículo': 'Agrega tu currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas veem seu perfil completo': 'Las empresas ven tu perfil completo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidate-se às vagas com um clique': 'Postúlate a vacantes con un clic', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerenciar currículo': 'Gestionar currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pronto para candidaturas': 'Listo para postulaciones', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir currículo': 'Eliminar currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo excluído.': 'Currículum eliminado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir seu currículo? Você precisará adicionar outro para se candidatar.': '¿Eliminar tu currículum? Deberás añadir otro para postularte.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível excluir o currículo agora.': 'No se pudo eliminar el currículum ahora.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo atualizado.': 'Currículum actualizado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o currículo agora.': 'No se pudo guardar el currículum ahora.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nossa história': 'Nuestra historia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectando talentos ao': 'Conectando talentos con el', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'da tecnologia': 'de la tecnología', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A DevIN nasceu para transformar a forma como desenvolvedores': 'DevIN nació para transformar la forma en que los desarrolladores', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'encontram oportunidades - "simples, rápido e eficiente".': 'encuentran oportunidades: "simple, rápido y eficiente".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Visão': 'Visión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.': 'Ser un referente en la conexión entre talentos tecnológicos y empresas, promoviendo el crecimiento profesional y la innovación en el mercado digital.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Missão': 'Misión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.': 'Conectar a desarrolladores de todos los niveles con oportunidades laborales, haciendo que el proceso de selección sea más sencillo y eficiente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quem somos': 'Sobre', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'nós?': 'nosotros', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.': 'DevIN nació con el propósito de transformar la forma en que los profesionales de tecnología encuentran oportunidades. Fue desarrollada en el entorno educativo de la Escola Profª Alcina Dantas Feijão.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.': 'Hoy, DevIN ofrece un entorno moderno donde las empresas pueden publicar ofertas y gestionar candidatos, mientras los usuarios crean perfiles y buscan empleos, prácticas y programas de aprendizaje.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Time fundador': 'Equipo fundador', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · caua.lima@scseduca.com.br': 'Cofundador · caua.lima@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · enzo.camargo@scseduca.com.br': 'Cofundador · enzo.camargo@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cofundador · joao.sousa2@scseduca.com.br': 'Cofundador · joao.sousa2@scseduca.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de Cauã Chiappin de Lima': 'Foto de Cauã Chiappin de Lima', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de Enzo Vasconcelos de Camargo': 'Foto de Enzo Vasconcelos de Camargo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de João Vitor da Silva e Sousa': 'Foto de João Vitor da Silva e Sousa' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const additionalTranslations = { // Explicação DevIN: Declara variável JavaScript additionalTranslations para reutilizar valor ou resultado.
        en: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Idioma': 'Language', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir menu': 'Open menu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conheça o DevIN': 'Discover DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Etapas': 'How it works', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contato': 'Contact', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entrar': 'Log in', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar-se': 'Sign up', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Comece novo.': 'Start fresh.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Comece o': 'Start your', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'universo': 'universe', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'futuro': 'future', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mundo': 'world', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'avanço': 'progress', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mercado': 'job market', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'amanhã': 'tomorrow', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontre vagas, estágios e oportunidades de jovem aprendiz em tecnologia.': 'Find technology jobs, internships, and youth apprenticeship opportunities.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar meu currículo': 'Create my resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'É uma empresa? Anuncie vagas': 'Are you a company? Post jobs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoa programando': 'Person programming', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo na DevIN': 'Resume on DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Desejado': 'Desired', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Somos um site especializado em vagas de emprego, estágios e jovem aprendiz. Ajudamos empresas a contratar pessoas qualificadas ou que desejam aprender.': 'We specialize in jobs, internships, and youth apprenticeships. We help companies hire qualified people and those eager to learn.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo': 'Resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Crie o seu currículo na DevIN e use para': 'Create your resume on DevIN and use it to', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'candidatar-se para vagas de empresas': 'apply for company job openings', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Feed de vagas': 'Job feed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore um feed de vagas, com inúmeras': 'Explore a job feed with many', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'oportunidades de trabalho, como': 'opportunities, including', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'oportunidades de trabalho, como jovem': 'opportunities such as youth', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'aprendiz, estágios e empregos.': 'apprenticeships, internships, and jobs.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'jovem': 'youth', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'aprendiz, estágios e empregos.': 'apprenticeships, internships, and jobs.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN em 3 etapas': 'DevIN in 3 steps', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Com poucos passos você consegue seu': 'Find your next job in just a few', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'trabalho/sua vaga': 'simple steps', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Crie sua': 'Create your', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'conta': 'account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatar-se': 'Apply', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aqui seu currículo já sai do jeitinho que as grandes empresas querem': 'Your resume will be formatted just the way major companies expect', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas que contratam estagiários e jovens aprendizes em tecnologia': 'Companies hiring technology interns and apprentices', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enfrentando dúvidas': 'Having questions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ou problemas?': 'or problems?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entre em contato conosco': 'Contact us', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Email suporte': 'Email support', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Central de ajuda': 'Help center', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dúvidas mais frequentes': 'Frequently asked questions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como crio minha conta e meu currículo?': 'How do I create my account and resume?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione “Cadastrar-se”, informe seus dados e, após entrar, complete seu currículo para se candidatar às oportunidades.': 'Select “Sign up,” enter your details, then complete your resume after logging in to apply for opportunities.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como me candidato a uma vaga?': 'How do I apply for a job?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Depois de fazer login e concluir seu currículo, acesse o feed de vagas e escolha a oportunidade que combina com você.': 'After logging in and completing your resume, browse the job feed and choose an opportunity that suits you.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como cadastro minha empresa?': 'How do I register my company?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Na tela de cadastro, selecione a opção “Empresa”, preencha os dados solicitados e finalize o registro.': 'On the sign-up page, select “Company,” enter the requested details, and complete registration.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como publico uma vaga e acompanho as candidaturas?': 'How do I post a job and track applications?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Após fazer login na área da empresa, crie a vaga e acompanhe os perfis dos candidatos na área de candidatos.': 'After logging in to the company area, create a job and review candidate profiles in the candidates section.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectamos estudantes a vagas, estágios e oportunidades de jovem aprendiz.': 'We connect students with jobs, internships, and apprenticeship opportunities.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Repositório no GitHub': 'GitHub repository', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escola Profª Alcina Dantas Feijão': 'Prof. Alcina Dantas Feijão School', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Início': 'Home', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre o DevIN': 'About DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar-se': 'Sign up', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como funciona': 'How it works', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo ideal': 'The ideal resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Email suporte': 'Email support', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tel: (11) 96551-2472': 'Phone: (11) 96551-2472', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'SAC': 'Customer support', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.': 'DevIN | Prof. Alcina Dantas Feijão School | © DevIN 2026. All rights reserved.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN Arcade': 'DevIN Arcade', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Navegação dos jogos': 'Game navigation', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogos públicos': 'Public games', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sair': 'Log out', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ÁREA SECRETA LIBERADA': 'SECRET AREA UNLOCKED', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogos DevIN': 'DevIN Games', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha um jogo. Seu recorde, maior fase alcançada e última atividade ficam salvos na sua conta.': 'Choose a game. Your high score, highest level, and latest activity are saved to your account.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Lista de jogos': 'Game list', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore fases, colete cristais e evite os perseguidores.': 'Explore levels, collect crystals, and avoid the chasers.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cresça, avance de fase e não bata nas bordas ou em si mesmo.': 'Grow, advance through levels, and avoid hitting the walls or yourself.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Complete linhas para pontuar e aumentar o nível.': 'Complete lines to score and level up.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Rebata a bola, limpe os blocos e alcance novas fases.': 'Bounce the ball, clear the blocks, and reach new levels.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontre os pares com o menor número de tentativas.': 'Find all pairs in as few attempts as possible.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Corra, pule obstáculos e avance por trechos cada vez mais rápidos.': 'Run, jump over obstacles, and advance through increasingly faster sections.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Área de jogo': 'Game area', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um jogo': 'Select a game', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Os controles e a pontuação aparecerão aqui.': 'Controls and score will appear here.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reiniciar': 'Restart', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tela do jogo': 'Game screen', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha um jogo para começar.': 'Choose a game to begin.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ANTES DE COMEÇAR': 'BEFORE YOU START', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Iniciar jogo': 'Start game', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'FIM DE JOGO': 'GAME OVER', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Partida encerrada': 'Game over', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Classificação — 10 melhores': 'Leaderboard — Top 10', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando classificação…': 'Loading leaderboard…', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogar novamente': 'Play again', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pontos:': 'Score:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fase:': 'Level:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recorde:': 'High score:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Painel de progresso': 'Progress panel', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados de jogo vinculados às contas. A remoção apaga somente o registro selecionado.': 'Game data linked to accounts. Removing only deletes the selected record.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando registros…': 'Loading records…', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Área de jogos': 'DevIN | Games', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectado como': 'Signed in as', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: setas ou WASD para mover.': 'Controls: arrow keys or WASD to move.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encaixe as peças e complete linhas para pontuar e aumentar o nível.': 'Fit the pieces and complete lines to score and level up.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: ← → move, ↑ gira, ↓ acelera e Espaço derruba a peça.': 'Controls: ← → move, ↑ rotate, ↓ speed up, and Space drops the piece.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: ← →, A/D ou mouse para mover a plataforma.': 'Controls: ← →, A/D, or mouse to move the paddle.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: clique nas cartas para revelar os pares.': 'Controls: click the cards to reveal pairs.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: Espaço, ↑ ou clique para pular.': 'Controls: Space, ↑, or click to jump.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Leia as instruções e inicie quando estiver pronto.': 'Read the instructions and start when you are ready.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Progresso salvo.': 'Progress saved.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há pontuações para este jogo.': 'There are no scores for this game yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há progresso salvo.': 'There is no saved progress yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o progresso.': 'Could not save progress.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A área de jogos precisa ser liberada pelo dashboard.': 'The games area must be unlocked from the dashboard.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Método não permitido.': 'Method not allowed.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogo inválido.': 'Invalid game.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados de progresso inválidos.': 'Invalid progress data.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Inicie uma partida válida antes de salvar o progresso.': 'Start a valid game before saving progress.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pontuação ou fase incompatível com o tempo de partida.': 'Score or level is not compatible with the game duration.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Registro inválido.': 'Invalid record.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ação não permitida.': 'Action not allowed.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível processar a solicitação do arcade.': 'Could not process the arcade request.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conta': 'Account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil': 'Profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogo': 'Game', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recorde': 'High score', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Maior fase': 'Highest level', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Última atividade': 'Last activity', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ação': 'Action', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Remover': 'Remove', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Labirinto DevIN': 'DevIN Maze', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore o labirinto, colete cristais e evite os perseguidores. Cristais especiais deixam os perseguidores vulneráveis.': 'Explore the maze, collect crystals, and avoid the chasers. Special crystals make the chasers vulnerable.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Colete orbes para crescer e avançar de fase sem bater nas bordas ou em si mesmo.': 'Collect orbs to grow and advance without hitting the walls or yourself.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Blocos': 'Blocks', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quebra-blocos': 'Breakout', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Memória': 'Memory', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salto de Ritmo': 'Rhythm Jump', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatos': 'Candidates', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas': 'Jobs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'My applications', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas': 'Companies', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Posts das empresas': 'Company posts', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de vagas': 'Job reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias recebidas': 'Received reports', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponíveis': 'Available', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum registro disponível.': 'No records available.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum candidato cadastrado.': 'No candidates registered.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga publicada.': 'No jobs posted.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga foi denunciada.': 'No jobs have been reported.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar perfil': 'Edit profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicionar currículo': 'Add a resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo': 'Your resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo para se candidatar': 'Add a resume to apply', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'MEU CURRÍCULO': 'MY RESUME', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.': 'Review, edit, and download your document. You can replace the uploaded file at any time.', // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.': 'You can upload an existing file or create a resume with AI assistance.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há currículo nesta conta': 'There is no resume on this account yet', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.': 'To apply for jobs, upload a PDF/Word file or fill in your details to create a resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolher como continuar': 'Choose how to continue', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar ou melhorar': 'Edit or improve', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.': 'We will use your preferred name, education, courses, experience, and languages to create a clear, professional resume. AI should not invent details; review the text before using it.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.': 'I understand that generating a resume sends my resume data to Google Gemini. The API key is protected on the server.', // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
            'Gerar com IA': 'Generate with AI', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar / Imprimir PDF': 'Download / Print PDF', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar DOCX': 'Download DOCX', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar arquivo original': 'Download original file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Substituir currículo': 'Replace resume', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar nome e dados': 'Edit name and details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus dados só serão enviados quando você confirmar e solicitar a geração.': 'Your data will only be sent after you confirm and request generation.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar conteúdo do currículo': 'Edit resume content', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo profissional': 'Professional summary', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Formação': 'Education', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência profissional': 'Work experience', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos e certificações': 'Courses and certifications', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idiomas': 'Languages', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.': 'Data is sent to Google Gemini only after consent, and generation may use the quota of the API key configured by DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo aparecerá aqui': 'Your resume will appear here', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados e clique em': 'Confirm sending your data and click', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerar currículo com IA': 'Generate resume with AI', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Arquivo enviado': 'Uploaded file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir ou baixar arquivo original': 'Open or download original file', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia do currículo enviado': 'Uploaded resume preview', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicar nova vaga': 'Post a new job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Título da vaga': 'Job title', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ex.: Desenvolvedor PHP': 'E.g., PHP Developer', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descreva as atividades e requisitos da vaga.': 'Describe the job duties and requirements.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe 0 no salário para exibir "A combinar".': 'Enter 0 for salary to display "Negotiable".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicar vaga': 'Post job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não encerrar automaticamente': 'Do not close automatically', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atingir o limite de aprovados': 'Reach the approved-candidate limit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Chegar à data e hora de encerramento': 'Reach the closing date and time', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atingir o limite ou a data, o que vier primeiro': 'Reach the limit or closing time, whichever comes first', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Data e hora de encerramento': 'Closing date and time', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'No modo “qualquer”, a vaga encerra quando atingir o limite de aprovados ou o prazo.': 'In “either” mode, the job closes when it reaches the approved-candidate limit or the deadline.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga encerrada': 'Job closed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidaturas até:': 'Applications until:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encerramento::': 'Closing::', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Data de encerramento::': 'Closing date::', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você ainda não publicou nenhuma vaga.': 'You have not posted any jobs yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar': 'Save', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta vaga e as candidaturas dela?': 'Delete this job and its applications?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Título': 'Title', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar': 'Edit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou e-mail': 'Search by name or email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome ou e-mail': 'Name or email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolaridade': 'Education', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos os níveis': 'All education levels', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos': 'Courses', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por curso': 'Filter by course', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos os cursos': 'All courses', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Habilidades': 'Skills', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por habilidade': 'Filter by skill', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todas as habilidades': 'All skills', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência': 'Experience', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por experiência': 'Filter by experience', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todas as experiências': 'All experience levels', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum candidato corresponde aos filtros.': 'No candidates match these filters.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há candidatos para esta vaga.': 'There are no candidates for this job yet.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovar candidato': 'Approve candidate', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato': 'Candidate', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura': 'Application', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura recebida': 'Application received', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovar': 'Approve', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusar': 'Reject', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusado': 'Rejected', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovado': 'Approved', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pendente': 'Pending', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga publicada': 'Job posted', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicação': 'Posting', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contato': 'Contact', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestões ou pedir suporte.': 'Contact the DevIN team to learn more about the project, share suggestions, or request support.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explicando tudo sobre a vaga selecionada': 'Details about the selected job', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Use este espaço para visualizar detalhes da vaga, pessoa ou post escolhido no painel.': 'Use this space to view details about the selected job, person, or post.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga em que o candidato se inscreveu': 'Job the candidate applied for', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma vaga para ver sua descrição completa aqui.': 'Select a job to see its full description here.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Alterar foto de perfil': 'Change profile photo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de perfil': 'Profile photo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome da empresa': 'Company name', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da conta': 'Account email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Personalize o idioma da interface da sua conta.': 'Customize your account interface language.', // Explicação DevIN: Declara o tipo da e inicia sua estrutura.
            'Escolha o idioma usado na sua experiência na DevIN.': 'Choose the language for your DevIN experience.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Essa ação é permanente e não pode ser desfeita.': 'This action is permanent and cannot be undone.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um arquivo PDF, DOC ou DOCX.': 'Select a PDF, DOC, or DOCX file.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração por IA ainda não está configurada. Adicione a chave Gemini no ambiente do servidor.': 'AI generation is not configured yet. Add the Gemini key to the server environment.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Solicitação inválida. Atualize a página e tente novamente.': 'Invalid request. Refresh the page and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados para gerar o currículo.': 'Confirm sending your data to generate the resume.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você atingiu o limite de 4 gerações por hora. Tente novamente mais tarde.': 'You have reached the limit of 4 generations per hour. Please try again later.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idioma salvo com sucesso.': 'Language saved successfully.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o idioma. Tente novamente.': 'Could not save the language. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Administrativo': 'DevIN | Admin Dashboard', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Empresa': 'DevIN | Company Dashboard', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Candidato': 'DevIN | Candidate Dashboard', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua mensagem': 'Your message', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens privadas entre usuários da DevIN': 'Private messages between DevIN users', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Voltar para contatos': 'Back to contacts', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando contatos...': 'Loading contacts...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga mantida': 'Job kept', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga excluída': 'Job deleted', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciante': 'Reporter', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil denunciado': 'Reported profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Support DevIN': 'Support DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia #': 'Report #', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga #': 'Job #', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviada em': 'Submitted at', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo': 'Reason', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Análise': 'Review', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagem da denúncia': 'Report details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Relato da denúncia': 'Report statement', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados da denúncia': 'Report information', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail do perfil': 'Profile email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail do denunciante': 'Reporter email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da empresa': 'Company email', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A denúncia foi aceita. O perfil denunciado foi removido e o denunciante foi notificado.': 'Report accepted. The reported profile was removed, and the reporter was notified.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A denúncia foi recusada. O perfil foi mantido e o denunciante foi notificado.': 'Report declined. The profile was kept, and the reporter was notified.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Decisão registrada, mas não foi possível enviar a mensagem ao denunciante.': 'Decision recorded, but the reporter could not be notified.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes do registro': 'Record details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informacoes da plataforma': 'Platform information', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informacao disponivel': 'Information available', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Registro selecionado': 'Selected record', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponivel na plataforma': 'Available on the platform', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem detalhes disponíveis.': 'No details available.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia da foto de perfil': 'Profile photo preview', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato DevIN': 'DevIN candidate', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir registro': 'Delete record', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir Registro': 'Delete record', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir perfil': 'Delete profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir empresa': 'Delete company', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir este candidato permanentemente?': 'Permanently delete this candidate?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta empresa permanentemente?': 'Permanently delete this company?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta vaga?': 'Delete this job?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicação::Registro da vaga na plataforma': 'Posting::Job record on the platform', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não se encaixa': 'Not a match', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura para': 'Application for', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato para': 'Candidate for', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confira os requisitos e envie sua candidatura': 'Review the requirements and submit your application', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovadas': 'Approved', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusadas': 'Rejected', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Em análise': 'Under review', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa cadastrada': 'Registered company', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil ativo': 'Active profile', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastro na plataforma': 'Platform registration', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CNPJ': 'Company tax ID', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciante:': 'Reporter:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por vaga ...': 'Search for a job...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos': 'All', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da vaga': 'Job details', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre': 'About', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza que deseja sair da sua conta?': 'Are you sure you want to log out?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Choose a JPG, PNG, or WEBP image up to 5 MB.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        es: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Idioma': 'Idioma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir menu': 'Abrir menú', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conheça o DevIN': 'Conoce DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Etapas': 'Pasos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contato': 'Contacto', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entrar': 'Iniciar sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar-se': 'Registrarse', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Comece novo.': 'Empieza de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Comece o': 'Empieza el', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'universo': 'universo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'futuro': 'futuro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mundo': 'mundo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'avanço': 'progreso', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mercado': 'mercado laboral', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'amanhã': 'mañana', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontre vagas, estágios e oportunidades de jovem aprendiz em tecnologia.': 'Encuentra empleos, prácticas y oportunidades de aprendizaje juvenil en tecnología.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar meu currículo': 'Crear mi currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'É uma empresa? Anuncie vagas': '¿Eres una empresa? Publica empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoa programando': 'Persona programando', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo na DevIN': 'Currículum en DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Desejado': 'Deseado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Somos um site especializado em vagas de emprego, estágios e jovem aprendiz. Ajudamos empresas a contratar pessoas qualificadas ou que desejam aprender.': 'Somos una plataforma especializada en empleos, prácticas y programas de aprendizaje juvenil. Ayudamos a las empresas a contratar personas cualificadas y con ganas de aprender.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo': 'Currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Crie o seu currículo na DevIN e use para': 'Crea tu currículum en DevIN y úsalo para', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'candidatar-se para vagas de empresas': 'postularte a empleos de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Feed de vagas': 'Lista de empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore um feed de vagas, com inúmeras': 'Explora una lista de empleos con muchas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'oportunidades de trabalho, como': 'oportunidades, como', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'oportunidades de trabalho, como jovem': 'oportunidades laborales, como programas de', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'aprendiz, estágios e empregos.': 'aprendizaje juvenil, prácticas y empleos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'jovem': 'aprendiz', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'aprendiz, estágios e empregos.': 'juvenil, prácticas y empleos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN em 3 etapas': 'DevIN en 3 pasos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Com poucos passos você consegue seu': 'Consigue tu próximo empleo en pocos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'trabalho/sua vaga': 'pasos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Crie sua': 'Crea tu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'conta': 'cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatar-se': 'Postularse', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aqui seu currículo já sai do jeitinho que as grandes empresas querem': 'Tu currículum tendrá el formato que las grandes empresas buscan', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas que contratam estagiários e jovens aprendizes em tecnologia': 'Empresas que contratan practicantes y aprendices en tecnología', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enfrentando dúvidas': '¿Tienes dudas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ou problemas?': 'o problemas?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entre em contato conosco': 'Ponte en contacto', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Email suporte': 'Correo de soporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Central de ajuda': 'Centro de ayuda', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dúvidas mais frequentes': 'Preguntas frecuentes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como crio minha conta e meu currículo?': '¿Cómo creo mi cuenta y mi currículum?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione “Cadastrar-se”, informe seus dados e, após entrar, complete seu currículo para se candidatar às oportunidades.': 'Selecciona “Registrarse”, completa tus datos y, después de iniciar sesión, termina tu currículum para postularte a las oportunidades.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como me candidato a uma vaga?': '¿Cómo me postulo a un empleo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Depois de fazer login e concluir seu currículo, acesse o feed de vagas e escolha a oportunidade que combina com você.': 'Después de iniciar sesión y completar tu currículum, consulta los empleos y elige una oportunidad adecuada para ti.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como cadastro minha empresa?': '¿Cómo registro mi empresa?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Na tela de cadastro, selecione a opção “Empresa”, preencha os dados solicitados e finalize o registro.': 'En la pantalla de registro, selecciona “Empresa”, completa los datos solicitados y finaliza el registro.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como publico uma vaga e acompanho as candidaturas?': '¿Cómo publico un empleo y hago seguimiento de las postulaciones?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Após fazer login na área da empresa, crie a vaga e acompanhe os perfis dos candidatos na área de candidatos.': 'Después de iniciar sesión en el área de empresa, publica el empleo y revisa los perfiles en la sección de candidatos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectamos estudantes a vagas, estágios e oportunidades de jovem aprendiz.': 'Conectamos a estudiantes con empleos, prácticas y oportunidades de aprendizaje juvenil.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Repositório no GitHub': 'Repositorio en GitHub', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escola Profª Alcina Dantas Feijão': 'Escuela Prof. Alcina Dantas Feijão', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Início': 'Inicio', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre o DevIN': 'Acerca de DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como funciona': 'Cómo funciona', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Currículo ideal': 'Currículum ideal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tel: (11) 96551-2472': 'Tel.: (11) 96551-2472', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'SAC': 'Atención al cliente', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.': 'DevIN | Escuela Prof. Alcina Dantas Feijão | © DevIN 2026. Todos los derechos reservados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN Arcade': 'DevIN Arcade', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Navegação dos jogos': 'Navegación de juegos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogos públicos': 'Juegos públicos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sair': 'Salir', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ÁREA SECRETA LIBERADA': 'ÁREA SECRETA DESBLOQUEADA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogos DevIN': 'Juegos DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha um jogo. Seu recorde, maior fase alcançada e última atividade ficam salvos na sua conta.': 'Elige un juego. Tu récord, nivel máximo y última actividad se guardan en tu cuenta.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Lista de jogos': 'Lista de juegos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore fases, colete cristais e evite os perseguidores.': 'Explora niveles, recoge cristales y evita a los perseguidores.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cresça, avance de fase e não bata nas bordas ou em si mesmo.': 'Crece, avanza de nivel y evita chocar contra los bordes o contigo mismo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Complete linhas para pontuar e aumentar o nível.': 'Completa líneas para sumar puntos y subir de nivel.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Rebata a bola, limpe os blocos e alcance novas fases.': 'Rebota la pelota, despeja los bloques y alcanza nuevos niveles.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encontre os pares com o menor número de tentativas.': 'Encuentra todas las parejas con el menor número de intentos posible.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Corra, pule obstáculos e avance por trechos cada vez mais rápidos.': 'Corre, salta los obstáculos y avanza por tramos cada vez más rápidos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Área de jogo': 'Área de juego', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione um jogo': 'Selecciona un juego', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Os controles e a pontuação aparecerão aqui.': 'Los controles y la puntuación aparecerán aquí.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reiniciar': 'Reiniciar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tela do jogo': 'Pantalla del juego', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolha um jogo para começar.': 'Elige un juego para empezar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ANTES DE COMEÇAR': 'ANTES DE EMPEZAR', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Iniciar jogo': 'Iniciar juego', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'FIM DE JOGO': 'FIN DEL JUEGO', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Partida encerrada': 'Partida finalizada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Classificação — 10 melhores': 'Clasificación — 10 mejores', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando classificação…': 'Cargando clasificación…', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogar novamente': 'Jugar de nuevo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pontos:': 'Puntos:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fase:': 'Nivel:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recorde:': 'Récord:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Painel de progresso': 'Panel de progreso', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados de jogo vinculados às contas. A remoção apaga somente o registro selecionado.': 'Datos de juego vinculados a las cuentas. Al eliminar, solo se borra el registro seleccionado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando registros…': 'Cargando registros…', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Área de jogos': 'DevIN | Juegos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conectado como': 'Conectado como', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: setas ou WASD para mover.': 'Controles: flechas o WASD para moverte.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encaixe as peças e complete linhas para pontuar e aumentar o nível.': 'Encaja las piezas y completa líneas para sumar puntos y subir de nivel.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: ← → move, ↑ gira, ↓ acelera e Espaço derruba a peça.': 'Controles: ← → mover, ↑ girar, ↓ acelerar y Espacio baja la pieza.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: ← →, A/D ou mouse para mover a plataforma.': 'Controles: ← →, A/D o ratón para mover la plataforma.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: clique nas cartas para revelar os pares.': 'Controles: haz clic en las cartas para revelar las parejas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Controles: Espaço, ↑ ou clique para pular.': 'Controles: Espacio, ↑ o clic para saltar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Leia as instruções e inicie quando estiver pronto.': 'Lee las instrucciones e inicia cuando estés listo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Progresso salvo.': 'Progreso guardado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há pontuações para este jogo.': 'Aún no hay puntuaciones para este juego.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há progresso salvo.': 'Aún no hay progreso guardado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o progresso.': 'No se pudo guardar el progreso.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A área de jogos precisa ser liberada pelo dashboard.': 'Debes desbloquear el área de juegos desde el panel.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Método não permitido.': 'Método no permitido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogo inválido.': 'Juego no válido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados de progresso inválidos.': 'Datos de progreso no válidos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Inicie uma partida válida antes de salvar o progresso.': 'Inicia una partida válida antes de guardar el progreso.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pontuação ou fase incompatível com o tempo de partida.': 'La puntuación o el nivel no corresponden a la duración de la partida.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Registro inválido.': 'Registro no válido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ação não permitida.': 'Acción no permitida.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível processar a solicitação do arcade.': 'No se pudo procesar la solicitud del arcade.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conta': 'Cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil': 'Perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Jogo': 'Juego', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recorde': 'Récord', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Maior fase': 'Nivel máximo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Última atividade': 'Última actividad', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ação': 'Acción', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Remover': 'Eliminar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Labirinto DevIN': 'Laberinto DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore o labirinto, colete cristais e evite os perseguidores. Cristais especiais deixam os perseguidores vulneráveis.': 'Explora el laberinto, recoge cristales y evita a los perseguidores. Los cristales especiales los vuelven vulnerables.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Colete orbes para crescer e avançar de fase sem bater nas bordas ou em si mesmo.': 'Recoge orbes para crecer y avanzar de nivel sin chocar contra los bordes o contigo mismo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Blocos': 'Bloques', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quebra-blocos': 'Rompebloques', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Memória': 'Memoria', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salto de Ritmo': 'Salto rítmico', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatos': 'Candidatos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vagas': 'Empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'Mis postulaciones', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresas': 'Empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Posts das empresas': 'Publicaciones de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Reports de vagas': 'Denuncias de empleos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncias recebidas': 'Denuncias recibidas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponíveis': 'Disponibles', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum registro disponível.': 'No hay registros disponibles.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum candidato cadastrado.': 'No hay candidatos registrados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga publicada.': 'No hay empleos publicados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga foi denunciada.': 'No se han denunciado empleos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar perfil': 'Editar perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicionar currículo': 'Agregar currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo': 'Tu currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adicione um currículo para se candidatar': 'Agrega un currículum para postularte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'MEU CURRÍCULO': 'MI CURRÍCULUM', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.': 'Revisa, edita y descarga tu documento. Puedes reemplazar el archivo enviado cuando quieras.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.': 'Puedes subir un archivo existente o crear un currículum con ayuda de IA.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há currículo nesta conta': 'Aún no hay un currículum en esta cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.': 'Para postularte a empleos, sube un archivo PDF/Word o completa tus datos para crear un currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolher como continuar': 'Elegir cómo continuar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar ou melhorar': 'Editar o mejorar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.': 'Usaremos tu nombre preferido, estudios, cursos, experiencia e idiomas para crear un currículum claro y profesional. La IA no debe inventar datos; revisa el texto antes de usarlo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.': 'Entiendo que, al generar, los datos de mi currículum se enviarán a Google Gemini. La clave de API está protegida en el servidor.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerar com IA': 'Generar con IA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar / Imprimir PDF': 'Descargar / Imprimir PDF', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar DOCX': 'Descargar DOCX', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Baixar arquivo original': 'Descargar archivo original', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Substituir currículo': 'Reemplazar currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar nome e dados': 'Editar nombre y datos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus dados só serão enviados quando você confirmar e solicitar a geração.': 'Tus datos solo se enviarán cuando confirmes y solicites la generación.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar conteúdo do currículo': 'Editar contenido del currículum', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo profissional': 'Resumen profesional', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Formação': 'Formación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência profissional': 'Experiencia profesional', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos e certificações': 'Cursos y certificaciones', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idiomas': 'Idiomas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.': 'Los datos se envían a Google Gemini solo después de tu consentimiento. La generación puede consumir la cuota de la clave configurada por DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu currículo aparecerá aqui': 'Tu currículum aparecerá aquí', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados e clique em': 'Confirma el envío de los datos y haz clic en', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerar currículo com IA': 'Generar currículum con IA', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Arquivo enviado': 'Archivo enviado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Abrir ou baixar arquivo original': 'Abrir o descargar el archivo original', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia do currículo enviado': 'Vista previa del currículum enviado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicar nova vaga': 'Publicar un nuevo empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Título da vaga': 'Título del empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ex.: Desenvolvedor PHP': 'Ej.: Desarrollador PHP', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Descreva as atividades e requisitos da vaga.': 'Describe las tareas y los requisitos del empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe 0 no salário para exibir "A combinar".': 'Indica 0 para mostrar "A convenir".', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicar vaga': 'Publicar empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não encerrar automaticamente': 'No cerrar automáticamente', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atingir o limite de aprovados': 'Alcanzar el límite de aprobados', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Chegar à data e hora de encerramento': 'Llegar a la fecha y hora de cierre', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atingir o limite ou a data, o que vier primeiro': 'Alcanzar el límite o la fecha, lo que ocurra primero', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Data e hora de encerramento': 'Fecha y hora de cierre', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'No modo “qualquer”, a vaga encerra quando atingir o limite de aprovados ou o prazo.': 'En el modo “cualquiera”, el empleo se cierra al alcanzar el límite de aprobados o la fecha límite.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga encerrada': 'Empleo cerrado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidaturas até:': 'Postulaciones hasta:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encerramento::': 'Cierre::', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Data de encerramento::': 'Fecha de cierre::', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você ainda não publicou nenhuma vaga.': 'Aún no has publicado ningún empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Salvar': 'Guardar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta vaga e as candidaturas dela?': '¿Eliminar este empleo y sus postulaciones?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Título': 'Título', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Editar': 'Editar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por nome ou e-mail': 'Buscar por nombre o correo electrónico', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome ou e-mail': 'Nombre o correo electrónico', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escolaridade': 'Estudios', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos os níveis': 'Todos los niveles', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cursos': 'Cursos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por curso': 'Filtrar por curso', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos os cursos': 'Todos los cursos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Habilidades': 'Habilidades', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por habilidade': 'Filtrar por habilidad', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todas as habilidades': 'Todas las habilidades', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Experiência': 'Experiencia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Filtrar por experiência': 'Filtrar por experiencia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todas as experiências': 'Todos los niveles de experiencia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhum candidato corresponde aos filtros.': 'Ningún candidato coincide con los filtros.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ainda não há candidatos para esta vaga.': 'Aún no hay candidatos para este empleo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovar candidato': 'Aprobar candidato', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato': 'Candidato', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura': 'Postulación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura recebida': 'Postulación recibida', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovar': 'Aprobar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusar': 'Rechazar', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusado': 'Rechazado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovado': 'Aprobado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pendente': 'Pendiente', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga publicada': 'Empleo publicado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicação': 'Publicación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contato': 'Contacto', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestões ou pedir suporte.': 'Contacta al equipo de DevIN para conocer el proyecto, enviar sugerencias o solicitar ayuda.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explicando tudo sobre a vaga selecionada': 'Detalles del empleo seleccionado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Use este espaço para visualizar detalhes da vaga, pessoa ou post escolhido no painel.': 'Usa este espacio para ver los detalles del empleo, persona o publicación seleccionados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga em que o candidato se inscreveu': 'Empleo al que se postuló el candidato', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma vaga para ver sua descrição completa aqui.': 'Selecciona un empleo para ver aquí su descripción completa.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Alterar foto de perfil': 'Cambiar foto de perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Foto de perfil': 'Foto de perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome da empresa': 'Nombre de la empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da conta': 'Correo electrónico de la cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Personalize o idioma da interface da sua conta.': 'Personaliza el idioma de la interfaz de tu cuenta.', // Explicação DevIN: Declara o tipo da e inicia sua estrutura.
            'Escolha o idioma usado na sua experiência na DevIN.': 'Elige el idioma para tu experiencia en DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Essa ação é permanente e não pode ser desfeita.': 'Esta acción es permanente y no se puede deshacer.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A geração por IA ainda não está configurada. Adicione a chave Gemini no ambiente do servidor.': 'La generación con IA aún no está configurada. Agrega la clave de Gemini al entorno del servidor.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Solicitação inválida. Atualize a página e tente novamente.': 'Solicitud no válida. Actualiza la página e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme o envio dos dados para gerar o currículo.': 'Confirma el envío de los datos para generar el currículum.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você atingiu o limite de 4 gerações por hora. Tente novamente mais tarde.': 'Has alcanzado el límite de 4 generaciones por hora. Inténtalo más tarde.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Idioma salvo com sucesso.': 'Idioma guardado correctamente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível salvar o idioma. Tente novamente.': 'No se pudo guardar el idioma. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Administrativo': 'DevIN | Panel de administración', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Empresa': 'DevIN | Panel de empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Dashboard Candidato': 'DevIN | Panel de candidato', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua mensagem': 'Tu mensaje', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens privadas entre usuários da DevIN': 'Mensajes privados entre usuarios de DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Voltar para contatos': 'Volver a los contactos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Carregando contatos...': 'Cargando contactos...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga mantida': 'Empleo mantenido', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga excluída': 'Empleo eliminado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciante': 'Denunciante', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil denunciado': 'Perfil reportado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Support DevIN': 'Support DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denúncia #': 'Denuncia #', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Vaga #': 'Empleo #', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Enviada em': 'Enviada el', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Motivo': 'Motivo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Análise': 'Revisión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagem da denúncia': 'Detalles de la denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Relato da denúncia': 'Relato de la denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados da denúncia': 'Datos de la denuncia', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail do perfil': 'Correo electrónico del perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail do denunciante': 'Correo de quien denunció', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail da empresa': 'Correo de la empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A denúncia foi aceita. O perfil denunciado foi removido e o denunciante foi notificado.': 'Denuncia aceptada. El perfil reportado fue eliminado y se notificó a quien la presentó.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A denúncia foi recusada. O perfil foi mantido e o denunciante foi notificado.': 'Denuncia rechazada. El perfil se mantuvo y se notificó a quien la presentó.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Decisão registrada, mas não foi possível enviar a mensagem ao denunciante.': 'Decisión registrada, pero no se pudo enviar un mensaje a quien presentó la denuncia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes do registro': 'Detalles del registro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informacoes da plataforma': 'Información de la plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informacao disponivel': 'Información disponible', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Registro selecionado': 'Registro seleccionado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Disponivel na plataforma': 'Disponible en la plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sem detalhes disponíveis.': 'No hay detalles disponibles.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Prévia da foto de perfil': 'Vista previa de la foto de perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato DevIN': 'Candidato de DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir registro': 'Eliminar registro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir Registro': 'Eliminar registro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir perfil': 'Eliminar perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir empresa': 'Eliminar empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir este candidato permanentemente?': '¿Eliminar permanentemente a este candidato?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta empresa permanentemente?': '¿Eliminar permanentemente esta empresa?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Excluir esta vaga?': '¿Eliminar este empleo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Publicação::Registro da vaga na plataforma': 'Publicación::Registro del empleo en la plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não se encaixa': 'No cumple con el perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura para': 'Postulación para', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidato para': 'Candidato para', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confira os requisitos e envie sua candidatura': 'Revisa los requisitos y envía tu postulación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Aprovadas': 'Aprobadas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recusadas': 'Rechazadas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Em análise': 'En revisión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa cadastrada': 'Empresa registrada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Perfil ativo': 'Perfil activo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastro na plataforma': 'Registro en la plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CNPJ': 'Identificación fiscal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Denunciante:': 'Denunciante:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pesquisar por vaga ...': 'Buscar empleo...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Todos': 'Todos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Detalhes da vaga': 'Detalles del empleo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sobre': 'Acerca de', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Tem certeza que deseja sair da sua conta?': '¿Seguro que quieres cerrar sesión?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Elige una imagen JPG, PNG o WEBP de hasta 5 MB.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const publicTranslations = { // Explicação DevIN: Declara variável JavaScript publicTranslations para reutilizar valor ou resultado.
        en: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Crie o seu currículo na DevIN e use para candidatar-se para vagas de empresas': 'Create your resume on DevIN and use it to apply for company job openings', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore um feed de vagas, com inúmeras oportunidades de trabalho, como jovem aprendiz, estágios e empregos.': 'Explore a job feed with countless opportunities, such as youth apprenticeships, internships and jobs.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'PRIVACIDADE E SEGURANÇA': 'PRIVACY AND SECURITY', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entenda como o DevIN trata os dados usados para criar sua conta e conectar você a oportunidades.': 'Understand how DevIN handles the data used to create your account and connect you to opportunities.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Última atualização: 12 de agosto de 2026': 'Last updated: August 12, 2026', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nesta página': 'On this page', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Índice': 'Contents', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '1. Dados que coletamos': '1. Data we collect', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '2. Como usamos seus dados': '2. How we use your data', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '3. Compartilhamento': '3. Sharing', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '4. Segurança e armazenamento': '4. Security and storage', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '5. Seus direitos': '5. Your rights', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '6. Alterações nesta política': '6. Changes to this policy', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados que coletamos': 'Data we collect', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como usamos seus dados': 'How we use your data', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Compartilhamento': 'Sharing', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Segurança e armazenamento': 'Security and storage', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus direitos': 'Your rights', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Alterações nesta política': 'Changes to this policy', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Coletamos os dados fornecidos no cadastro, como nome, e-mail, telefone, CPF, endereço e informações profissionais. Também podemos registrar dados de acesso necessários para segurança e funcionamento da plataforma.': 'We collect the data provided at sign-up, such as name, email, phone, CPF, address and professional information. We may also record access data needed for the security and operation of the platform.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usamos essas informações para criar e manter sua conta, permitir candidaturas e divulgação de vagas, entrar em contato sobre o uso da plataforma e aprimorar nossos serviços.': 'We use this information to create and maintain your account, enable job applications and postings, contact you about using the platform and improve our services.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quando você se candidata a uma vaga, os dados necessários do seu perfil podem ser compartilhados com a empresa responsável pela oportunidade. Não comercializamos dados pessoais.': 'When you apply for a job, the necessary data from your profile may be shared with the company responsible for the opportunity. We do not sell personal data.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adotamos medidas técnicas e organizacionais para proteger os dados contra acesso não autorizado, perda ou alteração. Os dados são mantidos pelo tempo necessário às finalidades descritas e às obrigações legais aplicáveis.': 'We adopt technical and organizational measures to protect data against unauthorized access, loss or alteration. Data is kept for as long as necessary for the purposes described and applicable legal obligations.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você pode solicitar confirmação do tratamento, acesso, correção, anonimização, eliminação ou informações sobre compartilhamento dos seus dados, conforme a legislação aplicável.': 'You may request confirmation of processing, access, correction, anonymization, deletion or information about the sharing of your data, in accordance with applicable law.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta política pode ser atualizada para refletir mudanças no DevIN ou na legislação. A data de atualização no topo da página indica a versão mais recente.': 'This policy may be updated to reflect changes in DevIN or in the law. The update date at the top of the page indicates the latest version.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Importante': 'Important', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este texto é uma base para o projeto. Antes de publicar o DevIN para uso real, defina o canal de contato do encarregado de dados e revise a política conforme a operação da plataforma.': 'This text is a starting point for the project. Before launching DevIN for real use, define the data protection officer contact channel and review the policy according to how the platform operates.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '← Voltar ao login': '← Back to login', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Política de Privacidade': 'DevIN | Privacy Policy', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.': '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. All rights reserved.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados': '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. All rights reserved', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.': '| Escola Profª Alcina Dantas Feijão | DevIN 2026. All rights reserved.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados': '| Escola Profª Alcina Dantas Feijão | DevIN 2026. All rights reserved', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Login': 'Login', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar conta': 'Create account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoal': 'Personal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'OU': 'OR', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa': 'Company', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome:*': 'Name:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome da Empresa:*': 'Company name:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail:*': 'Email:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail:': 'Email:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CPF:*': 'CPF:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CNPJ:*': 'CNPJ:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CEP:*': 'Postal code:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Telefone:*': 'Phone:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senha:*': 'Password:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senha:': 'Password:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme a sua senha:*': 'Confirm your password:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senhas não coincidem': 'Passwords do not match', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'No mínimo 8 caracteres': 'At least 8 characters', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pelo menos 1 letra maiúscula (A-Z)': 'At least 1 uppercase letter (A-Z)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pelo menos 1 caractere especial (ex: ! @ # $)': 'At least 1 special character (e.g. ! @ # $)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar': 'Sign up', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Já tem conta?': 'Already have an account?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Faça login': 'Log in', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar ou ocultar senha': 'Show or hide password', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar senha': 'Show password', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar Senha': 'Show password', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ocultar senha': 'Hide password', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Robô DevIN': 'DevIN robot', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu e-mail...': 'Your email...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua senha...': 'Your password...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esqueceu a Senha?': 'Forgot your password?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar-se': 'Sign up', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ao continuar, você reconhece a': 'By continuing, you acknowledge the', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Política de Privacidade': 'Privacy Policy', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'do DevIN.': 'of DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escola Profª Alcina Dantas Feijão': 'Escola Profª Alcina Dantas Feijão', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '© DevIN 2026. Todos os direitos reservados.': '© DevIN 2026. All rights reserved.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '© DevIN 2026. Todos os direitos reservados': '© DevIN 2026. All rights reserved', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Login': 'DevIN | Login', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Criar Conta Pessoal': 'DevIN | Create personal account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Criar Conta Empresa': 'DevIN | Create company account', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Por favor, corrija os erros nos campos de senha antes de prosseguir.': 'Please fix the password field errors before continuing.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nao foi possivel preparar o formulario. Atualize a pagina e tente novamente.': 'Could not prepare the form. Refresh the page and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nao foi possivel preparar o login. Atualize a pagina e tente novamente.': 'Could not prepare the login. Refresh the page and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Muitas tentativas de cadastro. Aguarde e tente novamente.': 'Too many sign-up attempts. Please wait and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Muitas tentativas. Aguarde e tente novamente.': 'Too many attempts. Please wait and try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe seu nome.': 'Enter your name.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe o nome da empresa.': 'Enter the company name.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um e-mail válido.': 'Enter a valid email.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CPF válido com 11 dígitos.': 'Enter a valid CPF with 11 digits.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CNPJ válido com 14 dígitos.': 'Enter a valid CNPJ with 14 digits.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CEP válido com 8 dígitos.': 'Enter a valid postal code with 8 digits.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um telefone válido (10 ou 11 dígitos com DDD).': 'Enter a valid phone number (10 or 11 digits with area code).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe uma senha.': 'Enter a password.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'As senhas não coincidem.': 'The passwords do not match.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve ter no mínimo 8 caracteres.': 'The password must have at least 8 characters.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve possuir pelo menos uma letra maiúscula (A-Z).': 'The password must have at least one uppercase letter (A-Z).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve possuir pelo menos um caractere especial (ex: ! @ # $).': 'The password must have at least one special character (e.g. ! @ # $).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível validar o e-mail.': 'Could not validate the email.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este e-mail já está cadastrado. Use outro e-mail ou faça login.': 'This email is already registered. Use another email or log in.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CPF já está cadastrado em nosso sistema.': 'This CPF is already registered in our system.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CNPJ já está cadastrado em nosso sistema.': 'This CNPJ is already registered in our system.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CPF ou e-mail já está cadastrado em nosso sistema.': 'This CPF or email is already registered in our system.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CNPJ ou e-mail já está cadastrado em nosso sistema.': 'This CNPJ or email is already registered in our system.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível realizar o cadastro. Tente novamente.': 'Could not complete the sign-up. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível cadastrar a empresa. Tente novamente.': 'Could not register the company. Please try again.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível proteger a senha.': 'Could not secure the password.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Erro ao preparar o cadastro.': 'Error preparing the sign-up.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Erro ao preparar o cadastro da empresa.': 'Error preparing the company sign-up.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha e-mail e senha corretamente.': 'Fill in your email and password correctly.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail ou senha inválidos.': 'Invalid email or password.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        es: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Crie o seu currículo na DevIN e use para candidatar-se para vagas de empresas': 'Crea tu currículum en DevIN y úsalo para postularte a empleos de empresas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Explore um feed de vagas, com inúmeras oportunidades de trabalho, como jovem aprendiz, estágios e empregos.': 'Explora una lista de empleos con innumerables oportunidades, como jóvenes aprendices, pasantías y empleos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'PRIVACIDADE E SEGURANÇA': 'PRIVACIDAD Y SEGURIDAD', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Entenda como o DevIN trata os dados usados para criar sua conta e conectar você a oportunidades.': 'Entiende cómo DevIN trata los datos usados para crear tu cuenta y conectarte con oportunidades.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Última atualização: 12 de agosto de 2026': 'Última actualización: 12 de agosto de 2026', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nesta página': 'En esta página', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Índice': 'Índice', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '1. Dados que coletamos': '1. Datos que recopilamos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '2. Como usamos seus dados': '2. Cómo usamos tus datos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '3. Compartilhamento': '3. Intercambio', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '4. Segurança e armazenamento': '4. Seguridad y almacenamiento', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '5. Seus direitos': '5. Tus derechos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '6. Alterações nesta política': '6. Cambios en esta política', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Dados que coletamos': 'Datos que recopilamos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Como usamos seus dados': 'Cómo usamos tus datos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Compartilhamento': 'Intercambio', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Segurança e armazenamento': 'Seguridad y almacenamiento', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seus direitos': 'Tus derechos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Alterações nesta política': 'Cambios en esta política', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Coletamos os dados fornecidos no cadastro, como nome, e-mail, telefone, CPF, endereço e informações profissionais. Também podemos registrar dados de acesso necessários para segurança e funcionamento da plataforma.': 'Recopilamos los datos proporcionados en el registro, como nombre, correo, teléfono, CPF, dirección e información profesional. También podemos registrar datos de acceso necesarios para la seguridad y el funcionamiento de la plataforma.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Usamos essas informações para criar e manter sua conta, permitir candidaturas e divulgação de vagas, entrar em contato sobre o uso da plataforma e aprimorar nossos serviços.': 'Usamos esta información para crear y mantener tu cuenta, permitir postulaciones y publicación de empleos, contactarte sobre el uso de la plataforma y mejorar nuestros servicios.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Quando você se candidata a uma vaga, os dados necessários do seu perfil podem ser compartilhados com a empresa responsável pela oportunidade. Não comercializamos dados pessoais.': 'Cuando te postulas a un empleo, los datos necesarios de tu perfil pueden compartirse con la empresa responsable de la oportunidad. No vendemos datos personales.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Adotamos medidas técnicas e organizacionais para proteger os dados contra acesso não autorizado, perda ou alteração. Os dados são mantidos pelo tempo necessário às finalidades descritas e às obrigações legais aplicáveis.': 'Adoptamos medidas técnicas y organizativas para proteger los datos contra el acceso no autorizado, la pérdida o la alteración. Los datos se conservan durante el tiempo necesario para los fines descritos y las obligaciones legales aplicables.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Você pode solicitar confirmação do tratamento, acesso, correção, anonimização, eliminação ou informações sobre compartilhamento dos seus dados, conforme a legislação aplicável.': 'Puedes solicitar la confirmación del tratamiento, acceso, corrección, anonimización, eliminación o información sobre el intercambio de tus datos, conforme a la legislación aplicable.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esta política pode ser atualizada para refletir mudanças no DevIN ou na legislação. A data de atualização no topo da página indica a versão mais recente.': 'Esta política puede actualizarse para reflejar cambios en DevIN o en la legislación. La fecha de actualización en la parte superior indica la versión más reciente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Importante': 'Importante', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este texto é uma base para o projeto. Antes de publicar o DevIN para uso real, defina o canal de contato do encarregado de dados e revise a política conforme a operação da plataforma.': 'Este texto es una base para el proyecto. Antes de publicar DevIN para uso real, define el canal de contacto del encargado de datos y revisa la política según la operación de la plataforma.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '← Voltar ao login': '← Volver al inicio de sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Política de Privacidade': 'DevIN | Política de Privacidad', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.': '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos los derechos reservados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados': '| Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos los derechos reservados', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.': '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos los derechos reservados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados': '| Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos los derechos reservados', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Login': 'Iniciar sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Criar conta': 'Crear cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pessoal': 'Personal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'OU': 'O', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Empresa': 'Empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome:*': 'Nombre:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nome da Empresa:*': 'Nombre de la empresa:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail:*': 'Correo electrónico:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail:': 'Correo electrónico:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CPF:*': 'CPF:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CNPJ:*': 'CNPJ:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'CEP:*': 'Código postal:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Telefone:*': 'Teléfono:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senha:*': 'Contraseña:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senha:': 'Contraseña:', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirme a sua senha:*': 'Confirma tu contraseña:*', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Senhas não coincidem': 'Las contraseñas no coinciden', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'No mínimo 8 caracteres': 'Mínimo 8 caracteres', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pelo menos 1 letra maiúscula (A-Z)': 'Al menos 1 letra mayúscula (A-Z)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Pelo menos 1 caractere especial (ex: ! @ # $)': 'Al menos 1 carácter especial (ej: ! @ # $)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar': 'Registrarse', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Já tem conta?': '¿Ya tienes cuenta?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Faça login': 'Inicia sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar ou ocultar senha': 'Mostrar u ocultar contraseña', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar senha': 'Mostrar contraseña', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mostrar Senha': 'Mostrar contraseña', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ocultar senha': 'Ocultar contraseña', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Robô DevIN': 'Robot DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Seu e-mail...': 'Tu correo electrónico...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Sua senha...': 'Tu contraseña...', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Esqueceu a Senha?': '¿Olvidaste tu contraseña?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Cadastrar-se': 'Registrarse', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Ao continuar, você reconhece a': 'Al continuar, reconoces la', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Política de Privacidade': 'Política de Privacidad', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'do DevIN.': 'de DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Escola Profª Alcina Dantas Feijão': 'Escola Profª Alcina Dantas Feijão', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '© DevIN 2026. Todos os direitos reservados.': '© DevIN 2026. Todos los derechos reservados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '© DevIN 2026. Todos os direitos reservados': '© DevIN 2026. Todos los derechos reservados', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Login': 'DevIN | Iniciar sesión', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Criar Conta Pessoal': 'DevIN | Crear cuenta personal', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'DevIN | Criar Conta Empresa': 'DevIN | Crear cuenta de empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Por favor, corrija os erros nos campos de senha antes de prosseguir.': 'Corrige los errores en los campos de contraseña antes de continuar.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nao foi possivel preparar o formulario. Atualize a pagina e tente novamente.': 'No se pudo preparar el formulario. Actualiza la página e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nao foi possivel preparar o login. Atualize a pagina e tente novamente.': 'No se pudo preparar el inicio de sesión. Actualiza la página e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Muitas tentativas de cadastro. Aguarde e tente novamente.': 'Demasiados intentos de registro. Espera e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Muitas tentativas. Aguarde e tente novamente.': 'Demasiados intentos. Espera e inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe seu nome.': 'Ingresa tu nombre.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe o nome da empresa.': 'Ingresa el nombre de la empresa.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um e-mail válido.': 'Ingresa un correo electrónico válido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CPF válido com 11 dígitos.': 'Ingresa un CPF válido de 11 dígitos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CNPJ válido com 14 dígitos.': 'Ingresa un CNPJ válido de 14 dígitos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um CEP válido com 8 dígitos.': 'Ingresa un código postal válido de 8 dígitos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe um telefone válido (10 ou 11 dígitos com DDD).': 'Ingresa un teléfono válido (10 u 11 dígitos con código de área).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Informe uma senha.': 'Ingresa una contraseña.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'As senhas não coincidem.': 'Las contraseñas no coinciden.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve ter no mínimo 8 caracteres.': 'La contraseña debe tener al menos 8 caracteres.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve possuir pelo menos uma letra maiúscula (A-Z).': 'La contraseña debe tener al menos una letra mayúscula (A-Z).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'A senha deve possuir pelo menos um caractere especial (ex: ! @ # $).': 'La contraseña debe tener al menos un carácter especial (ej: ! @ # $).', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível validar o e-mail.': 'No se pudo validar el correo electrónico.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este e-mail já está cadastrado. Use outro e-mail ou faça login.': 'Este correo ya está registrado. Usa otro o inicia sesión.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CPF já está cadastrado em nosso sistema.': 'Este CPF ya está registrado en nuestro sistema.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CNPJ já está cadastrado em nosso sistema.': 'Este CNPJ ya está registrado en nuestro sistema.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CPF ou e-mail já está cadastrado em nosso sistema.': 'Este CPF o correo ya está registrado en nuestro sistema.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Este CNPJ ou e-mail já está cadastrado em nosso sistema.': 'Este CNPJ o correo ya está registrado en nuestro sistema.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível realizar o cadastro. Tente novamente.': 'No se pudo completar el registro. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível cadastrar a empresa. Tente novamente.': 'No se pudo registrar la empresa. Inténtalo de nuevo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Não foi possível proteger a senha.': 'No se pudo proteger la contraseña.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Erro ao preparar o cadastro.': 'Error al preparar el registro.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Erro ao preparar o cadastro da empresa.': 'Error al preparar el registro de la empresa.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Preencha e-mail e senha corretamente.': 'Completa correctamente tu correo y contraseña.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'E-mail ou senha inválidos.': 'Correo o contraseña inválidos.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const dictionary = { // Explicação DevIN: Declara variável JavaScript dictionary para reutilizar valor ou resultado.
        ...(translations[language] ?? translations['pt-BR']), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ...(additionalTranslations[language] ?? {}), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ...(publicTranslations[language] ?? {}) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Complemento compartilhado por perfis e JARVIS, sem alterar traduções antigas.
    // As palavras ENVIAR e EXCLUIR permanecem intactas por serem confirmações seguras.
    const newerTranslations = { // Explicação DevIN: Declara variável JavaScript newerTranslations para reutilizar valor ou resultado.
        en: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Central da minha conta': 'My account hub', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerencie seus dados, privacidade, atendimento e atividades sem sair do painel.': 'Manage your data, privacy, support and activities from your dashboard.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Configurações da conta': 'Account settings', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Privacidade e dados': 'Privacy and data', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Suporte DevIN': 'DevIN Support', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'My applications', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerenciar candidatos': 'Manage applicants', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'JARVIS Admin': 'JARVIS Admin', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirmar contratação': 'Confirm hiring', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirmar minha contratação': 'Confirm my employment', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encerrar minhas outras candidaturas pendentes (opcional)': 'Withdraw my other pending applications (optional)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contratação confirmada': 'Employment confirmed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura retirada após contratação': 'Application withdrawn after employment', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Diagnóstico JARVIS': 'JARVIS diagnostics', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens oficiais': 'Official messages', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atividades': 'Activities', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversas': 'Conversations', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Histórico': 'History', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Consultas': 'Search tools', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo do DevIN': 'DevIN summary', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga foi editada.': 'No job posting was modified.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        es: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            'Central da minha conta': 'Centro de mi cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerencie seus dados, privacidade, atendimento e atividades sem sair do painel.': 'Gestiona tus datos, privacidad, soporte y actividades desde tu panel.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Configurações da conta': 'Configuración de la cuenta', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Privacidade e dados': 'Privacidad y datos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Suporte DevIN': 'Soporte DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Minhas candidaturas': 'Mis solicitudes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Gerenciar candidatos': 'Gestionar candidatos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'JARVIS Admin': 'JARVIS Admin', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirmar contratação': 'Confirmar contratación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Confirmar minha contratação': 'Confirmar mi contratación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Encerrar minhas outras candidaturas pendentes (opcional)': 'Retirar mis otras solicitudes pendientes (opcional)', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Contratação confirmada': 'Contratación confirmada', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Candidatura retirada após contratação': 'Solicitud retirada después de la contratación', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Diagnóstico JARVIS': 'Diagnóstico JARVIS', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Mensagens oficiais': 'Mensajes oficiales', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Atividades': 'Actividades', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Conversas': 'Conversaciones', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Histórico': 'Historial', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Consultas': 'Consultas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Resumo do DevIN': 'Resumen de DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Nenhuma vaga foi editada.': 'Ninguna vacante fue modificada.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    Object.assign(dictionary, newerTranslations[language] || {}); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
    // Tradução das validações de identidade e do formulário de edição de e-mail.
    const profileEmailTranslations = [ // Explicação DevIN: Mantém os três idiomas para textos sensíveis de perfil.
        ["Senha atual (somente para alterar e-mail)", "Current password (only to change email)", "Contraseña actual (solo para cambiar el correo)"], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Confirme sua senha se mudar o e-mail", "Confirm your password if you change the email", "Confirma tu contraseña si cambias el correo"], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Confirme sua senha atual para alterar o e-mail.", "Confirm your current password to change your email.", "Confirma tu contraseña actual para cambiar el correo."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Informe um nome e e-mail válidos (até 254 caracteres).", "Enter a valid name and email (up to 254 characters).", "Introduce un nombre y correo válidos (hasta 254 caracteres)."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Este e-mail já está em uso por outra conta.", "This email address is already used by another account.", "Este correo electrónico ya está en uso por otra cuenta."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["O e-mail ultrapassa o tamanho permitido pelo cadastro. Informe um endereço menor.", "The email exceeds the account's maximum length. Enter a shorter address.", "El correo supera el tamaño permitido. Introduce una dirección más corta."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Outra atualização de e-mail está em andamento. Tente novamente.", "Another email update is in progress. Please try again.", "Hay otra actualización de correo en curso. Inténtalo de nuevo."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["E-mail alterado com sucesso. No próximo acesso, entre com o novo endereço.", "Email changed successfully. Use the new address at your next sign-in.", "Correo actualizado correctamente. Usa la nueva dirección al iniciar sesión."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Não foi possível atualizar o perfil. Seus dados foram mantidos.", "Your profile could not be updated. Your data was kept unchanged.", "No se pudo actualizar tu perfil. Tus datos se mantuvieron sin cambios."], // Explicação DevIN: Tradução do formulário de e-mail.
        ["Não foi possível salvar as alterações. O cadastro foi mantido.", "Changes could not be saved. Your account remains unchanged.", "No se pudieron guardar los cambios. Tu cuenta sigue sin cambios."], // Explicação DevIN: Tradução do formulário de e-mail.
    ]; // Explicação DevIN: Fecha a tabela de textos do perfil.
    if (language !== 'pt-BR') { // Explicação DevIN: Mantém a versão em português como padrão.
        const column = language === 'en' ? 1 : 2; // Explicação DevIN: Seleciona inglês ou espanhol.
        Object.assign(dictionary, Object.fromEntries(profileEmailTranslations.map(line => [line[0], line[column]]))); // Explicação DevIN: Integra rótulos e erros à tradução existente.
    } // Explicação DevIN: Finaliza o mapeamento da identidade.
    // Tradução compartilhada da Central de Suporte em português, inglês e espanhol.
    const supportTranslations = [ // Explicação DevIN: Declara variável JavaScript supportTranslations para reutilizar valor ou resultado.
        ["Atendimento associado à conta", "Support for account", "Atención asociada a la cuenta"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Anexos são privados e acessíveis apenas ao dono do chamado e aos ADMs.", "Attachments are private and available only to the request owner and administrators.", "Los archivos adjuntos son privados y solo están disponibles para el titular de la solicitud y los administradores."], // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        ["Escolher arquivo", "Choose file", "Elegir archivo"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Nenhum arquivo selecionado", "No file selected", "Ningún archivo seleccionado"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Tentar novamente", "Try again", "Reintentar"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Não foi possível carregar os chamados.", "Could not load requests.", "No se pudieron cargar las solicitudes."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["A Central de Suporte está temporariamente indisponível. Tente novamente mais tarde.", "The Support Center is temporarily unavailable. Please try again later.", "El centro de soporte no está disponible temporalmente. Inténtalo de nuevo más tarde."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["As tabelas do suporte não estão instaladas. Execute a migração de suporte.", "The support tables are not installed. Run the support migration.", "Las tablas de soporte no están instaladas. Ejecuta la migración de soporte."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Ocorreu um problema com o servidor. Tente novamente mais tarde.", "There was a server problem. Please try again later.", "Se produjo un problema en el servidor. Inténtalo de nuevo más tarde."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Falha ao enviar chamado.", "Could not submit request.", "No se pudo enviar la solicitud."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.

        ["Suporte", "Support", "Soporte"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["SUPORTE DEVIN", "DEVIN SUPPORT", "SOPORTE DEVIN"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["CENTRAL DE ATENDIMENTO · DEVIN", "DEVIN HELP DESK", "CENTRO DE ATENCIÓN · DEVIN"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Como podemos ajudar?", "How can we help?", "¿Cómo podemos ayudarte?"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encontrou um erro ou tem alguma dúvida? Conte para nossa equipe.", "Found an error or have a question? Tell our team.", "¿Encontraste un error o tienes alguna pregunta? Cuéntaselo a nuestro equipo."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["A resposta aparecerá aqui, nas Mensagens e no seu e-mail.", "The reply will appear here, in Messages and in your email.", "La respuesta aparecerá aquí, en Mensajes y en tu correo electrónico."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Áreas da Central de Ajuda", "Help Center sections", "Secciones del centro de ayuda"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Novo chamado", "New request", "Nueva solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Meus chamados", "My requests", "Mis solicitudes"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Abrir chamado", "Submit a request", "Abrir una solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Atendimento associado à conta", "Support for account", "Atención asociada a la cuenta"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Qual é o assunto?", "What is this about?", "¿Cuál es el asunto?"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Selecione uma categoria", "Select a category", "Selecciona una categoría"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Erro no site", "Website error", "Error en el sitio"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Dúvida ou orientação", "Question or guidance", "Duda u orientación"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Dúvida", "Question", "Duda"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Conta e acesso", "Account and access", "Cuenta y acceso"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Vagas e candidaturas", "Jobs and applications", "Vacantes y postulaciones"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Segurança e privacidade", "Security and privacy", "Seguridad y privacidad"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Sugestão de melhoria", "Improvement suggestion", "Sugerencia de mejora"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Sugestão", "Suggestion", "Sugerencia"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Outros assuntos", "Other topics", "Otros asuntos"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Outro assunto", "Other topic", "Otro asunto"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Descreva o que aconteceu", "Describe what happened", "Describe lo que ocurrió"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Explique o problema, onde ocorreu e o que você esperava encontrar. Não envie senhas nem tokens.", "Explain the issue, where it occurred and what you expected. Do not send passwords or tokens.", "Explica el problema, dónde ocurrió y qué esperabas encontrar. No envíes contraseñas ni tokens."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["caracteres", "characters", "caracteres"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Anexo", "Attachment", "Adjunto"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["(opcional, até 20 MB)", "(optional, up to 20 MB)", "(opcional, hasta 20 MB)"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Remover", "Remove", "Quitar"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Formatos: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX ou ZIP.", "Formats: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX or ZIP.", "Formatos: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX o ZIP."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Anexos são privados e acessíveis apenas ao dono do chamado e aos ADMs.", "Attachments are private and can only be accessed by the request owner and administrators.", "Los archivos adjuntos son privados y solo pueden acceder el titular de la solicitud y los administradores."], // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        ["Enviar solicitação", "Submit request", "Enviar solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Acompanhe as respostas sem precisar reenviar o problema.", "Track replies without having to submit the issue again.", "Consulta las respuestas sin tener que volver a enviar el problema."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Carregando seus chamados...", "Loading your requests...", "Cargando tus solicitudes..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["← Voltar aos meus chamados", "← Back to my requests", "← Volver a mis solicitudes"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Suporte aos usuários", "User support", "Atención a usuarios"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Chamados recebidos de candidatos e empresas, com acompanhamento, anexos e resposta individual.", "Requests from candidates and companies, with tracking, attachments and individual replies.", "Solicitudes de candidatos y empresas, con seguimiento, archivos adjuntos y respuestas individuales."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Carregando chamados...", "Loading requests...", "Cargando solicitudes..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Atualizar", "Refresh", "Actualizar"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Áreas do atendimento", "Support sections", "Secciones de atención"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Caixa de entrada", "Inbox", "Bandeja de entrada"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Atendimento", "Support", "Atención"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encontrar chamado", "Find a request", "Buscar una solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Nome, e-mail, categoria ou número...", "Name, email, category or number...", "Nombre, correo, categoría o número..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Filtrar atendimento", "Filter requests", "Filtrar solicitudes"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Todos os chamados", "All requests", "Todas las solicitudes"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Aguardando resposta", "Awaiting reply", "Esperando respuesta"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Respondidos", "Answered", "Respondidas"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encerrados", "Closed", "Cerradas"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Carregando solicitações...", "Loading requests...", "Cargando solicitudes..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Detalhes e resposta", "Details and reply", "Detalles y respuesta"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Voltar à lista", "Back to list", "Volver a la lista"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Escolha um chamado à esquerda para ler a descrição, ver anexos e responder.", "Select a request on the left to read its description, view attachments and reply.", "Selecciona una solicitud a la izquierda para leer su descripción, ver archivos adjuntos y responder."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Responder como Suporte DevIN", "Reply as DevIN Support", "Responder como Soporte DevIN"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Escreva uma resposta clara e respeitosa. Não solicite senhas ou tokens.", "Write a clear and respectful reply. Never request passwords or tokens.", "Escribe una respuesta clara y respetuosa. No solicites contraseñas ni tokens."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Responder ao usuário", "Reply to user", "Responder al usuario"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encerrar chamado", "Close request", "Cerrar solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Ao responder, o usuário recebe a mensagem na plataforma e uma tentativa de e-mail. Se o SMTP falhar, uma opção de reenvio ficará disponível.", "When you reply, the user receives an in-app message and an email is attempted. If SMTP fails, a retry option will be available.", "Al responder, el usuario recibe un mensaje en la plataforma y se intenta enviar un correo. Si falla SMTP, habrá una opción para reintentarlo."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Nenhum chamado encontrado.", "No requests found.", "No se encontraron solicitudes."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Buscando detalhes...", "Loading details...", "Cargando detalles..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Descrição enviada", "Submitted description", "Descripción enviada"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Histórico do atendimento", "Support history", "Historial de atención"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Ainda não há respostas da equipe.", "No replies from the team yet.", "Todavía no hay respuestas del equipo."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Tentar entrega novamente", "Retry delivery", "Reintentar envío"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["O anexo ultrapassa 20 MB. Escolha um arquivo menor.", "The attachment exceeds 20 MB. Choose a smaller file.", "El archivo adjunto supera los 20 MB. Elige uno más pequeño."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["O anexo ultrapassa 20 MB.", "The attachment exceeds 20 MB.", "El archivo adjunto supera los 20 MB."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Enviando sua solicitação...", "Submitting your request...", "Enviando tu solicitud..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Registrando a resposta...", "Saving the reply...", "Guardando la respuesta..."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encerrar este chamado? As mensagens continuam registradas.", "Close this request? The messages will remain saved.", "¿Cerrar esta solicitud? Los mensajes permanecerán guardados."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Chamado encerrado.", "Request closed.", "Solicitud cerrada."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["O servidor não conseguiu atender agora.", "The server could not process your request right now.", "El servidor no pudo procesar tu solicitud en este momento."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["O suporte ainda não foi configurado no banco. Aplique a migração de suporte.", "Support is not yet configured in the database. Apply the support migration.", "El soporte aún no está configurado en la base de datos. Aplica la migración de soporte."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Não foi possível concluir a solicitação. Verifique a configuração do suporte.", "The request could not be completed. Check the support configuration.", "No se pudo completar la solicitud. Comprueba la configuración del soporte."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Respondido", "Answered", "Respondida"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Encerrado", "Closed", "Cerrada"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Usuário:", "User:", "Usuario:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["E-mail:", "Email:", "Correo:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Conta:", "Account:", "Cuenta:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Candidato", "Candidate", "Candidato"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Empresa", "Company", "Empresa"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Aberto em:", "Opened on:", "Abierto el:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Baixar anexo:", "Download attachment:", "Descargar adjunto:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Suporte DevIN ·", "DevIN Support ·", "Soporte DevIN ·"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Entrega pendente:", "Delivery pending:", "Entrega pendiente:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["mensagem interna", "in-app message", "mensaje interno"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Mensagens:", "Messages:", "Mensajes:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["enviadas", "sent", "enviados"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["pendentes", "pending", "pendientes"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["enviado", "sent", "enviado"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["pendente", "pending", "pendiente"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["enviada", "sent", "enviada"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Chamado", "Request", "Solicitud"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["enviado com sucesso.", "submitted successfully.", "enviada correctamente."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["aguardando atendimento", "awaiting attention", "pendientes de atención"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["chamados exibidos", "requests shown", "solicitudes mostradas"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["Resposta registrada. Mensagem interna:", "Reply saved. In-app message:", "Respuesta guardada. Mensaje interno:"], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        ["O chamado não existe ou já está fechado.", "This request does not exist or is already closed.", "Esta solicitud no existe o ya está cerrada."], // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    if (language !== 'pt-BR') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const index = language === 'en' ? 1 : 2; // Explicação DevIN: Declara variável JavaScript index para reutilizar valor ou resultado.
        Object.assign(dictionary, Object.fromEntries(supportTranslations.map(item => [item[0], item[index]]))); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const translate = (value) => { // Explicação DevIN: Declara variável JavaScript translate para reutilizar valor ou resultado.
        const raw = String(value ?? ''); // Explicação DevIN: Declara variável JavaScript raw para reutilizar valor ou resultado.
        const trimmed = raw.trim(); // Explicação DevIN: Declara variável JavaScript trimmed para reutilizar valor ou resultado.
        const normalized = trimmed.replace(/\s+/g, ' '); // Explicação DevIN: Declara variável JavaScript normalized para reutilizar valor ou resultado.
        let translated = dictionary[trimmed] ?? dictionary[normalized]; // Explicação DevIN: Declara variável JavaScript translated para reutilizar valor ou resultado.
        if (translated === undefined && language !== 'pt-BR') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const countdown = trimmed.match(/^O jogo começa em (\d+)\.\.\.$/); // Explicação DevIN: Declara variável JavaScript countdown para reutilizar valor ou resultado.
            const gameOver = trimmed.match(/^Fim de jogo — (\d+) pontos\. Reinicie para jogar novamente\.$/); // Explicação DevIN: Declara variável JavaScript gameOver para reutilizar valor ou resultado.
            const score = trimmed.match(/^Sua pontuação: (\d+) pontos · Fase (\d+)$/); // Explicação DevIN: Declara variável JavaScript score para reutilizar valor ou resultado.
            const highestLevel = trimmed.match(/^Maior fase registrada: (\d+)\.$/); // Explicação DevIN: Declara variável JavaScript highestLevel para reutilizar valor ou resultado.
            const leaderboardScore = trimmed.match(/^(\d+) pts · fase (\d+)$/); // Explicação DevIN: Declara variável JavaScript leaderboardScore para reutilizar valor ou resultado.
            const removeProgress = trimmed.match(/^Remover o progresso de (.+) em (.+)\?$/); // Explicação DevIN: Declara variável JavaScript removeProgress para reutilizar valor ou resultado.
            const connectedAs = trimmed.match(/^Conectado como (.+)$/); // Explicação DevIN: Declara variável JavaScript connectedAs para reutilizar valor ou resultado.

            if (countdown) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Game starts in ${countdown[1]}...` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `El juego comienza en ${countdown[1]}...`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (gameOver) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Game over — ${gameOver[1]} points. Restart to play again.` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `Fin del juego — ${gameOver[1]} puntos. Reinicia para jugar de nuevo.`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (score) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Your score: ${score[1]} points · Level ${score[2]}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `Tu puntuación: ${score[1]} puntos · Nivel ${score[2]}`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (highestLevel) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Highest level recorded: ${highestLevel[1]}.` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `Nivel máximo registrado: ${highestLevel[1]}.`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (leaderboardScore) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `${leaderboardScore[1]} pts · level ${leaderboardScore[2]}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `${leaderboardScore[1]} pts · nivel ${leaderboardScore[2]}`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (removeProgress) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Remove ${removeProgress[1]}'s progress in ${removeProgress[2]}?` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `¿Eliminar el progreso de ${removeProgress[1]} en ${removeProgress[2]}?`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } else if (connectedAs) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translated = language === 'en' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? `Signed in as ${connectedAs[1]}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : `Conectado como ${connectedAs[1]}`; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const count = trimmed.match(/^(\d+)\s+(candidatos?|vagas?|candidaturas?|posts?|reports?|registros?|mensagens não lidas)$/); // Explicação DevIN: Declara variável JavaScript count para reutilizar valor ou resultado.
        if (translated === undefined && count && language !== 'pt-BR') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const amount = Number(count[1]); // Explicação DevIN: Declara variável JavaScript amount para reutilizar valor ou resultado.
            const item = count[2]; // Explicação DevIN: Declara variável JavaScript item para reutilizar valor ou resultado.
            const countTranslations = { // Explicação DevIN: Declara variável JavaScript countTranslations para reutilizar valor ou resultado.
                en: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidato: amount === 1 ? 'candidate' : 'candidates', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidatos: amount === 1 ? 'candidate' : 'candidates', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    vaga: amount === 1 ? 'job' : 'jobs', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    vagas: amount === 1 ? 'job' : 'jobs', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidatura: amount === 1 ? 'application' : 'applications', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidaturas: amount === 1 ? 'application' : 'applications', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    post: amount === 1 ? 'post' : 'posts', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    posts: amount === 1 ? 'post' : 'posts', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    report: amount === 1 ? 'report' : 'reports', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    reports: amount === 1 ? 'report' : 'reports', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    registros: amount === 1 ? 'record' : 'records', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    'mensagens não lidas': amount === 1 ? 'unread message' : 'unread messages' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                es: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidato: amount === 1 ? 'candidato' : 'candidatos', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidatos: amount === 1 ? 'candidato' : 'candidatos', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    vaga: amount === 1 ? 'empleo' : 'empleos', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    vagas: amount === 1 ? 'empleo' : 'empleos', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidatura: amount === 1 ? 'solicitud' : 'solicitudes', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    candidaturas: amount === 1 ? 'solicitud' : 'solicitudes', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    post: amount === 1 ? 'publicación' : 'publicaciones', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    posts: amount === 1 ? 'publicación' : 'publicaciones', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    report: amount === 1 ? 'denuncia' : 'denuncias', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    reports: amount === 1 ? 'denuncia' : 'denuncias', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    registros: amount === 1 ? 'registro' : 'registros', // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                    'mensagens não lidas': amount === 1 ? 'mensaje sin leer' : 'mensajes sin leer' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            translated = `${count[1]} ${countTranslations[language][item]}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (translated !== undefined) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return raw.replace(trimmed, translated); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return raw; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    window.DevINTranslate = translate; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    const translatedTitle = translate(document.title); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (translatedTitle !== document.title) document.title = translatedTitle; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    if (language === 'pt-BR') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const translatedAttributes = ['placeholder', 'aria-label', 'title', 'alt', 'data-confirm-logout']; // Explicação DevIN: Declara variável JavaScript translatedAttributes para reutilizar valor ou resultado.
    const translateTextNode = (node) => { // Explicação DevIN: Declara variável JavaScript translateTextNode para reutilizar valor ou resultado.
        if (node.parentElement && node.parentElement.closest('script, style, textarea, input, [data-support-root]')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const translated = translate(node.nodeValue); // Explicação DevIN: Declara variável JavaScript translated para reutilizar valor ou resultado.
        if (translated !== node.nodeValue) node.nodeValue = translated; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const translateAttributes = (element) => { // Explicação DevIN: Declara variável JavaScript translateAttributes para reutilizar valor ou resultado.
        const elements = [element, ...element.querySelectorAll( // Explicação DevIN: Declara variável JavaScript elements para reutilizar valor ou resultado.
            translatedAttributes.map((attribute) => `[${attribute}]`).join(',') // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        )]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        elements.forEach((target) => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
            if (target.closest?.('[data-support-root]')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            for (const attribute of translatedAttributes) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (!target.hasAttribute(attribute)) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const value = target.getAttribute(attribute); // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
                const translated = translate(value); // Explicação DevIN: Declara variável JavaScript translated para reutilizar valor ou resultado.
                if (translated !== value) target.setAttribute(attribute, translated); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const translateElement = (element) => { // Explicação DevIN: Declara variável JavaScript translateElement para reutilizar valor ou resultado.
        if (element.closest?.('[data-support-root]')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        let node; // Explicação DevIN: Declara variável JavaScript node para reutilizar valor ou resultado.
        while ((node = walker.nextNode())) translateTextNode(node); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        translateAttributes(element); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    translateElement(document.body); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    const observer = new MutationObserver((mutations) => { // Explicação DevIN: Declara variável JavaScript observer para reutilizar valor ou resultado.
        for (const mutation of mutations) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (mutation.type === 'characterData') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                translateTextNode(mutation.target); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            mutation.addedNodes.forEach((node) => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                if (node.nodeType === Node.TEXT_NODE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    translateTextNode(node); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                } else if (node.nodeType === Node.ELEMENT_NODE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    translateElement(node); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (mutation.type === 'attributes' && translatedAttributes.includes(mutation.attributeName)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const element = mutation.target; // Explicação DevIN: Declara variável JavaScript element para reutilizar valor ou resultado.
                if (element.closest?.('[data-support-root]')) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const value = element.getAttribute(mutation.attributeName); // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
                const translated = translate(value); // Explicação DevIN: Declara variável JavaScript translated para reutilizar valor ou resultado.
                if (translated !== value) element.setAttribute(mutation.attributeName, translated); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    observer.observe(document.body, { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        subtree: true, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        childList: true, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        characterData: true, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        attributes: true, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
        attributeFilter: translatedAttributes // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo da.
