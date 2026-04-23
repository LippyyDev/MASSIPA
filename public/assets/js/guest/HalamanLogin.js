// JS extracted from HalamanLogin.php
// Trigger animasi fade-in saat halaman selesai dimuat
window.addEventListener('DOMContentLoaded', function() {
    // "Ingat Saya" ditangani server-side via httpOnly cookie (remember_username).
    // localStorage sengaja TIDAK digunakan — localStorage bisa dibaca XSS,
    // sedangkan httpOnly cookie tidak bisa diakses JavaScript sama sekali.
    // Username pre-fill sudah di-render langsung oleh PHP di atribut value="" form.
    // Bersihkan data lama dari mekanisme sebelumnya (migrasi localStorage → httpOnly cookie):
    try { localStorage.removeItem('rememberedUsername'); } catch (e) {}

    // Toggle password visibility
    const toggler = document.getElementById("togglePassword");
    const pwdInput = document.getElementById("password");
    
    if (toggler && pwdInput) {
        toggler.addEventListener("click", function () {
            const type = pwdInput.getAttribute("type") === "password" ? "text" : "password";
            pwdInput.setAttribute("type", type);
            const icon = this.querySelector("i");
            if (icon) {
                if (type === 'password') {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            }
        });
    }

    // Slider otomatis
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slider-img');
    
    if (slides.length > 0) {
        function showSlide(idx) {
            slides.forEach((img, i) => {
                img.classList.toggle('active', i === idx);
            });
            currentSlide = idx;
        }
        
        let sliderInterval = setInterval(() => {
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }, 3500);
    }
});
