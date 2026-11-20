function initNav() {
  const hamburger = document.getElementById('hamburgerBtn');
  const menu = document.getElementById('navMenu');

  hamburger.addEventListener('click', () => {
    menu.classList.toggle('open');
  });

  document.querySelectorAll('.nav-menu > li').forEach(item => {
    const link = item.querySelector('a');
    const dropdown = item.querySelector('.dropdown');
    if (!dropdown) return;

    link.addEventListener('click', (e) => {
      e.preventDefault();
      const yaAbierto = item.classList.contains('open');
      document.querySelectorAll('.nav-menu > li').forEach(li => li.classList.remove('open'));
      if (!yaAbierto) item.classList.add('open');
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-menu') && !e.target.closest('.hamburger')) {
      menu.classList.remove('open');
      document.querySelectorAll('.nav-menu > li').forEach(li => li.classList.remove('open'));
    }
  });
}

document.addEventListener('DOMContentLoaded', initNav);
