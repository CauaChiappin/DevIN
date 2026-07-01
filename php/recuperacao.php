<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - DevIN</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

    <div class="card">
        <h2>Recuperação de senha</h2>
        <div class="logo">Dev<span>IN</span></div>
        
        <form action="processar.php?acao=solicitar" method="POST">
            <label for="email">Email:</label>
            <div class="input-container">
                <span class="icon">✉</span>
                <input type="email" id="email" name="email" placeholder="Informe seu email..." required>
            </div>
            <button type="submit" class="btn-enviar">Enviar</button>
        </form>
    </div>

    <footer>
        DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

</body>
</html>