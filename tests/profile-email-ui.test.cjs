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

test('fotos do perfil distinguem falhas e confirmam a persistência', () => { // Protege feedback e armazenamento de avatares.
    const controller = read('php/controllers/ProfileController.php'); // Carrega controlador compartilhado.
    assert.match(controller, /UPLOAD_ERR_PARTIAL/); // Verifica detecção de envio parcial.
    assert.match(controller, /UPLOAD_ERR_INI_SIZE/); // Verifica detecção do limite PHP.
    assert.match(controller, /mkdir\(\$uploadDirectory, 0755, true\)/); // Confere pasta pública acessível.
    assert.match(controller, /is_writable\(\$uploadDirectory\)/); // Confere escrita.
    assert.match(controller, /\$persisted\['foto'\]/); // Confirmação no banco.
    assert.match(controller, /\$conn->rollback\(\)/); // Falha não conclui transação.
    for (const role of ['pessoa', 'empresa']) { // Garante tratamento nos dois perfis com foto editável.
        const page = read('php/' + role + '.php'); // Inspeciona página.
        assert.match(page, /enctype="multipart\/form-data"/); // Envia bytes e campos.
        assert.match(page, /name="foto" type="file"/); // Exibe seletor.
        assert.match(page, /\$dashboardLoadError/); // Falhas de carregamento visíveis.
        assert.match(page, /dashboard-toast--error/); // Alerta no próprio painel.
    } // Finaliza perfis com foto.
}); // Finaliza contrato de upload.

test('formulários mostram erro CSRF legível sem alterar API JSON', () => { // Evita falha silenciosa ao entrar ou salvar.
    const security = read('php/config/security.php'); // Carrega validação de sessão.
    assert.match(security, /function requireValidCsrf\(bool \$asJson = true\)/); // JSON continua padrão para APIs.
    assert.match(security, /Content-Type: application\/json/); // Resposta JSON preservada.
    assert.match(security, /Content-Type: text\/html/); // Resposta HTML para formulários.
    assert.match(security, /post_max_size/); // Detecta POST acima do limite.
    for (const role of ['login', 'pessoa', 'empresa', 'adm']) { // Página usa feedback visível.
        assert.match(read('php/' + role + '.php'), /requireValidCsrf\(false\)/); // Seleciona resposta HTML.
    } // Finaliza páginas.
}); // Finaliza contrato de erros.
