// JS untuk halaman lupa password
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

    // Tidak ada logika tambahan untuk cooldown; form kode muncul setelah server men-set session
});

