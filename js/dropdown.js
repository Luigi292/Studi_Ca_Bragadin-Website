// js/dropdown.js
function initDropdown() {
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');
  const chiSiamoLink = document.querySelector('.dropdown > .navLink');
  const dropdownMenu = document.querySelector('.dropdown-menu');
  
  // Add dropdown arrow if it doesn't exist
  if (chiSiamoLink && !chiSiamoLink.querySelector('.dropdown-arrow')) {
    const dropdownArrow = document.createElement('span');
    dropdownArrow.className = 'dropdown-arrow';
    chiSiamoLink.appendChild(dropdownArrow);
  }

  // Mobile menu toggle
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', function() {
      hamburger.classList.toggle('active');
      navMenu.classList.toggle('active');
      
      // Close dropdown when mobile menu is closed
      if (!navMenu.classList.contains('active') && dropdownMenu) {
        dropdownMenu.classList.remove('active');
      }
    });
  }
  
  // Dropdown toggle for mobile
  if (chiSiamoLink && dropdownMenu) {
    chiSiamoLink.addEventListener('click', function(e) {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        dropdownMenu.classList.toggle('active');
      }
    });

    // Desktop hover effect
    chiSiamoLink.addEventListener('mouseenter', function() {
      if (window.innerWidth > 768 && dropdownMenu) {
        dropdownMenu.style.opacity = '1';
        dropdownMenu.style.pointerEvents = 'auto';
        dropdownMenu.style.transform = 'translateY(0)';
      }
    });

    // Keep dropdown open when hovering over it
    if (dropdownMenu) {
      dropdownMenu.addEventListener('mouseenter', function() {
        if (window.innerWidth > 768) {
          dropdownMenu.style.opacity = '1';
          dropdownMenu.style.pointerEvents = 'auto';
          dropdownMenu.style.transform = 'translateY(0)';
        }
      });

      // Close when mouse leaves
      dropdownMenu.addEventListener('mouseleave', function() {
        if (window.innerWidth > 768) {
          dropdownMenu.style.opacity = '0';
          dropdownMenu.style.pointerEvents = 'none';
          dropdownMenu.style.transform = 'translateY(10px)';
        }
      });
    }
  }
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown') && window.innerWidth > 768 && dropdownMenu) {
      dropdownMenu.style.opacity = '0';
      dropdownMenu.style.pointerEvents = 'none';
      dropdownMenu.style.transform = 'translateY(10px)';
    }
  });
}

// Initialize if loaded after DOM is ready
if (document.readyState !== 'loading') {
  initDropdown();
}