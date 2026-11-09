<?php
declare(strict_types=1);
// Montado pelo dashboard após autenticação ADM.
$toolsEnabled = (string) ($_ENV['DEVIN_JARVIS_ACTIONS_ENABLED']
    ?? $_SERVER['DEVIN_JARVIS_ACTIONS_ENABLED']
    ?? getenv('DEVIN_JARVIS_ACTIONS_ENABLED') ?: '0') === '1';
?>
<section class="jarvis-admin" id="jarvisAdmin" data-endpoint="api/jarvis/chat.php" data-conversations="api/jarvis/conversations.php" data-tasks="api/jarvis/tasks.php" data-tools="api/jarvis/admin_tools.php" data-csrf="<?= h(csrfToken()) ?>" data-page="<?= h($pagina) ?>">
    <button class="jarvis-admin__launcher" type="button" aria-label="Abrir JARVIS Admin" aria-controls="jarvisAdminPanel" aria-expanded="false" data-jarvis-toggle><span aria-hidden="true">✦</span><span class="jarvis-admin__launcher-label">JARVIS</span></button>
    <section class="jarvis-admin__panel" id="jarvisAdminPanel" role="dialog" aria-modal="false" aria-label="Assistente JARVIS Admin" hidden>
        <header class="jarvis-admin__header">
            <span class="jarvis-admin__mark" aria-hidden="true">✦</span>
            <div class="jarvis-admin__identity"><strong>JARVIS Admin</strong><small>Copiloto administrativo · DevIN</small></div>
            <button type="button" class="jarvis-admin__icon-button" aria-label="Expandir chat" title="Expandir chat" data-jarvis-expand>⛶</button>
            <button type="button" class="jarvis-admin__icon-button" aria-label="Fechar chat" data-jarvis-close>×</button>
        </header>
        <nav class="jarvis-admin__tabs" aria-label="Seções do JARVIS">
            <button type="button" class="is-active" aria-pressed="true" data-jarvis-tab="chat">Conversas</button>
            <button type="button" aria-pressed="false" data-jarvis-tab="tasks">Atividades</button>
            <button type="button" aria-pressed="false" data-jarvis-tab="lookup" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Consultar</button>
            <button type="button" aria-pressed="false" data-jarvis-tab="outbox" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Mensagens</button>
            <button type="button" aria-pressed="false" data-jarvis-tab="audit" <?= $toolsEnabled ? '' : 'disabled title="Exige instalação da migration e ativação no servidor"' ?>>Histórico</button>
        </nav>
        <section class="jarvis-admin__pane jarvis-admin__pane--chat" data-jarvis-pane="chat" aria-label="Conversas">
            <div class="jarvis-admin__conversation-bar">
                <select aria-label="Escolher conversa" data-jarvis-conversation><option value="">Nova conversa</option></select>
                <button type="button" title="Nova conversa" aria-label="Nova conversa" data-jarvis-new>＋</button>
                <button type="button" title="Excluir conversa atual" aria-label="Excluir conversa atual" data-jarvis-delete>⌫</button>
            </div>
            <label class="jarvis-admin__search-wrap"><span class="jarvis-admin__sr-only">Pesquisar conversas</span><input type="search" maxlength="80" placeholder="Buscar palavras no histórico…" data-jarvis-search></label>
            <div class="jarvis-admin__messages" role="log" aria-live="polite" aria-label="Mensagens do JARVIS" data-jarvis-messages>
                <p class="jarvis-admin__welcome" data-jarvis-welcome>Olá! Suas conversas ficam salvas para continuar no computador ou celular. O JARVIS ainda não executa alterações administrativas.</p>
            </div>
            <div class="jarvis-admin__quick-actions"><button type="button" data-jarvis-summary>Resumo do DevIN</button></div>
            <label class="jarvis-admin__context-option" title="Somente com sua autorização, mensagens anteriores desta conversa serão enviadas ao modelo externo."><input type="checkbox" data-jarvis-use-history><span>Usar mensagens anteriores como contexto da IA</span></label>
            <p class="jarvis-admin__status" role="status" aria-live="polite" data-jarvis-status></p>
            <form class="jarvis-admin__composer" data-jarvis-form>
                <label class="jarvis-admin__sr-only" for="jarvisAdminInput">Mensagem para o JARVIS</label>
                <textarea id="jarvisAdminInput" rows="1" maxlength="3000" placeholder="Pergunte ao JARVIS..." data-jarvis-input></textarea>
                <button type="submit" aria-label="Enviar mensagem" data-jarvis-send>➤</button>
            </form>
        </section>
        <section class="jarvis-admin__pane jarvis-admin__pane--tasks" data-jarvis-pane="tasks" aria-label="Central de atividades" hidden>
            <p class="jarvis-admin__tasks-info">Organize atividades e decisões. Marcar uma tarefa como concluída <strong>não executa alterações</strong> no DevIN.</p>
            <div class="jarvis-admin__examples" aria-label="Exemplos de atividades">
                <span>Experimente criar uma atividade:</span>
                <button type="button" data-jarvis-task-example="reports">Analisar denúncias</button>
                <button type="button" data-jarvis-task-example="jobs">Revisar vagas</button>
                <button type="button" data-jarvis-task-example="metrics">Preparar relatório</button>
            </div>
            <form class="jarvis-admin__task-form" data-jarvis-task-form>
                <label>Nova atividade<input type="text" maxlength="150" required placeholder="Ex.: Analisar denúncias pendentes" data-jarvis-task-title></label>
                <label>Descrição (opcional)<textarea rows="2" maxlength="1200" placeholder="O que precisa ser verificado?" data-jarvis-task-description></textarea></label>
                <button type="submit" data-jarvis-task-add>Adicionar à fila</button>
            </form>
            <p class="jarvis-admin__status" role="status" aria-live="polite" data-jarvis-task-status></p>
            <div class="jarvis-admin__tasks-list" data-jarvis-tasks-list></div>
        </section>
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="lookup" aria-label="Consultas controladas" hidden>
            <p class="jarvis-admin__tasks-info">Buscas administrativas limitadas. A IA não exclui nem bloqueia contas.</p>
            <form class="jarvis-admin__tool-form" data-jarvis-lookup-form>
                <label>Pesquisar em
                    <select data-jarvis-record-kind>
                        <option value="vaga">Vagas</option>
                        <option value="pessoa">Candidatos</option>
                        <option value="empresa">Empresas</option>
                        <option value="reports_vagas">Denúncias de vagas</option>
                        <option value="reports_perfis">Denúncias de perfis</option>
                    </select>
                </label>
                <label>Nome, título ou ID
                    <input type="search" maxlength="100" placeholder="Pesquisar registros" data-jarvis-record-query>
                </label>
                <button type="submit">Consultar registros</button>
            </form>
            <p class="jarvis-admin__status" role="status" data-jarvis-lookup-status></p>
            <div class="jarvis-admin__tool-results" data-jarvis-record-results></div>
            <label class="jarvis-admin__tool-question">Pergunta para análise (opcional)
                <textarea rows="2" maxlength="450" placeholder="O que a IA deve verificar?" data-jarvis-record-question></textarea>
            </label>
            <div class="jarvis-admin__analysis" role="status" data-jarvis-analysis></div>
        </section>
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="outbox" aria-label="Mensagens oficiais" hidden>
            <p class="jarvis-admin__tasks-info">O JARVIS prepara mensagens individuais em nome de Support DevIN. Nenhum envio acontece sem revisão e aprovação explícita do ADM. Limite: 15 por ADM/dia e 2 por destinatário/dia.</p>
            <form class="jarvis-admin__tool-form" data-jarvis-compose-form>
                <label>Destinatário
                    <select data-jarvis-recipient-type>
                        <option value="pessoa">Candidato</option>
                        <option value="empresa">Empresa</option>
                    </select>
                </label>
                <label>ID do destinatário
                    <input type="number" min="1" step="1" required data-jarvis-recipient-id placeholder="ID do perfil">
                </label>
                <label>Orientação para o JARVIS
                    <textarea rows="2" maxlength="450" placeholder="Escreva uma mensagem educada sobre..." data-jarvis-brief></textarea>
                </label>
                <button type="submit">Gerar rascunho com IA</button>
            </form>
            <p class="jarvis-admin__status" role="status" data-jarvis-outbox-status></p>
            <div class="jarvis-admin__tool-results" data-jarvis-outbox-list></div>
        </section>
        <section class="jarvis-admin__pane jarvis-admin__pane--tools" data-jarvis-pane="audit" aria-label="Histórico de ações" hidden>
            <p class="jarvis-admin__tasks-info">Histórico de operações realizadas ou preparadas pelo JARVIS e aprovadas por administradores. Não há comandos automáticos de banimento ou exclusão.</p>
            <button type="button" class="jarvis-admin__tool-refresh" data-jarvis-audit-refresh>Atualizar histórico</button>
            <p class="jarvis-admin__status" role="status" data-jarvis-audit-status></p>
            <div class="jarvis-admin__tool-results" data-jarvis-audit-list></div>
        </section>
        <p class="jarvis-admin__disclaimer">IA experimental. Não envie senhas, tokens ou dados pessoais desnecessários. Mensagens oficiais exigem aprovação humana.</p>
    </section>
</section>
