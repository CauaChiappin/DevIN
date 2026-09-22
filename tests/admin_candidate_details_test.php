<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/helpers.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . "\nEsperado: " . var_export($expected, true)
            . "\nRecebido: " . var_export($actual, true)
        );
    }
}

$details = dashboardCandidateDetails([
    'nome' => 'Marina Silva',
    'nome_social' => 'Marina Alves',
    'email' => 'marina@example.com',
    'cpf' => '12345678901',
    'grau_de_escolaridade' => 'Ensino superior completo',
    'cursos' => "PHP, MySQL\nLaravel",
    'idiomas' => 'Português nativo; Inglês intermediário',
    'experiencia' => 'Desenvolvedora web na DevIN (2024 - atual)',
]);

assertSameValue('Marina Alves', $details['name'], 'Usa o nome social do currículo quando informado.');
assertSameValue(
    'E-mail: marina@example.com | CPF: 12345678901 | Escolaridade: Ensino superior completo',
    $details['summary'],
    'Mostra a escolaridade do currículo no resumo.'
);
assertSameValue(
    'PHP|MySQL|Laravel|Português nativo|Inglês intermediário',
    $details['tags'],
    'Expõe cursos e idiomas do currículo como habilidades separadas.'
);
assertSameValue(
    'Experiência profissional::Desenvolvedora web na DevIN (2024 - atual)',
    $details['experience'],
    'Expõe a experiência profissional cadastrada no currículo.'
);

$missingDetails = dashboardCandidateDetails([
    'nome' => 'João Souza',
    'email' => 'joao@example.com',
    'cpf' => '10987654321',
]);

assertSameValue('João Souza', $missingDetails['name'], 'Mantém o nome da conta sem nome social.');
assertSameValue('Nenhuma habilidade informada', $missingDetails['tags'], 'Indica quando o currículo não possui habilidades.');
assertSameValue(
    'Experiência profissional::Nenhuma experiência informada.',
    $missingDetails['experience'],
    'Indica quando o currículo não possui experiência.'
);

echo "OK: detalhes de currículo do candidato\n";
