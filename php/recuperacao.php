<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - DevIN</title>
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>

    <div class="card">
           <div class="logo">Dev<span>IN</span></div>
        <h2>Recuperação de senha</h2><br>
     
        
        <form id="formSolicitar">
            <label for="email">Email:</label>
            <div class="input-container">
                <span class="icon">✉</span>
                <input type="email" id="email" name="email" placeholder="Informe seu email..." required>
            </div>

            <div class="actions-group">
                <button type="submit" id="btnEnviar" class="btn-enviar">Enviar</button>
                <a href="../php/login.php" class="btn-cancelar">Voltar</a>
            </div>
        </form>

        <div id="mensagemFeedback" class="msg-status"></div>
    </div>

    <footer>
        DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

    <script>
        document.getElementById('formSolicitar').addEventListener('submit', async function(e) {
            e.preventDefault();

            const emailInput = document.getElementById('email').value;
            const btnEnviar = document.getElementById('btnEnviar');
            const feedback = document.getElementById('mensagemFeedback');

            btnEnviar.disabled = true;
            btnEnviar.innerText = "Enviando...";
            feedback.style.display = "none";

            try {
                const resposta = await fetch('processar.php?acao=solicitar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailInput })
                });

                const textoResposta = await resposta.text();
                let resultado = JSON.parse(textoResposta);

                feedback.style.display = "block";

                if (resultado.success) {
                    feedback.className = "msg-status msg-sucesso";
                    let htmlContent = resultado.message;
                    if (resultado.link_teste) {
                        htmlContent += `<br><br><small><b>Link de teste local:</b><br><a href="${resultado.link_teste}">${resultado.link_teste}</a></small>`;
                    }
                    feedback.innerHTML = htmlContent;
                    document.getElementById('email').value = '';
                } else {
                    feedback.className = "msg-status msg-erro";
                    feedback.innerText = resultado.message;
                }
            } catch (erro) {
                feedback.style.display = "block";
                feedback.className = "msg-status msg-erro";
                feedback.innerText = "Erro ao comunicar com o servidor.";
            } finally {
                btnEnviar.disabled = false;
                btnEnviar.innerText = "Enviar";
            }
        });
    </script>

</body>
</html>