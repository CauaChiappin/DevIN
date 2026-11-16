#!/usr/bin/env python3
"""Explicações inline para o código DevIN.

Uso:
  python3 scripts/line_explanations.py --write
  python3 scripts/line_explanations.py --check
  python3 scripts/line_explanations.py --stats

Não requer API de IA, chave, banco ou pacote externo.
As descrições são comentários nativos e não devem alterar a execução.
"""
from __future__ import annotations # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.

import argparse # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import json # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import re # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import subprocess # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import sys # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
from pathlib import Path # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.

ROOT = Path(__file__).resolve().parents[1] # Explicação DevIN: Atribui resultado à variável Python ROOT.
SUFFIXES = {".php", ".js", ".cjs", ".mjs", ".css", ".html", ".htm", ".sql", # Explicação DevIN: Atribui resultado à variável Python SUFFIXES.
            ".json", ".yml", ".yaml", ".svg", ".xml", ".sh", ".ts", ".tsx", ".py"} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
SPECIAL_FILES = {".env.example", ".htaccess", ".gitignore", "Dockerfile", "Makefile"} # Explicação DevIN: Atribui resultado à variável Python SPECIAL_FILES.
IGNORE_SEGMENTS = {"vendor", "node_modules", ".git", "dist", "build", # Explicação DevIN: Atribui resultado à variável Python IGNORE_SEGMENTS.
                   "__pycache__", "coverage", "third_party"} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
