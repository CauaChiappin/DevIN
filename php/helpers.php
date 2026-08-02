<?php
function ativo(string $paginaAtual, string $pagina): string
{
    return $paginaAtual === $pagina ? 'ativo' : '';
}

function h(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/*
 * Monta o avatar reutilizado no menu, no modal e no perfil.
 * Se não houver foto válida, retorna somente o span com o fundo padrão do CSS.
 */
function profileAvatar(array $perfil, string $classes): string
{
    // Pega o caminho salvo no banco; string vazia é usada quando não existe foto.
    $foto = $perfil['foto'] ?? '';
    // Só renderiza a tag img para caminhos de upload existentes; caso contrário, mantém o avatar padrão.
    $imagem = is_string($foto) && str_starts_with($foto, 'uploads/') && is_file(__DIR__ . '/' . $foto)
        // h() escapa o caminho antes de colocá-lo no HTML, evitando injeção de código.
        ? '<img src="' . h($foto) . '" alt="Foto de perfil">'
        : '';

    // Junta as classes visuais e a imagem (quando válida) dentro de um único avatar.
    return '<span class="' . h($classes) . ' current-user-avatar">' . $imagem . '</span>';
}

function dashboardIcon(string $name): string
{
    $paths = match ($name) {
        'brand' => '<path d="M4 5.5h16v13H4z"/><path d="M12 5.5v13"/>',
        'home' => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building' => '<path d="M3 21h18M5 21V5h10v16M15 9h4v12M8 9h4M8 13h4M8 17h4"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-3v-.08A1.7 1.7 0 0 0 10.68 18.36a1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.02 14.7 1.7 1.7 0 0 0 5.46 13.7H5v-3h.08a1.7 1.7 0 0 0 1.56-1.03A1.7 1.7 0 0 0 6.3 7.8l-.06-.06 2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.3 4.46V4h3v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.56 1.03H20v3h-.08A1.7 1.7 0 0 0 18.36 14.36z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>',
        default => '',
    };

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}
?>
