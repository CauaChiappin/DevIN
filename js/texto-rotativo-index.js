/*
 * REVISÃO DETALHADA DEVIN — js/texto-rotativo-index.js
 * Finalidade: texto rotativo index.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

const rotatingIndexWord = document.querySelector('[data-rotating-word-index]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const rotatingIndexPoint = document.querySelector('[data-rotating-point-index]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

if (rotatingIndexWord) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  // Alterna palavras e move o ponto decorativo para acompanhar a largura do texto.
  const words = ['universo', 'futuro', 'mundo', 'avanço', 'mercado', 'amanhã']; // Explicação DevIN: Declara variável JavaScript words para reutilizar valor ou resultado.
  let currentWord = 0; // Explicação DevIN: Declara variável JavaScript currentWord para reutilizar valor ou resultado.
  let changingWord = false; // Explicação DevIN: Declara variável JavaScript changingWord para reutilizar valor ou resultado.

  rotatingIndexWord.style.width = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

  window.setInterval(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (changingWord) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    changingWord = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    rotatingIndexWord.classList.add('saindo'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    window.setTimeout(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      // Mede o ponto antes e depois da troca para animar sua mudança de posição.
      const pointBeforeChange = rotatingIndexPoint?.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript pointBeforeChange para reutilizar valor ou resultado.
      currentWord = (currentWord + 1) % words.length; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      rotatingIndexWord.textContent = words[currentWord]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      rotatingIndexWord.classList.remove('saindo'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
      void rotatingIndexWord.offsetWidth; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
      rotatingIndexWord.classList.add('entrando'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

      if (rotatingIndexPoint && pointBeforeChange) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const pointAfterChange = rotatingIndexPoint.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript pointAfterChange para reutilizar valor ou resultado.
        rotatingIndexPoint.style.setProperty( // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
          '--ponto-deslocamento', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
          `${pointBeforeChange.left - pointAfterChange.left}px` // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        rotatingIndexPoint.classList.add('movendo'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

      window.setTimeout(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        rotatingIndexWord.classList.remove('entrando'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        rotatingIndexPoint?.classList.remove('movendo'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        changingWord = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      }, 520); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }, 480); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }, 3400); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
