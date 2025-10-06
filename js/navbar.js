// navbar.js - Complete navbar functionality with scroll behavior, dropdowns, mobile menu, and copyright
// Updated for multi-language support (Italian/English) and dropdown arrow highlighting
document.addEventListener('DOMContentLoaded', function() {
  // Navbar elements
  const navbar = document.querySelector('.navbar');
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');
  const body = document.body;
  const dropdowns = document.querySelectorAll('.dropdown');
  
  // Initialize navbar state
  let isMobileMenuOpen = false;
  let lastScrollTop = 0;
  const scrollThreshold = 100;
  const scrollHideDistance = 30;
  
  // Dropdown state management
  let dropdownCloseTimeout = null;
  const DROPDOWN_CLOSE_DELAY = 300; // ms delay before closing dropdown

  // Check if device is mobile (hamburger visible)
  function isMobileDevice() {
    return hamburger && window.getComputedStyle(hamburger).display !== 'none';
  }

  // Set current year for copyright
  function setCopyrightYear() {
    const currentYearElement = document.querySelector('.current-year');
    if (currentYearElement) {
      currentYearElement.textContent = new Date().getFullYear();
    }
  }

  // Add dropdown arrows if they don't exist
  function addDropdownArrows() {
    dropdowns.forEach(dropdown => {
      const link = dropdown.querySelector('.navLink');
      if (link && !link.querySelector('.dropdown-arrow')) {
        const dropdownArrow = document.createElement('span');
        dropdownArrow.className = 'dropdown-arrow';
        link.appendChild(dropdownArrow);
      }
    });
  }

  // Add CSS for dropdown arrow highlighting - FIXED ARROW VISIBILITY
  function addDropdownArrowStyles() {
    const style = document.createElement('style');
    style.textContent = `
      .dropdown .navLink .dropdown-arrow {
        border-top-color: var(--white-color) !important;
      }
      
      .dropdown .navLink:hover .dropdown-arrow,
      .dropdown .navLink.active .dropdown-arrow {
        border-top-color: var(--secondary-color) !important;
      }
      
      .dropdown.active .navLink .dropdown-arrow {
        transform: rotate(180deg);
        border-top-color: var(--secondary-color) !important;
      }
      
      .dropdown-arrow {
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 8px;
        vertical-align: middle;
        border-top: 4px solid;
        border-right: 4px solid transparent;
        border-left: 4px solid transparent;
        transition: transform 0.3s ease, border-color 0.3s ease;
      }
      
      @media (max-width: 992px) {
        .dropdown.active .navLink .dropdown-arrow {
          transform: rotate(0deg);
          border-top-color: var(--secondary-color) !important;
        }
        
        .dropdown .navLink .dropdown-arrow {
          border-top-color: var(--white-color) !important;
        }
      }
    `;
    document.head.appendChild(style);
  }

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
      // Show navbar when menu is open
      navbar.classList.remove('hide');
    } else {
      body.classList.remove('body-no-scroll');
      navMenu.style.maxHeight = '';
      closeAllDropdowns();
    }
    
    hamburger.setAttribute('aria-expanded', isMobileMenuOpen);
  }

  // Enhanced dropdown management functions
  function openDropdown(dropdown) {
    // Clear any pending close timeout
    if (dropdownCloseTimeout) {
      clearTimeout(dropdownCloseTimeout);
      dropdownCloseTimeout = null;
    }
    
    const menu = dropdown.querySelector('.dropdown-menu');
    dropdown.classList.add('active');
    menu.classList.add('active');
    
    if (window.innerWidth <= 992) {
      // For mobile, calculate height and animate
      const items = menu.querySelectorAll('li');
      const itemHeight = items.length > 0 ? items[0].offsetHeight : 0;
      menu.style.maxHeight = `${items.length * itemHeight}px`;
    } else {
      // Desktop styles
      menu.style.opacity = '1';
      menu.style.pointerEvents = 'auto';
      menu.style.transform = 'translateY(0)';
      menu.style.visibility = 'visible';
    }
  }

  function closeDropdown(dropdown) {
    const menu = dropdown.querySelector('.dropdown-menu');
    dropdown.classList.remove('active');
    menu.classList.remove('active');
    
    if (window.innerWidth <= 992) {
      menu.style.maxHeight = '0';
    } else {
      menu.style.opacity = '0';
      menu.style.pointerEvents = 'none';
      menu.style.transform = 'translateY(10px)';
      // Delay visibility change for smooth transition
      setTimeout(() => {
        if (!dropdown.classList.contains('active')) {
          menu.style.visibility = 'hidden';
        }
      }, 300);
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
      
      // Initialize dropdown menu for desktop
      if (window.innerWidth > 992 && menu) {
        menu.style.visibility = 'hidden';
      }
      
      // Desktop hover behavior - ENHANCED WITH DELAY
      dropdown.addEventListener('mouseenter', () => {
        if (window.innerWidth > 992) {
          openDropdown(dropdown);
          closeOtherDropdowns(dropdown);
        }
      });
      
      dropdown.addEventListener('mouseleave', (e) => {
        if (window.innerWidth > 992) {
          // Use timeout to allow moving to dropdown menu
          dropdownCloseTimeout = setTimeout(() => {
            if (!isMouseOverDropdown(dropdown, e)) {
              closeDropdown(dropdown);
            }
          }, DROPDOWN_CLOSE_DELAY);
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
      
      // Keep dropdown open when hovering over menu (desktop)
      if (menu) {
        menu.addEventListener('mouseenter', () => {
          if (window.innerWidth > 992) {
            // Clear any pending close timeout when entering menu
            if (dropdownCloseTimeout) {
              clearTimeout(dropdownCloseTimeout);
              dropdownCloseTimeout = null;
            }
            openDropdown(dropdown);
          }
        });

        menu.addEventListener('mouseleave', (e) => {
          if (window.innerWidth > 992) {
            // Close dropdown after delay when leaving menu
            dropdownCloseTimeout = setTimeout(() => {
              if (!isMouseOverDropdown(dropdown, e)) {
                closeDropdown(dropdown);
              }
            }, DROPDOWN_CLOSE_DELAY);
          }
        });
      }
    });
  }

  // Helper function to check if mouse is over dropdown or its menu
  function isMouseOverDropdown(dropdown, event) {
    const relatedTarget = event.relatedTarget;
    if (!relatedTarget) return false;
    
    return dropdown.contains(relatedTarget) || 
           relatedTarget === dropdown || 
           relatedTarget.closest('.dropdown') === dropdown;
  }

  // Close menu when clicking regular links (mobile)
  function setupRegularLinks() {
    document.querySelectorAll('.navMenu .navLink:not(.dropdown .navLink)').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 992 && isMobileMenuOpen) {
          toggleMobileMenu();
        }
      });
    });
  }

  // Enhanced active link highlighting with multi-language support
  function highlightActiveLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const allNavLinks = document.querySelectorAll('.navLink, .dropdown-menu a');
    
    // Define page groups for both languages
    const aboutPages = {
      'it': ['chi-siamo.html', 'avv-maximiliano-lenzi.html', 'andrea-maretto.html', 'alberto-cecolin.html'],
      'en': ['about.html', 'lenzi-page.html', 'andrea-page.html', 'alberto-page.html']
    };
    
    // First reset all active states
    allNavLinks.forEach(link => {
      link.classList.remove('active');
    });
    
    // Determine current language based on page structure
    let currentLanguage = 'en';
    if (currentPage.includes('chi-siamo') || 
        currentPage.includes('avv-maximiliano-lenzi') || 
        currentPage.includes('andrea-maretto') || 
        currentPage.includes('alberto-cecolin')) {
      currentLanguage = 'it';
    }
    
    // Check if current page is in About/Chi Siamo dropdown
    if (aboutPages[currentLanguage].includes(currentPage)) {
      // Find the main dropdown link (either "About" or "Chi Siamo")
      const aboutLink = document.querySelector('.dropdown .navLink[href*="about"], .dropdown .navLink[href*="chi-siamo"]');
      if (aboutLink) {
        aboutLink.classList.add('active');
        const parentDropdown = aboutLink.closest('.dropdown');
        if (parentDropdown) {
          parentDropdown.classList.add('active');
        }
      }
      
      // Highlight the specific dropdown item
      const activeDropdownItem = document.querySelector(`.dropdown-menu a[href="${currentPage}"], .dropdown-menu a[href*="${currentPage}"]`);
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

  // Scroll behavior for navbar - MOBILE ONLY
  function setupScrollBehavior() {
    if (!navbar) return;

    // Store the original scroll handler
    const originalScrollHandler = window.onscroll;
    
    window.addEventListener('scroll', function() {
      // Call original scroll handler if it exists (for footer.js functionality)
      if (typeof originalScrollHandler === 'function') {
        originalScrollHandler();
      }
      
      // Don't hide navbar if mobile menu is open or not on mobile device
      if (document.body.classList.contains('body-no-scroll') || !isMobileDevice()) return;
      
      const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
      
      if (currentScroll > scrollThreshold) {
        if (Math.abs(currentScroll - lastScrollTop) > scrollHideDistance) {
          // Scrolling down - hide navbar
          if (currentScroll > lastScrollTop) {
            navbar.classList.add('hide');
            closeAllDropdowns();
          } 
          // Scrolling up - show navbar
          else {
            navbar.classList.remove('hide');
          }
          lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
        }
      } else {
        // At top of page - always show navbar
        navbar.classList.remove('hide');
      }
    }, { passive: true });
  }

  // Handle resize events
  function handleResize() {
    if (window.innerWidth > 992) {
      // Desktop view
      if (isMobileMenuOpen) {
        toggleMobileMenu();
      }
      closeAllDropdowns();
      body.classList.remove('body-no-scroll');
      // Always show navbar on desktop
      navbar.classList.remove('hide');
      
      // Initialize dropdown visibility for desktop
      dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu && !dropdown.classList.contains('active')) {
          menu.style.visibility = 'hidden';
        }
      });
    } else {
      // Mobile view - reset dropdown heights and visibility
      dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.dropdown-menu');
        menu.style.visibility = 'visible'; // Always visible on mobile
        if (dropdown.classList.contains('active')) {
          const items = menu.querySelectorAll('li');
          const itemHeight = items.length > 0 ? items[0].offsetHeight : 0;
          menu.style.maxHeight = `${items.length * itemHeight}px`;
        } else {
          menu.style.maxHeight = '0';
        }
      });
    }
    
    // Reset scroll position tracking on resize
    lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Ensure navbar is visible after resize if not scrolling
    if (!document.body.classList.contains('body-no-scroll')) {
      navbar.classList.remove('hide');
    }
  }

  // Close dropdown when clicking outside (desktop)
  function setupOutsideClickHandler() {
    document.addEventListener('click', function(e) {
      // Close mobile menu when clicking outside navbar
      if (!e.target.closest('.navbar')) {
        if (window.innerWidth <= 992 && isMobileMenuOpen) {
          toggleMobileMenu();
        }
      }
      
      // Close desktop dropdowns when clicking outside
      if (window.innerWidth > 992) {
        if (!e.target.closest('.dropdown')) {
          closeAllDropdowns();
        }
      }
    });
  }

  // Initialize everything
  function initNavbar() {
    if (!navbar) return;
    
    setCopyrightYear();
    addDropdownArrows();
    addDropdownArrowStyles();
    
    // Mobile menu toggle
    if (hamburger && navMenu) {
      hamburger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMobileMenu();
      });
    }

    setupDropdowns();
    setupRegularLinks();
    highlightActiveLink();
    setupScrollBehavior();
    setupOutsideClickHandler();
    
    window.addEventListener('resize', handleResize);
  }

  // Initialize the navbar
  initNavbar();
});