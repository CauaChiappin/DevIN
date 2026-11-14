#!/usr/bin/env python3
"""Explicações linha a linha do código DevIN, sem tocar nos arquivos executáveis.

Uso:
  python3 scripts/line_explanations.py --write
  python3 scripts/line_explanations.py --check
  python3 scripts/line_explanations.py --stats

Não requer API de IA, chave, banco ou pacote externo.
As descrições são assistidas por regras de linguagem e devem ser revisadas.
"""
from __future__ import annotations

import argparse
import hashlib
import json
import re
import subprocess
import sys
from pathlib import Path
from urllib.parse import quote

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "docs" / "line-by-line"
SUFFIXES = {".php", ".js", ".cjs", ".mjs", ".css", ".html", ".htm", ".sql",
            ".json", ".yml", ".yaml", ".svg", ".xml", ".sh", ".ts", ".tsx"}
SPECIAL_FILES = {".env.example", ".htaccess", ".gitignore", "Dockerfile", "Makefile"}
IGNORE_SEGMENTS = {"vendor", "node_modules", ".git", "dist", "build",
                   "__pycache__", "coverage", "third_party"}
EXCLUDED = {"composer.lock", "package-lock.json", "pnpm-lock.yaml", "yarn.lock"}
LANGUAGE = {
    ".php": "PHP", ".js": "JavaScript", ".cjs": "JavaScript/CommonJS",
    ".mjs": "JavaScript/ESM", ".css": "CSS", ".html": "HTML", ".htm": "HTML",
    ".sql": "SQL/MySQL", ".json": "JSON", ".yml": "YAML", ".yaml": "YAML",
    ".svg": "SVG/XML", ".xml": "XML", ".sh": "Shell",
    ".ts": "TypeScript", ".tsx": "TypeScript/JSX",
}
CSS_PROPERTIES = {
    "display": "modo de layout", "position": "modo de posicionamento",
    "width": "largura", "height": "altura", "min-width": "largura mínima",
    "max-width": "largura máxima", "min-height": "altura mínima",
    "max-height": "altura máxima", "padding": "espaçamento interno",
    "margin": "espaçamento externo", "color": "cor do texto",
    "background": "fundo", "background-color": "cor de fundo",
    "border": "borda", "border-radius": "arredondamento dos cantos",
    "box-shadow": "sombra", "font-size": "tamanho da fonte",
    "font-family": "família tipográfica", "font-weight": "peso tipográfico",
    "line-height": "altura de linha", "gap": "espaçamento entre itens",
    "flex": "comportamento de flexibilidade", "flex-direction": "direção dos itens",
    "grid-template-columns": "colunas da grade", "grid-template-rows": "linhas da grade",
    "align-items": "alinhamento transversal", "justify-content": "distribuição dos itens",
    "overflow": "tratamento de conteúdo excedente", "overflow-x": "transbordamento horizontal",
    "overflow-y": "transbordamento vertical", "cursor": "aparência do cursor",
    "opacity": "opacidade", "transform": "transformação geométrica",
    "transition": "transição animada", "z-index": "ordem de sobreposição",
    "top": "distância em relação à borda superior",
    "bottom": "distância em relação à borda inferior",
    "left": "distância em relação à borda esquerda",
    "right": "distância em relação à borda direita",
    "touch-action": "tratamento de gestos de toque",
    "white-space": "tratamento de espaços e quebras de linha",
    "word-break": "regras de quebra de palavras", "overflow-wrap": "quebra de texto longo",
    "box-sizing": "cálculo das dimensões",
}
TAG_MEANING = {
    "html": "elemento raiz do documento HTML",
    "head": "metadados e dependências do documento",
    "body": "corpo visível da página",
    "main": "conteúdo principal da página",
    "header": "cabeçalho da seção ou página",
    "footer": "rodapé", "section": "seção temática",
    "article": "conteúdo independente", "nav": "navegação",
    "aside": "conteúdo auxiliar", "div": "agrupamento de elementos",
    "span": "trecho em linha", "p": "parágrafo",
    "h1": "título principal", "h2": "subtítulo de segundo nível",
    "h3": "subtítulo de terceiro nível",
    "form": "formulário de entrada de dados",
    "input": "campo de entrada",
    "textarea": "campo de texto multilinha",
    "select": "seleção de opções",
    "option": "opção selecionável",
    "button": "botão de ação",
    "label": "rótulo associado a um campo",
    "a": "link de navegação",
    "img": "imagem na interface",
    "script": "script ou dependência JavaScript",
    "link": "dependência ou relação com arquivo externo",
    "meta": "metadado de navegação",
    "table": "tabela de dados",
    "tr": "linha de tabela",
    "td": "célula de dados",
    "th": "cabeçalho de coluna ou linha",
    "ul": "lista sem numeração",
    "li": "item da lista",
    "svg": "gráfico vetorial",
    "path": "contorno vetorial",
    "circle": "círculo vetorial",
}


