<?php
declare(strict_types=1);

// Rota legada sem cadastro de credenciais: redireciona apenas acessos GET.
// Todas as mudanças de Token 2 acontecem exclusivamente em JarvisT.php.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}
header('Cache-Control: no-store');
header('Location: JarvisT.php', true, 303);
exit;
