function initReveal() {
  const elementos = document.querySelectorAll('.reveal');
  function animarContador(el) {
    if (el.dataset.animado) return;
    el.dataset.animado = '1';
    const target = parseInt(el.dataset.target, 10);
    if (isNaN(target)) return;
    let actual = 0;
    const paso = Math.max(1, Math.round(target / 30));
    const intervalo = setInterval(() => {
      actual += paso;
      if (actual >= target) { actual = target; clearInterval(intervalo); }
      el.textContent = actual;
    }, 20);
  }
  const observer = new IntersectionObserver((entradas) => {
    entradas.forEach(entrada => {
      if (entrada.isIntersecting) {
        entrada.target.classList.add('visible');
        entrada.target.querySelectorAll('.count').forEach(animarContador);
      }
    });
  }, { threshold: 0.2 });
  elementos.forEach(el => observer.observe(el));
}
document.addEventListener('DOMContentLoaded', initReveal);
