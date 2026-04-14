// JS extracted from HalamanLogin.php
// Trigger animasi fade-in saat halaman selesai dimuat
window.addEventListener('DOMContentLoaded', function() {
    // Fitur Ingat Saya dengan Local Storage
    const savedUsername = localStorage.getItem('rememberedUsername');
    if (savedUsername) {
        const usernameInput = document.getElementById('username');
        const rememberCheckbox = document.getElementById('remember');
        if (usernameInput) {
            usernameInput.value = savedUsername;
        }
        if (rememberCheckbox) {
            rememberCheckbox.checked = true;
        }
    }

    // Saat form login disubmit
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const username = document.getElementById('username').value;
            const remember = document.getElementById('remember').checked;
            if (remember) {
                localStorage.setItem('rememberedUsername', username);
            } else {
                localStorage.removeItem('rememberedUsername');
            }
        });
    }

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
