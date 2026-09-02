# Checkpoint

## TodoCheckpointDraft

- Completed: inventário, lint inicial de PHP, descoberta de referências, confirmação da duplicação em `php/cadastro_empresa.php` e caminhos de imagens inexistentes.
- Active slice: diagnóstico final e correção do cadastro/fluxos compartilhados.
- Next step: consolidar `php/cadastro_empresa.php`, corrigir `empresa.php`, referências inválidas e contratos dos formulários.
- Blockers: nenhum bloqueio de edição; schema SQL não está versionado.

## Evidence

- `C:\xampp\php\php.exe -l` passou em todos os PHP próprios e no PHPMailer.
- Balanceamento básico de chaves/parênteses passou nos CSS e JS.
- `img/favicon.svg`, `img/favicon.png` e `img/julio.jpg` não existem; `img/julio.jpeg` existe.
- `php/cadastro_empresa.php` contém um segundo `<?php` dentro da primeira página HTML.

## DriftCheckDraft

- Intent: alinhado.
- Compatibility: preservada como restrição.
- New owner/fallback: nenhum planejado.
- Retirement: duplicação e refs quebradas serão removidas.
- Decision: continue.
