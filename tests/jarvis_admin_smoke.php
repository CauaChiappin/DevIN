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
$free = ModelRouter::candidates('openrouter', 'z-ai/glm-5.3-flash:free', 'outro/modelo:free');
checkJarvis($free === ['z-ai/glm-5.3-flash:free', 'outro/modelo:free'], 'fallback gratuito configurado');
checkJarvis(
    ModelRouter::candidates('openrouter', 'openrouter/free', 'modelo-pago') === ['openrouter/free'],
    'não ativar fallback pago automaticamente'
);
checkJarvis(ModelRouter::candidates('openrouter', 'modelo', 'https://site-malicioso/') === ['modelo'], 'bloquear URLs');
checkJarvis(ModelRouter::shouldFallback(429) && ModelRouter::shouldFallback(503), 'retry apenas transitório');
checkJarvis(!ModelRouter::shouldFallback(401) && !ModelRouter::shouldFallback(403), 'não repetir falhas de credenciais');

echo "JARVIS Admin smoke: OK\n";
