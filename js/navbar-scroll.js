// navbar-scroll.js - Handle navbar scroll behavior with mobile menu consideration
document.addEventListener('DOMContentLoaded', function() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  let lastScrollTop = 0;
  const scrollThreshold = 100;
  const scrollHideDistance = 50;

  window.addEventListener('scroll', function() {
    // Don't hide navbar if mobile menu is open
    if (document.body.classList.contains('body-no-scroll')) return;
    
    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    if (currentScroll > scrollThreshold) {
      if (Math.abs(currentScroll - lastScrollTop) > scrollHideDistance) {
        if (currentScroll > lastScrollTop) {
          navbar.classList.add('hide');
        } else {
          navbar.classList.remove('hide');
        }
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
      }
    } else {
      navbar.classList.remove('hide');
    }
  });

  window.addEventListener('resize', function() {
    if (!document.body.classList.contains('body-no-scroll')) {
      navbar.classList.remove('hide');
    }
    lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
  });
});