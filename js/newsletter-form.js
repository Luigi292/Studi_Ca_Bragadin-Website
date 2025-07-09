document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('mainNewsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset error messages
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });
            
            // Hide success message if shown
            document.getElementById('formSuccessMessage').style.display = 'none';
            
            // Get form data
            const formData = new FormData(this);
            
            // Add AJAX submission
            fetch('process-newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successMessage = document.getElementById('formSuccessMessage');
                    successMessage.textContent = data.message;
                    successMessage.style.display = 'block';
                    
                    // Reset form
                    newsletterForm.reset();
                    
                    // Scroll to success message
                    successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Show errors
                    if (data.errors) {
                        for (const [field, message] of Object.entries(data.errors)) {
                            const errorElement = document.getElementById(`${field}Error`);
                            if (errorElement) {
                                errorElement.textContent = message;
                                errorElement.style.display = 'block';
                            }
                        }
                    } else if (data.message) {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Si è verificato un errore durante l'invio del modulo. Riprova più tardi.");
            });
        });
    }
});