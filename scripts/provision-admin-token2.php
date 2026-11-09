<?php
declare(strict_types=1);

// Atribuição automática do Token 2 foi substituída pela configuração feita
// individualmente pelo ADM; impedimos atribuição acidental pelo operador.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
fwrite(STDERR, "Comando descontinuado: cada ADM configura seu próprio Token 2.\n");
fwrite(STDERR, "Para emitir um código individual temporário, use:\n");
fwrite(STDERR, "php scripts/issue-admin-token2-activation.php <id_administrador>\n");
exit(2);
