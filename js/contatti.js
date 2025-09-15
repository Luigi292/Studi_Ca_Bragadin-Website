// contatti.js - Contact page specific functionality

document.addEventListener('DOMContentLoaded', function() {
    // Service options for the contact form
    const serviceOptions = {
        avv_lenzi: [
            { value: "contrattualistica_aziendale", text: "Contrattualistica Aziendale" },
            { value: "recupero_crediti", text: "Recupero Crediti" },
            { value: "ecommerce", text: "E-commerce" },
            { value: "contenzioso_civile", text: "Contenzioso Civile" },
            { value: "diritto_bancario", text: "Diritto Bancario" },
            { value: "diritto_fallimentare", text: "Diritto Fallimentare" },
            { value: "proprieta_industriale", text: "Proprietà Industriale" },
            { value: "diritto_societario", text: "Diritto Societario" },
            { value: "altro_legale", text: "Altro (specificare nel messaggio)" }
        ],
        dott_maretto: [
            { value: "contabilita_bilanci", text: "Contabilità e Bilanci" },
            { value: "servizi_fiscali", text: "Servizi Fiscali" },
            { value: "contrattualistica_aziendale", text: "Contrattualistica Aziendale" },
            { value: "consulenza_bancaria_finanziaria", text: "Consulenza Bancaria e Finanziaria" },
            { value: "consulenza_aziendale_controllo_gestione", text: "Consulenza Aziendale e Controllo di Gestione" },
            { value: "consulenza_enti_pubblici", text: "Consulenza per Enti Pubblici" },
            { value: "altro_commerciale", text: "Altro (specificare nel messaggio)" }
        ],
        dott_cecolin: [
            { value: "contabilita_bilanci", text: "Contabilità e Bilanci" },
            { value: "fiscalita_dichiarazioni", text: "Fiscalità e Dichiarazioni" },
            { value: "controllo_gestione_pianificazione", text: "Controllo di Gestione e Pianificazione" },
            { value: "contrattualistica_impresa", text: "Contrattualistica d'Impresa" },
            { value: "operazioni_straordinarie", text: "Operazioni Straordinarie" },
            { value: "revisione_governance", text: "Revisione e Governance Societaria" },
            { value: "altro_societario", text: "Altro (specificare nel messaggio)" }
        ]
    };

    // Contact form handling
    const professionalSelect = document.getElementById('professional');
    const serviceSelect = document.getElementById('service');
    const form = document.getElementById('professionalContactForm');

    // Dynamic service options
    if (professionalSelect && serviceSelect) {
        professionalSelect.addEventListener('change', function() {
            const selectedProfessional = this.value;
            serviceSelect.innerHTML = '<option value="">Seleziona un servizio</option>';

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
            submitButton.textContent = 'Invio in corso...';

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
                modalMessage.textContent = 'Per favore, compila tutti i campi obbligatori (*)';
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
                        throw new Error(err.message || 'Errore del server');
                    }).catch(() => {
                        // If JSON parsing fails, throw with status text
                        throw new Error(response.statusText || 'Errore di rete');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    modalMessage.innerHTML = data.message || "La sua richiesta è stata inviata correttamente. Riceverà una risposta al più presto. Se la richiesta è urgente o non dovesse ricevere risposta entro 48 ore, la invitiamo a contattare telefonicamente il professionista al numero indicato nella sua pagina profilo o nell'email di conferma ricevuta.";
                    modal.style.display = 'flex';
                    setTimeout(() => { modal.style.opacity = '1'; }, 10);
                    form.reset();
                    if (serviceSelect) {
                        serviceSelect.innerHTML = '<option value="">Seleziona un servizio</option>';
                    }
                } else {
                    throw new Error(data.message || 'Errore sconosciuto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalMessage.textContent = error.message || 'Si è verificato un errore durante l\'invio del messaggio. Si prega di riprovare più tardi.';
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