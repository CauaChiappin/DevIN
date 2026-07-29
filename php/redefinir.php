<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha - DevIN</title>
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>

    <div class="card card-redefinir">
        <h2>Criar Nova Senha</h2>
        <div class="logo">Dev<span>IN</span></div>
        
        <form action="processar.php?acao=salvar" method="POST" id="formRedefinir" onsubmit="return validarSenhas()">
            <input type="hidden" id="token" name="token">

            <div class="form-group">
                <label for="senha">Nova Senha:*</label>
                <div class="input-container">
                    <span class="icon">🔒</span>
                    <input type="password" id="senha" name="senha" placeholder="Digite a nova senha..." required onkeyup="validarRequisitos()">
                </div>
            </div>

            <div class="password-requirements">
                <div class="req-item req-invalid" id="req-length">
                    <span class="req-icon">✖</span> No mínimo 8 caracteres
                </div>
                <div class="req-item req-invalid" id="req-upper">
                    <span class="req-icon">✖</span> Pelo menos 1 letra maiúscula (A-Z)
                </div>
                <div class="req-item req-invalid" id="req-special">
                    <span class="req-icon">✖</span> Pelo menos 1 caracter especial (como ! @ # $)
                </div>
            </div>

            <div class="form-group">
                <label for="confirme_senha">Confirme a sua senha:*</label>
                <div class="input-container">
                    <span class="icon">🔒</span>
                    <input type="password" id="confirme_senha" name="confirme_senha" placeholder="Confirme a nova senha..." required>
                </div>
            </div>

            <div class="botoes-grupo">
                <button type="submit" class="btn-enviar">Salvar Nova Senha</button>
                <a href="login.php" class="btn-voltar">Voltar</a>
            </div>
        </form>
    </div>

    <footer>
        DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

    <script>
        // Pega token da URL (?token=xyz)
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            document.getElementById('token').value = token;
        } else {
            alert('Token inválido ou ausente!');
            window.location.href = 'recuperacao.php';
        }

        function validarRequisitos() {
            const senha = document.getElementById('senha').value;

            // Requisito: Mínimo 8 caracteres
            const reqLength = document.getElementById('req-length');
            if (senha.length >= 8) {
                reqLength.className = 'req-item req-valid';
                reqLength.querySelector('.req-icon').innerText = '✔';
            } else {
                reqLength.className = 'req-item req-invalid';
                reqLength.querySelector('.req-icon').innerText = '✖';
            }

            // Requisito: Letra maiúscula
            const reqUpper = document.getElementById('req-upper');
            if (/[A-Z]/.test(senha)) {
                reqUpper.className = 'req-item req-valid';
                reqUpper.querySelector('.req-icon').innerText = '✔';
            } else {
                reqUpper.className = 'req-item req-invalid';
                reqUpper.querySelector('.req-icon').innerText = '✖';
            }

            // Requisito: Caractere especial
            const reqSpecial = document.getElementById('req-special');
            if (/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                reqSpecial.className = 'req-item req-valid';
                reqSpecial.querySelector('.req-icon').innerText = '✔';
            } else {
                reqSpecial.className = 'req-item req-invalid';
                reqSpecial.querySelector('.req-icon').innerText = '✖';
            }
        }

        function validarSenhas() {
            const senha = document.getElementById('senha').value;
            const confirme = document.getElementById('confirme_senha').value;

            if (senha !== confirme) {
                alert('As senhas digitadas não coincidem!');
                return false;
            }
            if (senha.length < 8 || !/[A-Z]/.test(senha) || !/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                alert('A senha deve atender a todos os requisitos de segurança.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>