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
import io # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import json # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import re # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import subprocess # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import sys # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
import tokenize # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
from pathlib import Path # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.

ROOT = Path(__file__).resolve().parents[1] # Explicação DevIN: Atribui resultado à variável Python ROOT.
SUFFIXES = {".php", ".js", ".cjs", ".mjs", ".css", ".html", ".htm", ".sql", # Explicação DevIN: Atribui resultado à variável Python SUFFIXES.
            ".json", ".yml", ".yaml", ".svg", ".xml", ".sh", ".ts", ".tsx", ".py"} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
SPECIAL_FILES = {".env.example", ".htaccess", ".gitignore", "Dockerfile", "Makefile"} # Explicação DevIN: Atribui resultado à variável Python SPECIAL_FILES.
IGNORE_SEGMENTS = {"vendor", "node_modules", ".git", "dist", "build", # Explicação DevIN: Atribui resultado à variável Python IGNORE_SEGMENTS.
                   "__pycache__", "coverage", "third_party"} # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
# Arquivos consumidos diretamente por Apache/dotenv não aceitam comentários finais sem risco de falha.
EXCLUDED = {"composer.lock", "package-lock.json", "pnpm-lock.yaml", "yarn.lock", ".env.example", ".htaccess"} # Explicação DevIN: Atribui resultado à variável Python EXCLUDED.
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
    """Extrai identificador seguro, sem copiar literais, senhas ou conteúdo da linha.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
    name = re.search(r"[$]?[A-Za-z_][A-Za-z0-9_.$:-]{0,65}", text) # Explicação DevIN: Atribui resultado à variável Python name.
    return (name.group(0) if name else "instrução atual")[:limit] # Explicação DevIN: Entrega resultado ou encerra a função Python.


def line_explanation(raw: str, language: str, previous: str) -> tuple[str, str]: # Explicação DevIN: Declara função Python line_explanation, com seus argumentos e lógica.
    """Resumir uma linha sem reproduzir valores literais potencialmente sensíveis.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
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
        if line.startswith(("'''", '"""')): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Inicia ou encerra bloco de texto/documentação literal Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith(("#",)): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Comentário Python: explica ou organiza o trecho do script.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("print("): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Escreve informação na saída do terminal Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if line.startswith("subprocess."): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Invoca utilitário de execução de comandos do sistema por subprocesso.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        if previous != "trecho principal": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return "Continua expressão ou bloco Python pertencente a " + previous + ".", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
        return "Executa parte de uma expressão, chamada ou configuração Python.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.

    # PHP/JS/TS e trechos de código com sintaxe de programação
    if line.startswith("<?php"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Abre código PHP interpretado no servidor.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line in ("?>",): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Encerra trecho PHP e retorna ao conteúdo de marcação.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\bdeclare\s*\(\s*strict_types\s*=\s*1", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Habilita a verificação estrita de tipos no PHP.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^\s*namespace\s+", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Define o namespace para organizar classes e funções PHP.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^\s*(?:use|import|export\s+.*from)\s+", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Importa ou exporta dependência, nome, classe ou módulo.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if "require_once" in line or "require " in line or "include_once" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Inclui dependência PHP necessária à execução do módulo.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    match = re.search(r"\b(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_]\w*)", line) # Explicação DevIN: Atribui resultado à variável Python match.
    if match: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara o tipo " + match.group(1) + " e inicia sua estrutura.", "tipo " + match.group(1) # Explicação DevIN: Entrega resultado ou encerra a função Python.
    match = re.search(r"\bfunction\s+([A-Za-z_]\w*)\s*\(", line) # Explicação DevIN: Atribui resultado à variável Python match.
    if match: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara função/método " + match.group(1) + ", com seus parâmetros e comportamento.", "função " + match.group(1) # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:async\s+)?function\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Define função anônima usada como operação ou callback.", "função anônima" # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:if|elseif|else\s+if)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^(?:else\b|}\s*else\b)", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Define ramo alternativo para condição anterior.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:for|foreach|while|switch)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Inicia repetição ou seleção de casos a partir de expressão de controle.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^(case\b|default\s*:)", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Define um dos caminhos possíveis de uma seleção de casos.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\btry\s*\{", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Inicia bloco de operação com tratamento de possíveis falhas.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\bcatch\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Captura exceção ou erro para tratar a falha de execução.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\bfinally\s*\{", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara limpeza ou finalização executada ao encerrar a tentativa.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\bthrow\s+", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Interrompe o fluxo atual levantando exceção ou erro.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^(?:return|yield)\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Retorna ou produz resultado da função, conforme a expressão informada.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^(?:break|continue)\s*;?", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Controla a continuação ou saída de laço/seleção.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\.addEventListener\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Registra callback para responder a uma interação ou evento do navegador.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\bfetch\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Inicia solicitação HTTP assíncrona a um endereço configurado.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:json_decode|json_encode|JSON\.parse|JSON\.stringify)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Converte dados entre estrutura interna e representação JSON.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:password_hash|password_verify|hash_equals)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Executa operação criptográfica de senha ou comparação protegida.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:prepare|bind_param|execute)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Prepara, parametriza ou executa operação SQL por interface do banco.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:query|fetch_assoc|fetch_all|get_result)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Executa ou lê resultados de uma consulta ao banco.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:localStorage|sessionStorage)\.", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Lê ou atualiza preferências guardadas no armazenamento local do navegador.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:document|window)\.", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Interage com a página, elementos visíveis ou eventos do navegador.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:header|http_response_code|setcookie|session_start)\s*\(", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Ajusta cabeçalho, sessão ou resposta HTTP do servidor.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^(?:echo|print|printf)\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Produz saída ou conteúdo da resposta do programa.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    assign = re.match(r"^(?:const|let|var)\s+([A-Za-z_$][\w$]*)\b", line) # Explicação DevIN: Atribui resultado à variável Python assign.
    if assign: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara variável JavaScript " + assign.group(1) + " para reutilizar valor ou resultado.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    assign = re.match(r"^(\$[A-Za-z_]\w*)\s*=", line) # Explicação DevIN: Atribui resultado à variável Python assign.
    if assign: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Atribui um valor à variável PHP " + assign.group(1) + ".", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.search(r"\b(?:public|private|protected)\s+(?:static\s+)?", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Define membro, visibilidade ou método de uma classe.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^[$A-Za-z_.][\w.$\[\]'\"-]*\s*(?:\+?=|=>)", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Atribui propriedade, resultado ou associação de chave e valor.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line.startswith(("<", "</")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Combina execução de código com conteúdo HTML gerado dinamicamente.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line in ("{", "};", "}", "})", "});", ");", "]", "],", "},", "},);"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if re.match(r"^[\]\}\);,]+$", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Finaliza expressão, lista, função ou bloco sintático.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line.startswith(("'", '"', chr(96))): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Declara ou continua um valor literal usado pela instrução em andamento.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if line.startswith(("&&", "||", "?", ":", ".", "->", "=>")): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Continua a expressão lógica, encadeamento ou associação do comando anterior.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if language == "Shell": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Executa comando de automação ou configura argumento do shell.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if previous != "trecho principal": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return "Continua expressão ou bloco pertencente a " + previous + ".", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.
    return "Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.", previous # Explicação DevIN: Entrega resultado ou encerra a função Python.


def comment_for(language: str) -> tuple[str, str] | None: # Explicação DevIN: Declara função Python comment_for, com seus argumentos e lógica.
    """Retorna delimitadores para comentários válidos na linguagem.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
    styles = { # Explicação DevIN: Atribui resultado à variável Python styles.
        "PHP": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "JavaScript": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "JavaScript/CommonJS": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "JavaScript/ESM": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "TypeScript": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "TypeScript/JSX": ("// ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "CSS": ("/* ", " */"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "HTML": ("<!-- ", " -->"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "SVG/XML": ("<!-- ", " -->"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "XML": ("<!-- ", " -->"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "SQL/MySQL": ("-- ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "YAML": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "Shell": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "Python": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "dotenv": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "Apache/.htaccess": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "Dockerfile": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
        "Makefile": ("# ", ""), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
    } # Explicação DevIN: Continua expressão ou bloco Python pertencente a função comment_for.
    return styles.get(language) # Explicação DevIN: Entrega resultado ou encerra a função Python.


def strip_annotation(line: str) -> str: # Explicação DevIN: Declara função Python strip_annotation, com seus argumentos e lógica.
    patterns = ( # Explicação DevIN: Atribui resultado à variável Python patterns.
        r" ?// " + re.escape(ANNOTATION_MARKER) + r".*$", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
        r" ?/\* " + re.escape(ANNOTATION_MARKER) + r".*?\*/$", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
        r" ?<!-- " + re.escape(ANNOTATION_MARKER) + r".*? -->$", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
        r" ?-- " + re.escape(ANNOTATION_MARKER) + r".*$", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
        r" ?# " + re.escape(ANNOTATION_MARKER) + r".*$", # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
    ) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função strip_annotation.
    result = line.rstrip("\r\n") # Explicação DevIN: Atribui resultado à variável Python result.
    for pattern in patterns: # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        result = re.sub(pattern, "", result) # Explicação DevIN: Atribui resultado à variável Python result.
    return result # Explicação DevIN: Entrega resultado ou encerra a função Python.


def is_comment_line(line: str, language: str) -> bool: # Explicação DevIN: Declara função Python is_comment_line, com seus argumentos e lógica.
    stripped = line.lstrip() # Explicação DevIN: Atribui resultado à variável Python stripped.
    if language in ("PHP", "JavaScript", "JavaScript/CommonJS", "JavaScript/ESM", # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    "TypeScript", "TypeScript/JSX", "CSS"): # Explicação DevIN: Continua expressão ou bloco Python pertencente a função is_comment_line.
        return stripped.startswith(("//", "/*", "*", "*/")) # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if language in ("HTML", "SVG/XML", "XML"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return stripped.startswith(("<!--", "<?xml")) # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if language == "SQL/MySQL": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return stripped.startswith(("--", "/*", "*", "*/")) # Explicação DevIN: Entrega resultado ou encerra a função Python.
    if language in ("YAML", "Shell", "Python", "dotenv", "Apache/.htaccess", # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    "Dockerfile", "Makefile"): # Explicação DevIN: Continua expressão ou bloco Python pertencente a função is_comment_line.
        return stripped.startswith("#") # Explicação DevIN: Entrega resultado ou encerra a função Python.
    return False # Explicação DevIN: Entrega resultado ou encerra a função Python.


def php_line_state(line: str, quote: str | None) -> tuple[str | None, bool]: # Explicação DevIN: Declara função Python php_line_state, com seus argumentos e lógica.
    """Detecta fechamento PHP fora de literais para não confundir XML/URLs.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
    index = 0 # Explicação DevIN: Atribui resultado à variável Python index.
    while index < len(line): # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        char = line[index] # Explicação DevIN: Atribui resultado à variável Python char.
        if quote is not None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if char == "\\": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                index += 2 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função php_line_state.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if char == quote: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                quote = None # Explicação DevIN: Atribui resultado à variável Python quote.
            index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função php_line_state.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if line.startswith("//", index) or char == "#": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            break # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if line.startswith("/*", index): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            break # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if char in ("'", '"'): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            quote = char # Explicação DevIN: Atribui resultado à variável Python quote.
            index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função php_line_state.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if line.startswith("?>", index): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return quote, True # Explicação DevIN: Entrega resultado ou encerra a função Python.
        index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função php_line_state.
    return quote, False # Explicação DevIN: Entrega resultado ou encerra a função Python.


def has_inline_comment(line: str, language: str) -> bool: # Explicação DevIN: Declara função Python has_inline_comment, com seus argumentos e lógica.
    """Ignora marcadores de comentário que aparecem dentro de strings.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
    markers = { # Explicação DevIN: Atribui resultado à variável Python markers.
        "PHP": ("//", "#", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "JavaScript": ("//", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "JavaScript/CommonJS": ("//", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "JavaScript/ESM": ("//", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "TypeScript": ("//", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "TypeScript/JSX": ("//", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "CSS": ("/*",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "SQL/MySQL": ("--", "/*"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "HTML": ("<!--",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "SVG/XML": ("<!--",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "XML": ("<!--",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "YAML": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "Shell": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "Python": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "dotenv": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "Apache/.htaccess": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "Dockerfile": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
        "Makefile": ("#",), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
    }.get(language, ()) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
    quote: str | None = None # Explicação DevIN: Atribui resultado à variável Python quote.
    index = 0 # Explicação DevIN: Atribui resultado à variável Python index.
    while index < len(line): # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        char = line[index] # Explicação DevIN: Atribui resultado à variável Python char.
        if quote is not None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if char == "\\": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                index += 2 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if char == quote: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                quote = None # Explicação DevIN: Atribui resultado à variável Python quote.
            index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if char in ("'", '"', "`"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            quote = char # Explicação DevIN: Atribui resultado à variável Python quote.
            index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if any(line.startswith(marker, index) for marker in markers): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            return True # Explicação DevIN: Entrega resultado ou encerra a função Python.
        index += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função has_inline_comment.
    return False # Explicação DevIN: Entrega resultado ou encerra a função Python.


def python_source_regions(source: str) -> tuple[set[int], dict[int, int]]: # Explicação DevIN: Declara função Python python_source_regions, com seus argumentos e lógica.
    tokens = tokenize.generate_tokens(io.StringIO(source).readline) # Explicação DevIN: Atribui resultado à variável Python tokens.
    lines: set[int] = set() # Explicação DevIN: Atribui resultado à variável Python lines.
    annotations: dict[int, int] = {} # Explicação DevIN: Atribui resultado à variável Python annotations.
    fstring_starts: list[int] = [] # Explicação DevIN: Atribui resultado à variável Python fstring_starts.
    for token in tokens: # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        if token.type == tokenize.STRING and token.start[0] != token.end[0]: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            lines.update(range(token.start[0], token.end[0] + 1)) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função python_source_regions.
        elif token.type == getattr(tokenize, "FSTRING_START", -1): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            fstring_starts.append(token.start[0]) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função python_source_regions.
        elif token.type == getattr(tokenize, "FSTRING_END", -1) and fstring_starts: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            start = fstring_starts.pop() # Explicação DevIN: Atribui resultado à variável Python start.
            if start != token.end[0]: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                lines.update(range(start, token.end[0] + 1)) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função python_source_regions.
        elif token.type == tokenize.COMMENT and token.string.startswith("# " + ANNOTATION_MARKER): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            annotations[token.start[0]] = token.start[1] # Explicação DevIN: Continua expressão ou bloco Python pertencente a função python_source_regions.
    return lines, annotations # Explicação DevIN: Entrega resultado ou encerra a função Python.


def annotate_source(path: Path, source: str) -> tuple[str, int, int]: # Explicação DevIN: Declara função Python annotate_source, com seus argumentos e lógica.
    """Adiciona explicações como comentários, sem tocar em linhas de dados.""" # Explicação DevIN: Inicia ou encerra bloco de texto/documentação literal Python.
    language = lang_of(path) # Explicação DevIN: Atribui resultado à variável Python language.
    base_style = comment_for(language) # Explicação DevIN: Atribui resultado à variável Python base_style.
    if base_style is None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
        return source, 0, 0 # Explicação DevIN: Entrega resultado ou encerra a função Python.

    original_lines = source.splitlines(keepends=True) # Explicação DevIN: Atribui resultado à variável Python original_lines.
    output: list[str] = [] # Explicação DevIN: Atribui resultado à variável Python output.
    previous = "trecho principal" # Explicação DevIN: Atribui resultado à variável Python previous.
    mode = language # Explicação DevIN: Atribui resultado à variável Python mode.
    in_php = False # Explicação DevIN: Atribui resultado à variável Python in_php.
    in_script = False # Explicação DevIN: Atribui resultado à variável Python in_script.
    in_style = False # Explicação DevIN: Atribui resultado à variável Python in_style.
    in_css_comment = False # Explicação DevIN: Atribui resultado à variável Python in_css_comment.
    in_rcdata = "" # Explicação DevIN: Atribui resultado à variável Python in_rcdata.
    in_tag = False # Explicação DevIN: Atribui resultado à variável Python in_tag.
    in_template = False # Explicação DevIN: Atribui resultado à variável Python in_template.
    python_string_lines, python_annotations = python_source_regions(source) if language == "Python" else (set(), {}) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
    php_quote: str | None = None # Explicação DevIN: Atribui resultado à variável Python php_quote.
    in_heredoc = "" # Explicação DevIN: Atribui resultado à variável Python in_heredoc.
    yaml_shell_block = False # Explicação DevIN: Atribui resultado à variável Python yaml_shell_block.
    yaml_block_indent: int | None = None # Explicação DevIN: Atribui resultado à variável Python yaml_block_indent.
    added = 0 # Explicação DevIN: Atribui resultado à variável Python added.
    skipped = 0 # Explicação DevIN: Atribui resultado à variável Python skipped.

    for line_number, raw in enumerate(original_lines, 1): # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
        if line_number in python_string_lines: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        ending = "\r\n" if raw.endswith("\r\n") else "\n" if raw.endswith("\n") else "" # Explicação DevIN: Atribui resultado à variável Python ending.
        if language == "Python": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            annotation_column = python_annotations.get(line_number) # Explicação DevIN: Atribui resultado à variável Python annotation_column.
            content = raw[:annotation_column].rstrip() if annotation_column is not None else raw # Explicação DevIN: Atribui resultado à variável Python content.
        else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
            content = strip_annotation(raw) # Explicação DevIN: Atribui resultado à variável Python content.
        line = content.rstrip("\r\n") # Explicação DevIN: Atribui resultado à variável Python line.
        stripped = line.strip() # Explicação DevIN: Atribui resultado à variável Python stripped.
        if not stripped: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        raw = line + ending # Explicação DevIN: Atribui resultado à variável Python raw.

        if path.suffix.lower() in (".html", ".htm", ".svg", ".xml"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if in_rcdata: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                if re.search(r"</\s*" + re.escape(in_rcdata) + r"\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_rcdata = "" # Explicação DevIN: Atribui resultado à variável Python in_rcdata.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if in_script and re.search(r"</\s*script\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_script = False # Explicação DevIN: Atribui resultado à variável Python in_script.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if in_style and re.search(r"</\s*style\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_style = False # Explicação DevIN: Atribui resultado à variável Python in_style.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if in_script: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                mode = "JavaScript" # Explicação DevIN: Atribui resultado à variável Python mode.
            elif in_style: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                mode = "CSS" # Explicação DevIN: Atribui resultado à variável Python mode.
            else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                mode = language # Explicação DevIN: Atribui resultado à variável Python mode.
                if in_tag: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_tag = ">" not in line # Explicação DevIN: Atribui resultado à variável Python in_tag.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if re.search(r"<\s*script\b", line, re.I) and not re.search(r"</\s*script\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_script = True # Explicação DevIN: Atribui resultado à variável Python in_script.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if re.search(r"<\s*style\b", line, re.I) and not re.search(r"</\s*style\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_style = True # Explicação DevIN: Atribui resultado à variável Python in_style.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                rcdata = re.search(r"<\s*(title|textarea)\b", line, re.I) # Explicação DevIN: Atribui resultado à variável Python rcdata.
                if rcdata and not re.search(r"</\s*" + rcdata.group(1) + r"\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_rcdata = rcdata.group(1) # Explicação DevIN: Atribui resultado à variável Python in_rcdata.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if re.search(r"<\s*[A-Za-z][^>]*$", line) and not line.rstrip().endswith("/>"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_tag = True # Explicação DevIN: Atribui resultado à variável Python in_tag.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        elif path.suffix.lower() == ".php": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if re.match(r"^\s*<\?(?:php|=)", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_php = True # Explicação DevIN: Atribui resultado à variável Python in_php.
            mode = "PHP" if in_php else "HTML" # Explicação DevIN: Atribui resultado à variável Python mode.
            if mode == "PHP" and not in_heredoc: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                php_quote, has_php_close = php_line_state(line, php_quote) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                has_php_close = False # Explicação DevIN: Atribui resultado à variável Python has_php_close.
            if has_php_close: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_php = False # Explicação DevIN: Atribui resultado à variável Python in_php.
                php_quote = None # Explicação DevIN: Atribui resultado à variável Python php_quote.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if mode == "HTML": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                if in_rcdata: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    if re.search(r"</\s*" + re.escape(in_rcdata) + r"\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                        in_rcdata = "" # Explicação DevIN: Atribui resultado à variável Python in_rcdata.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if in_script and re.search(r"</\s*script\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_script = False # Explicação DevIN: Atribui resultado à variável Python in_script.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if in_style and re.search(r"</\s*style\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_style = False # Explicação DevIN: Atribui resultado à variável Python in_style.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                if in_script: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    mode = "JavaScript" # Explicação DevIN: Atribui resultado à variável Python mode.
                elif in_style: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    mode = "CSS" # Explicação DevIN: Atribui resultado à variável Python mode.
                elif in_tag: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_tag = ">" not in line # Explicação DevIN: Atribui resultado à variável Python in_tag.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                elif re.search(r"<\s*script\b", line, re.I) and not re.search(r"</\s*script\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_script = True # Explicação DevIN: Atribui resultado à variável Python in_script.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                elif re.search(r"<\s*style\b", line, re.I) and not re.search(r"</\s*style\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_style = True # Explicação DevIN: Atribui resultado à variável Python in_style.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                    rcdata = re.search(r"<\s*(title|textarea)\b", line, re.I) # Explicação DevIN: Atribui resultado à variável Python rcdata.
                    if rcdata and not re.search(r"</\s*" + rcdata.group(1) + r"\s*>", line, re.I): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                        in_rcdata = rcdata.group(1) # Explicação DevIN: Atribui resultado à variável Python in_rcdata.
                        output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                        skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                        continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                    if re.search(r"<\s*[A-Za-z][^>]*$", line) and not line.rstrip().endswith("/>"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                        in_tag = True # Explicação DevIN: Atribui resultado à variável Python in_tag.
                        output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                        skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                        continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        elif path.suffix.lower() in (".yml", ".yaml"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            indent = len(line) - len(line.lstrip()) # Explicação DevIN: Atribui resultado à variável Python indent.
            if yaml_block_indent is not None and indent <= yaml_block_indent: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                yaml_block_indent = None # Explicação DevIN: Atribui resultado à variável Python yaml_block_indent.
                yaml_shell_block = False # Explicação DevIN: Atribui resultado à variável Python yaml_shell_block.
            if yaml_block_indent is not None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                if yaml_shell_block and not line.rstrip().endswith("\\"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    mode = "Shell" # Explicação DevIN: Atribui resultado à variável Python mode.
                else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                    output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            else: # Explicação DevIN: Define o caminho alternativo de uma condição anterior.
                block = re.match(r"^(\s*[^#].*?:)\s*[|>][+-]?\s*(?:#.*)?$", line) # Explicação DevIN: Atribui resultado à variável Python block.
                if block: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    yaml_block_indent = len(block.group(1)) - len(block.group(1).lstrip()) # Explicação DevIN: Atribui resultado à variável Python yaml_block_indent.
                    yaml_shell_block = bool(re.search(r"\brun\s*:", block.group(1))) # Explicação DevIN: Atribui resultado à variável Python yaml_shell_block.
                mode = "YAML" # Explicação DevIN: Atribui resultado à variável Python mode.

        if mode == "CSS": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            started_in_comment = in_css_comment # Explicação DevIN: Atribui resultado à variável Python started_in_comment.
            position = 0 # Explicação DevIN: Atribui resultado à variável Python position.
            while position < len(line): # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
                if in_css_comment: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    closing = line.find("*/", position) # Explicação DevIN: Atribui resultado à variável Python closing.
                    if closing < 0: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                        break # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                    in_css_comment = False # Explicação DevIN: Atribui resultado à variável Python in_css_comment.
                    position = closing + 2 # Explicação DevIN: Atribui resultado à variável Python position.
                    continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                token = re.search(r""" "(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|/\* """.strip(), line[position:]) # Explicação DevIN: Atribui resultado à variável Python token.
                if token is None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    break # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
                position += token.end() # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                if token.group() == "/*": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_css_comment = True # Explicação DevIN: Atribui resultado à variável Python in_css_comment.
            if started_in_comment or in_css_comment: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        style = comment_for(mode) # Explicação DevIN: Atribui resultado à variável Python style.
        if style is None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        # Literal bodies and continued strings are data, not source statements.
        if in_heredoc: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            if stripped == in_heredoc or stripped == in_heredoc + ";": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_heredoc = "" # Explicação DevIN: Atribui resultado à variável Python in_heredoc.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if php_quote is not None: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode in ("JavaScript", "JavaScript/CommonJS", "JavaScript/ESM", # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    "TypeScript", "TypeScript/JSX"): # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            tick_count = len(re.findall(r"(?<!\\)`", line)) # Explicação DevIN: Atribui resultado à variável Python tick_count.
            if in_template: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                if tick_count: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                    in_template = False # Explicação DevIN: Atribui resultado à variável Python in_template.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
            if tick_count % 2: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_template = True # Explicação DevIN: Atribui resultado à variável Python in_template.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "PHP" and "<<<" in line: # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            heredoc = re.search(r"<<<\s*['\"]?([A-Za-z_][A-Za-z0-9_]*)", line) # Explicação DevIN: Atribui resultado à variável Python heredoc.
            if heredoc and not re.search(r"\b" + re.escape(heredoc.group(1)) + r"\b", line[heredoc.end():]): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
                in_heredoc = heredoc.group(1) # Explicação DevIN: Atribui resultado à variável Python in_heredoc.
                output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
                continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        if is_comment_line(line, mode): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Shell" and line.rstrip().endswith("\\"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Python" and line.rstrip().endswith("\\"): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if has_inline_comment(line, mode): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "PHP" and re.match(r"^\s*<\?(?:php)?\s*\??\s*$", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            description, previous = line_explanation(line, mode, previous) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            opening = "// " + ANNOTATION_MARKER + " " + description # Explicação DevIN: Atribui resultado à variável Python opening.
            output.append(line + " " + opening + ending) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            added += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode in ("HTML", "SVG/XML", "XML") and re.match(r"^\s*<\?xml\b", line): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.
        if mode == "Python" and (line.startswith("#!") or re.match(r"^\s*#.*coding[:=]", line)): # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
            output.append(raw) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            skipped += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
            continue # Explicação DevIN: Controla a execução do laço ou define bloco intencionalmente vazio.

        description, previous = line_explanation(line, mode, previous) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
        prefix, suffix = style # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
        annotated = line + " " + prefix + ANNOTATION_MARKER + " " + description + suffix # Explicação DevIN: Atribui resultado à variável Python annotated.
        output.append(annotated + ending) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.
        added += 1 # Explicação DevIN: Continua expressão ou bloco Python pertencente a função annotate_source.

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
