<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
// Testa traduções sem enviar dados de usuários para modelos externos.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisLocale.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
foreach (['pt-BR','en','es'] as $language) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $instruction=JarvisLocale::instruction($language); // Explicação DevIN: Atribui um valor à variável PHP $instruction.
    if (!str_contains($instruction,'ENVIAR/EXCLUIR')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('A tradução não pode mudar a confirmação administrativa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $summary=JarvisLocale::summary($language,['Candidatos'=>3,'Empresas'=>2,'Vagas'=>8]); // Explicação DevIN: Atribui um valor à variável PHP $summary.
    if (!str_contains($summary,': 3') || !str_contains($summary,': 2')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Métricas foram perdidas na tradução.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($language==='en' && !str_contains($summary,'Candidates')) throw new RuntimeException('Inglês indisponível'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if ($language==='es' && !str_contains($summary,'Vacantes')) throw new RuntimeException('Espanhol indisponível'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (JarvisLocale::normalize('xx')!=='pt-BR') throw new RuntimeException('Fallback inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
echo "Jarvis locales: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
