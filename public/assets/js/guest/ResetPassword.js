// JS untuk halaman reset password
window.addEventListener('DOMContentLoaded', function() {
    // Slider otomatis
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slider-img');

    if (slides.length > 0) {
        const showSlide = (idx) => {
            slides.forEach((img, i) => {
                img.classList.toggle('active', i === idx);
            });
            currentSlide = idx;
        };

        setInterval(() => {
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }, 3500);
    }

    // Toggle visibility untuk input password
    const toggles = document.querySelectorAll('[data-toggle-password]');
    toggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            if (!input) return;

            const newType = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', newType);

            const icon = btn.querySelector('i');
            if (icon) {
                if (newType === 'password') {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            }
        });
    });
});

