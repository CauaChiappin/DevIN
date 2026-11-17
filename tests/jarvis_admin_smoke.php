<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

// Smoke checks sem banco, rede ou credenciais.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/AdminSkillManager.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ModelRouter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/JobLanguageAudit.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisAdminRecords.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisModeration.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Security/AdminRolePolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function checkJarvis(bool $condition, string $message): void // Explicação DevIN: Declara função/método checkJarvis, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Falha no teste JARVIS Admin: ' . $message); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$selected = AdminSkillManager::select('Ajude a revisar denúncia de uma vaga suspeita'); // Explicação DevIN: Atribui um valor à variável PHP $selected.
$ids = array_column($selected, 'id'); // Explicação DevIN: Atribui um valor à variável PHP $ids.
checkJarvis(count($selected) <= 2, 'no máximo duas habilidades por pergunta'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(in_array('reports', $ids, true), 'detecção de denúncia com acento'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(in_array('jobs', $ids, true), 'detecção de vaga'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminSkillManager::select('Olá, tudo bem?') === [], 'saudação não ativa habilidades'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminSkillManager::select('Analise o currículo do candidato')[0]['id'] === 'people', 'suporte a usuários'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$context = AdminSkillManager::prompt($selected); // Explicação DevIN: Atribui um valor à variável PHP $context.
checkJarvis(str_contains($context, 'Gerenciador administrativo ativo'), 'gerenciador sempre participa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(count(AdminSkillManager::labels($selected)) === count($selected), 'exibe apenas skills selecionadas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.

$single = ModelRouter::candidates('groq', 'modelo-primario', 'modelo-secundario:free'); // Explicação DevIN: Atribui um valor à variável PHP $single.
checkJarvis($single === ['modelo-primario'], 'não alternar em provedor diferente de OpenRouter'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$default = ModelRouter::candidates('openrouter', 'z-ai/glm-5.3-flash:free'); // Explicação DevIN: Atribui um valor à variável PHP $default.
checkJarvis(count($default) === 11, 'modelo principal e 10 reservas padrão'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis($default[10] === 'openrouter/free', 'roteador gratuito como último recurso'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(count(array_unique($default)) === count($default), 'nenhum modelo duplicado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(array_reduce($default, static fn(bool $ok, string $model): bool => $ok && ModelRouter::isFree($model), true), 'todos gratuitos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$singleBackup = ModelRouter::candidates('openrouter', 'z-ai/glm-5.3-flash:free', 'google/gemma-4-31b-it:free'); // Explicação DevIN: Atribui um valor à variável PHP $singleBackup.
checkJarvis($singleBackup === ['z-ai/glm-5.3-flash:free', 'google/gemma-4-31b-it:free'], 'compatibilidade com fallback legado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$badLegacy = ModelRouter::candidates('openrouter', 'openrouter/free', 'sk-or-v1-simulated'); // Explicação DevIN: Atribui um valor à variável PHP $badLegacy.
checkJarvis(count($badLegacy) === 10 && !in_array('sk-or-v1-simulated', $badLegacy, true), 'ignora chave colada em campo de modelo e mantém reservas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$config = ModelRouter::candidates('openrouter', 'openrouter/free', '', 'google/gemma-4-31b-it:free,openai/gpt-6,google/gemma-4-31b-it:free,liquid/lfm-3-1.7b:free'); // Explicação DevIN: Atribui um valor à variável PHP $config.
checkJarvis($config === ['openrouter/free','google/gemma-4-31b-it:free','liquid/lfm-3-1.7b:free'], 'CSV filtra modelos pagos, inválidos e repetidos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(ModelRouter::shouldTrySecondKey(401) && ModelRouter::shouldTrySecondKey(402) // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    && ModelRouter::shouldTrySecondKey(429) && ModelRouter::shouldTrySecondKey(503), 'reserva por credencial/cota/indisponibilidade'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
checkJarvis(!ModelRouter::shouldTrySecondKey(400) && !ModelRouter::shouldTrySecondKey(404), 'não repetir falhas de pedido/modelo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    ModelRouter::candidates('openrouter', 'openai/gpt-6'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    throw new RuntimeException('Modelo pago passou pela validação.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} catch (InvalidArgumentException $expected) {} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.


checkJarvis( // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    JobLanguageAudit::requested('VERIFIQUE SE HA VAGAS COM O LINGUAJAR OFENSIVO/INAPROPRIAO'), // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'reconhece pedido real do ADM incluindo grafia informal' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
checkJarvis( // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    JobLanguageAudit::requested('Verifique vagas ofensivas'), // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'reconhece ordem direta de varredura' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
checkJarvis( // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    !JobLanguageAudit::requested('Como escrever uma vaga para desenvolvedor?'), // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'nao intercepta conversa normal sobre vagas' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
checkJarvis( // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    JobLanguageAudit::reviewText('Vaga para backend', 'Obrigatório saber PHP e MySQL.') === [], // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'requisitos profissionais não devem ser tratados como ofensa' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$flagged = JobLanguageAudit::reviewText('Vaga restrita', 'Apenas homens. Candidato idiota não serve.'); // Explicação DevIN: Atribui um valor à variável PHP $flagged.
checkJarvis(count($flagged) >= 2, 'sinaliza exclusao explicita e insultos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
$formatted = JobLanguageAudit::format([ // Explicação DevIN: Atribui um valor à variável PHP $formatted.
    'total' => 2, 'analisadas' => 2, 'sinalizadas' => 1, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'amostra' => [['id' => 4, 'titulo' => 'Vaga', 'indicadores' => $flagged]], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'parcial' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
checkJarvis(str_contains($formatted, 'Vaga #4') // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    && str_contains($formatted, 'revisão humana') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && str_contains($formatted, 'Nenhuma vaga foi editada'), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    'relatorio com evidencias e aviso de revisao humana'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
// A matriz nunca autoriza exclusões para perfis sem poder explícito.
checkJarvis(!AdminRolePolicy::can('consulta','admin_delete_vaga'), 'consulta sem exclusao'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminRolePolicy::can('moderacao','admin_delete_vaga'), 'moderacao de vaga'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(!AdminRolePolicy::can('moderacao','admin_delete_empresa'), 'moderador nao exclui empresa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminRolePolicy::can('gestao','admin_delete_empresa'), 'gestao pode excluir empresa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(!AdminRolePolicy::can('desconhecido','admin_delete_vaga'), 'cargo desconhecido sem acesso'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminRolePolicy::can('moderacao','jarvis_analyze'), 'moderador pode analisar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(!AdminRolePolicy::can('moderacao','jarvis_send'), 'moderador nao envia comunicacao'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(AdminRolePolicy::can('gestao','jarvis_send'), 'gestor pode aprovar comunicacao'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.

checkJarvis(AdminRolePolicy::can('consulta','update_profile'), 'ADM altera somente perfil proprio'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.

checkJarvis(JarvisModeration::validKind('vaga'), 'permitir alvo vaga'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(JarvisModeration::validKind('empresa'), 'permitir alvo empresa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(JarvisModeration::validKind('pessoa'), 'permitir alvo pessoa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(!JarvisModeration::validKind('administrador'), 'impedir exclusao de ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(!JarvisModeration::validKind('reports_vagas'), 'impedir exclusao fora das 3 categorias'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
checkJarvis(JarvisAdminRecords::suggestionScore('Desenvolvedor PHP', 'Desenvolvedor PHP')===100, // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'ranking da vaga com titulo exato'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
checkJarvis(JarvisAdminRecords::suggestionScore('Desenvolvedor', 'Desenvolvedor PHP')===85, // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'ranking de vaga que comeca com o termo'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
checkJarvis(JarvisAdminRecords::suggestionScore('PHP', 'Desenvolvedor PHP')===70, // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'ranking de trecho do titulo'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
checkJarvis(JarvisAdminRecords::suggestionScore('DevIN', 'Estágio PHP', 'DevIN')===65, // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'ranking da empresa associada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
checkJarvis(JarvisAdminRecords::suggestionScore('Estagiário', 'Vaga para contabilidade')===0, // Explicação DevIN: Continua expressão ou bloco pertencente a função checkJarvis.
    'nao inventar coincidencias'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
echo "JARVIS Admin smoke: OK\\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
