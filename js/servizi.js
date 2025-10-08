// servizi.js - Enhanced Service Toggle Functionality

document.addEventListener('DOMContentLoaded', function() {
  // Initialize all service categories as hidden
  const serviceCategories = document.querySelectorAll('.service-category');
  
  // Enhanced Scroll Animation for All Elements
  const animatedElements = document.querySelectorAll(
    '.service-feature, .approach-item, .collaboration-banner, .section-header'
  );
  
  // Create Intersection Observer for scroll animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Add highlight to the element currently in view
        entry.target.classList.add('scroll-in-view');
      } else {
        // Remove highlight when element leaves view
        entry.target.classList.remove('scroll-in-view');
      }
    });
  }, {
    threshold: 0.3,
    rootMargin: '-10% 0px -10% 0px'
  });
  
  // Observe all animated elements
  animatedElements.forEach(element => {
    observer.observe(element);
  });
  
  // Add scroll event listener for more precise highlighting
  let scrollTimeout;
  window.addEventListener('scroll', function() {
    // Clear previous timeout
    clearTimeout(scrollTimeout);
    
    // Set new timeout to debounce the scroll event
    scrollTimeout = setTimeout(() => {
      const viewportCenter = window.innerHeight / 2;
      let closestElement = null;
      let closestDistance = Infinity;
      
      // Find the element closest to the center of the viewport
      animatedElements.forEach(element => {
        const rect = element.getBoundingClientRect();
        const elementCenter = rect.top + (rect.height / 2);
        const distance = Math.abs(viewportCenter - elementCenter);
        
        // Check if element is in viewport
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          if (distance < closestDistance) {
            closestDistance = distance;
            closestElement = element;
          }
        }
      });
      
      // Highlight the closest element to center
      if (closestElement) {
        animatedElements.forEach(el => {
          el.classList.remove('scroll-in-view');
        });
        closestElement.classList.add('scroll-in-view');
      }
    }, 100); // Debounce time
  }, { passive: true });
  
  // Initial check on page load
  setTimeout(() => {
    const viewportCenter = window.innerHeight / 2;
    let closestElement = null;
    let closestDistance = Infinity;
    
    animatedElements.forEach(element => {
      const rect = element.getBoundingClientRect();
      const elementCenter = rect.top + (rect.height / 2);
      const distance = Math.abs(viewportCenter - elementCenter);
      
      if (rect.top < window.innerHeight && rect.bottom > 0 && distance < closestDistance) {
        closestDistance = distance;
        closestElement = element;
      }
    });
    
    if (closestElement) {
      closestElement.classList.add('scroll-in-view');
    }
  }, 500);
});