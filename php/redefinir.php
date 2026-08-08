<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - DevIN</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

    <div class="card">
        <h2>Criar Nova Senha</h2>
        <div class="logo">Dev<span>IN</span></div>
        
        <form action="processar.php?acao=salvar" method="POST">
            <input type="hidden" id="token" name="token">

            <label for="senha">Nova Senha:</label>
            <div class="input-container">
                <input type="password" id="senha" name="senha" placeholder="Digite a nova senha..." required>
            </div>

            <button type="submit" class="btn-enviar">Salvar Nova Senha</button>
        </form>
    </div>

    <script>
        // JS para pegar o token da URL (?token=xyz) e colocar no formulário
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            document.getElementById('token').value = token;
        } else {
            alert('Token inválido ou ausente!');
        }
    </script>
</body>
</html>