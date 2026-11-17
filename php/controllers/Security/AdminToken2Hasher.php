<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Security/AdminToken2Hasher.php
 * Finalidade: armazenar hashes dos segredos administrativos.
 * Domínio: controle de acesso.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * O Token 2 não expira e não possui limite de caracteres na interface.
 * SHA-256 reduz entradas longas a 64 caracteres ANTES do password_hash(),
 * evitando o truncamento silencioso em implementações bcrypt.
 * Há apenas um teto técnico de tamanho de requisição para evitar DoS.
 */
final class AdminToken2Hasher // Explicação DevIN: Declara o tipo AdminToken2Hasher e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public const MAX_INPUT_BYTES = 65536; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhe DevIN: Método digest: encapsula uma responsabilidade reutilizável do módulo no contexto de armazenar hashes dos segredos administrativos. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function digest(string $token): string // Explicação DevIN: Declara função/método digest, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return hash('sha256', "DevIN/admin-token2/v1\0" . $token); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método create: cria registros após checagem da entrada no contexto de armazenar hashes dos segredos administrativos. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function create(string $token): string // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($token === '' || strlen($token) > self::MAX_INPUT_BYTES) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Token 2 inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT; // Explicação DevIN: Atribui um valor à variável PHP $algorithm.
        $stored = password_hash(self::digest($token), $algorithm); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
        if (!is_string($stored)) throw new RuntimeException('Falha ao proteger credencial.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return $stored; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método verify: verifica condições antes de liberar o próximo passo no contexto de armazenar hashes dos segredos administrativos. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function verify(string $token, ?string $stored): bool // Explicação DevIN: Declara função/método verify, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return is_string($stored) && $stored !== '' && $token !== '' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            && strlen($token) <= self::MAX_INPUT_BYTES // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhe DevIN: Valida uma senha contra o hash armazenado sem recuperar a senha original.
            && password_verify(self::digest($token), $stored); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
