<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

/* O e-mail guardado na sessão representa a conta escolhida na etapa anterior; sem ele,
 * não existe destinatário associado ao código e a pessoa deve reiniciar a recuperação. */
if (empty($_SESSION['recuperacao_email'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: recuperacao.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$erro =$_SESSION['erro_codigo'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $erro.
$sucesso =$_SESSION['sucesso_codigo'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $sucesso.
// Consome as mensagens flash para que elas apareçam apenas nesta resposta.
unset($_SESSION['erro_codigo'],$_SESSION['sucesso_codigo']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

$ultimoEnvio =$_SESSION['ultimo_envio_codigo'] ?? 0; // Explicação DevIN: Atribui um valor à variável PHP $ultimoEnvio.
$tempoPassado = time() -$ultimoEnvio; // Explicação DevIN: Atribui um valor à variável PHP $tempoPassado.
$cooldownRestante = max(0, 60 -$tempoPassado); // Explicação DevIN: Atribui um valor à variável PHP $cooldownRestante.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Inserir Código</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/recuperacao.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="recovery-wrapper"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
        <section class="card-box" aria-labelledby="code-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <a class="brand-link" href="index.php">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
            <h1 id="code-title">Recuperação de senha</h1> <!-- Explicação DevIN: Declara título principal. -->
            <p class="subtitle">insira o código:</p> <!-- Explicação DevIN: Declara parágrafo. -->

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></div> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formCodigo"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <!-- A ação informa ao backend qual etapa do fluxo deve processar; CSRF valida a origem da solicitação. -->
                <input type="hidden" name="acao" value="validar_codigo"> <!-- Explicação DevIN: Declara campo de entrada. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                <input type="hidden" name="codigo" id="codigo-final"> <!-- Explicação DevIN: Declara campo de entrada. -->

           <div class="code-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
    <input type="text" id="code-1" name="digit_1" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
    <input type="text" id="code-2" name="digit_2" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
    <input type="text" id="code-3" name="digit_3" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
    <span class="code-separator">-</span> <!-- Explicação DevIN: Declara trecho em linha. -->
    <input type="text" id="code-4" name="digit_4" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
    <input type="text" id="code-5" name="digit_5" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
    <input type="text" id="code-6" name="digit_6" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
</div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                <div class="resend-block"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <span>Não recebeu o código? </span> <!-- Explicação DevIN: Declara trecho em linha. -->
                    <button type="button" id="btnReenviar" class="link-resend" <?= $cooldownRestante > 0 ? 'disabled' : '' ?>> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. -->
                        reenviar código <?= $cooldownRestante > 0 ? "({$cooldownRestante}s)" : '' ?> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="btn-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <a href="recuperacao.php" class="btn btn-secondary">Voltar</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <button type="submit" class="btn btn-primary" id="btnEntrar">entrar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->

            <form action="processar.php" method="POST" id="formReenviar"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <input type="hidden" name="acao" value="reenviar_codigo"> <!-- Explicação DevIN: Declara campo de entrada. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

    <footer class="recovery-footer"> <!-- Explicação DevIN: Declara rodapé. -->
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados. <!-- Explicação DevIN: Declara trecho em linha. -->
    </footer> <!-- Explicação DevIN: Fecha elemento footer do documento. -->

    <script>
        document.addEventListener('DOMContentLoaded', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const inputs = document.querySelectorAll('.code-input'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const hiddenInput = document.getElementById('codigo-final'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const btnReenviar = document.getElementById('btnReenviar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const formReenviar = document.getElementById('formReenviar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const formCodigo = document.getElementById('formCodigo'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const btnEntrar = document.getElementById('btnEntrar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            let cooldown = <?= $cooldownRestante ?>; // Explicação DevIN: Declara variável JavaScript cooldown para reutilizar valor ou resultado.

            function updateHiddenInput() { // Explicação DevIN: Declara função/método updateHiddenInput, com seus parâmetros e comportamento.
                // Junta os campos visuais em um único valor que o backend recebe como código completo.
                let fullCode = ''; // Explicação DevIN: Declara variável JavaScript fullCode para reutilizar valor ou resultado.
                inputs.forEach(input => fullCode += input.value.toUpperCase()); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                hiddenInput.value = fullCode; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            inputs.forEach((input, index) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                input.addEventListener('input', (e) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    // Mantém cada campo em maiúsculas e remove caracteres fora do formato do código.
                    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (e.target.value.length === 1 && index < inputs.length - 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        inputs[index + 1].focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    updateHiddenInput(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                input.addEventListener('keydown', (e) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    if (e.key === 'Backspace' && !e.target.value && index > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        inputs[index - 1].focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                input.addEventListener('paste', (e) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    // Distribui um código copiado entre os seis campos, sem deixar o navegador colar em um só.
                    e.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().toUpperCase().replace(/[^A-Z0-9]/g, ''); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                    if (pasteData.length === 6) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        pasteData.split('').forEach((char, i) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                            if (inputs[i]) inputs[i].value = char; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        inputs[inputs.length - 1].focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        updateHiddenInput(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (cooldown > 0 && btnReenviar) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Atualiza a contagem visual até o instante em que o reenvio volta a ficar disponível.
                const interval = setInterval(() => { // Explicação DevIN: Declara variável JavaScript interval para reutilizar valor ou resultado.
                    cooldown--; // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    if (cooldown <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        clearInterval(interval); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        btnReenviar.removeAttribute('disabled'); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        btnReenviar.textContent = 'reenviar código'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                        btnReenviar.textContent = `reenviar código (${cooldown}s)`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }, 1000); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (btnReenviar) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                btnReenviar.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    if (!btnReenviar.hasAttribute('disabled')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        // Bloqueia cliques repetidos no navegador e envia o formulário de reenvio ao servidor.
                        btnReenviar.setAttribute('disabled', 'true'); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        formReenviar.submit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (formCodigo && btnEntrar) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                formCodigo.addEventListener('submit', (e) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    updateHiddenInput(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                    // Evita uma requisição incompleta; a validade real do código continua sendo conferida no servidor.
                    if (hiddenInput.value.length !== 6) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        e.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        alert('Preencha os 6 caracteres do código.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateHiddenInput.
                        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    btnEntrar.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    btnEntrar.textContent = 'validando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    </script>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
