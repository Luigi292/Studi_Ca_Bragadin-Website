document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('carousel');
    const slides = carousel.querySelectorAll('.slide');
    let currentSlide = 0;
    
    function showSlide(n) {
      slides.forEach(slide => slide.classList.remove('active'));
      slides[n].classList.add('active');
    }
    
    function nextSlide() {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }
    
    // Change slide every 5 seconds
    setInterval(nextSlide, 5000);
  });