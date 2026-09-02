$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$html = Get-Content -Raw (Join-Path $root 'html/index.html')
$css = Get-Content -Raw (Join-Path $root 'css/style.css')

if ($html -notmatch 'href="\.\./css/style\.css"') {
    throw 'A página inicial não referencia css/style.css.'
}

foreach ($selector in '.cabecalho-site', '.principal h1', '.rodape') {
    if ($css -notmatch [regex]::Escape($selector)) {
        throw "O stylesheet da home não contém o seletor obrigatório: $selector"
    }
}

foreach ($fragment in 'class="marca-rodape"', 'class="coluna-rodape"', 'class="linha-rodape"') {
    if ($html -notmatch [regex]::Escape($fragment)) {
        throw "O footer da home nÃ£o contÃ©m a estrutura esperada: $fragment"
    }
}

foreach ($selector in '.marca-rodape h3', '.coluna-rodape a', '.icone-social', '.linha-rodape') {
    if ($css -notmatch [regex]::Escape($selector)) {
        throw "O stylesheet da home nÃ£o contÃ©m o estilo do footer: $selector"
    }
}

if ($css -match [regex]::Escape('.recovery-page')) {
    throw 'O stylesheet da home contém estilos da página de recuperação.'
}

Write-Host 'CSS da página inicial validado.'
