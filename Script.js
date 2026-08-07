// MENU TOGGLE
const menuIcon = document.querySelector('#menu-icon');
const navbar = document.querySelector('.navbar');

menuIcon.onclick = () => {
    menuIcon.classList.toggle('bx-x');
    navbar.classList.toggle('active');
};

// SCROLL SECTIONS ACTIVE LINK
const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('header nav a');

window.onscroll = () => {
    const top = window.scrollY;

    sections.forEach(sec => {
        const offset = sec.offsetTop - 150;
        const height = sec.offsetHeight;
        const id = sec.getAttribute('id');

        if (top >= offset && top < offset + height) {
            navLinks.forEach(link => link.classList.remove('active'));
            const activeLink = document.querySelector(`header nav a[href*="${id}"]`);
            if (activeLink) activeLink.classList.add('active');
        }
    });

    const header = document.querySelector('.header');
    header.classList.toggle('sticky', window.scrollY > 100);
};

// FERMER MENU LORS DU CLICK SUR UN LIEN
navLinks.forEach(link => {
    link.onclick = () => {
        menuIcon.classList.remove('bx-x');
        navbar.classList.remove('active');
    };
});

// SWIPER
new Swiper('.mySwiper', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    grabCursor: true,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});

// DARK MODE
const darkModeIcon = document.querySelector('#darkMode-icon');
const savedTheme = localStorage.getItem('theme');

if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    darkModeIcon.classList.add('bx-sun');
}

darkModeIcon.onclick = () => {
    darkModeIcon.classList.toggle('bx-sun');
    document.body.classList.toggle('dark-mode');
    localStorage.setItem(
        'theme',
        document.body.classList.contains('dark-mode') ? 'dark' : 'light'
    );
};

// SCROLL REVEAL
ScrollReveal({
    reset: false,
    distance: '80px',
    duration: 1600,
    delay: 120,
});

ScrollReveal().reveal('.home-content, .heading', { origin: 'top' });
ScrollReveal().reveal('.home-img img, .service-container, .portfolio-box, .testimonial-wrapper, .contact-form', { origin: 'bottom' });
ScrollReveal().reveal('.home-content h1, .about-img img', { origin: 'left' });
ScrollReveal().reveal('.home-content h3, .home-content p, .about-content', { origin: 'right' });

// CONTACT FORM
const form = document.querySelector('.contact-form');
const statusBox = document.querySelector('#contact-status');
const submitBtn = document.querySelector('#contact-submit');

const statusMessages = {
    missing: { type: 'error', text: 'Merci de remplir tous les champs obligatoires.' },
    invalid: { type: 'error', text: 'Email invalide ou données trop longues. Vérifiez votre saisie.' },
    wait: { type: 'warning', text: 'Un peu de patience : attendez quelques secondes avant de renvoyer.' },
    spam: { type: 'error', text: 'Envoi bloqué (anti-spam).' },
    config: { type: 'error', text: 'Configuration mail manquante côté serveur (config.php).' },
    error: { type: 'error', text: "L'envoi a échoué. Réessayez dans un instant." },
};

function showStatus(type, text) {
    if (!statusBox) return;
    statusBox.hidden = false;
    statusBox.className = `contact-status is-${type}`;
    statusBox.textContent = text;
}

function clearFieldErrors() {
    form.querySelectorAll('.field').forEach(field => field.classList.remove('is-invalid'));
    form.querySelectorAll('.field-error').forEach(el => {
        el.textContent = '';
    });
}

function setFieldError(name, message) {
    const input = form.querySelector(`[name="${name}"]`);
    if (!input) return;
    const field = input.closest('.field');
    const error = form.querySelector(`[data-error-for="${name}"]`);
    if (field) field.classList.add('is-invalid');
    if (error) error.textContent = message;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

const params = new URLSearchParams(window.location.search);
const status = params.get('status');
if (status && statusMessages[status]) {
    showStatus(statusMessages[status].type, statusMessages[status].text);
}

if (form) {
    form.addEventListener('submit', function (e) {
        clearFieldErrors();

        const nom = form.querySelector("input[name='nom']").value.trim();
        const email = form.querySelector("input[name='email']").value.trim();
        const message = form.querySelector("textarea[name='message']").value.trim();

        let hasError = false;

        if (nom === '') {
            setFieldError('nom', 'Le nom est requis.');
            hasError = true;
        }

        if (email === '') {
            setFieldError('email', "L'email est requis.");
            hasError = true;
        } else if (!isValidEmail(email)) {
            setFieldError('email', 'Format email invalide.');
            hasError = true;
        }

        if (message === '') {
            setFieldError('message', 'Le message est requis.');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            showStatus('error', 'Merci de corriger les champs en rouge.');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            const text = submitBtn.querySelector('.btn-text');
            const loading = submitBtn.querySelector('.btn-loading');
            if (text) text.hidden = true;
            if (loading) loading.hidden = false;
        }

        showStatus('warning', 'Envoi en cours...');
    });
}