def source_paths() -> list[Path]:
    result = subprocess.run(
        ["git", "ls-files", "-z"], cwd=ROOT, check=True, capture_output=True
    )
    files = []
    for value in result.stdout.decode("utf-8", "replace").split("\0"):
        if not value:
            continue
        rel = Path(value)
        if rel.name in EXCLUDED or set(rel.parts) & IGNORE_SEGMENTS:
            continue
        if rel.parts[:2] == ("docs", "line-by-line"):
            continue
        if rel.suffix.lower() not in SUFFIXES and rel.name not in SPECIAL_FILES:
            continue
        file = ROOT / rel
        if not file.is_file():
            continue
        files.append(rel)
    return sorted(files, key=lambda p: str(p))


def lang_of(path: Path) -> str:
    if path.name == ".htaccess":
        return "Apache/.htaccess"
    if path.name == ".env.example":
        return "dotenv"
    if path.name == ".gitignore":
        return "Git ignore"
    if path.name in ("Makefile", "Dockerfile"):
        return path.name
    return LANGUAGE.get(path.suffix.lower(), "configuração")


def ident(text: str, limit: int = 55) -> str:
    """Extrai identificador seguro, sem copiar literais, senhas ou conteúdo da linha."""
    name = re.search(r"[$]?[A-Za-z_][A-Za-z0-9_.$:-]{0,65}", text)
    return (name.group(0) if name else "instrução atual")[:limit]


