(() => {
    'use strict';

    const language = ['en', 'es'].includes(document.documentElement.lang) ? document.documentElement.lang : 'pt-BR';
    const translations = {
        'pt-BR': {
            'Início': 'Início',
            'Minhas Vagas': 'Minhas Vagas',
            'Mensagens': 'Mensagens',
            'Sobre nós': 'Sobre nós',
            'Perfil': 'Perfil',
            'Meu perfil': 'Meu perfil',
            'Configurações': 'Configurações',
            'Sair da Conta': 'Sair da Conta',
            'Pesquisar por nome ou habilidade...': 'Pesquisar por nome ou habilidade...',
            'Disponíveis': 'Disponíveis',
            'Idioma': 'Idioma',
            'Português': 'Português',
            'Inglês': 'Inglês',
            'Espanhol': 'Espanhol',
            'Salvar alterações': 'Salvar alterações',
            'Excluir conta': 'Excluir conta',
            'Fechar': 'Fechar',
            'Nome': 'Nome',
            'E-mail': 'E-mail',
            'Celular': 'Celular',
            'CEP': 'CEP',
            'Salvar': 'Salvar',
            'Contatos': 'Contatos',
            'Conversa': 'Conversa',
            'Escolha alguém para conversar': 'Escolha alguém para conversar',
            'Escreva uma mensagem...': 'Escreva uma mensagem...',
            'Enviar': 'Enviar',
            'Processando...': 'Processando...',
            'Nenhum contato encontrado.': 'Nenhum contato encontrado.',
            'Ainda não há outros usuários cadastrados.': 'Ainda não há outros usuários cadastrados.',
            'Atualização automática': 'Atualização automática',
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'Esta conversa ainda não tem mensagens. Envie a primeira!',
            'Excluir esta conversa só para você?': 'Excluir esta conversa só para você?',
            'Excluir esta mensagem para você e para o outro participante?': 'Excluir esta mensagem para você e para o outro participante?',
            'Excluir esta mensagem só para você?': 'Excluir esta mensagem só para você?',
            'Você apagou esta mensagem': 'Você apagou esta mensagem',
            'Esta mensagem foi apagada': 'Esta mensagem foi apagada',
            'Ações da mensagem': 'Ações da mensagem',
            'Excluir para mim': 'Excluir para mim',
            'Excluir para todos': 'Excluir para todos',
            'Nova mensagem de': 'Nova mensagem de',
            'mensagens não lidas.': 'mensagens não lidas.',
            'Tem certeza que deseja sair da sua conta?': 'Tem certeza que deseja sair da sua conta?',
            'Tem certeza? Esta ação exclui sua conta permanentemente.': 'Tem certeza? Esta ação exclui sua conta permanentemente.',
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.'
        },
        en: {
            'Início': 'Home',
            'Minhas Vagas': 'My Jobs',
            'Mensagens': 'Messages',
            'Sobre nós': 'About us',
            'Perfil': 'Profile',
            'Meu perfil': 'My profile',
            'Configurações': 'Settings',
            'Sair da Conta': 'Log out',
            'Pesquisar por nome ou habilidade...': 'Search by name or skill...',
            'Disponíveis': 'Available',
            'Idioma': 'Language',
            'Português': 'Portuguese',
            'Inglês': 'English',
            'Espanhol': 'Spanish',
            'Salvar alterações': 'Save changes',
            'Excluir conta': 'Delete account',
            'Fechar': 'Close',
            'Nome': 'Name',
            'E-mail': 'Email',
            'Celular': 'Mobile phone',
            'CEP': 'ZIP code',
            'Salvar': 'Save',
            'Contatos': 'Contacts',
            'Conversa': 'Conversation',
            'Escolha alguém para conversar': 'Choose someone to chat with',
            'Escreva uma mensagem...': 'Write a message...',
            'Enviar': 'Send',
            'Processando...': 'Processing...',
            'Nenhum contato encontrado.': 'No contacts found.',
            'Ainda não há outros usuários cadastrados.': 'There are no other registered users yet.',
            'Atualização automática': 'Live updates',
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'This conversation has no messages yet. Send the first one!',
            'Excluir esta conversa só para você?': 'Delete this conversation just for you?',
            'Excluir esta mensagem para você e para o outro participante?': 'Delete this message for you and the other participant?',
            'Excluir esta mensagem só para você?': 'Delete this message just for you?',
            'Você apagou esta mensagem': 'You deleted this message',
            'Esta mensagem foi apagada': 'This message was deleted',
            'Ações da mensagem': 'Message actions',
            'Excluir para mim': 'Delete for me',
            'Excluir para todos': 'Delete for everyone',
            'Nova mensagem de': 'New message from',
            'mensagens não lidas.': 'unread messages.',
            'Tem certeza que deseja sair da sua conta?': 'Are you sure you want to log out?',
            'Tem certeza? Esta ação exclui sua conta permanentemente.': 'Are you sure? This will permanently delete your account.',
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Choose a JPG, PNG, or WEBP image up to 5 MB.'
        },
        es: {
            'Início': 'Inicio',
            'Minhas Vagas': 'Mis empleos',
            'Mensagens': 'Mensajes',
            'Sobre nós': 'Sobre nosotros',
            'Perfil': 'Perfil',
            'Meu perfil': 'Mi perfil',
            'Configurações': 'Configuración',
            'Sair da Conta': 'Cerrar sesión',
            'Pesquisar por nome ou habilidade...': 'Buscar por nombre o habilidad...',
            'Disponíveis': 'Disponibles',
            'Idioma': 'Idioma',
            'Português': 'Portugués',
            'Inglês': 'Inglés',
            'Espanhol': 'Español',
            'Salvar alterações': 'Guardar cambios',
            'Excluir conta': 'Eliminar cuenta',
            'Fechar': 'Cerrar',
            'Nome': 'Nombre',
            'E-mail': 'Correo electrónico',
            'Celular': 'Teléfono móvil',
            'CEP': 'Código postal',
            'Salvar': 'Guardar',
            'Contatos': 'Contactos',
            'Conversa': 'Conversación',
            'Escolha alguém para conversar': 'Elige a alguien para conversar',
            'Escreva uma mensagem...': 'Escribe un mensaje...',
            'Enviar': 'Enviar',
            'Processando...': 'Procesando...',
            'Nenhum contato encontrado.': 'No se encontraron contactos.',
            'Ainda não há outros usuários cadastrados.': 'Todavía no hay otros usuarios registrados.',
            'Atualização automática': 'Actualización automática',
            'Esta conversa ainda não tem mensagens. Envie a primeira!': 'Esta conversación aún no tiene mensajes. ¡Envía el primero!',
            'Excluir esta conversa só para você?': '¿Eliminar esta conversación solo para ti?',
            'Excluir esta mensagem para você e para o outro participante?': '¿Eliminar este mensaje para ti y para la otra persona?',
            'Excluir esta mensagem só para você?': '¿Eliminar este mensaje solo para ti?',
            'Você apagou esta mensagem': 'Eliminaste este mensaje',
            'Esta mensagem foi apagada': 'Este mensaje fue eliminado',
            'Ações da mensagem': 'Acciones del mensaje',
            'Excluir para mim': 'Eliminar para mí',
            'Excluir para todos': 'Eliminar para todos',
            'Nova mensagem de': 'Nuevo mensaje de',
            'mensagens não lidas.': 'mensajes sin leer.',
            'Tem certeza que deseja sair da sua conta?': '¿Seguro que quieres cerrar sesión?',
            'Tem certeza? Esta ação exclui sua conta permanentemente.': '¿Estás seguro? Esta acción eliminará tu cuenta permanentemente.',
            'Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.': 'Elige una imagen JPG, PNG o WEBP de hasta 5 MB.'
        }
    };

    const translate = (value) => {
        const raw = String(value ?? '');
        const dictionary = translations[language] ?? translations['pt-BR'];
        const trimmed = raw.trim();
        const translated = dictionary[trimmed];
        if (translated !== undefined) {
            return raw.replace(trimmed, translated);
        }
        return raw;
    };

    window.DevINTranslate = translate;

    if (language === 'pt-BR') return;

    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    let node;
    while ((node = walker.nextNode())) {
        if (node.parentElement && node.parentElement.closest('script, style, textarea, input')) continue;
        node.nodeValue = translate(node.nodeValue);
    }

    document.querySelectorAll('*').forEach((element) => {
        for (const attribute of ['placeholder', 'aria-label', 'title', 'data-confirm-logout']) {
            if (element.hasAttribute(attribute)) {
                element.setAttribute(attribute, translate(element.getAttribute(attribute)));
            }
        }
    });
})();
