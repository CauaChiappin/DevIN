<?php
session_start();

// Gera token CSRF caso não exista
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = '';
$sucesso = '';

// Captura o token ou e-mail vindo da URL/Sessão (ajuste conforme seu fluxo de recuperação)
$token_recuperacao = $_GET['token'] ?? $_POST['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_post = $_POST['csrf_token'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // 1. Validação de CSRF
    if (!hash_equals($_SESSION['csrf_token'], $csrf_post)) {
        $erro = 'Sessão inválida. Atualize a página e tente novamente.';
    } 
    // 2. Verifica se os campos estão preenchidos e se coincidem
    elseif (empty($senha) || empty($confirmar_senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } 
    // 3. Validação das regras de negócio da senha no Backend
    elseif (
        strlen($senha) < 8 || 
        !preg_match('/[A-Z]/', $senha) || 
        !preg_match('/[\W_]/', $senha)
    ) {
        $erro = 'A senha não atende a todos os requisitos mínimos de segurança.';
    } else {
        // 4. Atualização segura com Hash (Exemplo de integração com PDO)
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        /* 
        // DESCOMENTE E AJUSTE CONFORME SUA CONEXÃO DE BANCO (ex: require_once 'conexao.php';)
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha, token_recuperacao = NULL WHERE token_recuperacao = :token");
            $stmt->execute([
                ':senha' => $senha_hash,
                ':token' => $token_recuperacao
            ]);

            if ($stmt->rowCount() > 0) {
                header('Location: login.php?status=senha_atualizada');
                exit;
            } else {
                $erro = 'Link de recuperação inválido ou expirado.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao atualizar a senha. Tente novamente.';
        }
        */

        // Mensagem de teste caso o banco ainda não esteja conectado neste arquivo:
        $sucesso = 'Senha redefinida com sucesso!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Recuperação de Senha</title>
    <link rel="icon" href="../img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>

    <div class="container-recuperacao">
        <div class="card-recuperacao">
            <h1 class="logo">Dev<span>IN</span></h1>
            <h2 class="titulo">Recuperação de senha</h2>

            <?php if (!empty($erro)): ?>
                <div class="alerta alerta-erro">
                    <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alerta alerta-sucesso">
                    <?php echo htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="redefinir.php" method="POST" id="formRedefinir" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_recuperacao, ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Campo Senha -->
                <div class="grupo-input">
                    <label for="senha">Senha:</label>
                    <div class="campo-senha">
                        <input type="password" id="senha" name="senha" required autocomplete="new-password">
                        <img src="../img/olho_fechado.png" id="toggleSenha" class="icone-olho" alt="Mostrar/Ocultar Senha">
                    </div>
                </div>

                <!-- Lista de Requisitos da Senha -->
                <ul class="requisitos-senha">
                    <li id="req-tamanho" class="invalido">
                        <span class="icone-req">✓</span> No mínimo 8 caracteres
                    </li>
                    <li id="req-maiuscula" class="invalido">
                        <span class="icone-req">✓</span> Pelo menos 1 letra maiúscula (A-Z)
                    </li>
                    <li id="req-especial" class="invalido">
                        <span class="icone-req">✓</span> Pelo menos 1 caracter especial (como ! @ # $)
                    </li>
                </ul>

                <!-- Campo Confirmar Senha -->
                <div class="grupo-input">
                    <label for="confirmar_senha">Confirmar Senha:</label>
                    <div class="campo-senha">
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                        <img src="../img/olho_fechado.png" id="toggleConfirmarSenha" class="icone-olho" alt="Mostrar/Ocultar Confirmar Senha">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Cadastrar</button>
            </form>
        </div>
    </div>

    <script>
        // 1. Função que alterna a visibilidade da senha e troca a imagem do olho
        function configurarOlhoSenha(inputId, imgId) {
            const input = document.getElementById(inputId);
            const icone = document.getElementById(imgId);

            if (!input || !icone) return;

            icone.addEventListener('click', function () {
                const estaOculta = input.type === 'password';
                
                // Alterna o tipo do input
                input.type = estaOculta ? 'text' : 'password';
                
                // Quando a senha está visível (text) -> olho_aberto.png
                // Quando a senha está oculta (password) -> olho_fechado.png
                icone.src = estaOculta ? '../img/olho_aberto.png' : '../img/olho_fechado.png';
            });
        }

        // Inicializa nos dois campos da página
        configurarOlhoSenha('senha', 'toggleSenha');
        configurarOlhoSenha('confirmar_senha', 'toggleConfirmarSenha');

        // 2. Validação visual em tempo real dos requisitos da senha
        const inputSenha = document.getElementById('senha');
        const reqTamanho = document.getElementById('req-tamanho');
        const reqMaiuscula = document.getElementById('req-maiuscula');
        const reqEspecial = document.getElementById('req-especial');

        inputSenha.addEventListener('input', function () {
            const valor = inputSenha.value;

            // Mínimo 8 caracteres
            if (valor.length >= 8) {
                reqTamanho.classList.add('valido');
                reqTamanho.classList.remove('invalido');
            } else {
                reqTamanho.classList.add('invalido');
                reqTamanho.classList.remove('valido');
            }

            // Pelo menos 1 letra maiúscula
            if (/[A-Z]/.test(valor)) {
                reqMaiuscula.classList.add('valido');
                reqMaiuscula.classList.remove('invalido');
            } else {
                reqMaiuscula.classList.add('invalido');
                reqMaiuscula.classList.remove('valido');
            }

            // Pelo menos 1 caractere especial
            if (/[\W_]/.test(valor)) {
                reqEspecial.classList.add('valido');
                reqEspecial.classList.remove('invalido');
            } else {
                reqEspecial.classList.add('invalido');
                reqEspecial.classList.remove('valido');
            }
        });
    </script>
</body>
</html>