def line_explanation(raw: str, language: str, previous: str) -> tuple[str, str]:
    """Resumir uma linha sem reproduzir valores literais potencialmente sensíveis."""
    line = raw.strip()
    if not line:
        return "Linha em branco: separa visualmente trechos do código.", previous

    if line.startswith(("/*", "* ", "*/", "//", "<!--", "-- ", "# ")) and not line.startswith("#!"):
        return "Comentário do autor: documenta a intenção, uma ressalva ou a organização do trecho.", previous
    if line.startswith("#!") and language == "Shell":
        return "Declara o interpretador responsável por executar o script.", previous

    if language == "CSS":
        if line.startswith("@media"):
            return "Abre regra responsiva: aplica estilos somente sob determinada condição de tela.", "regra responsiva CSS"
        if line.startswith("@supports"):
            return "Abre regra condicional CSS conforme suporte do navegador.", "suporte CSS"
        if line.startswith("@keyframes"):
            return "Define sequência de quadros de uma animação CSS.", "animação CSS"
        if line.startswith(("--",)) and ":" in line:
            return "Define variável CSS customizada, reutilizada por outras regras.", previous
        found = re.match(r"([a-zA-Z-]+)\s*:\s*(.+?);?\s*$", line)
        if found:
            prop = found.group(1)
            meaning = CSS_PROPERTIES.get(prop, "propriedade visual ou de layout")
            return "Define " + meaning + " por meio da propriedade CSS " + prop + ".", previous
        if line.endswith("{"):
            name = line[:-1].strip()
            label = "seletor de elemento/classe" if not name.startswith("@") else "regra especial"
            return "Inicia bloco de estilos associado ao " + label + " desta linha.", "bloco CSS"
        if line == "}":
            return "Fecha o bloco atual de estilos CSS.", previous
        if line.startswith(("from ", "to ", "0%", "100%", "50%")):
            return "Declara etapa de uma animação ou transição CSS.", previous
        return "Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior.", previous

    if language in ("HTML", "SVG/XML", "XML") or (
        language == "PHP" and line.startswith(("<", "</")) and not line.startswith("<?")
    ):
        if line.startswith("<?php"):
            return "Abre trecho PHP interpretado no servidor antes de produzir HTML.", previous
        if line.startswith("<?="):
            return "Insere um valor dinâmico no HTML usando expressão PHP.", previous
        if line.startswith("</"):
            tag = re.search(r"</\s*([\w:-]+)", line)
            return "Fecha elemento " + (tag.group(1) if tag else "HTML") + " do documento.", previous
        tags = re.findall(r"<\s*([A-Za-z][\w:-]*)\b", line)
        if tags:
            name = tags[0].lower()
            description = TAG_MEANING.get(name, "elemento de marcação")
            extra = ""
            if re.search(r"\baria-[\w-]+", line):
                extra += " Inclui atributos de acessibilidade."
            if re.search(r"\b(onclick|onsubmit|onchange)\s*=", line):
                extra += " Vincula um evento da interface."
            if "<?" in line:
                extra += " Contém dado dinâmico processado pelo PHP."
            if len(tags) > 1:
                extra += " A linha reúne múltiplos elementos."
            return "Declara " + description + "." + extra, "marcação " + name
        if line.startswith("<!--"):
            return "Comentário da marcação HTML, sem efeito visual.", previous
        return "Continua atributos ou conteúdo do elemento de marcação aberto anteriormente.", previous

    if language in ("JSON", "YAML", "dotenv", "Apache/.htaccess", "Git ignore", "Makefile", "Dockerfile"):
        if language == "Git ignore":
            return "Regra Git: define caminho ou padrão a não versionar (ou exceção).", previous
        if language == "Apache/.htaccess":
            if line.startswith(("<If", "</If", "<Files", "</Files")):
                return "Abre ou fecha bloco de regras Apache para limitar acesso ou reescrever solicitações.", previous
            if re.search(r"\bRewriteRule\b", line):
                return "Aplica regra de reescrita ou redirecionamento de URL no Apache.", previous
            if re.search(r"\bRewriteCond\b", line):
                return "Define condição necessária para uma regra Apache de reescrita.", previous
            return "Configura diretiva do servidor Apache associada à segurança ou ao roteamento.", previous
        if language == "dotenv":
            if "=" in line and not line.startswith("#"):
                return "Declara uma variável de configuração de exemplo; seu valor real deve permanecer fora do Git.", previous
            return "Organiza a configuração de exemplo do projeto.", previous
        if language == "JSON":
            member = re.match(r'["\']([^"\']+)["\']\s*:', line)
            if member:
                key = member.group(1)
                return "Define a chave JSON " + key[:60] + " e seu valor de configuração.", "chave JSON"
            if line in ("{", "["):
                return "Inicia objeto ou lista de dados JSON.", previous
            if line.startswith(("}", "]")):
                return "Encerra objeto ou lista de dados JSON.", previous
            return "Continua valor, lista ou estrutura de configuração JSON.", previous
        if language == "YAML":
            label = re.match(r"([\w-]+)\s*:", line)
            if label:
                key = label.group(1)
                ydesc = {
                    "on": "acionadores do workflow",
                    "jobs": "conjunto de trabalhos do workflow",
                    "steps": "sequência de etapas",
                    "runs-on": "ambiente executor",
                    "uses": "ação reutilizável",
                    "run": "comando de execução",
                    "env": "variáveis de ambiente",
                    "permissions": "permissões do workflow",
                    "branches": "filtro de branches",
                    "paths": "filtro de caminhos",
                    "services": "serviços auxiliares",
                    "if": "condição de execução",
                    "name": "nome identificador",
                }.get(key, "propriedade YAML")
                return "Configura " + ydesc + " (campo " + key + ").", "configuração YAML"
            if line.startswith("- "):
                return "Adiciona item a uma lista da configuração YAML.", previous
            return "Continua valor ou parâmetro da configuração YAML.", previous
        if line.startswith(("@", ".PHONY")):
            return "Declara diretiva ou alvo especial do arquivo de build.", previous
        if "=" in line:
            return "Atribui configuração ou variável de execução do arquivo.", previous
        return "Define comando ou diretiva de configuração para o ambiente.", previous

    if language == "SQL/MySQL":
        upper = line.upper()
        if upper.startswith("CREATE TABLE"):
            return "Cria tabela persistente no banco de dados, se aplicável com proteção de existência.", "criação de tabela"
        if upper.startswith("ALTER TABLE"):
            return "Inicia mudança de estrutura de tabela existente.", "alteração de tabela"
        if upper.startswith(("INSERT INTO", "REPLACE INTO")):
            return "Insere dados na tabela indicada pela instrução SQL.", "inserção SQL"
        if upper.startswith("UPDATE "):
            return "Inicia atualização de registros; o filtro posterior determina o alcance.", "atualização SQL"
        if upper.startswith("DELETE FROM"):
            return "Inicia exclusão SQL de registros sujeitos às condições da instrução.", "exclusão SQL"
        if upper.startswith("SELECT "):
            return "Seleciona campos ou resultados para uma consulta ao banco.", "consulta SQL"
        if upper.startswith("WHERE "):
            return "Restringe os registros afetados pela condição SQL.", previous
        if "PRIMARY KEY" in upper:
            return "Declara a chave primária para identificar registros sem duplicação.", previous
        if "FOREIGN KEY" in upper:
            return "Declara relação de integridade referencial entre tabelas.", previous
        if "INDEX " in upper or upper.startswith("KEY "):
            return "Cria índice para auxiliar buscas ou ordenação no banco.", previous
        if "ENGINE=" in upper or "CHARSET" in upper:
            return "Define mecanismo de armazenamento ou conjunto de caracteres da tabela.", previous
        if "DEFAULT" in upper or re.match(r"^[\w]+\s+(VARCHAR|TEXT|BIGINT|INT|DATE|ENUM|TIMESTAMP|DATETIME)", upper):
            return "Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.", previous
        if upper.startswith(("VALUES", "ORDER BY", "LIMIT ", "GROUP BY", "JOIN ", "ON ")):
            return "Completa cláusula de consulta ou manipulação SQL.", previous
        return "Continua a definição, restrição ou comando SQL do bloco atual.", previous

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


