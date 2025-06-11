// Contact page functionality

document.addEventListener('DOMContentLoaded', function() {
    initializeContactPage();
});

function initializeContactPage() {
    initializeContactForm();
    initializeFAQ();
}

function initializeContactForm() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactFormSubmit);
        
        // Add real-time validation
        const inputs = contactForm.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateContactField);
            input.addEventListener('input', clearContactFieldError);
        });
    }
}

async function handleContactFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Validate form
    if (!validateContactForm(form)) {
        showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
        return;
    }
    
    // Update button state
    submitButton.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
        </svg>
        Envoi en cours...
    `;
    submitButton.disabled = true;
    
    try {
        // Prepare contact data
        const contactData = {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            subject: formData.get('subject'),
            message: formData.get('message'),
            timestamp: new Date().toISOString()
        };
        
        const response = await fetch('backend/api/contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(contactData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Message envoyé avec succès! Nous vous répondrons bientôt.', 'success');
            form.reset();
            
            // Google Analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'contact_form_submit', {
                    event_category: 'engagement',
                    event_label: contactData.subject
                });
            }
            
        } else {
            throw new Error(data.message || 'Erreur lors de l\'envoi');
        }
        
    } catch (error) {
        console.error('Contact form error:', error);
        showNotification('Erreur lors de l\'envoi du message. Veuillez réessayer ou nous contacter directement.', 'error');
    }
    
    // Reset button
    submitButton.innerHTML = originalText;
    submitButton.disabled = false;
}

function validateContactForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateContactField({ target: field })) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateContactField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    clearContactFieldError(e);
    
    if (field.hasAttribute('required') && !value) {
        showContactFieldError(field, 'Ce champ est obligatoire');
        return false;
    }
    
    // Specific validations
    switch(field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showContactFieldError(field, 'Adresse email invalide');
                return false;
            }
            break;
        case 'tel':
            if (value && !isValidPhoneNumber(value)) {
                showContactFieldError(field, 'Numéro de téléphone invalide');
                return false;
            }
            break;
    }
    
    // Message length validation
    if (field.name === 'message' && value.length < 10) {
        showContactFieldError(field, 'Le message doit contenir au moins 10 caractères');
        return false;
    }
    
    return true;
}

function clearContactFieldError(e) {
    const field = e.target;
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('error');
}

function showContactFieldError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.cssText = `
        color: var(--error-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    `;
    
    field.parentNode.appendChild(errorElement);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhoneNumber(phone) {
    // Remove all non-digits
    const digits = phone.replace(/\D/g, '');
    
    // Check if it's a valid length (at least 9 digits)
    return digits.length >= 9;
}

function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const header = item.querySelector('h3');
        const content = item.querySelector('.faq-content');
        
        if (header && content) {
            // Initially hide content
            content.style.display = 'none';
            header.style.cursor = 'pointer';
            header.style.userSelect = 'none';
            
            // Add click event
            header.addEventListener('click', function() {
                const isOpen = content.style.display === 'block';
                
                // Close all other FAQ items
                faqItems.forEach(otherItem => {
                    const otherContent = otherItem.querySelector('.faq-content');
                    const otherHeader = otherItem.querySelector('h3');
                    if (otherContent && otherHeader !== header) {
                        otherContent.style.display = 'none';
                        otherHeader.classList.remove('active');
                    }
                });
                
                // Toggle current item
                if (isOpen) {
                    content.style.display = 'none';
                    header.classList.remove('active');
                } else {
                    content.style.display = 'block';
                    header.classList.add('active');
                }
            });
        }
    });
}

// Add CSS for contact page specific styles
const style = document.createElement('style');
style.textContent = `
    .contact-page {
        padding: 3rem 0;
    }
    
    .contact-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        margin-top: 2rem;
    }
    
    .contact-info {
        display: grid;
        gap: 1.5rem;
    }
    
    .contact-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .contact-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .contact-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    
    .contact-card h3 {
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
    }
    
    .contact-card p {
        color: var(--neutral-600);
        margin-bottom: 1rem;
    }
    
    .contact-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .contact-link:hover {
        color: var(--primary-dark);
    }
    
    .form-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .form-card h2 {
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }
    
    .contact-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .contact-form .form-group {
        margin-bottom: 1.5rem;
    }
    
    .contact-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--secondary-color);
    }
    
    .contact-form input,
    .contact-form textarea,
    .contact-form select {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--neutral-300);
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }
    
    .contact-form input:focus,
    .contact-form textarea:focus,
    .contact-form select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(201, 161, 66, 0.1);
    }
    
    .contact-form input.error,
    .contact-form textarea.error,
    .contact-form select.error {
        border-color: var(--error-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .faq-section {
        padding: 4rem 0;
        background: var(--neutral-50);
    }
    
    .faq-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .faq-item {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .faq-item h3 {
        padding: 1.5rem;
        margin: 0;
        background: var(--primary-color);
        color: white;
        position: relative;
        transition: background-color 0.3s ease;
    }
    
    .faq-item h3:hover {
        background: var(--primary-dark);
    }
    
    .faq-item h3.active {
        background: var(--primary-dark);
    }
    
    .faq-item h3::after {
        content: '+';
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }
    
    .faq-item h3.active::after {
        transform: translateY(-50%) rotate(45deg);
    }
    
    .faq-content {
        padding: 1.5rem;
        animation: fadeIn 0.3s ease;
    }
    
    .faq-content p {
        margin-bottom: 1rem;
    }
    
    .faq-content p:last-child {
        margin-bottom: 0;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @media (max-width: 1024px) {
        .contact-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .contact-form .form-row {
            grid-template-columns: 1fr;
        }
        
        .contact-page {
            padding: 2rem 0;
        }
        
        .form-card,
        .contact-card {
            padding: 1.5rem;
        }
    }
`;
document.head.appendChild(style);