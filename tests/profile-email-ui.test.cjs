/**
 * Regressões do formulário de e-mail dos três painéis DevIN.
 * Não acessa banco de produção nem depende de credenciais reais.
 */
const test = require('node:test'); // Explicação DevIN: Usa o executor Node.
const assert = require('node:assert/strict'); // Explicação DevIN: Compara condições sem conversão implícita.
const fs = require('node:fs'); // Explicação DevIN: Lê os arquivos sob teste.
const path = require('node:path'); // Explicação DevIN: Resolve caminhos locais.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Calcula raiz do repositório.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Examina código versionado.

test('os três perfis exigem senha apenas na troca de e-mail', () => { // Explicação DevIN: Previne alteração de login sem confirmação.
    const controller = read('php/controllers/ProfileController.php'); // Explicação DevIN: Inspeciona a rotina real de atualização.
    assert.match(controller, /password_verify\(\$password/); // Explicação DevIN: Confirma a verificação de senha por hash.
    assert.match(controller, /\$emailChanged/); // Explicação DevIN: Senha só é exigida ao mudar o endereço.
    assert.match(controller, /begin_transaction\(\)/); // Explicação DevIN: Impede mudanças parciais.
    assert.match(controller, /FOR UPDATE/); // Explicação DevIN: Evita alterações concorrentes da própria conta.
    assert.match(controller, /ensureUniqueEmail\(\$tipo, \$id, \$email, \$conn\)/); // Explicação DevIN: Valida duplicidade na mesma conexão.
    assert.match(controller, /getCode\(\) === 1062/); // Explicação DevIN: Mapeia colisões de índice único.
    assert.match(controller, /\$conn->rollback\(\)/); // Explicação DevIN: Reverte falhas.
    for (const role of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Protege todos os dashboards.
        const page = read('php/' + role + '.php'); // Explicação DevIN: Inspeciona o formulário do perfil.
        assert.match(page, /name="senha_atual" type="password"/); // Explicação DevIN: Disponibiliza campo de confirmação.
        assert.match(page, /maxlength="254" autocomplete="email"/); // Explicação DevIN: Impõe limite de entrada no navegador.
        assert.match(page, /\$savedProfile\['email'\]/); // Explicação DevIN: Sessão usa o e-mail salvo, não POST não confiável.
        assert.doesNotMatch(page, /\$_SESSION\['usuario_email'\]\s*=\s*trim\(requestString\(\$_POST/); // Explicação DevIN: Impede sessão divergente do banco.
        assert.match(page, /&perfil=meu/); // Explicação DevIN: Mostra o resultado após salvar.
    } // Explicação DevIN: Conclui revisão de pessoa, empresa e ADM.
}); // Explicação DevIN: Encerra o teste do formulário.

test('formulário e mensagens de validação acompanham o idioma selecionado', () => { // Explicação DevIN: Mantém interface multilíngue consistente.
    const i18n = read('js/dashboard-i18n.js'); // Explicação DevIN: Usa o tradutor principal.
    assert.match(i18n, /Senha atual \(somente para alterar e-mail\)/); // Explicação DevIN: Localiza nova orientação.
    assert.match(i18n, /Current password \(only to change email\)/); // Explicação DevIN: Confirma inglês.
    assert.match(i18n, /Contraseña actual \(solo para cambiar el correo\)/); // Explicação DevIN: Confirma espanhol.
    assert.match(i18n, /O e-mail ultrapassa o tamanho permitido/); // Explicação DevIN: Traduz limites reais do esquema.
}); // Explicação DevIN: Finaliza teste de idioma.
