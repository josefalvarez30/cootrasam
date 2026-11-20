function initCarousel3D() {
  document.querySelectorAll('.carousel3d').forEach(carousel => {
    const cards = Array.from(carousel.querySelectorAll('.card3d'));
    const prevBtn = carousel.querySelector('.carousel3d-prev');
    const nextBtn = carousel.querySelector('.carousel3d-next');
    const dotsContainer = carousel.querySelector('.carousel3d-dots');
    let index = 0;

    cards.forEach((_, i) => {
      const dot = document.createElement('div');
      dot.className = 'carousel3d-dot' + (i === 0 ? ' active' : '');
      dot.addEventListener('click', () => irA(i));
      dotsContainer.appendChild(dot);
    });
    const dots = Array.from(dotsContainer.children);

    function actualizar() {
      cards.forEach((card, i) => {
        let diff = i - index;
        if (diff > cards.length / 2) diff -= cards.length;
        if (diff < -cards.length / 2) diff += cards.length;
        if (diff === 0) card.dataset.pos = 'active';
        else if (diff === -1 || diff === cards.length - 1) card.dataset.pos = 'prev';
        else if (diff === 1 || diff === -(cards.length - 1)) card.dataset.pos = 'next';
        else card.dataset.pos = 'hidden';
      });
      dots.forEach((d, i) => d.classList.toggle('active', i === index));
    }
    function irA(i) { index = (i + cards.length) % cards.length; actualizar(); }

    cards.forEach((card, i) => card.addEventListener('click', () => { if (card.dataset.pos !== 'active') irA(i); }));
    nextBtn.addEventListener('click', () => irA(index + 1));
    prevBtn.addEventListener('click', () => irA(index - 1));

    let auto = setInterval(() => irA(index + 1), 5000);
    carousel.addEventListener('mouseenter', () => clearInterval(auto));
    carousel.addEventListener('mouseleave', () => { auto = setInterval(() => irA(index + 1), 5000); });

    actualizar();
  });
}
document.addEventListener('DOMContentLoaded', initCarousel3D);
