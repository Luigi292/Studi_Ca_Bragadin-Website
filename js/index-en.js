// js/index-en.js - English version with full functionality matching Italian version
document.addEventListener('DOMContentLoaded', function() {
  // ===== HERO CAROUSEL =====
  function initHeroCarousel() {
    const carousel = document.getElementById('carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.slide');
    const indicatorsContainer = document.createElement('div');
    indicatorsContainer.className = 'carousel-indicators';
    carousel.appendChild(indicatorsContainer);
    
    let current = 0;
    let autoAdvance;

    // Create indicators
    slides.forEach((_, index) => {
      const indicator = document.createElement('button');
      indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
      indicator.setAttribute('aria-label', `Go to slide ${index + 1}`);
      indicator.addEventListener('click', () => goToSlide(index));
      indicatorsContainer.appendChild(indicator);
    });

    function show(index) {
      // Update slides
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
      });
      
      // Update indicators
      document.querySelectorAll('.carousel-indicator').forEach((indicator, i) => {
        indicator.classList.toggle('active', i === index);
      });
      
      current = index;
    }

    function next() {
      const nextIndex = (current + 1) % slides.length;
      show(nextIndex);
    }

    function prev() {
      const prevIndex = (current - 1 + slides.length) % slides.length;
      show(prevIndex);
    }

    function goToSlide(index) {
      show(index);
      resetAutoAdvance();
    }

    function startAutoAdvance() {
      autoAdvance = setInterval(next, 9000);
    }

    function resetAutoAdvance() {
      clearInterval(autoAdvance);
      startAutoAdvance();
    }

    // Add keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        prev();
        resetAutoAdvance();
      } else if (e.key === 'ArrowRight') {
        next();
        resetAutoAdvance();
      }
    });

    // Add touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    carousel.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    carousel.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, { passive: true });

    function handleSwipe() {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;
      
      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          next();
        } else {
          prev();
        }
        resetAutoAdvance();
      }
    }

    // Initialize
    show(0);
    startAutoAdvance();
  }

  // ===== COLLABORATION SECTION BACKGROUND =====
  function adjustBackground() {
    const section = document.querySelector('.collaboration-section');
    const bg = document.querySelector('.collaboration-bg');
    if (!section || !bg) return;
    
    // Set background height to match section
    bg.style.height = section.offsetHeight + 'px';
  }

  // ===== NEWS PREVIEWS =====
  function loadNewsPreviews() {
    const newsGrid = document.getElementById('newsPreviewGrid');
    if (!newsGrid) return;

    newsGrid.innerHTML = '<div class="news-loading"><div class="news-loading-spinner"></div></div>';

    // Always try to fetch the JSON file - using ../ for English version
    fetch('../news-data.json')
      .then(response => {
        if (!response.ok) {
          // If the file doesn't exist or there's a network error
          showNoNewsPreview();
          return null;
        }
        return response.json();
      })
      .then(newsData => {
        // Check if newsData is empty, null, undefined, or not an array
        if (!newsData || !Array.isArray(newsData) || newsData.length === 0) {
          showNoNewsPreview();
          return;
        }

        // Sort by most recent first
        const sortedNews = [...newsData].sort((a, b) => 
          new Date(b.fullDate || b.date) - new Date(a.fullDate || a.date)
        );

        // Take top 3
        const topNews = sortedNews.slice(0, 3);
        
        renderNewsPreview(topNews);
      })
      .catch(error => {
        console.error('Error loading news:', error);
        // Show "no news" message for any error
        showNoNewsPreview();
      });

    function renderNewsPreview(newsItems) {
      newsGrid.innerHTML = '';
      
      newsItems.forEach(news => {
        const card = document.createElement('div');
        card.className = 'news-preview-card';
        card.innerHTML = `
          <div class="news-preview-card-header">
            <span class="news-preview-date">${news.date}</span>
            <span class="news-preview-category">${news.category}</span>
          </div>
          <div class="news-preview-content">
            <h3>${news.title}</h3>
            <p>${news.preview}</p>
            <div class="news-preview-actions">
              <a href="newspage.html#news-${news.id}" class="news-preview-more">Read more</a>
            </div>
          </div>
        `;
        newsGrid.appendChild(card);
      });
    }

    function showNoNewsPreview() {
      newsGrid.innerHTML = `
        <div class="no-news-message">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <h2>No news published at the moment</h2>
          <p>Our professionals are preparing new content. Check back soon!</p>
        </div>
      `;
    }
  }

  // ===== BACK TO TOP BUTTON =====
  function initBackToTop() {
    const backToTop = document.querySelector('.js-top');
    if (backToTop) {
      window.addEventListener('scroll', () => {
        backToTop.classList.toggle('active', window.scrollY > 300);
      });
      backToTop.addEventListener('click', e => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  }

  // ===== GALLERY FUNCTIONALITY =====
  function initGallery() {
    const gallerySection = document.querySelector('.studio-gallery-section');
    if (!gallerySection) return;

    // Define gallery images with descriptions - using ../images/ for English version
    const galleryImages = [
      { src: '../images/front-hall.jpg', alt: 'Ca Bragadin Studios - Main hall' },
      { src: '../images/mid-hall.jpg', alt: 'Ca Bragadin Studios - Waiting room' },
      { src: '../images/glass-room.jpg', alt: 'Ca Bragadin Studios - Glass meeting room' },
      { src: '../images/glass-room2.jpg', alt: 'Ca Bragadin Studios - Modern workspace' },
      { src: '../images/glass-room3.jpg', alt: 'Ca Bragadin Studios - Collaborative space' },
      { src: '../images/team-glass-room.jpg', alt: 'Ca Bragadin Studios - Team in meeting room' },
      { src: '../images/team1.jpg', alt: 'Ca Bragadin Studios - Professional team' },
      { src: '../images/team2.jpg', alt: 'Ca Bragadin Studios - Professional collaboration' },
      { src: '../images/team-balcony.jpg', alt: 'Ca Bragadin Studios - Team on balcony' },
      { src: '../images/ufficio-lenzi.jpg', alt: 'Law. Lenzi Office - Private office' }
    ];

    const track = document.getElementById('gallery-track');
    const dotsContainer = document.getElementById('gallery-dots');
    const fullscreen = document.getElementById('gallery-fullscreen');
    const fullscreenImage = document.getElementById('fullscreen-image');
    const closeButton = document.querySelector('.fullscreen-close');
    const carouselPrevButton = document.querySelector('.carousel-button--prev');
    const carouselNextButton = document.querySelector('.carousel-button--next');

    let currentIndex = 0;
    let isTransitioning = false;

    // Create slides and dots
    function createGallery() {
      if (!track || !dotsContainer) return;
      
      track.innerHTML = '';
      dotsContainer.innerHTML = '';

      galleryImages.forEach((image, index) => {
        // Create slide
        const slide = document.createElement('div');
        slide.className = 'carousel-slide';
        slide.innerHTML = `
          <img src="${image.src}" alt="${image.alt}" class="carousel-image" data-index="${index}">
        `;
        track.appendChild(slide);

        // Create dot
        const dot = document.createElement('button');
        dot.className = `carousel-dot ${index === 0 ? 'active' : ''}`;
        dot.setAttribute('aria-label', `Go to image ${index + 1}`);
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
      });
    }

    // Go to specific slide with smooth transition
    function goToSlide(index) {
      if (isTransitioning) return;
      
      isTransitioning = true;
      currentIndex = index;
      updateCarousel();
      
      // Reset transition lock after animation completes
      setTimeout(() => {
        isTransitioning = false;
      }, 500);
    }

    // Update carousel position and active states
    function updateCarousel() {
      if (!track) return;
      
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      
      // Update dots
      document.querySelectorAll('.carousel-dot').forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
      });
    }

    // Next slide with infinite loop (no visual scrolling back)
    function nextSlide() {
      if (isTransitioning || !track) return;
      
      isTransitioning = true;
      
      // If we're at the last image, jump to first without animation
      if (currentIndex === galleryImages.length - 1) {
        // Temporarily disable transition
        track.style.transition = 'none';
        currentIndex = 0;
        updateCarousel();
        
        // Force reflow
        track.offsetHeight;
        
        // Re-enable transition
        track.style.transition = 'transform 0.5s ease';
      } else {
        currentIndex++;
        updateCarousel();
      }
      
      // Reset transition lock after animation completes
      setTimeout(() => {
        isTransitioning = false;
      }, 500);
    }

    // Previous slide with infinite loop (no visual scrolling back)
    function prevSlide() {
      if (isTransitioning || !track) return;
      
      isTransitioning = true;
      
      // If we're at the first image, jump to last without animation
      if (currentIndex === 0) {
        // Temporarily disable transition
        track.style.transition = 'none';
        currentIndex = galleryImages.length - 1;
        updateCarousel();
        
        // Force reflow
        track.offsetHeight;
        
        // Re-enable transition
        track.style.transition = 'transform 0.5s ease';
      } else {
        currentIndex--;
        updateCarousel();
      }
      
      // Reset transition lock after animation completes
      setTimeout(() => {
        isTransitioning = false;
      }, 500);
    }

    // Open fullscreen view (without navigation arrows)
    function openFullscreen(index) {
      if (!fullscreen || !fullscreenImage) return;
      
      currentIndex = index;
      fullscreenImage.src = galleryImages[currentIndex].src;
      fullscreenImage.alt = galleryImages[currentIndex].alt;
      fullscreen.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Close fullscreen view
    function closeFullscreen() {
      if (!fullscreen) return;
      
      fullscreen.classList.remove('active');
      document.body.style.overflow = '';
    }

    // Initialize event listeners
    function initEventListeners() {
      // Carousel navigation
      if (carouselPrevButton) {
        carouselPrevButton.addEventListener('click', prevSlide);
      }
      
      if (carouselNextButton) {
        carouselNextButton.addEventListener('click', nextSlide);
      }

      // Image click to open fullscreen
      if (track) {
        track.addEventListener('click', (e) => {
          if (e.target.classList.contains('carousel-image')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            openFullscreen(index);
          }
        });
      }

      // Fullscreen close
      if (closeButton) {
        closeButton.addEventListener('click', closeFullscreen);
      }
      
      // Close fullscreen when clicking outside image
      if (fullscreen) {
        fullscreen.addEventListener('click', (e) => {
          if (e.target === fullscreen) {
            closeFullscreen();
          }
        });
      }

      // Keyboard navigation in fullscreen mode
      document.addEventListener('keydown', (e) => {
        if (!fullscreen || !fullscreen.classList.contains('active')) return;
        
        switch(e.key) {
          case 'Escape':
            closeFullscreen();
            break;
          // Note: Removed arrow key navigation in fullscreen as requested
        }
      });

      // Touch swipe for mobile
      let startX = 0;
      let endX = 0;

      if (track) {
        track.addEventListener('touchstart', (e) => {
          startX = e.touches[0].clientX;
        });

        track.addEventListener('touchend', (e) => {
          endX = e.changedTouches[0].clientX;
          handleSwipe();
        });
      }

      function handleSwipe() {
        const diff = startX - endX;
        const swipeThreshold = 50;

        if (Math.abs(diff) > swipeThreshold && !isTransitioning) {
          if (diff > 0) {
            nextSlide();
          } else {
            prevSlide();
          }
        }
      }
    }

    // Auto-advance carousel
    function startAutoAdvance() {
      setInterval(() => {
        if (!fullscreen || !fullscreen.classList.contains('active')) {
          nextSlide();
        }
      }, 6000);
    }

    // Initialize everything
    createGallery();
    initEventListeners();
    startAutoAdvance();
  }

  // ===== INITIALIZE ALL FUNCTIONALITY =====
  function initializeAll() {
    // Initialize hero carousel
    initHeroCarousel();
    
    // Initialize collaboration section background
    adjustBackground();
    window.addEventListener('load', adjustBackground);
    window.addEventListener('resize', adjustBackground);
    
    // Initialize news previews
    loadNewsPreviews();
    
    // Initialize back to top button
    initBackToTop();
    
    // Initialize gallery
    initGallery();
  }

  // Start everything
  initializeAll();
});