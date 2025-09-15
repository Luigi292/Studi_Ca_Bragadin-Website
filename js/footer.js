// footer.js - Footer and general site functionality (updated for compatibility)
document.addEventListener('DOMContentLoaded', function() {
    // Set current year in footer
    document.querySelectorAll('.current-year').forEach(el => {
        el.textContent = new Date().getFullYear();
    });

    // Initialize AOS if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Scroll to top functionality - store as a named function
    function handleScrollToTop() {
        const scrollBtn = document.querySelector(".js-top");
        if (scrollBtn && window.scrollY > 300) {
            scrollBtn.classList.add("active");
        } else if (scrollBtn) {
            scrollBtn.classList.remove("active");
        }
    }

    // Set up scroll event
    window.addEventListener("scroll", handleScrollToTop);

    // Set up click event
    const goTopButton = document.querySelector(".js-gotop");
    if (goTopButton) {
        goTopButton.addEventListener("click", function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }
    
    // Make the scroll handler available globally for navbar compatibility
    window.footerScrollHandler = handleScrollToTop;
});