def render(path: Path) -> tuple[str, int]:
    absolute = ROOT / path
    data = absolute.read_bytes()
    try:
        source = data.decode("utf-8-sig")
    except UnicodeDecodeError as exc:
        raise RuntimeError("Arquivo de código sem UTF-8: " + str(path)) from exc
    language = lang_of(path)
    lines = source.splitlines()
    sha = hashlib.sha256(data).hexdigest()
    rel = path.as_posix()
    link = "https://github.com/CauaChiappin/DevIN/blob/main/" + quote(rel, safe="/._-")
    doc = [
        "# Explicação linha a linha — " + rel,
        "",
        "**Arquivo original:** [abrir código no GitHub](" + link + ")  ",
        "**Linguagem:** " + language + "  ",
        "**Linhas de origem:** " + str(len(lines)) + "  ",
        "**SHA-256 da origem:** " + sha,
        "",
        "> Descrições iniciais produzidas automaticamente, linha a linha, por regras de sintaxe.",
        "> Explicações de comportamento complexo ou ambíguo exigem revisão técnica humana.",
        "> Os links abrem diretamente a linha correspondente do código real; nenhum",
        "> arquivo executável é alterado pela geração desta documentação.",
        "",
        "| Linha do código | Explicação da linha |",
        "| ---: | --- |",
    ]
    previous = "trecho principal"
    for index, line in enumerate(lines, start=1):
        description, previous = line_explanation(line, language, previous)
        description = description.replace("|", "&#124;").replace("\n", " ")
        doc.append("| [L" + str(index) + "](" + link + "#L" + str(index)
                   + ") | " + description + " |")
    doc.append("")
    return "\n".join(doc), len(lines)


