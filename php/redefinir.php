<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - DevIN</title>
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>

    <div class="card">
        <h2>Criar Nova Senha</h2>
        <div class="logo">Dev<span>IN</span></div>
        
        <form id="formRedefinir">
            <input type="hidden" id="token" name="token">

            <label for="senha">Nova Senha:*</label>
            <div class="input-container">
                <span class="icon">🔒</span>
                <input type="password" id="senha" name="senha" placeholder="Digite a nova senha..." required>
                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Ocultar/Mostrar Senha">
            </div>

            <div class="password-requirements">
                <div class="requirement-item req-invalid" id="req-length">
                    <span class="req-icon">⚠️</span> No mínimo 8 caracteres
                </div>
                <div class="requirement-item req-invalid" id="req-upper">
                    <span class="req-icon">⚠️</span> Pelo menos 1 letra maiúscula (A-Z)
                </div>
                <div class="requirement-item req-invalid" id="req-special">
                    <span class="req-icon">⚠️</span> Pelo menos 1 caracter especial (como ! @ # $)
                </div>
            </div>

            <label for="confirme_senha">Confirme a sua senha:*</label>
            <div class="input-container">
                <span class="icon">🔒</span>
                <input type="password" id="confirme_senha" name="confirme_senha" placeholder="Confirme a nova senha..." required>
                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Ocultar/Mostrar Senha">
            </div>
            <span id="error-match" class="error-message-text">Senhas não coincidem</span>

            <div class="actions-group">
                <button type="submit" id="btnSalvar" class="btn-enviar">Salvar Nova Senha</button>
                <a href="../php/recuperacao.php" class="btn-cancelar">Voltar</a>
            </div>
        </form>

        <div id="mensagemFeedback" class="msg-status"></div>
    </div>

    <footer>
        DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

    <script>
        const senhaInput = document.getElementById('senha');
        const confirmeSenhaInput = document.getElementById('confirme_senha');
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqSpecial = document.getElementById('req-special');
        const errorMatch = document.getElementById('error-match');
        const feedback = document.getElementById('mensagemFeedback');

        // Alternar visibilidade da senha trocando a imagem
        function togglePasswordVisibility(inputId, imgElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                imgElement.src = '../img/olho_aberto.png';
            } else {
                input.type = 'password';
                imgElement.src = '../img/olho_fechado.png';
            }
        }

        // Atualizar marcadores visuais (✅ / ⚠️)
        function updateRequirement(element, isValid) {
            const icon = element.querySelector('.req-icon');
            if (isValid) {
                element.classList.remove('req-invalid');
                element.classList.add('req-valid');
                icon.textContent = '✅';
            } else {
                element.classList.remove('req-valid');
                element.classList.add('req-invalid');
                icon.textContent = '⚠️';
            }
        }

        // Validação em tempo real
        senhaInput.addEventListener('input', () => {
            const val = senhaInput.value;
            updateRequirement(reqLength, val.length >= 8);
            updateRequirement(reqUpper, /[A-Z]/.test(val));
            updateRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/.test(val));
            checkPasswordMatch();
        });

        function checkPasswordMatch() {
            if (confirmeSenhaInput.value === '') {
                errorMatch.classList.remove('visible');
                return;
            }
            if (senhaInput.value !== confirmeSenhaInput.value) {
                errorMatch.classList.add('visible');
            } else {
                errorMatch.classList.remove('visible');
            }
        }

        confirmeSenhaInput.addEventListener('input', checkPasswordMatch);

        // Obter Token da URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (token) {
            document.getElementById('token').value = token;
        } else {
            feedback.style.display = "block";
            feedback.className = "msg-status msg-erro";
            feedback.innerText = "Token de recuperação ausente ou inválido na URL.";
            document.getElementById('btnSalvar').disabled = true;
        }

        // Envio do formulário via Fetch
        document.getElementById('formRedefinir').addEventListener('submit', async function(e) {
            e.preventDefault();

            const val = senhaInput.value;
            const isAllValid = (val.length >= 8) && /[A-Z]/.test(val) && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/.test(val);
            const isMatch = senhaInput.value === confirmeSenhaInput.value;

            if (!isAllValid || !isMatch) {
                feedback.style.display = "block";
                feedback.className = "msg-status msg-erro";
                feedback.innerText = "Por favor, atenda a todos os requisitos de senha antes de salvar.";
                return;
            }

            const tokenVal = document.getElementById('token').value;
            const btnSalvar = document.getElementById('btnSalvar');

            btnSalvar.disabled = true;
            btnSalvar.innerText = "Salvando...";
            feedback.style.display = "none";

            try {
                const resposta = await fetch('processar.php?acao=salvar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: tokenVal, senha: val })
                });

                const textoResposta = await resposta.text();
                let resultado;

                try {
                    resultado = JSON.parse(textoResposta);
                } catch (e) {
                    console.error("Resposta do servidor não foi um JSON válido:", textoResposta);
                    throw new Error("O servidor retornou uma resposta inválida. Abra o console (F12) para detalhes.");
                }

                feedback.style.display = "block";

                if (resultado.success) {
                    feedback.className = "msg-status msg-sucesso";
                    feedback.innerText = resultado.message;

                    setTimeout(() => {
                        window.location.href = '../php/login.php';
                    }, 2000);
                } else {
                    feedback.className = "msg-status msg-erro";
                    feedback.innerText = resultado.message;
                    btnSalvar.disabled = false;
                    btnSalvar.innerText = "Salvar Nova Senha";
                }
            } catch (erro) {
                feedback.style.display = "block";
                feedback.className = "msg-status msg-erro";
                feedback.innerText = erro.message || "Erro ao conectar com o servidor.";
                btnSalvar.disabled = false;
                btnSalvar.innerText = "Salvar Nova Senha";
            }
        });
    </script>

</body>
</html>