const rotatingIndexWord = document.querySelector('[data-rotating-word-index]');
const rotatingIndexPoint = document.querySelector('[data-rotating-point-index]');

if (rotatingIndexWord) {
  const words = ['futuro', 'mundo', 'universo', 'avanço', 'mercado', 'amanhã'];
  let currentWord = 0;
  let changingWord = false;

  rotatingIndexWord.style.width = '';

  window.setInterval(() => {
    if (changingWord) return;
    changingWord = true;
    rotatingIndexWord.classList.add('saindo');

    window.setTimeout(() => {
      const pointBeforeChange = rotatingIndexPoint?.getBoundingClientRect();
      currentWord = (currentWord + 1) % words.length;
      rotatingIndexWord.textContent = words[currentWord];
      rotatingIndexWord.classList.remove('saindo');
      void rotatingIndexWord.offsetWidth;
      rotatingIndexWord.classList.add('entrando');

      if (rotatingIndexPoint && pointBeforeChange) {
        const pointAfterChange = rotatingIndexPoint.getBoundingClientRect();
        rotatingIndexPoint.style.setProperty(
          '--ponto-deslocamento',
          `${pointBeforeChange.left - pointAfterChange.left}px`
        );
        rotatingIndexPoint.classList.add('movendo');
      }

      window.setTimeout(() => {
        rotatingIndexWord.classList.remove('entrando');
        rotatingIndexPoint?.classList.remove('movendo');
        changingWord = false;
      }, 520);
    }, 480);
  }, 3400);
}
