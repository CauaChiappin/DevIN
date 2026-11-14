"""Verifica explicações estruturais, cobertura e ausência de valores sensíveis."""
from __future__ import annotations

import unittest

from scripts.line_explanations import (
    expected_docs,
    line_explanation,
    source_paths,
)


class LineExplanationsTest(unittest.TestCase):
    def test_php_structures_are_classified(self) -> None:
        result, _ = line_explanation("function csrfToken(): string {", "PHP", "trecho principal")
        self.assertIn("csrfToken", result)
        result, _ = line_explanation("if ($id < 1) {", "PHP", "trecho principal")
        self.assertIn("condicional", result)
        result, _ = line_explanation("$stmt->execute();", "PHP", "função pesquisa")
        self.assertIn("SQL", result)

    def test_css_and_html_structures_are_classified(self) -> None:
        result, _ = line_explanation("display:flex;", "CSS", "bloco CSS")
        self.assertIn("layout", result)
        result, _ = line_explanation('<button aria-label="Enviar mensagem">', "HTML", "trecho principal")
        self.assertIn("botão", result)
        self.assertIn("acessibilidade", result)

    def test_configuration_never_copies_api_keys(self) -> None:
        secret = "sk-or-v1-THIS_IS_A_SENTINEL_DO_NOT_COPY"
        for line, language in [
            ("DEVIN_JARVIS_API_KEY=" + secret, "dotenv"),
            ("DB_PASS=" + secret, "dotenv"),
        ]:
            explanation, _ = line_explanation(line, language, "trecho principal")
            self.assertNotIn(secret, explanation)
            self.assertIn("variável", explanation)

    def test_generates_one_anchored_explanation_for_each_source_line(self) -> None:
        sources = source_paths()
        generated, total_files, total_lines = expected_docs()
        self.assertEqual(len(sources), total_files)
        self.assertGreater(total_files, 100)
        self.assertGreater(total_lines, 25000)
        self.assertIn("scripts/line_explanations.py", [str(s) for s in sources])
        for source in sources:
            dest = next(
                (p for p in generated if p.as_posix().endswith(source.as_posix() + ".md")),
                None,
            )
            self.assertIsNotNone(dest, "Documentação não criada: " + str(source))
            rendered = generated[dest]
            actual_lines = len((dest.parents[len(source.parts)] / source).read_text(
                encoding="utf-8-sig"
            ).splitlines())
            self.assertEqual(
                rendered.count("\n| [L"), actual_lines,
                "Documentação não cobre todas as linhas: " + str(source),
            )


if __name__ == "__main__":
    unittest.main()
