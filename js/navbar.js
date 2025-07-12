// navbar.js - Enhanced navbar functionality with better dropdown and active state management
document.addEventListener('DOMContentLoaded', function() {
  // Navbar elements
  const navbar = document.querySelector('.navbar');
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');
  const body = document.body;
  const dropdowns = document.querySelectorAll('.dropdown');
  
  // Initialize navbar state
  let isMobileMenuOpen = false;
  
  // Mobile menu toggle function with scroll lock
  function toggleMobileMenu() {
    isMobileMenuOpen = !isMobileMenuOpen;
    hamburger.classList.toggle('active', isMobileMenuOpen);
    navMenu.classList.toggle('active', isMobileMenuOpen);
    
    // Toggle body scroll lock
    if (isMobileMenuOpen) {
      body.classList.add('body-no-scroll');
      // Calculate and set menu height for proper scrolling
      const viewportHeight = window.innerHeight;
      navMenu.style.maxHeight = `${viewportHeight}px`;
    } else {
      body.classList.remove('body-no-scroll');
      navMenu.style.maxHeight = '';
      closeAllDropdowns();
    }
    
    hamburger.setAttribute('aria-expanded', isMobileMenuOpen);
  }

  // Enhanced dropdown management functions
  function openDropdown(dropdown) {
    const menu = dropdown.querySelector('.dropdown-menu');
    dropdown.classList.add('active');
    menu.classList.add('active');
    
    if (window.innerWidth <= 992) {
      // For mobile, calculate height and animate
      const items = menu.querySelectorAll('li');
      const itemHeight = items.length > 0 ? items[0].offsetHeight : 0;
      menu.style.maxHeight = `${items.length * itemHeight}px`;
    }
  }

  function closeDropdown(dropdown) {
    const menu = dropdown.querySelector('.dropdown-menu');
    dropdown.classList.remove('active');
    menu.classList.remove('active');
    if (window.innerWidth <= 992) {
      menu.style.maxHeight = '0';
    }
  }

  function closeAllDropdowns() {
    dropdowns.forEach(dropdown => closeDropdown(dropdown));
  }

  function closeOtherDropdowns(current) {
    dropdowns.forEach(dropdown => {
      if (dropdown !== current) closeDropdown(dropdown);
    });
  }

  // Enhanced dropdown event listeners
  function setupDropdowns() {
    dropdowns.forEach(dropdown => {
      const link = dropdown.querySelector('.navLink');
      const menu = dropdown.querySelector('.dropdown-menu');
      
      // Desktop hover behavior
      dropdown.addEventListener('mouseenter', () => {
        if (window.innerWidth > 992) {
          openDropdown(dropdown);
          closeOtherDropdowns(dropdown);
        }
      });
      
      dropdown.addEventListener('mouseleave', () => {
        if (window.innerWidth > 992) {
          closeDropdown(dropdown);
        }
      });
      
      // Mobile click behavior
      link.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
          e.preventDefault();
          e.stopPropagation();
          if (dropdown.classList.contains('active')) {
            closeDropdown(dropdown);
          } else {
            openDropdown(dropdown);
            closeOtherDropdowns(dropdown);
          }
        }
      });
    });
  }

  // Close menu when clicking regular links (mobile)
  document.querySelectorAll('.navMenu .navLink:not(.dropdown .navLink)').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 992 && isMobileMenuOpen) {
        toggleMobileMenu();
      }
    });
  });

  // Enhanced active link highlighting
  function highlightActiveLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const allNavLinks = document.querySelectorAll('.navLink, .dropdown-menu a');
    const chiSiamoPages = ['chi-siamo.html', 'avv-maximiliano-lenzi.html', 'andrea-maretto.html', 'alberto-cecolin.html'];
    
    // First reset all active states
    allNavLinks.forEach(link => {
      link.classList.remove('active');
    });
    
    // Check if current page is in Chi Siamo dropdown
    if (chiSiamoPages.includes(currentPage)) {
      const chiSiamoLink = document.querySelector('.dropdown .navLink[href="chi-siamo.html"]');
      if (chiSiamoLink) {
        chiSiamoLink.classList.add('active');
        const parentDropdown = chiSiamoLink.closest('.dropdown');
        if (parentDropdown) {
          parentDropdown.classList.add('active');
        }
      }
      
      // Highlight the specific dropdown item
      const activeDropdownItem = document.querySelector(`.dropdown-menu a[href="${currentPage}"]`);
      if (activeDropdownItem) {
        activeDropdownItem.classList.add('active');
      }
    }
    
    // Regular active link check
    allNavLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (href === currentPage || (href.endsWith(currentPage) && currentPage !== 'index.html')) {
        link.classList.add('active');
        const parent = link.closest('.dropdown');
        if (parent) {
          parent.classList.add('active');
        }
      }
    });
  }

  function handleResize() {
    if (window.innerWidth > 992) {
      // Desktop view
      if (isMobileMenuOpen) {
        toggleMobileMenu();
      }
      closeAllDropdowns();
      body.classList.remove('body-no-scroll');
    } else {
      // Mobile view - reset dropdown heights
      dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (dropdown.classList.contains('active')) {
          const items = menu.querySelectorAll('li');
          const itemHeight = items.length > 0 ? items[0].offsetHeight : 0;
          menu.style.maxHeight = `${items.length * itemHeight}px`;
        } else {
          menu.style.maxHeight = '0';
        }
      });
    }
  }

  // Initialize everything
  function initNavbar() {
    // Mobile menu toggle
    if (hamburger && navMenu) {
      hamburger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMobileMenu();
      });
    }

    // Close menu when clicking outside (mobile)
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.navbar')) {
        if (window.innerWidth <= 992 && isMobileMenuOpen) {
          toggleMobileMenu();
        }
      }
    });

    setupDropdowns();
    highlightActiveLink();
    window.addEventListener('resize', handleResize);
  }

  initNavbar();
});