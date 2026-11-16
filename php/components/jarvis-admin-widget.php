<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
// Montado pelo dashboard após autenticação ADM.
$toolsEnabled = (string) ($_ENV['DEVIN_JARVIS_ACTIONS_ENABLED'] // Explicação DevIN: Atribui um valor à variável PHP $toolsEnabled.
    ?? $_SERVER['DEVIN_JARVIS_ACTIONS_ENABLED'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ?? getenv('DEVIN_JARVIS_ACTIONS_ENABLED') ?: '0') === '1'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
?>
<section class="jarvis-admin" id="jarvisAdmin" data-endpoint="api/jarvis/chat.php" data-conversations="api/jarvis/conversations.php" data-tasks="api/jarvis/tasks.php" data-tools="api/jarvis/admin_tools.php" data-csrf="<?= h(csrfToken()) ?>" data-page="<?= h($pagina) ?>"> <!-- Explicação DevIN: Declara seção temática. Contém dado dinâmico processado pelo PHP. -->
    <button class="jarvis-admin__launcher" type="button" aria-label="Abrir JarvisDevIN; segure e arraste para mover" title="JarvisDevIN — toque para abrir, segure para mover" aria-controls="jarvisAdminPanel" aria-expanded="false" data-jarvis-toggle><img class="jarvis-admin__jd-image" src="../img/jarvisdevin-jd.webp?v=<?= filemtime(__DIR__ . '/../../img/jarvisdevin-jd.webp') ?>" alt="" draggable="false" aria-hidden="true"></button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
    <section class="jarvis-admin__panel" id="jarvisAdminPanel" role="dialog" aria-modal="false" aria-label="Assistente JarvisDevIn" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
        <header class="jarvis-admin__header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <img class="jarvis-admin__jd-image" src="../img/jarvisdevin-jd.webp?v=<?= filemtime(__DIR__ . '/../../img/jarvisdevin-jd.webp') ?>" alt="" draggable="false" aria-hidden="true"> <!-- Explicação DevIN: Declara imagem na interface. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
            <div class="jarvis-admin__identity"><strong>JarvisDevIn</strong><small>Copiloto administrativo · DevIN</small></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
            <button type="button" class="jarvis-admin__icon-button" aria-label="Expandir chat" title="Expandir chat" data-jarvis-expand>⛶</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <button type="button" class="jarvis-admin__icon-button" aria-label="Fechar chat" data-jarvis-close>×</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
        <nav class="jarvis-admin__tabs" aria-label="Seções do JARVIS"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
            <button type="button" class="is-active" aria-pressed="true" data-jarvis-tab="chat">Conversas</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <button type="button" aria-pressed="false" data-jarvis-tab="tasks">Atividades</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <button type="button" aria-pressed="false" data-jarvis-tab="lookup" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Consultar</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
            <button type="button" aria-pressed="false" data-jarvis-tab="outbox" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Mensagens</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
            <button type="button" aria-pressed="false" data-jarvis-tab="audit" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Histórico</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
        </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
        <section class="jarvis-admin__pane jarvis-admin__pane--chat" data-jarvis-pane="chat" aria-label="Conversas"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <div class="jarvis-admin__conversation-bar"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <select aria-label="Escolher conversa" data-jarvis-conversation><option value="">Nova conversa</option></select> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
                <button type="button" title="Nova conversa" aria-label="Nova conversa" data-jarvis-new>＋</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                <button type="button" title="Excluir conversa atual" aria-label="Excluir conversa atual" data-jarvis-delete>⌫</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <label class="jarvis-admin__search-wrap"><span class="jarvis-admin__sr-only">Pesquisar conversas</span><input type="search" maxlength="80" placeholder="Buscar palavras no histórico…" data-jarvis-search></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
            <div class="jarvis-admin__messages" role="log" aria-live="polite" aria-label="Mensagens do JARVIS" data-jarvis-messages> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                <p class="jarvis-admin__welcome" data-jarvis-welcome>Olá! Suas conversas ficam salvas para continuar no computador ou celular. O JARVIS ainda não executa alterações administrativas.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <div class="jarvis-admin__quick-actions"><button type="button" data-jarvis-summary>Resumo do DevIN</button></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
            <label class="jarvis-admin__context-option" title="Somente com sua autorização, mensagens anteriores desta conversa serão enviadas ao modelo externo."><input type="checkbox" data-jarvis-use-history><span>Usar mensagens anteriores como contexto da IA</span></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
            <p class="jarvis-admin__status" role="status" aria-live="polite" data-jarvis-status></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
            <form class="jarvis-admin__composer" data-jarvis-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <label class="jarvis-admin__sr-only" for="jarvisAdminInput">Mensagem para o JarvisDevIn</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                <textarea id="jarvisAdminInput" rows="1" maxlength="3000" placeholder="Pergunte ao JarvisDevIn..." data-jarvis-input></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
                <button type="submit" aria-label="Enviar mensagem" data-jarvis-send>➤</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <section class="jarvis-admin__pane jarvis-admin__pane--tasks" data-jarvis-pane="tasks" aria-label="Central de atividades" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <p class="jarvis-admin__tasks-info">Organize atividades e decisões. Marcar uma tarefa como concluída <strong>não executa alterações</strong> no DevIN.</p> <!-- Explicação DevIN: Declara parágrafo. A linha reúne múltiplos elementos. -->
            <div class="jarvis-admin__examples" aria-label="Exemplos de atividades"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                <span>Experimente criar uma atividade:</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                <button type="button" data-jarvis-task-example="reports">Analisar denúncias</button> <!-- Explicação DevIN: Declara botão de ação. -->
                <button type="button" data-jarvis-task-example="jobs">Revisar vagas</button> <!-- Explicação DevIN: Declara botão de ação. -->
                <button type="button" data-jarvis-task-example="metrics">Preparar relatório</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <form class="jarvis-admin__task-form" data-jarvis-task-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <label>Nova atividade<input type="text" maxlength="150" required placeholder="Ex.: Analisar denúncias pendentes" data-jarvis-task-title></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <label>Descrição (opcional)<textarea rows="2" maxlength="1200" placeholder="O que precisa ser verificado?" data-jarvis-task-description></textarea></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                <button type="submit" data-jarvis-task-add>Adicionar à fila</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
            <p class="jarvis-admin__status" role="status" aria-live="polite" data-jarvis-task-status></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
            <div class="jarvis-admin__tasks-list" data-jarvis-tasks-list></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="lookup" aria-label="Consultas controladas" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <p class="jarvis-admin__tasks-info">Buscas administrativas limitadas. A IA não exclui nem bloqueia contas.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <form class="jarvis-admin__tool-form" data-jarvis-lookup-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <label>Pesquisar em <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <select data-jarvis-record-kind> <!-- Explicação DevIN: Declara seleção de opções. -->
                        <option value="vaga">Vagas</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="pessoa">Candidatos</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="empresa">Empresas</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="reports_vagas">Denúncias de vagas</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="reports_perfis">Denúncias de perfis</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                    </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <label>Nome, título ou ID <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <input type="search" maxlength="100" placeholder="Pesquisar registros" data-jarvis-record-query> <!-- Explicação DevIN: Declara campo de entrada. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <button type="submit">Consultar registros</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
            <p class="jarvis-admin__status" role="status" data-jarvis-lookup-status></p> <!-- Explicação DevIN: Declara parágrafo. -->
            <div class="jarvis-admin__tool-results" data-jarvis-record-results></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <label class="jarvis-admin__tool-question">Pergunta para análise (opcional) <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                <textarea rows="2" maxlength="450" placeholder="O que a IA deve verificar?" data-jarvis-record-question></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
            </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
            <div class="jarvis-admin__analysis" role="status" data-jarvis-analysis></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="outbox" aria-label="Mensagens oficiais" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <p class="jarvis-admin__tasks-info">O JARVIS prepara mensagens individuais em nome de Support DevIN. Nenhum envio acontece sem revisão e aprovação explícita do ADM. Limite: 15 por ADM/dia e 2 por destinatário/dia.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <form class="jarvis-admin__tool-form" data-jarvis-compose-form> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <label>Destinatário <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <select data-jarvis-recipient-type> <!-- Explicação DevIN: Declara seleção de opções. -->
                        <option value="pessoa">Candidato</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="empresa">Empresa</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                    </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <label>ID do destinatário <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <input type="number" min="1" step="1" required data-jarvis-recipient-id placeholder="ID do perfil"> <!-- Explicação DevIN: Declara campo de entrada. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <label>Orientação para o JARVIS <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <textarea rows="2" maxlength="450" placeholder="Escreva uma mensagem educada sobre..." data-jarvis-brief></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. -->
                </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <button type="submit">Gerar rascunho com IA</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
            <p class="jarvis-admin__status" role="status" data-jarvis-outbox-status></p> <!-- Explicação DevIN: Declara parágrafo. -->
            <div class="jarvis-admin__tool-results" data-jarvis-outbox-list></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="audit" aria-label="Histórico de ações" hidden> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <p class="jarvis-admin__tasks-info">Histórico de operações realizadas ou preparadas pelo JARVIS e aprovadas por administradores. Não há comandos automáticos de banimento ou exclusão.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            <button type="button" class="jarvis-admin__tool-refresh" data-jarvis-audit-refresh>Atualizar histórico</button> <!-- Explicação DevIN: Declara botão de ação. -->
            <p class="jarvis-admin__status" role="status" data-jarvis-audit-status></p> <!-- Explicação DevIN: Declara parágrafo. -->
            <div class="jarvis-admin__tool-results" data-jarvis-audit-list></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <p class="jarvis-admin__disclaimer">IA experimental. Não envie senhas, tokens ou dados pessoais desnecessários. Mensagens oficiais exigem aprovação humana.</p> <!-- Explicação DevIN: Declara parágrafo. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
</section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
