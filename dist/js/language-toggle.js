document.addEventListener('DOMContentLoaded', function() {
    const langButtons = document.querySelectorAll('.languageButton');
    
    langButtons.forEach(button => {
      button.addEventListener('click', function() {
        const lang = this.dataset.lang;
        
        // Remove active class from all buttons
        langButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Here you would typically load the appropriate language version
        // For static hosting, we'll just toggle a class on the body
        document.documentElement.lang = lang;
        document.body.classList.toggle('english-version', lang === 'en');
        
        // Update text content based on language
        updateContentForLanguage(lang);
      });
    });
    
    function updateContentForLanguage(lang) {
      // This is a simplified version - you would expand this
      const elements = document.querySelectorAll('[data-lang]');
      
      elements.forEach(el => {
        if (el.dataset.lang === lang) {
          el.style.display = 'block';
        } else {
          el.style.display = 'none';
        }
      });
      
      // Update specific elements
      if (lang === 'en') {
        document.querySelector('.hero h1').textContent = "Ca' Bragadin Accountants Firm";
        document.querySelector('.hero p').textContent = "Custom tax and legal solutions for your business";
        document.querySelector('.hero .ctaButton').textContent = "Contact Us";
        // Add more translations as needed
      } else {
        document.querySelector('.hero h1').textContent = "Studio Commercialisti Ca' Bragadin";
        document.querySelector('.hero p').textContent = "Soluzioni fiscali e legali su misura per la tua azienda";
        document.querySelector('.hero .ctaButton').textContent = "Contattaci";
      }
    }
  });