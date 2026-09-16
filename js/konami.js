const konamiCode = [
  'ArrowUp', 'ArrowUp', 
  'ArrowDown', 'ArrowDown', 
  'ArrowLeft', 'ArrowRight', 
  'ArrowLeft', 'ArrowRight', 
  'b', 'a'
];

let konamiIndex = 0;

document.addEventListener('keydown', (event) => {
  const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
  
  if (key === konamiCode[konamiIndex].toLowerCase()) {
    konamiIndex++;
    
    if (konamiIndex === konamiCode.length) {
      window.location.href = 'doom.html';
      konamiIndex = 0;
    }
  } else {
    konamiIndex = 0;
  }
});