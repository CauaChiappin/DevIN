/**
 * Regressões do formulário de e-mail dos três painéis DevIN.
 * Não acessa banco de produção nem depende de credenciais reais.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

test('os três perfis exigem senha apenas na troca de e-mail', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const controller = read('php/controllers/ProfileController.php'); // Explicação DevIN: Declara variável JavaScript controller para reutilizar valor ou resultado.
    assert.match(controller, /password_verify\(\$password/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /\$emailChanged/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /begin_transaction\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /FOR UPDATE/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /ensureUniqueEmail\(\$tipo, \$id, \$email, \$conn\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /getCode\(\) === 1062/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(controller, /\$conn->rollback\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    for (const role of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const page = read('php/' + role + '.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
        assert.match(page, /name="senha_atual" type="password"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.match(page, /maxlength="254" autocomplete="email"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.match(page, /\$savedProfile\['email'\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.doesNotMatch(page, /\$_SESSION\['usuario_email'\]\s*=\s*trim\(requestString\(\$_POST/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.match(page, /&perfil=meu/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('formulário e mensagens de validação acompanham o idioma selecionado', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const i18n = read('js/dashboard-i18n.js'); // Explicação DevIN: Declara variável JavaScript i18n para reutilizar valor ou resultado.
    assert.match(i18n, /Senha atual \(somente para alterar e-mail\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(i18n, /Current password \(only to change email\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(i18n, /Contraseña actual \(solo para cambiar el correo\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(i18n, /O e-mail ultrapassa o tamanho permitido/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
