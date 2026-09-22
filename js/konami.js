const konamiCode = [
  'ArrowUp', 'ArrowUp', 
  'ArrowDown', 'ArrowDown', 
  'ArrowLeft', 'ArrowRight', 
  'ArrowLeft', 'ArrowRight', 
  'b', 'a'
];

let konamiIndex = 0;

document.addEventListener('keydown', (event) => {
  const keyPressed = event.key.toLowerCase();
  const targetKey = konamiCode[konamiIndex].toLowerCase();
  
  if (keyPressed === targetKey) {
    konamiIndex++;
    
    if (konamiIndex === konamiCode.length) {
      window.location.href = '../html/jogos/doom.html';
      konamiIndex = 0;
    }
  } else {
    konamiIndex = 0;
  }
});