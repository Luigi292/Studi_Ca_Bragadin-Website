// modal.js - Service Modal functionality

document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const scopriLinks = document.querySelectorAll('.scopri-di-piu');
    const modals = document.querySelectorAll('.service-modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    scopriLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const serviceId = this.getAttribute('data-service');
            const modal = document.getElementById(`${serviceId}-modal`);
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.service-modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });
    
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('service-modal')) {
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});



// Timeline animation on scroll
document.addEventListener('DOMContentLoaded', function() {
  const timelineItems = document.querySelectorAll('.timeline-item');
  
  function checkVisibility() {
    timelineItems.forEach(item => {
      const position = item.getBoundingClientRect();
      
      // Checking if the item is in the viewport
      if(position.top < window.innerHeight * 0.85 && position.bottom >= 0) {
        item.classList.add('visible');
      }
    });
  }
  
  // Check initially
  checkVisibility();
  
  // Check on scroll
  window.addEventListener('scroll', checkVisibility);
});