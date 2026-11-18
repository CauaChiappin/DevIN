<?php
declare(strict_types=1);
// Testa traduções sem enviar dados de usuários para modelos externos.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisLocale.php';
foreach (['pt-BR','en','es'] as $language) {
    $instruction=JarvisLocale::instruction($language);
    if (!str_contains($instruction,'ENVIAR/EXCLUIR')) {
        throw new RuntimeException('A tradução não pode mudar a confirmação administrativa.');
    }
    $summary=JarvisLocale::summary($language,['Candidatos'=>3,'Empresas'=>2,'Vagas'=>8]);
    if (!str_contains($summary,': 3') || !str_contains($summary,': 2')) {
        throw new RuntimeException('Métricas foram perdidas na tradução.');
    }
    if ($language==='en' && !str_contains($summary,'Candidates')) throw new RuntimeException('Inglês indisponível');
    if ($language==='es' && !str_contains($summary,'Vacantes')) throw new RuntimeException('Espanhol indisponível');
}
if (JarvisLocale::normalize('xx')!=='pt-BR') throw new RuntimeException('Fallback inválido.');
echo "Jarvis locales: OK\n";
