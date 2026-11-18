<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Verificação do acesso secreto e do isolamento da sessão.
require_once __DIR__ . '/../php/controllers/ArcadeAccess.php'; // Verificação do acesso secreto e do isolamento da sessão.

function expectArcade(bool $condition, string $message): void // Verificação do acesso secreto e do isolamento da sessão.
{ // Verificação do acesso secreto e do isolamento da sessão.
    if (!$condition) { throw new RuntimeException($message); } // Verificação do acesso secreto e do isolamento da sessão.
} // Verificação do acesso secreto e do isolamento da sessão.
$_SESSION = []; // Verificação do acesso secreto e do isolamento da sessão.
$user = ['id' => 9, 'tipo' => 'pessoa']; // Verificação do acesso secreto e do isolamento da sessão.
expectArcade(!ArcadeAccess::isUnlocked($user), 'Sem liberação'); // Verificação do acesso secreto e do isolamento da sessão.
ArcadeAccess::grant($user); // Verificação do acesso secreto e do isolamento da sessão.
expectArcade(ArcadeAccess::isUnlocked($user), 'Após liberação'); // Verificação do acesso secreto e do isolamento da sessão.
expectArcade(!ArcadeAccess::isUnlocked(['id' => 10, 'tipo' => 'pessoa']), 'Outra conta'); // Verificação do acesso secreto e do isolamento da sessão.
ArcadeAccess::grant($user); // Verificação do acesso secreto e do isolamento da sessão.
$_SESSION['arcade_unlock']['liberado_em'] = time() - 7200; // Verificação do acesso secreto e do isolamento da sessão.
expectArcade(!ArcadeAccess::isUnlocked($user), 'Expirou'); // Verificação do acesso secreto e do isolamento da sessão.
ArcadeAccess::grant($user); // Verificação do acesso secreto e do isolamento da sessão.
expectArcade(!ArcadeAccess::isUnlocked(['id' => 9, 'tipo' => 'empresa']), 'Outro tipo de perfil'); // Verificação do acesso secreto e do isolamento da sessão.
echo "ArcadeAccess OK\n"; // Verificação do acesso secreto e do isolamento da sessão.
