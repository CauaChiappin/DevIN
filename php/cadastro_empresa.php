<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Criar Conta</title>
    <link rel="stylesheet" href="../css/cadastrostyle.css" >
</head>
<body>

    <div class="main-container">
        
        <section class="left-side">
            
             <div class="brand-logo">
          <a href="../php/index.php">Dev<span>IN</span></a>
            </div>

            <div class="toggle-container">
                <a href="cadastro_pessoa.php" class="toggle-btn pessoal">Pessoal</a>
                <span class="toggle-divider">OU</span>
                <a href="cadastro_empresa.php" class="toggle-btn empresa active">Empresa</a>
            </div>

            <h1 class="page-title">Criar conta</h1>

            <form action="" method="POST" class="register-form" id="formCadastro">
                
                <div class="form-columns">
                    <div class="form-column">
                        <div class="input-group">
                            <label for="nome">Nome:*</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="input-group">
                            <label for="cnpj">CNPJ:*</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
                        </div>

                        <div class="input-group">
                            <label for="cep">CEP:*</label>
                            <input type="text" id="cep" name="cep" placeholder="00000-000" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="confirme_senha">Confirme a sua senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="confirme_senha" name="confirme_senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Ocultar/Mostrar Senha">
                            </div>
                            <span id="error-match" class="error-message-text">Senhas não coincidem</span>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="input-group">
                            <label for="email">E-mail:*</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="input-group">
                            <label for="telefone">Telefone:*</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="senha">Senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="senha" name="senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Ocultar/Mostrar Senha">
                            </div>
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
                    </div>
                </div>

                <div class="form-footer-action">
                    <button type="submit" class="btn-submit">Cadastrar</button>
                    <p class="login-redirect">Já tem conta? <a href="../php/login.php">Faça login</a></p>
                </div>

            </form>

            <footer class="page-footer">
                Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
            </footer>

        </section>

        <section class="right-side">
            <a href="../php/login.php" class="btn-top-login">Login</a>
            
            <div class="mascot-container">
                <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img">
            </div>
        </section>

    </div>

    <div id="status-alert-container"></div>

    <script>
        const senhaInput = document.getElementById('senha');
        const confirmeSenhaInput = document.getElementById('confirme_senha');
        
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqSpecial = document.getElementById('req-special');
        const errorMatch = document.getElementById('error-match');

        // Função Atualizada para alternar visibilidade trocando as imagens fornecidas
        function togglePasswordVisibility(inputId, imgElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                imgElement.src = '/img/olho_fechado.png'; // Imagem para o estado VISÍVEL
            } else {
                input.type = 'password';
                imgElement.src = '/img/olho_fechado.png';       // Imagem para o estado OCULTO
            }
        }

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

        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const val = senhaInput.value;
            const isAllValid = (val.length >= 8) && /[A-Z]/.test(val) && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/.test(val);
            const isMatch = senhaInput.value === confirmeSenhaInput.value;

            if (!isAllValid || !isMatch) {
                e.preventDefault();
                alert('Por favor, corrija os erros nos campos de senha antes de prosseguir.');
            }
        });
    </script>

</body>
</html>



<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Falha de conexão com o banco de dados.");
    }

    if ($_POST['senha'] !== $_POST['confirme_senha']) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>As senhas não coincidem!</div>\";
        </script>";
        exit();
    }

    $nome = $conn->real_escape_string($_POST['nome']);
    $cnpj = $conn->real_escape_string($_POST['cnpj']);
    $cep = (int) preg_replace('/[^0-9]/', '', $_POST['cep']);
    $telefone = (int) preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO Empresa (nome, cnpj, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $nome, $cnpj, $cep, $email, $senha_hash, $telefone);

    if ($stmt->execute()) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast success-toast'>Empresa cadastrada com sucesso!</div>\";
        </script>";
    } else {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>Erro ao cadastrar: CNPJ ou E-mail já existentes.</div>\";
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>

