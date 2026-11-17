<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/redefinir.php
 * Finalidade: redefinir.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

/* A sessão só recebe esta marca após o servidor validar o código de recuperação. Sem ela,
 * o acesso direto à URL retorna ao início e não permite trocar a senha de outra conta. */
if (empty($_SESSION['recuperacao_verificada'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$erro = $_SESSION['erro_redefinir'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $erro.
unset($_SESSION['erro_redefinir']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Redefinir Senha</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/recuperacao.css?v=<?= filemtime(__DIR__ . '/../css/recuperacao.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="recovery-wrapper"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
        <section class="card-box" aria-labelledby="reset-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <a class="brand-link" href="index.php">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
            <h1 id="reset-title" class="title-page">Recuperação de senha</h1> <!-- Explicação DevIN: Declara título principal. -->

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formReset"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <!-- O backend usa a ação e o token para validar e processar a troca de senha. -->
                <input type="hidden" name="acao" value="redefinir_senha"> <!-- Explicação DevIN: Declara campo de entrada. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

                <div class="input-block"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <label for="nova_senha">Senha:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <div class="password-wrapper"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <input type="password" id="nova_senha" name="nova_senha" required minlength="8" autocomplete="new-password"> <!-- Explicação DevIN: Declara campo de entrada. -->
                        <button type="button" class="eye-toggle" data-toggle="nova_senha" aria-label="Mostrar senha"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                            <img class="toggle-password-eye" src="../img/olho_fechado.png" alt=""> <!-- Explicação DevIN: Declara imagem na interface. -->
                        </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="checklist"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <div class="check-item check-invalid" id="req-len"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="icon">ⓘ</span> No mínimo 8 caracteres <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="check-item check-invalid" id="req-upper"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="icon">ⓘ</span> Pelo menos 1 letra maiúscula (A-Z) <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="check-item check-invalid" id="req-special"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="icon">ⓘ</span> Pelo menos 1 caracter especial (como ! @ # $) <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="input-block"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <label for="confirmar_senha">Confirmar Senha:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <div class="password-wrapper"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="8" autocomplete="new-password"> <!-- Explicação DevIN: Declara campo de entrada. -->
                        <button type="button" class="eye-toggle" data-toggle="confirmar_senha" aria-label="Mostrar senha"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                            <img class="toggle-password-eye" src="../img/olho_fechado.png" alt=""> <!-- Explicação DevIN: Declara imagem na interface. -->
                        </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="btn-single-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <button type="submit" class="btn btn-primary btn-cadastrar" id="btnCadastrar">Cadastrar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

    <footer class="recovery-footer"> <!-- Explicação DevIN: Declara rodapé. -->
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados. <!-- Explicação DevIN: Declara trecho em linha. -->
    </footer> <!-- Explicação DevIN: Fecha elemento footer do documento. -->

    <script src="../js/redefinir.js?v=<?= filemtime(__DIR__ . '/../js/redefinir.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
