// footer.js - Footer and general site functionality

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

    // Scroll to top functionality
    window.addEventListener("scroll", function () {
        const scrollBtn = document.querySelector(".js-top");
        if (window.scrollY > 300) {
            scrollBtn.classList.add("active");
        } else {
            scrollBtn.classList.remove("active");
        }
    });

    document.querySelector(".js-gotop").addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
});