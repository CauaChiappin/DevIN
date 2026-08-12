const rotatingIndexWord = document.querySelector('[data-rotating-word-index]');

if (rotatingIndexWord) {
  const words = ['futuro', 'mundo', 'universo', 'avanço', 'mercado', 'amanhã'];
  let currentWord = 0;
  let changingWord = false;

  window.setInterval(() => {
    if (changingWord) return;
    changingWord = true;
    rotatingIndexWord.classList.add('saindo');

    window.setTimeout(() => {
      currentWord = (currentWord + 1) % words.length;
      rotatingIndexWord.textContent = words[currentWord];
      rotatingIndexWord.classList.remove('saindo');
      void rotatingIndexWord.offsetWidth;
      rotatingIndexWord.classList.add('entrando');

      window.setTimeout(() => {
        rotatingIndexWord.classList.remove('entrando');
        changingWord = false;
      }, 520);
    }, 480);
  }, 3400);
}
