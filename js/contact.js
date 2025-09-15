// contact.js - Contact page specific functionality (English version)

document.addEventListener('DOMContentLoaded', function() {
    // Service options for the contact form (English)
    const serviceOptions = {
        avv_lenzi: [
            { value: "contrattualistica_aziendale", text: "Corporate Contract Law" },
            { value: "recupero_crediti", text: "Debt Recovery" },
            { value: "ecommerce", text: "E-commerce" },
            { value: "contenzioso_civile", text: "Civil Litigation" },
            { value: "diritto_bancario", text: "Banking Law" },
            { value: "diritto_fallimentare", text: "Bankruptcy Law" },
            { value: "proprieta_industriale", text: "Industrial Property" },
            { value: "diritto_societario", text: "Corporate Law" },
            { value: "altro_legale", text: "Other (please specify in message)" }
        ],
        dott_maretto: [
            { value: "contabilita_bilanci", text: "Accounting and Financial Statements" },
            { value: "servizi_fiscali", text: "Tax Services" },
            { value: "contrattualistica_aziendale", text: "Corporate Contract Law" },
            { value: "consulenza_bancaria_finanziaria", text: "Banking and Financial Consulting" },
            { value: "consulenza_aziendale_controllo_gestione", text: "Business Consulting and Management Control" },
            { value: "consulenza_enti_pubblici", text: "Public Entity Consulting" },
            { value: "altro_commerciale", text: "Other (please specify in message)" }
        ],
        dott_cecolin: [
            { value: "contabilita_bilanci", text: "Accounting and Financial Statements" },
            { value: "fiscalita_dichiarazioni", text: "Taxation and Declarations" },
            { value: "controllo_gestione_pianificazione", text: "Management Control and Planning" },
            { value: "contrattualistica_impresa", text: "Business Contract Law" },
            { value: "operazioni_straordinarie", text: "Extraordinary Operations" },
            { value: "revisione_governance", text: "Audit and Corporate Governance" },
            { value: "altro_societario", text: "Other (please specify in message)" }
        ]
    };

    // Contact form handling
    const professionalSelect = document.getElementById('professional');
    const serviceSelect = document.getElementById('service');
    const form = document.getElementById('professionalContactForm');

    // Update form labels to English if needed
    function updateFormLabelsToEnglish() {
        const firstNameLabel = form.querySelector('label[for="firstName"]');
        const lastNameLabel = form.querySelector('label[for="lastName"]');
        const emailLabel = form.querySelector('label[for="email"]');
        const phoneLabel = form.querySelector('label[for="phone"]');
        const professionalLabel = form.querySelector('label[for="professional"]');
        const serviceLabel = form.querySelector('label[for="service"]');
        const messageLabel = form.querySelector('label[for="message"]');
        const submitButton = form.querySelector('.submit-button');
        const formSubtitle = form.querySelector('.form-subtitle');
        const formTitle = form.querySelector('h2');
        
        if (firstNameLabel) firstNameLabel.textContent = 'First Name*';
        if (lastNameLabel) lastNameLabel.textContent = 'Last Name*';
        if (emailLabel) emailLabel.textContent = 'Email*';
        if (phoneLabel) phoneLabel.textContent = 'Phone';
        if (professionalLabel) professionalLabel.textContent = 'Professional*';
        if (serviceLabel) serviceLabel.textContent = 'Required Service*';
        if (messageLabel) messageLabel.textContent = 'Message*';
        if (submitButton) submitButton.textContent = 'Send Request';
        if (formSubtitle) formSubtitle.textContent = 'Fill out the form and we will contact you within 24 hours';
        if (formTitle) formTitle.textContent = 'Request a Consultation';
        
        // Update privacy policy text
        const privacyLabel = form.querySelector('label[for="privacyConsent"]');
        if (privacyLabel) {
            privacyLabel.innerHTML = 'I have read and accept the <a href="privacy.html" target="_blank">Privacy Policy</a> and <a href="terms.html" target="_blank">Terms and Conditions</a> of this website.';
        }
        
        // Update select placeholders
        if (professionalSelect) {
            professionalSelect.querySelector('option[value=""]').textContent = 'Select a professional';
        }
        if (serviceSelect) {
            serviceSelect.querySelector('option[value=""]').textContent = 'Select a service';
        }
    }

    // Call the function to update labels
    updateFormLabelsToEnglish();

    // Dynamic service options
    if (professionalSelect && serviceSelect) {
        professionalSelect.addEventListener('change', function() {
            const selectedProfessional = this.value;
            serviceSelect.innerHTML = '<option value="">Select a service</option>';

            if (selectedProfessional && serviceOptions[selectedProfessional]) {
                serviceOptions[selectedProfessional].forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    serviceSelect.appendChild(opt);
                });
            }
        });
    }

    // Create modal element for form responses
    const modal = document.createElement('div');
    modal.className = 'form-modal';
    modal.style.display = 'none';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.7)';
    modal.style.zIndex = '1000';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.opacity = '0';
    modal.style.transition = 'opacity 0.3s ease';

    const modalContent = document.createElement('div');
    modalContent.style.backgroundColor = 'white';
    modalContent.style.padding = '30px';
    modalContent.style.borderRadius = '8px';
    modalContent.style.maxWidth = '500px';
    modalContent.style.width = '90%';
    modalContent.style.boxShadow = '0 5px 15px rgba(0,0,0,0.3)';
    modalContent.style.position = 'relative';

    const closeButton = document.createElement('button');
    closeButton.innerHTML = '&times;';
    closeButton.style.position = 'absolute';
    closeButton.style.top = '10px';
    closeButton.style.right = '10px';
    closeButton.style.background = 'none';
    closeButton.style.border = 'none';
    closeButton.style.fontSize = '24px';
    closeButton.style.cursor = 'pointer';
    closeButton.style.color = '#666';

    const modalMessage = document.createElement('p');
    modalMessage.style.margin = '20px 0';
    modalMessage.style.fontSize = '16px';
    modalMessage.style.lineHeight = '1.6';
    modalMessage.style.color = '#333';

    modalContent.appendChild(closeButton);
    modalContent.appendChild(modalMessage);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    closeButton.addEventListener('click', function() {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    });

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const submitButton = form.querySelector('.submit-button');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';

            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                modalMessage.textContent = 'Please fill in all required fields (*)';
                modal.style.display = 'flex';
                setTimeout(() => { modal.style.opacity = '1'; }, 10);
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                return;
            }

            // Get form data
            const formData = new FormData(form);

            // Send AJAX request
            fetch('process_contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    // Try to parse the error response as JSON
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error');
                    }).catch(() => {
                        // If JSON parsing fails, throw with status text
                        throw new Error(response.statusText || 'Network error');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    modalMessage.innerHTML = data.message || "Your request has been sent successfully. You will receive a response as soon as possible. If your request is urgent or you don't receive a response within 48 hours, please contact the professional directly at the phone number indicated on their profile page or in the confirmation email you received.";
                    modal.style.display = 'flex';
                    setTimeout(() => { modal.style.opacity = '1'; }, 10);
                    form.reset();
                    if (serviceSelect) {
                        serviceSelect.innerHTML = '<option value="">Select a service</option>';
                    }
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalMessage.textContent = error.message || 'An error occurred while sending your message. Please try again later.';
                modal.style.display = 'flex';
                setTimeout(() => { modal.style.opacity = '1'; }, 10);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
        });

        // Add input validation styling
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        });
    }
});