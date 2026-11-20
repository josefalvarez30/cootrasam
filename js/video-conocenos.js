function initVideoConocenos() {
  const principal = document.getElementById('videoConocenosMain');
  const fondo = document.querySelector('.video-conocenos-bg');
  if (!principal || !fondo) return;

  fondo.muted = true; // el fondo nunca suena, solo el video principal

  principal.addEventListener('play', () => {
    fondo.currentTime = principal.currentTime;
    fondo.play().catch(() => {}); // algunos navegadores bloquean autoplay, se ignora si falla
  });

  principal.addEventListener('pause', () => {
    fondo.pause();
  });

  principal.addEventListener('seeked', () => {
    fondo.currentTime = principal.currentTime;
  });

  // Corrige pequeños desfases que se puedan acumular con el tiempo
  principal.addEventListener('timeupdate', () => {
    if (Math.abs(fondo.currentTime - principal.currentTime) > 0.3) {
      fondo.currentTime = principal.currentTime;
    }
  });

  principal.addEventListener('ended', () => {
    fondo.pause();
  });
}

document.addEventListener('DOMContentLoaded', initVideoConocenos);
