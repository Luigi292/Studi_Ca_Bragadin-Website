// js/news.js - News page functionality
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let newsData = [];
  let currentPage = 1;
  const newsPerPage = 4;
  let currentNewsItems = [];

  // Function to fetch news data from JSON file
  async function fetchNewsData() {
    try {
      const response = await fetch('news-data.json');
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      newsData = await response.json();
      return newsData;
    } catch (error) {
      console.error('Error fetching news data:', error);
      showError('Impossibile caricare le news al momento. Riprova più tardi.');
      return [];
    }
  }

  // Function to render news items with pagination
  function renderNewsItems(newsItems, sortBy = 'recent', page = 1) {
    const newsList = document.getElementById('newsList');
    const pagination = document.getElementById('newsPagination');
    
    // Show loading state
    newsList.innerHTML = '<div class="loading-spinner"></div>';
    pagination.innerHTML = '';

    // Sort news items based on the selected option
    let sortedNews = [...newsItems];
    
    if (sortBy === 'recent') {
      sortedNews.sort((a, b) => new Date(b.fullDate) - new Date(a.fullDate));
    } else if (sortBy === 'oldest') {
      sortedNews.sort((a, b) => new Date(a.fullDate) - new Date(b.fullDate));
    } else if (sortBy === 'popular') {
      sortedNews.sort((a, b) => b.views - a.views);
    }

    // Update current news items
    currentNewsItems = sortedNews;
    
    // Calculate total pages
    const totalPages = Math.ceil(sortedNews.length / newsPerPage);
    
    // Calculate start and end index for current page
    const startIndex = (page - 1) * newsPerPage;
    const endIndex = Math.min(startIndex + newsPerPage, sortedNews.length);
    
    // Render news items for current page
    newsList.innerHTML = '';
    for (let i = startIndex; i < endIndex; i++) {
      const news = sortedNews[i];
      const newsItem = document.createElement('article');
      newsItem.className = 'news-preview-card';
      newsItem.setAttribute('data-id', news.id);
      newsItem.innerHTML = `
        <div class="news-preview-card-header">
          <span class="news-preview-date">${news.date}</span>
          <span class="news-preview-category">${news.category}</span>
        </div>
        <div class="news-preview-content">
          <h3>${news.title}</h3>
          <div class="news-preview-text">
            <p>${news.preview}</p>
          </div>
          <div class="news-full-content" style="display:none;">
            ${news.content}
          </div>
          <div class="news-preview-actions">
            <a href="#" class="news-read-more">Leggi tutto</a>
            <a href="${news.pdf}" class="news-preview-download" download>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              PDF
            </a>
          </div>
        </div>
      `;
      newsList.appendChild(newsItem);
    }

    // Render pagination controls if needed
    if (totalPages > 1) {
      let paginationHTML = '<div class="pagination-controls">';
      
      // Previous button
      if (page > 1) {
        paginationHTML += `<button class="pagination-button" data-page="${page - 1}">Precedente</button>`;
      }
      
      // Page numbers
      for (let i = 1; i <= totalPages; i++) {
        if (i === page) {
          paginationHTML += `<span class="current-page">${i}</span>`;
        } else {
          paginationHTML += `<button class="pagination-button" data-page="${i}">${i}</button>`;
        }
      }
      
      // Next button
      if (page < totalPages) {
        paginationHTML += `<button class="pagination-button" data-page="${page + 1}">Successivo</button>`;
      }
      
      paginationHTML += '</div>';
      pagination.innerHTML = paginationHTML;
      
      // Add event listeners to pagination buttons
      document.querySelectorAll('.pagination-button').forEach(button => {
        button.addEventListener('click', function() {
          const newPage = parseInt(this.getAttribute('data-page'));
          const activeCategory = document.querySelector('#newsCategories a.active');
          const category = activeCategory ? activeCategory.getAttribute('data-category') : 'all';
          
          if (category === 'all') {
            renderNewsItems(newsData, sortBy, newPage);
          } else {
            const filteredNews = newsData.filter(news => news.category === category);
            renderNewsItems(filteredNews, sortBy, newPage);
          }
          
          // Scroll to top of news list
          document.getElementById('newsList').scrollIntoView({ behavior: 'smooth' });
        });
      });
    }

    // Reattach event listeners to the new elements
    setupNewsModal();
  }

  // Function to render popular news in sidebar
  function renderPopularNews() {
    const popularNewsBox = document.getElementById('popularNewsBox');
    
    // Sort news by views and take top 4
    const popularNews = [...newsData]
      .sort((a, b) => b.views - a.views)
      .slice(0, 4);
    
    let popularNewsHTML = '<h3>News Popolari</h3>';
    
    if (popularNews.length === 0) {
      popularNewsHTML += '<p>Nessuna news popolare al momento</p>';
    } else {
      popularNews.forEach(news => {
        popularNewsHTML += `
          <div class="popular-news-item">
            <span class="popular-news-date">${news.date}</span>
            <a href="#" class="popular-news-title" data-id="${news.id}">${news.title}</a>
          </div>
        `;
      });
    }
    
    popularNewsBox.innerHTML = popularNewsHTML;
    
    // Add click event to popular news titles
    document.querySelectorAll('.popular-news-title').forEach(title => {
      title.addEventListener('click', function(e) {
        e.preventDefault();
        const newsId = parseInt(this.getAttribute('data-id'));
        const newsItem = newsData.find(item => item.id === newsId);
        if (newsItem) {
          showNewsModal(newsItem);
        }
      });
    });
  }

  // Function to render categories in sidebar
  function renderCategories() {
    const categoriesContainer = document.getElementById('newsCategories');
    
    // Get unique categories from news data
    const categories = [...new Set(newsData.map(news => news.category))];
    
    let categoriesHTML = '<li><a href="#" class="active" data-category="all">Tutte le news</a></li>';
    
    categories.forEach(category => {
      categoriesHTML += `
        <li><a href="#" data-category="${category}">${category}</a></li>
      `;
    });
    
    categoriesContainer.innerHTML = categoriesHTML;
    
    // Add click event to category links
    document.querySelectorAll('#newsCategories a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active state
        document.querySelectorAll('#newsCategories a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.getAttribute('data-category');
        const sortBy = document.getElementById('newsSort').value;
        
        if (category === 'all') {
          renderNewsItems(newsData, sortBy, 1);
        } else {
          const filteredNews = newsData.filter(news => news.category === category);
          renderNewsItems(filteredNews, sortBy, 1);
        }
      });
    });
  }

  // Function to setup news modal functionality
  function setupNewsModal() {
    const modal = document.getElementById('newsModal');
    
    document.querySelectorAll('.news-read-more').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const newsCard = this.closest('.news-preview-card');
        const newsId = parseInt(newsCard.getAttribute('data-id'));
        const newsItem = newsData.find(item => item.id === newsId);
        
        if (newsItem) {
          showNewsModal(newsItem);
        }
      });
    });
    
    document.querySelector('.modal-close').addEventListener('click', function() {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
    
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    });
  }

  // Function to show news in modal
  function showNewsModal(newsItem) {
    const modal = document.getElementById('newsModal');
    const modalBody = document.getElementById('modalBody');
    
    modalBody.innerHTML = `
      <div class="modal-header">
        <span class="modal-category">${newsItem.category}</span>
        <span class="modal-date">${newsItem.date}</span>
      </div>
      <h2 class="modal-title">${newsItem.title}</h2>
      <div class="modal-text">${newsItem.content}</div>
      <div class="modal-actions">
        <a href="${newsItem.pdf}" class="cta-button" download>Scarica PDF Completo</a>
      </div>
    `;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Increment view count (in a real app, this would be saved to a database)
    newsItem.views++;
    
    // Update the popular news in sidebar
    renderPopularNews();
  }

  // Function to show error message
  function showError(message) {
    const newsList = document.getElementById('newsList');
    newsList.innerHTML = `
      <div class="news-error">
        <p>${message}</p>
        <button class="retry-button" id="retryButton">Riprova</button>
      </div>
    `;
    
    document.getElementById('retryButton').addEventListener('click', initializePage);
  }

  // Function to show no news message
  function showNoNewsMessage() {
    const newsList = document.getElementById('newsList');
    const pagination = document.getElementById('newsPagination');
    const sortSection = document.querySelector('.news-sort');
    
    newsList.innerHTML = `
      <div class="no-news-message">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
        <h2>Nessuna news pubblicata al momento</h2>
        <p>I nostri professionisti stanno preparando nuovi contenuti. Torna a controllare presto!</p>
      </div>
    `;
    
    // Hide pagination and sort options
    pagination.style.display = 'none';
    sortSection.style.display = 'none';
  }

  // Initialize the page
  async function initializePage() {
    try {
      // Set current year in footer
      document.querySelector('.current-year').textContent = new Date().getFullYear();
      
      // Show loading state
      document.getElementById('newsList').innerHTML = '<div class="loading-spinner"></div>';
      document.getElementById('popularNewsBox').innerHTML = '<h3>News Popolari</h3><div class="loading-spinner"></div>';
      
      // Fetch news data
      await fetchNewsData();
      
      // Check if newsData is empty or invalid
      if (!newsData || !newsData.length || newsData.length === 0) {
        showNoNewsMessage();
        document.getElementById('popularNewsBox').innerHTML = '<h3>News Popolari</h3><p>Nessuna news al momento</p>';
        return;
      }
      
      // Initial render
      renderNewsItems(newsData);
      renderPopularNews();
      renderCategories();
      
      // Setup sort functionality
      document.getElementById('newsSort').addEventListener('change', function() {
        const sortBy = this.value;
        const activeCategory = document.querySelector('#newsCategories a.active');
        const category = activeCategory ? activeCategory.getAttribute('data-category') : 'all';
        
        if (category === 'all') {
          renderNewsItems(newsData, sortBy, 1);
        } else {
          const filteredNews = newsData.filter(news => news.category === category);
          renderNewsItems(filteredNews, sortBy, 1);
        }
      });

      // Scroll to top button functionality
      window.addEventListener("scroll", function() {
        const scrollBtn = document.querySelector(".js-top");
        if (window.scrollY > 300) {
          scrollBtn.classList.add("active");
        } else {
          scrollBtn.classList.remove("active");
        }
      });

      document.querySelector(".js-gotop").addEventListener("click", function(e) {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      });
    } catch (error) {
      console.error('Error initializing page:', error);
      showError('Si è verificato un errore durante il caricamento della pagina.');
    }
  }

  // Start the page initialization
  initializePage();
});