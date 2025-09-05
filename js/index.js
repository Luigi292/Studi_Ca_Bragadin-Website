
document.addEventListener('DOMContentLoaded', function() {
  // Remove any existing parallax code
  
  // Add this if you need to ensure the background covers the section
  function adjustBackground() {
    const section = document.querySelector('.collaboration-section');
    const bg = document.querySelector('.collaboration-bg');
    if (!section || !bg) return;
    
    // Set background height to match section
    bg.style.height = section.offsetHeight + 'px';
  }

  // Run on load and resize
  window.addEventListener('load', adjustBackground);
  window.addEventListener('resize', adjustBackground);
  adjustBackground();

  // Initialize carousel
  function initCarousel() {
    const carousel = document.getElementById('carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.slide');
    let current = 0;

    function show(index) {
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
      });
    }

    function next() {
      current = (current + 1) % slides.length;
      show(current);
    }

    show(0);
    setInterval(next, 5000);
  }

  function loadNewsPreviews() {
    const newsGrid = document.getElementById('newsPreviewGrid');
    if (!newsGrid) return;

    newsGrid.innerHTML = '<div class="news-loading"><div class="news-loading-spinner"></div></div>';

    fetch('news-data.json')
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
        // Show "no news" message instead of error for file not found
        if (error instanceof TypeError || error.message.includes('Failed to fetch')) {
          showNoNewsPreview();
        } else {
          showNewsError();
        }
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
              <a href="news.html#news-${news.id}" class="news-preview-more">Leggi tutto</a>
             
            </div>
          </div>
        `;
        newsGrid.appendChild(card);
      });
    }

    function showNoNewsPreview() {
      newsGrid.innerHTML = `
        <div class="no-news-message">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <h3>Nessuna news pubblicata al momento</h3>
          <p>I nostri professionisti stanno preparando nuovi contenuti. Torna a controllare presto!</p>
        </div>
      `;
    }

    function showNewsError() {
      newsGrid.innerHTML = `
        <div class="news-error">
          <p>Impossibile caricare le news al momento.</p>
          <a href="news.html">Vai alla pagina news</a>
        </div>`;
    }
  }

  // Initialize functions
  initCarousel();
  loadNewsPreviews();
  
  // Back to top button
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
});