EXCLUDED = {"composer.lock", "package-lock.json", "pnpm-lock.yaml", "yarn.lock"} # Explicação DevIN: Atribui resultado à variável Python EXCLUDED.
NON_COMMENTABLE_SUFFIXES = {".json"} # Explicação DevIN: Atribui resultado à variável Python NON_COMMENTABLE_SUFFIXES.
NON_COMMENTABLE_NAMES = {".gitignore"} # Explicação DevIN: Atribui resultado à variável Python NON_COMMENTABLE_NAMES.
ANNOTATION_MARKER = "Explicação DevIN:" # Explicação DevIN: Atribui resultado à variável Python ANNOTATION_MARKER.
LANGUAGE = { # Explicação DevIN: Atribui resultado à variável Python LANGUAGE.
    ".php": "PHP", ".js": "JavaScript", ".cjs": "JavaScript/CommonJS", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    ".mjs": "JavaScript/ESM", ".css": "CSS", ".html": "HTML", ".htm": "HTML", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    ".sql": "SQL/MySQL", ".json": "JSON", ".yml": "YAML", ".yaml": "YAML", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    ".svg": "SVG/XML", ".xml": "XML", ".sh": "Shell", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    ".ts": "TypeScript", ".tsx": "TypeScript/JSX", ".py": "Python", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
CSS_PROPERTIES = { # Explicação DevIN: Atribui resultado à variável Python CSS_PROPERTIES.
    "display": "modo de layout", "position": "modo de posicionamento", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "width": "largura", "height": "altura", "min-width": "largura mínima", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "max-width": "largura máxima", "min-height": "altura mínima", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "max-height": "altura máxima", "padding": "espaçamento interno", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "margin": "espaçamento externo", "color": "cor do texto", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "background": "fundo", "background-color": "cor de fundo", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "border": "borda", "border-radius": "arredondamento dos cantos", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "box-shadow": "sombra", "font-size": "tamanho da fonte", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "font-family": "família tipográfica", "font-weight": "peso tipográfico", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "line-height": "altura de linha", "gap": "espaçamento entre itens", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "flex": "comportamento de flexibilidade", "flex-direction": "direção dos itens", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "grid-template-columns": "colunas da grade", "grid-template-rows": "linhas da grade", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "align-items": "alinhamento transversal", "justify-content": "distribuição dos itens", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "overflow": "tratamento de conteúdo excedente", "overflow-x": "transbordamento horizontal", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "overflow-y": "transbordamento vertical", "cursor": "aparência do cursor", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "opacity": "opacidade", "transform": "transformação geométrica", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "transition": "transição animada", "z-index": "ordem de sobreposição", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "top": "distância em relação à borda superior", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "bottom": "distância em relação à borda inferior", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "left": "distância em relação à borda esquerda", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "right": "distância em relação à borda direita", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "touch-action": "tratamento de gestos de toque", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "white-space": "tratamento de espaços e quebras de linha", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "word-break": "regras de quebra de palavras", "overflow-wrap": "quebra de texto longo", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "box-sizing": "cálculo das dimensões", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
TAG_MEANING = { # Explicação DevIN: Atribui resultado à variável Python TAG_MEANING.
    "html": "elemento raiz do documento HTML", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "head": "metadados e dependências do documento", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "body": "corpo visível da página", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "main": "conteúdo principal da página", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "header": "cabeçalho da seção ou página", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "footer": "rodapé", "section": "seção temática", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "article": "conteúdo independente", "nav": "navegação", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "aside": "conteúdo auxiliar", "div": "agrupamento de elementos", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "span": "trecho em linha", "p": "parágrafo", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "h1": "título principal", "h2": "subtítulo de segundo nível", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "h3": "subtítulo de terceiro nível", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "form": "formulário de entrada de dados", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "input": "campo de entrada", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "textarea": "campo de texto multilinha", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "select": "seleção de opções", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "option": "opção selecionável", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "button": "botão de ação", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "label": "rótulo associado a um campo", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "a": "link de navegação", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "img": "imagem na interface", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "script": "script ou dependência JavaScript", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "link": "dependência ou relação com arquivo externo", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "meta": "metadado de navegação", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "table": "tabela de dados", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "tr": "linha de tabela", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "td": "célula de dados", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "th": "cabeçalho de coluna ou linha", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "ul": "lista sem numeração", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "li": "item da lista", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "svg": "gráfico vetorial", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "path": "contorno vetorial", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    "circle": "círculo vetorial", # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.


def source_paths() -> list[Path]: # Explicação DevIN: Declara função Python source_paths, com seus argumentos e lógica.
    result = subprocess.run( # Explicação DevIN: Atribui resultado à variável Python result.
        ["git", "ls-files", "-z"], cwd=ROOT, check=True, capture_output=True # Explicação DevIN: Continua expressão ou bloco Python pertencente a função source_paths.
    ) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função source_paths.
    files = [] # Explicação DevIN: Atribui resultado à variável Python files.
    for value in result.stdout.decode("utf-8", "replace").split("\0"): # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        if not value: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        rel = Path(value) # Explicação DevIN: Atribui resultado à variável Python rel.
        if (rel.name in EXCLUDED or rel.name in NON_COMMENTABLE_NAMES # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                or rel.suffix.lower() in NON_COMMENTABLE_SUFFIXES # Explicação DevIN: Continua expressão ou bloco Python pertencente a função source_paths.
                or set(rel.parts) & IGNORE_SEGMENTS): # Explicação DevIN: Continua expressão ou bloco Python pertencente a função source_paths.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if rel.parts[:2] == ("docs", "line-by-line"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if rel.suffix.lower() not in SUFFIXES and rel.name not in SPECIAL_FILES: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        file = ROOT / rel # Explicação DevIN: Atribui resultado à variável Python file.
        if not file.is_file(): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        files.append(rel) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função source_paths.
    return sorted(files, key=lambda p: str(p)) # Explicação DevIN: Entrega resultado ou encerra a função Python.


def lang_of(path: Path) -> str: # Explicação DevIN: Declara função Python lang_of, com seus argumentos e lógica.
    if path.name == ".htaccess": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Apache/.htaccess" # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if path.name == ".env.example": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "dotenv" # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if path.name == ".gitignore": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Git ignore" # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if path.name in ("Makefile", "Dockerfile"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return path.name # Explicação DevIN: Entrega resultado ou encerra a função Python.
    return LANGUAGE.get(path.suffix.lower(), "configuração") # Explicação DevIN: Entrega resultado ou encerra a função Python.


def ident(text: str, limit: int = 55) -> str: # Explicação DevIN: Declara função Python ident, com seus argumentos e lógica.
    """Extrai identificador seguro, sem copiar literais, senhas ou conteúdo da linha."""
    name = re.search(r"[$]?[A-Za-z_][A-Za-z0-9_.$:-]{0,65}", text) # Explicação DevIN: Atribui resultado à variável Python name.
    return (name.group(0) if name else "instrução atual")[:limit] # Explicação DevIN: Entrega resultado ou encerra a função Python.


def line_explanation(raw: str, language: str, previous: str) -> tuple[str, str]: # Explicação DevIN: Declara função Python line_explanation, com seus argumentos e lógica.
    """Resumir uma linha sem reproduzir valores literais potencialmente sensíveis."""
    line = raw.strip() # Explicação DevIN: Atribui resultado à variável Python line.
    if not line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Linha em branco: separa visualmente trechos do código.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if line.startswith(("/*", "* ", "*/", "//", "<!--", "-- ", "# ")) and not line.startswith("#!"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line.startswith("#!") and language == "Shell": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara o interpretador responsável por executar o script.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if language == "CSS": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        if line.startswith("@media"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Abre regra responsiva: aplica estilos somente sob determinada condição de tela.", "regra responsiva CSS" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("@supports"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Abre regra condicional CSS conforme suporte do navegador.", "suporte CSS" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("@keyframes"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Define sequência de quadros de uma animação CSS.", "animação CSS" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("--",)) and ":" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Define variável CSS customizada, reutilizada por outras regras.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        found = re.match(r"([a-zA-Z-]+)\s*:\s*(.+?);?\s*$", line) # Explicação DevIN: Atribui resultado à variável Python found.
        if found: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            prop = found.group(1) # Explicação DevIN: Atribui resultado à variável Python prop.
            meaning = CSS_PROPERTIES.get(prop, "propriedade visual ou de layout") # Explicação DevIN: Atribui resultado à variável Python meaning.
            return "Define " + meaning + " por meio da propriedade CSS " + prop + ".", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.endswith("{"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            name = line[:-1].strip() # Explicação DevIN: Atribui resultado à variável Python name.
            label = "seletor de elemento/classe" if not name.startswith("@") else "regra especial" # Explicação DevIN: Atribui resultado à variável Python label.
            return "Inicia bloco de estilos associado ao " + label + " desta linha.", "bloco CSS" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line == "}": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Fecha o bloco atual de estilos CSS.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("from ", "to ", "0%", "100%", "50%")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara etapa de uma animação ou transição CSS.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        return "Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if language in ("HTML", "SVG/XML", "XML") or ( # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        language == "PHP" and line.startswith(("<", "</")) and not line.startswith("<?") # Explicação DevIN: Atribui resultado à variável Python language.
    ): # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
        if line.startswith("<?php"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Abre trecho PHP interpretado no servidor antes de produzir HTML.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("<?="): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Insere um valor dinâmico no HTML usando expressão PHP.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("</"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            tag = re.search(r"</\s*([\w:-]+)", line) # Explicação DevIN: Atribui resultado à variável Python tag.
            return "Fecha elemento " + (tag.group(1) if tag else "HTML") + " do documento.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        tags = re.findall(r"<\s*([A-Za-z][\w:-]*)\b", line) # Explicação DevIN: Atribui resultado à variável Python tags.
        if tags: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            name = tags[0].lower() # Explicação DevIN: Atribui resultado à variável Python name.
            description = TAG_MEANING.get(name, "elemento de marcação") # Explicação DevIN: Atribui resultado à variável Python description.
            extra = "" # Explicação DevIN: Atribui resultado à variável Python extra.
            if re.search(r"\baria-[\w-]+", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                extra += " Inclui atributos de acessibilidade." # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            if re.search(r"\b(onclick|onsubmit|onchange)\s*=", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                extra += " Vincula um evento da interface." # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            if "<?" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                extra += " Contém dado dinâmico processado pelo PHP." # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            if len(tags) > 1: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                extra += " A linha reúne múltiplos elementos." # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            return "Declara " + description + "." + extra, "marcação " + name # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("<!--"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Comentário da marcação HTML, sem efeito visual.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        return "Continua atributos ou conteúdo do elemento de marcação aberto anteriormente.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if language in ("JSON", "YAML", "dotenv", "Apache/.htaccess", "Git ignore", "Makefile", "Dockerfile"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        if language == "Git ignore": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Regra Git: define caminho ou padrão a não versionar (ou exceção).", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if language == "Apache/.htaccess": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if line.startswith(("<If", "</If", "<Files", "</Files")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Abre ou fecha bloco de regras Apache para limitar acesso ou reescrever solicitações.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            if re.search(r"\bRewriteRule\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Aplica regra de reescrita ou redirecionamento de URL no Apache.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            if re.search(r"\bRewriteCond\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Define condição necessária para uma regra Apache de reescrita.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            return "Configura diretiva do servidor Apache associada à segurança ou ao roteamento.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if language == "dotenv": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if "=" in line and not line.startswith("#"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Declara uma variável de configuração de exemplo; seu valor real deve permanecer fora do Git.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            return "Organiza a configuração de exemplo do projeto.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if language == "JSON": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            member = re.match(r'["\']([^"\']+)["\']\s*:', line) # Explicação DevIN: Atribui resultado à variável Python member.
            if member: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                key = member.group(1) # Explicação DevIN: Atribui resultado à variável Python key.
                return "Define a chave JSON " + key[:60] + " e seu valor de configuração.", "chave JSON" # Explicação DevIN: Entrega resultado ou encerra a função Python.
            if line in ("{", "["): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Inicia objeto ou lista de dados JSON.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            if line.startswith(("}", "]")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Encerra objeto ou lista de dados JSON.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            return "Continua valor, lista ou estrutura de configuração JSON.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if language == "YAML": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            label = re.match(r"([\w-]+)\s*:", line) # Explicação DevIN: Atribui resultado à variável Python label.
            if label: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                key = label.group(1) # Explicação DevIN: Atribui resultado à variável Python key.
                ydesc = { # Explicação DevIN: Atribui resultado à variável Python ydesc.
                    "on": "acionadores do workflow", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "jobs": "conjunto de trabalhos do workflow", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "steps": "sequência de etapas", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "runs-on": "ambiente executor", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "uses": "ação reutilizável", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "run": "comando de execução", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "env": "variáveis de ambiente", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "permissions": "permissões do workflow", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "branches": "filtro de branches", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "paths": "filtro de caminhos", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "services": "serviços auxiliares", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "if": "condição de execução", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                    "name": "nome identificador", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                }.get(key, "propriedade YAML") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                return "Configura " + ydesc + " (campo " + key + ").", "configuração YAML" # Explicação DevIN: Entrega resultado ou encerra a função Python.
            if line.startswith("- "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                return "Adiciona item a uma lista da configuração YAML.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
            return "Continua valor ou parâmetro da configuração YAML.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("@", ".PHONY")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara diretiva ou alvo especial do arquivo de build.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "=" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Atribui configuração ou variável de execução do arquivo.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        return "Define comando ou diretiva de configuração para o ambiente.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if language == "SQL/MySQL": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        upper = line.upper() # Explicação DevIN: Atribui resultado à variável Python upper.
        if upper.startswith("CREATE TABLE"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Cria tabela persistente no banco de dados, se aplicável com proteção de existência.", "criação de tabela" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith("ALTER TABLE"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia mudança de estrutura de tabela existente.", "alteração de tabela" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith(("INSERT INTO", "REPLACE INTO")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Insere dados na tabela indicada pela instrução SQL.", "inserção SQL" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith("UPDATE "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia atualização de registros; o filtro posterior determina o alcance.", "atualização SQL" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith("DELETE FROM"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia exclusão SQL de registros sujeitos às condições da instrução.", "exclusão SQL" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith("SELECT "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Seleciona campos ou resultados para uma consulta ao banco.", "consulta SQL" # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith("WHERE "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Restringe os registros afetados pela condição SQL.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "PRIMARY KEY" in upper: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara a chave primária para identificar registros sem duplicação.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "FOREIGN KEY" in upper: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara relação de integridade referencial entre tabelas.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "INDEX " in upper or upper.startswith("KEY "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Cria índice para auxiliar buscas ou ordenação no banco.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "ENGINE=" in upper or "CHARSET" in upper: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Define mecanismo de armazenamento ou conjunto de caracteres da tabela.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if "DEFAULT" in upper or re.match(r"^[\w]+\s+(VARCHAR|TEXT|BIGINT|INT|DATE|ENUM|TIMESTAMP|DATETIME)", upper): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if upper.startswith(("VALUES", "ORDER BY", "LIMIT ", "GROUP BY", "JOIN ", "ON ")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Completa cláusula de consulta ou manipulação SQL.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        return "Continua a definição, restrição ou comando SQL do bloco atual.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    if language == "Python": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        if line.startswith(("from ", "import ")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Importa módulo, nome ou função que será utilizado neste script Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("@"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Aplica decorador para ajustar o comportamento da função/classe seguinte.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        found = re.match(r"(?:async\s+)?def\s+([A-Za-z_]\w*)\s*\(", line) # Explicação DevIN: Atribui resultado à variável Python found.
        if found: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara função Python " + found.group(1) + ", com seus argumentos e lógica.", "função " + found.group(1) # Explicação DevIN: Entrega resultado ou encerra a função Python.
        found = re.match(r"class\s+([A-Za-z_]\w*)\b", line) # Explicação DevIN: Atribui resultado à variável Python found.
        if found: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Declara classe Python " + found.group(1) + ".", "classe " + found.group(1) # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("if ", "elif ")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Avalia uma condição para decidir se o bloco Python seguinte será executado.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("else:"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Define o caminho alternativo de uma condição anterior.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("for ", "async for ", "while ")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia laço Python para percorrer dados ou repetir enquanto houver condição.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("with ", "async with ")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Abre contexto que administra um recurso durante o bloco.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("try:"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia bloco Python que pode gerar uma exceção.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("except ", "except:")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Captura exceção para tratamento de falhas do bloco anterior.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("finally:"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Executa instruções de finalização após a tentativa e seu tratamento.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("return"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Entrega resultado ou encerra a função Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("raise "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Lança uma exceção Python para sinalizar situação inválida.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("yield"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Produz um valor incremental por meio de um gerador Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("assert "): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Verifica condição obrigatória; caso falhe, dispara AssertionError.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("break", "continue", "pass")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Controla a execução do laço ou define bloco intencionalmente vazio.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        found = re.match(r"([A-Za-z_]\w*)\s*(?::[^=]+)?=", line) # Explicação DevIN: Atribui resultado à variável Python found.
        if found: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Atribui resultado à variável Python " + found.group(1) + ".", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("'''", '"""')):
            return "Inicia ou encerra bloco de texto/documentação literal Python.", previous
        if line.startswith(("#",)):
            return "Comentário Python: explica ou organiza o trecho do script.", previous
        if line.startswith("print("):
            return "Escreve informação na saída do terminal Python.", previous
        if line.startswith("subprocess."):
            return "Invoca utilitário de execução de comandos do sistema por subprocesso.", previous
        if previous != "trecho principal":
            return "Continua expressão ou bloco Python pertencente a " + previous + ".", previous
        return "Executa parte de uma expressão, chamada ou configuração Python.", previous

    # PHP/JS/TS e trechos de código com sintaxe de programação
    if line.startswith("<?php"):
        return "Abre código PHP interpretado no servidor.", previous
    if line in ("?>",):
        return "Encerra trecho PHP e retorna ao conteúdo de marcação.", previous
    if re.search(r"\bdeclare\s*\(\s*strict_types\s*=\s*1", line):
        return "Habilita a verificação estrita de tipos no PHP.", previous
    if re.match(r"^\s*namespace\s+", line):
        return "Define o namespace para organizar classes e funções PHP.", previous
    if re.match(r"^\s*(?:use|import|export\s+.*from)\s+", line):
        return "Importa ou exporta dependência, nome, classe ou módulo.", previous
    if "require_once" in line or "require " in line or "include_once" in line:
        return "Inclui dependência PHP necessária à execução do módulo.", previous
    match = re.search(r"\b(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_]\w*)", line)
    if match:
        return "Declara o tipo " + match.group(1) + " e inicia sua estrutura.", "tipo " + match.group(1)
    match = re.search(r"\bfunction\s+([A-Za-z_]\w*)\s*\(", line)
    if match:
        return "Declara função/método " + match.group(1) + ", com seus parâmetros e comportamento.", "função " + match.group(1)
    if re.search(r"\b(?:async\s+)?function\s*\(", line):
        return "Define função anônima usada como operação ou callback.", "função anônima"
    if re.search(r"\b(?:if|elseif|else\s+if)\s*\(", line):
        return "Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.", previous
    if re.match(r"^(?:else\b|}\s*else\b)", line):
        return "Define ramo alternativo para condição anterior.", previous
    if re.search(r"\b(?:for|foreach|while|switch)\s*\(", line):
        return "Inicia repetição ou seleção de casos a partir de expressão de controle.", previous
    if re.match(r"^(case\b|default\s*:)", line):
        return "Define um dos caminhos possíveis de uma seleção de casos.", previous
    if re.search(r"\btry\s*\{", line):
        return "Inicia bloco de operação com tratamento de possíveis falhas.", previous
    if re.search(r"\bcatch\s*\(", line):
        return "Captura exceção ou erro para tratar a falha de execução.", previous
    if re.search(r"\bfinally\s*\{", line):
        return "Declara limpeza ou finalização executada ao encerrar a tentativa.", previous
    if re.search(r"\bthrow\s+", line):
        return "Interrompe o fluxo atual levantando exceção ou erro.", previous
    if re.match(r"^(?:return|yield)\b", line):
        return "Retorna ou produz resultado da função, conforme a expressão informada.", previous
    if re.match(r"^(?:break|continue)\s*;?", line):
        return "Controla a continuação ou saída de laço/seleção.", previous
    if re.search(r"\.addEventListener\s*\(", line):
        return "Registra callback para responder a uma interação ou evento do navegador.", previous
    if re.search(r"\bfetch\s*\(", line):
        return "Inicia solicitação HTTP assíncrona a um endereço configurado.", previous
    if re.search(r"\b(?:json_decode|json_encode|JSON\.parse|JSON\.stringify)\s*\(", line):
        return "Converte dados entre estrutura interna e representação JSON.", previous
    if re.search(r"\b(?:password_hash|password_verify|hash_equals)\s*\(", line):
        return "Executa operação criptográfica de senha ou comparação protegida.", previous
    if re.search(r"\b(?:prepare|bind_param|execute)\s*\(", line):
        return "Prepara, parametriza ou executa operação SQL por interface do banco.", previous
    if re.search(r"\b(?:query|fetch_assoc|fetch_all|get_result)\s*\(", line):
        return "Executa ou lê resultados de uma consulta ao banco.", previous
    if re.search(r"\b(?:localStorage|sessionStorage)\.", line):
        return "Lê ou atualiza preferências guardadas no armazenamento local do navegador.", previous
    if re.search(r"\b(?:document|window)\.", line):
        return "Interage com a página, elementos visíveis ou eventos do navegador.", previous
    if re.search(r"\b(?:header|http_response_code|setcookie|session_start)\s*\(", line):
        return "Ajusta cabeçalho, sessão ou resposta HTTP do servidor.", previous
    if re.match(r"^(?:echo|print|printf)\b", line):
        return "Produz saída ou conteúdo da resposta do programa.", previous
    assign = re.match(r"^(?:const|let|var)\s+([A-Za-z_$][\w$]*)\b", line)
    if assign:
        return "Declara variável JavaScript " + assign.group(1) + " para reutilizar valor ou resultado.", previous
    assign = re.match(r"^(\$[A-Za-z_]\w*)\s*=", line)
    if assign:
        return "Atribui um valor à variável PHP " + assign.group(1) + ".", previous
    if re.search(r"\b(?:public|private|protected)\s+(?:static\s+)?", line):
        return "Define membro, visibilidade ou método de uma classe.", previous
    if re.match(r"^[$A-Za-z_.][\w.$\[\]'\"-]*\s*(?:\+?=|=>)", line):
        return "Atribui propriedade, resultado ou associação de chave e valor.", previous
    if line.startswith(("<", "</")):
        return "Combina execução de código com conteúdo HTML gerado dinamicamente.", previous
    if line in ("{", "};", "}", "})", "});", ");", "]", "],", "},", "},);"):
        return "Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.", previous
    if re.match(r"^[\]\}\);,]+$", line):
        return "Finaliza expressão, lista, função ou bloco sintático.", previous
    if line.startswith(("'", '"', chr(96))):
        return "Declara ou continua um valor literal usado pela instrução em andamento.", previous
    if line.startswith(("&&", "||", "?", ":", ".", "->", "=>")):
        return "Continua a expressão lógica, encadeamento ou associação do comando anterior.", previous
    if language == "Shell":
        return "Executa comando de automação ou configura argumento do shell.", previous
    if previous != "trecho principal":
        return "Continua expressão ou bloco pertencente a " + previous + ".", previous
    return "Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.", previous


def comment_for(language: str) -> tuple[str, str] | None:
    """Retorna delimitadores para comentários válidos na linguagem."""
    styles = {
        "PHP": ("// ", ""),
        "JavaScript": ("// ", ""),
        "JavaScript/CommonJS": ("// ", ""),
        "JavaScript/ESM": ("// ", ""),
        "TypeScript": ("// ", ""),
        "TypeScript/JSX": ("// ", ""),
        "CSS": ("/* ", " */"),
        "HTML": ("<!-- ", " -->"),
        "SVG/XML": ("<!-- ", " -->"),
        "XML": ("<!-- ", " -->"),
        "SQL/MySQL": ("-- ", ""),
        "YAML": ("# ", ""),
        "Shell": ("# ", ""),
        "Python": ("# ", ""),
        "dotenv": ("# ", ""),
        "Apache/.htaccess": ("# ", ""),
        "Dockerfile": ("# ", ""),
        "Makefile": ("# ", ""),
    }
    return styles.get(language)


def strip_annotation(line: str) -> str:
    patterns = (
        r" ?// " + re.escape(ANNOTATION_MARKER) + r".*$",
        r" ?/\* " + re.escape(ANNOTATION_MARKER) + r".*?\*/$",
        r" ?<!-- " + re.escape(ANNOTATION_MARKER) + r".*? -->$",
        r" ?-- " + re.escape(ANNOTATION_MARKER) + r".*$",
        r" ?# " + re.escape(ANNOTATION_MARKER) + r".*$",
    )
    result = line.rstrip("\r\n")
    for pattern in patterns:
        result = re.sub(pattern, "", result)
    return result


def is_comment_line(line: str, language: str) -> bool:
    stripped = line.lstrip()
    if language in ("PHP", "JavaScript", "JavaScript/CommonJS", "JavaScript/ESM",
                    "TypeScript", "TypeScript/JSX", "CSS"):
        return stripped.startswith(("//", "/*", "*", "*/"))
    if language in ("HTML", "SVG/XML", "XML"):
        return stripped.startswith(("<!--", "<?xml"))
    if language == "SQL/MySQL":
        return stripped.startswith(("--", "/*", "*", "*/"))
    if language in ("YAML", "Shell", "Python", "dotenv", "Apache/.htaccess",
                    "Dockerfile", "Makefile"):
        return stripped.startswith("#")
    return False


def php_line_state(line: str, quote: str | None) -> tuple[str | None, bool]:
    """Detecta fechamento PHP fora de literais para não confundir XML/URLs."""
    index = 0
    while index < len(line):
        char = line[index]
        if quote is not None:
            if char == "\\":
                index += 2
                continue
            if char == quote:
                quote = None
            index += 1
            continue
        if line.startswith("//", index) or char == "#":
            break
        if line.startswith("/*", index):
            break
        if char in ("'", '"'):
            quote = char
            index += 1
            continue
        if line.startswith("?>", index):
            return quote, True
        index += 1
    return quote, False


def has_inline_comment(line: str, language: str) -> bool:
    """Ignora marcadores de comentário que aparecem dentro de strings."""
    markers = {
        "PHP": ("//", "#", "/*"),
        "JavaScript": ("//", "/*"),
        "JavaScript/CommonJS": ("//", "/*"),
        "JavaScript/ESM": ("//", "/*"),
        "TypeScript": ("//", "/*"),
        "TypeScript/JSX": ("//", "/*"),
        "CSS": ("/*",),
        "SQL/MySQL": ("--", "/*"),
        "HTML": ("<!--",),
        "SVG/XML": ("<!--",),
        "XML": ("<!--",),
        "YAML": ("#",),
        "Shell": ("#",),
        "Python": ("#",),
        "dotenv": ("#",),
        "Apache/.htaccess": ("#",),
        "Dockerfile": ("#",),
        "Makefile": ("#",),
    }.get(language, ())
    quote: str | None = None
    index = 0
    while index < len(line):
        char = line[index]
        if quote is not None:
            if char == "\\":
                index += 2
                continue
            if char == quote:
                quote = None
            index += 1
            continue
        if char in ("'", '"', "`"):
            quote = char
            index += 1
            continue
        if any(line.startswith(marker, index) for marker in markers):
            return True
        index += 1
    return False


def annotate_source(path: Path, source: str) -> tuple[str, int, int]:
    """Adiciona explicações como comentários, sem tocar em linhas de dados."""
    language = lang_of(path)
    base_style = comment_for(language)
    if base_style is None:
        return source, 0, 0

    original_lines = source.splitlines(keepends=True)
    output: list[str] = []
    previous = "trecho principal"
    mode = language
    in_php = path.suffix.lower() == ".php"
    in_script = False
    in_style = False
    in_rcdata = ""
    in_tag = False
    in_template = False
    in_python_string = ""
    php_quote: str | None = None
    in_heredoc = ""
    yaml_shell_block = False
    yaml_block_indent: int | None = None
    added = 0
    skipped = 0

    for raw in original_lines:
        ending = "\r\n" if raw.endswith("\r\n") else "\n" if raw.endswith("\n") else ""
        content = strip_annotation(raw)
        line = content.rstrip("\r\n")
        stripped = line.strip()
        if not stripped:
            output.append(raw)
            continue
        raw = line + ending

        if path.suffix.lower() in (".html", ".htm", ".svg", ".xml"):
            if in_rcdata:
                if re.search(r"</\s*" + re.escape(in_rcdata) + r"\s*>", line, re.I):
                    in_rcdata = ""
                output.append(raw)
                skipped += 1
                continue
            if in_script and re.search(r"</\s*script\s*>", line, re.I):
                in_script = False
                output.append(raw)
                skipped += 1
                continue
            if in_style and re.search(r"</\s*style\s*>", line, re.I):
                in_style = False
                output.append(raw)
                skipped += 1
                continue
            if in_script:
                mode = "JavaScript"
            elif in_style:
                mode = "CSS"
            else:
                mode = language
                if in_tag:
                    in_tag = ">" not in line
                    output.append(raw)
                    skipped += 1
                    continue
                if re.search(r"<\s*script\b", line, re.I) and not re.search(r"</\s*script\s*>", line, re.I):
                    in_script = True
                    output.append(raw)
                    skipped += 1
                    continue
                if re.search(r"<\s*style\b", line, re.I) and not re.search(r"</\s*style\s*>", line, re.I):
                    in_style = True
                    output.append(raw)
                    skipped += 1
                    continue
                rcdata = re.search(r"<\s*(title|textarea)\b", line, re.I)
                if rcdata and not re.search(r"</\s*" + rcdata.group(1) + r"\s*>", line, re.I):
                    in_rcdata = rcdata.group(1)
                    output.append(raw)
                    skipped += 1
                    continue
                if re.search(r"<\s*[A-Za-z][^>]*$", line) and not line.rstrip().endswith("/>"):
                    in_tag = True
                    output.append(raw)
                    skipped += 1
                    continue

        elif path.suffix.lower() == ".php":
            if re.match(r"^\s*<\?(?:php|=)", line):
                in_php = True
            mode = "PHP" if in_php else "HTML"
            if mode == "PHP" and not in_heredoc:
                php_quote, has_php_close = php_line_state(line, php_quote)
            else:
                has_php_close = False
            if has_php_close:
                in_php = False
                php_quote = None
                output.append(raw)
                skipped += 1
                continue
            if mode == "HTML":
                if in_rcdata:
                    if re.search(r"</\s*" + re.escape(in_rcdata) + r"\s*>", line, re.I):
                        in_rcdata = ""
                    output.append(raw)
                    skipped += 1
                    continue
                if in_script and re.search(r"</\s*script\s*>", line, re.I):
                    in_script = False
                    output.append(raw)
                    skipped += 1
                    continue
                if in_style and re.search(r"</\s*style\s*>", line, re.I):
                    in_style = False
                    output.append(raw)
                    skipped += 1
                    continue
                if in_script:
                    mode = "JavaScript"
                elif in_style:
                    mode = "CSS"
                elif in_tag:
                    in_tag = ">" not in line
                    output.append(raw)
                    skipped += 1
                    continue
                elif re.search(r"<\s*script\b", line, re.I) and not re.search(r"</\s*script\s*>", line, re.I):
                    in_script = True
                    output.append(raw)
                    skipped += 1
                    continue
                elif re.search(r"<\s*style\b", line, re.I) and not re.search(r"</\s*style\s*>", line, re.I):
                    in_style = True
                    output.append(raw)
                    skipped += 1
                    continue
                else:
                    rcdata = re.search(r"<\s*(title|textarea)\b", line, re.I)
                    if rcdata and not re.search(r"</\s*" + rcdata.group(1) + r"\s*>", line, re.I):
                        in_rcdata = rcdata.group(1)
                        output.append(raw)
                        skipped += 1
                        continue
                    if re.search(r"<\s*[A-Za-z][^>]*$", line) and not line.rstrip().endswith("/>"):
                        in_tag = True
                        output.append(raw)
                        skipped += 1
                        continue

        elif path.suffix.lower() in (".yml", ".yaml"):
            indent = len(line) - len(line.lstrip())
            if yaml_block_indent is not None and indent <= yaml_block_indent:
                yaml_block_indent = None
                yaml_shell_block = False
            if yaml_block_indent is not None:
                if yaml_shell_block and not line.rstrip().endswith("\\"):
                    mode = "Shell"
                else:
                    output.append(raw)
                    skipped += 1
                    continue
            else:
                block = re.match(r"^(\s*[^#].*?:)\s*[|>][+-]?\s*(?:#.*)?$", line)
                if block:
                    yaml_block_indent = len(block.group(1)) - len(block.group(1).lstrip())
                    yaml_shell_block = bool(re.search(r"\brun\s*:", block.group(1)))
                mode = "YAML"

        style = comment_for(mode)
        if style is None:
            output.append(raw)
            skipped += 1
            continue

        # Literal bodies and continued strings are data, not source statements.
        if in_heredoc:
            if stripped == in_heredoc or stripped == in_heredoc + ";":
                in_heredoc = ""
            output.append(raw)
            skipped += 1
            continue
        if php_quote is not None:
            output.append(raw)
            skipped += 1
            continue
        if in_python_string:
            if in_python_string in line:
                in_python_string = ""
            output.append(raw)
            skipped += 1
            continue
        if mode in ("JavaScript", "JavaScript/CommonJS", "JavaScript/ESM",
                    "TypeScript", "TypeScript/JSX"):
            tick_count = len(re.findall(r"(?<!\\)`", line))
            if in_template:
                if tick_count:
                    in_template = False
                output.append(raw)
                skipped += 1
                continue
            if tick_count % 2:
                in_template = True
                output.append(raw)
                skipped += 1
                continue
        if mode == "Python":
            triple = re.search(r"(?<!\\)(\"\"\"|''')", line)
            if triple: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                token = triple.group(1) # Explicação DevIN: Atribui resultado à variável Python token.
                if line.count(token) == 1: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_python_string = token # Explicação DevIN: Atribui resultado à variável Python in_python_string.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "PHP" and "<<<" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            heredoc = re.search(r"<<<\s*['\"]?([A-Za-z_][A-Za-z0-9_]*)", line) # Explicação DevIN: Atribui resultado à variável Python heredoc.
            if heredoc and not re.search(r"\b" + re.escape(heredoc.group(1)) + r"\b", line[heredoc.end():]): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_heredoc = heredoc.group(1) # Explicação DevIN: Atribui resultado à variável Python in_heredoc.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        if is_comment_line(line, mode): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Shell" and line.rstrip().endswith("\\"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Python" and line.rstrip().endswith("\\"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if has_inline_comment(line, mode): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw)  # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1  # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue  # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "PHP" and re.match(r"^\s*<\?(?:php)?\s*\??\s*$", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            description, previous = line_explanation(line, mode, previous) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            opening = "// " + ANNOTATION_MARKER + " " + description # Explicação DevIN: Atribui resultado à variável Python opening.
            output.append(line + " " + opening + ending) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            added += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode in ("HTML", "SVG/XML", "XML") and re.match(r"^\s*<\?xml\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Python" and (line.startswith("#!") or re.match(r"^\s*#.*coding[:=]", line)): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        description, previous = line_explanation(line, mode, previous) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
        prefix, suffix = style # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
        annotated = line + " " + prefix + ANNOTATION_MARKER + " " + description + suffix # Explicação DevIN: Atribui resultado à variável Python annotated.
        output.append(annotated + ending) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.
        added += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função line_explanation.

    return "".join(output), added, skipped # Explicação DevIN: Entrega resultado ou encerra a função Python.


def run() -> int: # Explicação DevIN: Declara função Python run, com seus argumentos e lógica.
    parser = argparse.ArgumentParser(description=__doc__) # Explicação DevIN: Atribui resultado à variável Python parser.
    mode = parser.add_mutually_exclusive_group(required=True) # Explicação DevIN: Atribui resultado à variável Python mode.
    mode.add_argument("--write", action="store_true") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    mode.add_argument("--check", action="store_true") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    mode.add_argument("--stats", action="store_true") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    options = parser.parse_args() # Explicação DevIN: Atribui resultado à variável Python options.
    files = source_paths() # Explicação DevIN: Atribui resultado à variável Python files.
    total_added = 0 # Explicação DevIN: Atribui resultado à variável Python total_added.
    total_skipped = 0 # Explicação DevIN: Atribui resultado à variável Python total_skipped.
    stale: list[Path] = [] # Explicação DevIN: Atribui resultado à variável Python stale.
    for path in files: # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        absolute = ROOT / path # Explicação DevIN: Atribui resultado à variável Python absolute.
        try: # Explicação DevIN: Inicia bloco Python que pode gerar uma exceção.
            source = absolute.read_text(encoding="utf-8-sig") # Explicação DevIN: Atribui resultado à variável Python source.
        except UnicodeDecodeError as exc: # Explicação DevIN: Captura exceção para tratamento de falhas do bloco anterior.
            raise RuntimeError("Arquivo de código sem UTF-8: " + str(path)) from exc # Explicação DevIN: Lança uma exceção Python para sinalizar situação inválida.
        updated, added, skipped = annotate_source(path, source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        total_added += added # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        total_skipped += skipped # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        if updated != source: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if options.write: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                absolute.write_text(updated, encoding="utf-8", newline="") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
            else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                stale.append(path) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    print(json.dumps({ # Explicação DevIN: Escreve informação na saída do terminal Python.
        "source_files": len(files), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        "annotated_lines": total_added, # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        "skipped_lines": total_skipped, # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        "files_needing_annotations": len(stale), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
        "mode": "write" if options.write else "check" if options.check else "stats" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    }, ensure_ascii=False)) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
    if options.check and stale: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        for path in stale[:25]: # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
            print("Explicações ausentes/desatualizadas: " + path.as_posix(), file=sys.stderr) # Explicação DevIN: Escreve informação na saída do terminal Python.
        return 1 # Explicação DevIN: Entrega resultado ou encerra a função Python.
    return 0 # Explicação DevIN: Entrega resultado ou encerra a função Python.


if __name__ == "__main__": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
    sys.exit(run()) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função run.