def expected_docs() -> tuple[dict[Path, str], int, int]:
    generated: dict[Path, str] = {}
    total = 0
    for source in source_paths():
        output = OUTPUT / (source.as_posix() + ".md")
        rendered, count = render(source)
        generated[output] = rendered
        total += count
    index = [
        "# Índice — explicação de cada linha de código do DevIN",
        "",
        "Toda linha de todo arquivo de código versionado listado abaixo possui",
        "um registro vinculado à linha original, incluindo comentários e linhas vazias.",
        "As explicações são geradas por regras de leitura estática e marcadas",
        "como **pendentes de revisão humana**; a geração não prova o comportamento",
        "real em produção.",
        "",
        "**Arquivos documentados:** " + str(len(generated)) + "  ",
        "**Linhas de código e de formatação rastreadas:** " + str(total),
        "",
        "## Navegação por arquivo",
        "",
        "| Arquivo de origem | Explicação por linha | Linhas |",
        "| --- | --- | ---: |",
    ]
    for source in source_paths():
        doc = OUTPUT / (source.as_posix() + ".md")
        explanation = generated[doc]
        count = explanation.count("\n| [L")
        index.append("| " + source.as_posix().replace("|", "&#124;")
                     + " | [abrir explicações](" + quote(source.as_posix() + ".md", safe="/._-")
                     + ") | " + str(count) + " |")
    index.extend([
        "",
        "## Atualização obrigatória",
        "",
        "1. Modifique o código normalmente, sem adicionar comentários que mudem sua execução.",
        "2. Execute o gerador em modo de escrita para atualizar as páginas de cada linha.",
        "3. Execute o modo de conferência; o CI rejeita páginas ausentes ou desatualizadas.",
        "4. Revise manualmente os trechos críticos de autenticação, dados, segurança e regras de negócio.",
        "",
        "A automação do GitHub também executa o gerador depois de pushes e",
        "registra a documentação atualizada na própria branch, quando permitido.",
        "Não inclua senhas, tokens, dados reais de usuários ou arquivos binários.",
        "",
    ])
    generated[OUTPUT / "INDEX.md"] = "\n".join(index)
    return generated, len(generated) - 1, total


def run() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    mode = parser.add_mutually_exclusive_group(required=True)
    mode.add_argument("--write", action="store_true")
    mode.add_argument("--check", action="store_true")
    mode.add_argument("--stats", action="store_true")
    options = parser.parse_args()
    generated, file_count, lines = expected_docs()
    tracked = set(OUTPUT.rglob("*.md")) if OUTPUT.exists() else set()
    tracked -= {OUTPUT / "README.md"}
    obsolete = tracked - set(generated)
    outdated = [
        f for f, text in generated.items()
        if not f.is_file() or f.read_text(encoding="utf-8") != text
    ]
    if options.write:
        for file, content in generated.items():
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_text(content, encoding="utf-8", newline="\n")
        for old in obsolete:
            old.unlink()
    print(json.dumps({
        "source_files": file_count,
        "source_lines": lines,
        "outdated_docs": len(outdated),
        "obsolete_docs": len(obsolete),
        "mode": "write" if options.write else "check" if options.check else "stats"
    }, ensure_ascii=False))
    if options.check and (outdated or obsolete):
        for file in (outdated + list(obsolete))[:25]:
            print("Documentação ausente/desatualizada: " + file.relative_to(ROOT).as_posix(), file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(run())
