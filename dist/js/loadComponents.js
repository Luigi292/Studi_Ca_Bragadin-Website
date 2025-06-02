// Robust component loader with fallback
function loadComponents() {
  const components = [
    { url: 'components/navbar.html', id: 'navbar' },
    { url: 'components/footer.html', id: 'footer' }
  ];

  components.forEach(component => {
    fetch(component.url)
      .then(response => {
        if (!response.ok) throw new Error(`${response.status}`);
        return response.text();
      })
      .then(html => {
        document.getElementById(component.id).innerHTML = html;
        if (component.id === 'navbar') initDropdown();
        if (component.id === 'footer') {
          document.querySelector('.current-year').textContent = new Date().getFullYear();
        }
      })
      .catch(error => {
        console.error(`Failed to load ${component.url}:`, error);
        document.getElementById(component.id).innerHTML = `
          <div style="color:red;padding:1rem">
            Error loading ${component.id}. Please refresh the page.
          </div>
        `;
      });
  });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadComponents);
} else {
  loadComponents();
}