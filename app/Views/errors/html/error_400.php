<!--
    Author: MUHAMMAD ALIF QADRI 2025
    Licensed to: PTA MAKASSAR DAN SELURUH JAJARANNYA
    Copyright (c) 2025
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>400 Bad Request</title>
    <meta name="color-scheme" content="dark light">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('theme-mode');
                if (
                    mode === 'dark' ||
                    (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) { }
        })();
    </script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            width: 100vw;
            background: #f6f6ff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            overflow: hidden;
            transition: background 0.3s;
        }
        .spotlight-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            background: radial-gradient(ellipse at center, rgba(124,58,237,0.10) 0%, rgba(183,170,255,0.10) 40%, #f6f6ff 100%);
            filter: blur(0.5px);
            transition: background 0.3s;
        }
        .wave-bg {
            position: absolute;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
            will-change: transform;
        }
        .wave1 {
            animation: waveMove1 18s linear infinite;
        }
        .wave2 {
            animation: waveMove2 24s linear infinite;
        }
        .wave3 {
            animation: waveMove3 32s linear infinite;
        }
        @keyframes waveMove1 {
            0% { transform: translateX(0) translateY(0) scaleX(1); }
            50% { transform: translateX(40px) translateY(-20px) scaleX(1.03); }
            100% { transform: translateX(0) translateY(0) scaleX(1); }
        }
        @keyframes waveMove2 {
            0% { transform: translateX(0) translateY(0) scaleX(1); }
            50% { transform: translateX(-60px) translateY(30px) scaleX(0.97); }
            100% { transform: translateX(0) translateY(0) scaleX(1); }
        }
        @keyframes waveMove3 {
            0% { transform: translateX(0) translateY(0) scaleX(1); }
            50% { transform: translateX(30px) translateY(-15px) scaleX(1.01); }
            100% { transform: translateX(0) translateY(0) scaleX(1); }
        }
        .center-400 {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
        }
        .big-400 {
            font-size: 7vw;
            min-font-size: 3rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: transparent;
            -webkit-text-stroke: 2.5px #7c3aed;
            text-stroke: 2.5px #7c3aed;
            opacity: 0.92;
            text-align: center;
            margin-bottom: 0.2em;
            line-height: 1;
            filter: drop-shadow(0 2px 32px #b7aaff33);
            user-select: none;
            transition: -webkit-text-stroke 0.3s, text-stroke 0.3s;
        }
        .notfound-text {
            font-size: 2.1rem;
            font-weight: 700;
            color: #232336;
            text-shadow: 0 2px 16px #b7aaff22;
            margin-bottom: 2.2rem;
            text-align: center;
            transition: color 0.3s;
        }
        .desc-400 {
            font-size: 1.05rem;
            color: #7c3aed;
            margin-bottom: 2.2rem;
            text-align: center;
            max-width: 100vw;
            transition: color 0.3s;
            white-space: nowrap;
            overflow-wrap: anywhere;
        }
        .btn-home {
            display: inline-block;
            padding: 0.85rem 2.5rem;
            background: linear-gradient(90deg, rgba(124,58,237,0.55) 60%, rgba(183,170,255,0.38) 100%);
            color: #fff;
            font-weight: 700;
            border: 1.5px solid rgba(124,58,237,0.22);
            border-radius: 2rem;
            font-size: 1.15rem;
            letter-spacing: 0.05em;
            box-shadow: 0 2px 24px 0 rgba(124,58,237,0.18), 0 1.5px 8px 0 rgba(183,170,255,0.13);
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 2.5rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s, color 0.3s, border 0.2s;
            outline: none;
            text-shadow: 0 1px 8px rgba(124,58,237,0.10);
        }
        .btn-home:hover {
            background: linear-gradient(90deg, rgba(183,170,255,0.68) 0%, rgba(124,58,237,0.48) 100%);
            color: #fff;
            box-shadow: 0 0 16px 2px #b7aaff99, 0 4px 24px #b7aaff33;
            border: 1.5px solid rgba(124,58,237,0.32);
            transform: translateY(-2px) scale(1.04);
        }
        .copyright-400 {
            position: fixed;
            left: 0; right: 0; bottom: 0;
            text-align: center;
            font-size: 0.92rem;
            color: #7c3aed;
            opacity: 0.65;
            padding: 0.7rem 0 0.5rem 0;
            letter-spacing: 0.01em;
            background: transparent;
            z-index: 20;
            transition: color 0.3s;
        }
        @media (max-width: 600px) {
            .big-400 {
                font-size: 16vw;
            }
            .notfound-text {
                font-size: 1.2rem;
            }
            .desc-400 {
                font-size: 0.98rem;
                padding: 0 0.5rem;
            }
        }
        html.dark-mode body {
            background: #11121a;
        }
        html.dark-mode .spotlight-bg {
            background: radial-gradient(ellipse at center, rgba(124,58,237,0.18) 0%, rgba(183,170,255,0.12) 40%, rgba(17,18,26,0.98) 100%);
        }
        html.dark-mode .big-400 {
            -webkit-text-stroke: 2.5px #b7aaff;
            text-stroke: 2.5px #b7aaff;
            filter: drop-shadow(0 2px 32px #7c3aed55);
        }
        html.dark-mode .notfound-text {
            color: #fff;
            text-shadow: 0 2px 16px #7c3aed33;
        }
        html.dark-mode .desc-400 {
            color: #b7aaff;
        }
        html.dark-mode .btn-home {
            color: #fff;
            background: linear-gradient(90deg, rgba(124,58,237,0.32) 60%, rgba(183,170,255,0.22) 100%);
            border: 1.5px solid rgba(183,170,255,0.18);
        }
        html.dark-mode .btn-home:hover {
            color: #fff;
            background: linear-gradient(90deg, rgba(183,170,255,0.68) 0%, rgba(124,58,237,0.48) 100%);
            border: 1.5px solid rgba(183,170,255,0.32);
            box-shadow: 0 0 16px 2px #b7aaff99, 0 4px 24px #b7aaff33;
        }
        html.dark-mode .copyright-400 {
            color: #b7aaff;
        }
    </style>
</head>
<body>
    <div class="spotlight-bg">
        <svg class="wave-bg wave1" width="100%" height="100%" viewBox="0 0 1440 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <linearGradient id="waveGrad1" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#b7aaff" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.13"/>
                </linearGradient>
            </defs>
            <path d="M0 900 Q 360 700 720 900 T 1440 900 V900 H0Z" fill="url(#waveGrad1)" opacity="0.22"/>
        </svg>
        <svg class="wave-bg wave2" width="100%" height="100%" viewBox="0 0 1440 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <linearGradient id="waveGrad2" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.10"/>
                    <stop offset="100%" stop-color="#b7aaff" stop-opacity="0.10"/>
                </linearGradient>
            </defs>
            <path d="M0 700 Q 480 500 960 800 T 1440 700 V900 H0Z" fill="url(#waveGrad2)" opacity="0.18"/>
        </svg>
        <svg class="wave-bg wave3" width="100%" height="100%" viewBox="0 0 1440 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <linearGradient id="waveGrad3" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#b7aaff" stop-opacity="0.09"/>
                    <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.09"/>
                </linearGradient>
            </defs>
            <path d="M0 500 Q 600 300 1200 600 T 1440 500 V900 H0Z" fill="url(#waveGrad3)" opacity="0.13"/>
        </svg>
    </div>
    <div class="center-400">
        <div class="big-400">400</div>
        <div class="notfound-text">Bad Request!</div>
        <div class="desc-400">
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                Permintaan tidak valid.
            <?php endif; ?>
        </div>
        <button class="btn-home" onclick="window.history.length > 1 ? window.history.back() : window.location.href='/'">Kembali</button>
    </div>
    <div class="copyright-400">&copy; Pengadilan Tinggi Agama Makassar 2025</div>
</body>
</html>
