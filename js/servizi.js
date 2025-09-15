// Enhanced Scroll Animation for All Elements with Category Header Line Effect
document.addEventListener('DOMContentLoaded', function() {
  // Select all elements that should be highlighted when in view
  const animatedElements = document.querySelectorAll(
    '.service-item, .service-category, .approach-item, .collaboration-banner, .section-header, .category-header'
  );
  
  // Create a custom event for scroll-in-view changes
  const scrollInViewEvent = new CustomEvent('scrollInViewChanged', {
    detail: { element: null }
  });
  
  // Create Intersection Observer for scroll animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Remove highlight from all elements first
        animatedElements.forEach(el => {
          el.classList.remove('scroll-in-view');
        });
        
        // Add highlight to the element currently in view
        entry.target.classList.add('scroll-in-view');
        
        // Dispatch custom event with the element that's now in view
        scrollInViewEvent.detail.element = entry.target;
        document.dispatchEvent(scrollInViewEvent);
      }
    });
  }, {
    threshold: 0.6,
    rootMargin: '-25% 0px -25% 0px'
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
        
        // Dispatch custom event with the element that's now in view
        scrollInViewEvent.detail.element = closestElement;
        document.dispatchEvent(scrollInViewEvent);
      }
    }, 100); // Debounce time
  }, { passive: true });
  
  // Listen for custom event to handle category header line effect
  document.addEventListener('scrollInViewChanged', function(e) {
    const elementInView = e.detail.element;
    
    // If a service category is in view, add a special class to its header content
    if (elementInView && elementInView.classList.contains('service-category')) {
      const categoryHeaderContent = elementInView.querySelector('.category-header-content');
      if (categoryHeaderContent) {
        // Remove the class from all category header contents first
        document.querySelectorAll('.category-header-content').forEach(header => {
          header.classList.remove('scroll-highlighted');
        });
        
        // Add the class to the currently visible category header
        categoryHeaderContent.classList.add('scroll-highlighted');
      }
    } else {
      // Remove the class if no service category is in view
      document.querySelectorAll('.category-header-content').forEach(header => {
        header.classList.remove('scroll-highlighted');
      });
    }
  });
  
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
      
      // Also trigger the category header effect if applicable
      if (closestElement.classList.contains('service-category')) {
        const categoryHeaderContent = closestElement.querySelector('.category-header-content');
        if (categoryHeaderContent) {
          document.querySelectorAll('.category-header-content').forEach(header => {
            header.classList.remove('scroll-highlighted');
          });
          categoryHeaderContent.classList.add('scroll-highlighted');
        }
      }
    }
  }, 500);
});