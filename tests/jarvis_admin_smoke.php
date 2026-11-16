<?php
declare(strict_types=1);

// Smoke checks sem banco, rede ou credenciais.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/AdminSkillManager.php';
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ModelRouter.php';

function checkJarvis(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('Falha no teste JARVIS Admin: ' . $message);
    }
}

$selected = AdminSkillManager::select('Ajude a revisar denúncia de uma vaga suspeita');
$ids = array_column($selected, 'id');
checkJarvis(count($selected) <= 2, 'no máximo duas habilidades por pergunta');
checkJarvis(in_array('reports', $ids, true), 'detecção de denúncia com acento');
checkJarvis(in_array('jobs', $ids, true), 'detecção de vaga');
checkJarvis(AdminSkillManager::select('Olá, tudo bem?') === [], 'saudação não ativa habilidades');
checkJarvis(AdminSkillManager::select('Analise o currículo do candidato')[0]['id'] === 'people', 'suporte a usuários');
$context = AdminSkillManager::prompt($selected);
checkJarvis(str_contains($context, 'Gerenciador administrativo ativo'), 'gerenciador sempre participa');
checkJarvis(count(AdminSkillManager::labels($selected)) === count($selected), 'exibe apenas skills selecionadas');

$single = ModelRouter::candidates('groq', 'modelo-primario', 'modelo-secundario:free');
checkJarvis($single === ['modelo-primario'], 'não alternar em provedor diferente de OpenRouter');
$default = ModelRouter::candidates('openrouter', 'z-ai/glm-5.3-flash:free');
checkJarvis(count($default) === 11, 'modelo principal e 10 reservas padrão');
checkJarvis($default[10] === 'openrouter/free', 'roteador gratuito como último recurso');
checkJarvis(count(array_unique($default)) === count($default), 'nenhum modelo duplicado');
checkJarvis(array_reduce($default, static fn(bool $ok, string $model): bool => $ok && ModelRouter::isFree($model), true), 'todos gratuitos');
$singleBackup = ModelRouter::candidates('openrouter', 'z-ai/glm-5.3-flash:free', 'google/gemma-4-31b-it:free');
checkJarvis($singleBackup === ['z-ai/glm-5.3-flash:free', 'google/gemma-4-31b-it:free'], 'compatibilidade com fallback legado');
$badLegacy = ModelRouter::candidates('openrouter', 'openrouter/free', 'sk-or-v1-simulated');
checkJarvis(count($badLegacy) === 10 && !in_array('sk-or-v1-simulated', $badLegacy, true), 'ignora chave colada em campo de modelo e mantém reservas');
$config = ModelRouter::candidates('openrouter', 'openrouter/free', '', 'google/gemma-4-31b-it:free,openai/gpt-6,google/gemma-4-31b-it:free,liquid/lfm-3-1.7b:free');
checkJarvis($config === ['openrouter/free','google/gemma-4-31b-it:free','liquid/lfm-3-1.7b:free'], 'CSV filtra modelos pagos, inválidos e repetidos');
checkJarvis(ModelRouter::shouldTrySecondKey(401) && ModelRouter::shouldTrySecondKey(402)
    && ModelRouter::shouldTrySecondKey(429) && ModelRouter::shouldTrySecondKey(503), 'reserva por credencial/cota/indisponibilidade');
checkJarvis(!ModelRouter::shouldTrySecondKey(400) && !ModelRouter::shouldTrySecondKey(404), 'não repetir falhas de pedido/modelo');
try {
    ModelRouter::candidates('openrouter', 'openai/gpt-6');
    throw new RuntimeException('Modelo pago passou pela validação.');
} catch (InvalidArgumentException $expected) {}

echo "JARVIS Admin smoke: OK\\n";
