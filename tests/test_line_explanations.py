"""Verifica explicações estruturais, cobertura e ausência de valores sensíveis."""
from __future__ import annotations # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.

import unittest # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
from pathlib import Path # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.

from scripts.line_explanations import ( # Explicação DevIN: Importa módulo, nome ou função que será utilizado neste script Python.
    ANNOTATION_MARKER, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    NON_COMMENTABLE_NAMES, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    NON_COMMENTABLE_SUFFIXES, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    annotate_source, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    has_inline_comment, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    line_explanation, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    php_line_state, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
    source_paths, # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.
) # Explicação DevIN: Executa parte de uma expressão, chamada ou configuração Python.


class LineExplanationsTest(unittest.TestCase): # Explicação DevIN: Declara classe Python LineExplanationsTest.
    def test_php_structures_are_classified(self) -> None: # Explicação DevIN: Declara função Python test_php_structures_are_classified, com seus argumentos e lógica.
        result, _ = line_explanation("function csrfToken(): string {", "PHP", "trecho principal") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.
        self.assertIn("csrfToken", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.
        result, _ = line_explanation("if ($id < 1) {", "PHP", "trecho principal") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.
        self.assertIn("condicional", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.
        result, _ = line_explanation("$stmt->execute();", "PHP", "função pesquisa") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.
        self.assertIn("SQL", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_structures_are_classified.

    def test_css_and_html_structures_are_classified(self) -> None: # Explicação DevIN: Declara função Python test_css_and_html_structures_are_classified, com seus argumentos e lógica.
        result, _ = line_explanation("display:flex;", "CSS", "bloco CSS") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_css_and_html_structures_are_classified.
        self.assertIn("layout", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_css_and_html_structures_are_classified.
        result, _ = line_explanation('<button aria-label="Enviar mensagem">', "HTML", "trecho principal") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_css_and_html_structures_are_classified.
        self.assertIn("botão", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_css_and_html_structures_are_classified.
        self.assertIn("acessibilidade", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_css_and_html_structures_are_classified.

    def test_configuration_never_copies_api_keys(self) -> None: # Explicação DevIN: Declara função Python test_configuration_never_copies_api_keys, com seus argumentos e lógica.
        secret = "sk-or-v1-THIS_IS_A_SENTINEL_DO_NOT_COPY" # Explicação DevIN: Atribui resultado à variável Python secret.
        for line, language in [ # Explicação DevIN: Inicia laço Python para percorrer dados ou repetir enquanto houver condição.
            ("DEVIN_JARVIS_API_KEY=" + secret, "dotenv"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.
            ("DB_PASS=" + secret, "dotenv"), # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.
        ]: # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.
            explanation, _ = line_explanation(line, language, "trecho principal") # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.
            self.assertNotIn(secret, explanation) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.
            self.assertIn("variável", explanation) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_configuration_never_copies_api_keys.

    def test_comments_are_added_inside_source_and_are_idempotent(self) -> None: # Explicação DevIN: Declara função Python test_comments_are_added_inside_source_and_are_idempotent, com seus argumentos e lógica.
        path = Path("example.js") # Explicação DevIN: Atribui resultado à variável Python path.
        source = "const total = 2;\nreturn total;\n" # Explicação DevIN: Atribui resultado à variável Python source.
        once, added, _ = annotate_source(path, source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        twice, added_again, _ = annotate_source(path, once) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        self.assertEqual(added, 2) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        self.assertEqual(added_again, 2) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        self.assertEqual(once, twice) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        self.assertEqual(once.count(ANNOTATION_MARKER), 2) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.
        self.assertIn("const total = 2; // " + ANNOTATION_MARKER, once) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comments_are_added_inside_source_and_are_idempotent.

    def test_html_script_and_style_use_their_native_comment_syntax(self) -> None: # Explicação DevIN: Declara função Python test_html_script_and_style_use_their_native_comment_syntax, com seus argumentos e lógica.
        source = ( # Explicação DevIN: Atribui resultado à variável Python source.
            "<main>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "<script>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "const ready = true;\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "</script>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "<style>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            ".card {\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "display: flex;\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "}\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "</style>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
            "</main>\n" # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        ) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        result, _, _ = annotate_source(Path("page.html"), source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        self.assertIn("const ready = true; // " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        self.assertIn(".card { /* " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        self.assertIn("<main> <!-- " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        self.assertIn("<script>\n", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.
        self.assertNotIn("<script> <!-- " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_html_script_and_style_use_their_native_comment_syntax.

    def test_multiline_title_text_is_not_changed_by_html_comments(self) -> None: # Explicação DevIN: Declara função Python test_multiline_title_text_is_not_changed_by_html_comments, com seus argumentos e lógica.
        source = "<title>\nPage title\n</title>\n" # Explicação DevIN: Atribui resultado à variável Python source.
        result, _, skipped = annotate_source(Path("page.html"), source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_title_text_is_not_changed_by_html_comments.
        self.assertEqual(result, source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_title_text_is_not_changed_by_html_comments.
        self.assertEqual(skipped, 3) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_title_text_is_not_changed_by_html_comments.

    def test_php_templates_switch_comment_syntax_at_php_boundaries(self) -> None: # Explicação DevIN: Declara função Python test_php_templates_switch_comment_syntax_at_php_boundaries, com seus argumentos e lógica.
        source = "<?php\n$ready = true;\n?>\n<div>Ready</div>\n" # Explicação DevIN: Atribui resultado à variável Python source.
        result, _, _ = annotate_source(Path("page.php"), source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_templates_switch_comment_syntax_at_php_boundaries.
        self.assertIn("$ready = true; // " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_templates_switch_comment_syntax_at_php_boundaries.
        self.assertIn("?>\n", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_templates_switch_comment_syntax_at_php_boundaries.
        self.assertIn("<div>Ready</div> <!-- " + ANNOTATION_MARKER, result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_php_templates_switch_comment_syntax_at_php_boundaries.

    def test_comment_detection_ignores_url_and_xml_string_contents(self) -> None: # Explicação DevIN: Declara função Python test_comment_detection_ignores_url_and_xml_string_contents, com seus argumentos e lógica.
        self.assertFalse(has_inline_comment("$url = 'https://example.test/a';", "PHP")) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comment_detection_ignores_url_and_xml_string_contents.
        quote, closes_php = php_line_state("$xml = '<?xml version=\"1.0\"?>';", None) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comment_detection_ignores_url_and_xml_string_contents.
        self.assertIsNone(quote) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comment_detection_ignores_url_and_xml_string_contents.
        self.assertFalse(closes_php) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_comment_detection_ignores_url_and_xml_string_contents.

    def test_strict_json_and_gitignore_are_not_annotated(self) -> None: # Explicação DevIN: Declara função Python test_strict_json_and_gitignore_are_not_annotated, com seus argumentos e lógica.
        self.assertEqual(NON_COMMENTABLE_SUFFIXES, {".json"}) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_strict_json_and_gitignore_are_not_annotated.
        self.assertIn(".gitignore", NON_COMMENTABLE_NAMES) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_strict_json_and_gitignore_are_not_annotated.
        self.assertNotIn(Path("composer.json"), source_paths()) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_strict_json_and_gitignore_are_not_annotated.
        self.assertNotIn(Path(".gitignore"), source_paths()) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_strict_json_and_gitignore_are_not_annotated.

    def test_multiline_template_literals_are_left_unchanged(self) -> None: # Explicação DevIN: Declara função Python test_multiline_template_literals_are_left_unchanged, com seus argumentos e lógica.
        source = 'const text = `first line\nsecond line\n`;\nconst ready = true;\n' # Explicação DevIN: Atribui resultado à variável Python source.
        result, added, skipped = annotate_source(Path("example.js"), source) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_template_literals_are_left_unchanged.
        self.assertIn("first line\nsecond line\n`;", result) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_template_literals_are_left_unchanged.
        self.assertEqual(added, 1) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_template_literals_are_left_unchanged.
        self.assertGreaterEqual(skipped, 2) # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_template_literals_are_left_unchanged.


if __name__ == "__main__": # Explicação DevIN: Avalia uma condição para decidir se o bloco Python seguinte será executado.
    unittest.main() # Explicação DevIN: Continua expressão ou bloco Python pertencente a função test_multiline_template_literals_are_left_unchanged.
