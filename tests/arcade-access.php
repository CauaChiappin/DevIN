<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/controllers/ArcadeAccess.php';

function expectArcade(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}
$_SESSION = [];
$user = ['id' => 9, 'tipo' => 'pessoa'];
expectArcade(!ArcadeAccess::isUnlocked($user), 'Sem liberação');
ArcadeAccess::grant($user);
expectArcade(ArcadeAccess::isUnlocked($user), 'Após liberação');
expectArcade(!ArcadeAccess::isUnlocked(['id' => 10, 'tipo' => 'pessoa']), 'Outra conta');
ArcadeAccess::grant($user);
$_SESSION['arcade_unlock']['liberado_em'] = time() - 7200;
expectArcade(!ArcadeAccess::isUnlocked($user), 'Expirou');
ArcadeAccess::grant($user);
expectArcade(!ArcadeAccess::isUnlocked(['id' => 9, 'tipo' => 'empresa']), 'Outro tipo de perfil');
echo "ArcadeAccess OK\n";
