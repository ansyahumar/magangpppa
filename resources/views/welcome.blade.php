<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.fav')
    <title>Sistem Penilaian Mandiri - KemenPPPA</title>

    <!-- Fonts, Bootstrap 5, & Vendor Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #ff6f61;
            --brand-secondary: #4e54c8;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --card-bg: rgba(255, 255, 255, 0.75);
            --card-border: rgba(15, 23, 42, 0.08);
            --text-main: #0f172a;
            --text-muted: #475569;
            --badge-bg: rgba(15, 23, 42, 0.04);
            --bento-hover-bg: rgba(255, 255, 255, 0.9);
            --footer-border: rgba(15, 23, 42, 0.08);
        }

        body.dark {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #020617 100%);
            --card-bg: rgba(30, 41, 59, 0.45);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --badge-bg: rgba(255, 255, 255, 0.06);
            --bento-hover-bg: rgba(255, 255, 255, 0.08);
            --footer-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-x: hidden;
            position: relative;
            transition: background 0.4s ease, color 0.4s ease;
        }

        /* Animated Ambient Mesh Background Blobs */
        .ambient-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,111,97,0.12) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            z-index: -1;
            animation: floatGlow 10s ease-in-out infinite alternate;
        }
        .ambient-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(78,84,200,0.1) 0%, transparent 70%);
            bottom: -100px;
            left: -150px;
            z-index: -1;
            animation: floatGlow 12s ease-in-out infinite alternate-reverse;
        }
        body.dark .ambient-glow { background: radial-gradient(circle, rgba(255,111,97,0.18) 0%, transparent 70%); }
        body.dark .ambient-glow-2 { background: radial-gradient(circle, rgba(78,84,200,0.15) 0%, transparent 70%); }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.1); }
        }

        /* Top Bar / Header */
        .premium-header {
            padding: 30px 0;
            background: transparent;
        }

        /* Master Glass Card Container */
        .master-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--card-border);
            border-radius: 32px;
            padding: 60px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.03);
            transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }
        body.dark .master-card {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }

        /* System Pill Badge */
        .sys-badge {
            background: var(--badge-bg);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 8px 18px;
            border-radius: 50px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.4s ease, border-color 0.4s ease, color 0.4s ease;
        }

        /* Typography */
        .hero-title {
            color: var(--text-main);
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -1.5px;
            line-height: 1.2;
            transition: color 0.4s ease;
        }
        .hero-title span {
            background: linear-gradient(135deg, var(--brand-primary), #ff8a7f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            color: var(--text-muted);
            font-size: 1.15rem;
            line-height: 1.6;
            max-width: 620px;
            transition: color 0.4s ease;
        }

        /* Premium Floating Action Button */
        .btn-premium-login {
            background: linear-gradient(135deg, var(--brand-primary) 0%, #f45849 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1rem;
            padding: 18px 44px;
            border-radius: 100px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(255, 111, 97, 0.35);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .btn-premium-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s;
        }
        .btn-premium-login:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 35px rgba(255, 111, 97, 0.5);
        }
        .btn-premium-login:hover::before {
            left: 100%;
        }

        /* Bento Minimalistic Indicators */
        .bento-badge {
            background: var(--badge-bg);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            padding: 16px 28px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease, color 0.4s ease, background 0.4s ease;
        }
        .bento-badge i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        .bento-badge:hover {
            background: var(--bento-hover-bg);
            transform: translateY(-2px);
        }
        .bento-badge:hover i {
            transform: scale(1.2);
        }

        /* Border Divider Fix for Dark Mode */
        .bento-divider {
            border-top: 1px solid var(--footer-border);
            transition: border-color 0.4s ease;
        }

        /* Theme Toggle Button Customized */
        .theme-control-btn {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            width: 45px;
            height: 45px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .theme-control-btn:hover {
            transform: rotate(15deg);
            background: var(--text-main);
            color: var(--card-bg);
        }
        
        .header-brand-title {
            color: var(--text-main);
            transition: color 0.4s ease;
        }
        .header-brand-sub {
            color: var(--text-muted);
            transition: color 0.4s ease;
        }
    </style>
</head>
<body>

<!-- Ambient Graphics Background -->
<div class="ambient-glow"></div>
<div class="ambient-glow-2"></div>

<!-- Header / Top Bar Minimalis Elegan -->
<header class="premium-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3" data-aos="fade-down" data-aos-duration="800">
            <img src="{{ asset('images/logo.png') }}" alt="Logo KemenPPPA" height="40">
            <div class="d-flex flex-column text-start">
                <span class="fw-bold fs-6 lh-1 tracking-tight header-brand-title">KemenPPPA</span>
                <small class="fw-semibold header-brand-sub" style="font-size: 0.65rem; letter-spacing: 0.5px; opacity: 0.8;">REPUBLIK INDONESIA</small>
            </div>
        </div>
        
        <div data-aos="fade-down" data-aos-duration="800" data-aos-delay="100">
            <button class="btn theme-control-btn" onclick="toggleTheme()" id="themeBtn" aria-label="Ubah Tema">
                <i class="bi bi-moon-stars-fill fs-5"></i>
            </button>
        </div>
    </div>
</header>

<!-- Main Gate / Dashboard Center Content -->
<main class="my-auto py-4">
    <div class="container d-flex justify-content-center">
        <div class="master-card text-center w-100 max-w-4xl" data-aos="zoom-in-up" data-aos-duration="900" style="max-width: 860px;">
            
            <!-- System Tag Badge -->
            <div class="mb-4">
                <span class="sys-badge">
                    <span class="spinner-grow spinner-grow-sm text-danger" role="status" style="animation-duration: 1.5s;"></span>
                    Integrated Portal Gateway
                </span>
            </div>

            <!-- Main Heading Text -->
            <h1 class="hero-title mb-3">
                Sistem Informasi Penilaian Mandiri <br>
                & Evaluasi Kinerja <span>Sektoral</span>
            </h1>

            <!-- Descriptive Text -->
            <p class="hero-subtitle mx-auto mb-5">
                Satu pintu akses aman bagi internal kementerian untuk pengumpulan data instrumen evaluasi berkala, akuntabilitas capaian indikator, serta monitoring tim monev secara terpusat.
            </p>

            <!-- Main Premium Call-to-Action Button -->
            <div class="mb-5">
                <a href="{{ route('login') }}" class="btn-premium-login">
                    Masuk ke Sistem Aplikasi <i class="bi bi-chevron-right fs-6"></i>
                </a>
            </div>

            <!-- Bento-Inspired Feature Badges Area -->
            <div class="d-flex flex-wrap justify-content-center gap-3 bento-divider pt-4">
                <div class="bento-badge">
                    <i class="bi bi-cpu-fill text-primary"></i>
                    <span>Instrumen SPBE</span>
                </div>
                <div class="bento-badge">
                    <i class="bi bi-bar-chart-line-fill text-info"></i>
                    <span>Matriks PEMDI</span>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Footer Layout -->
<footer class="py-4">
    <div class="container text-center small" style="opacity: 0.6;">
        <p class="mb-0">&copy; 2026 Kementerian Pemberdayaan Perempuan dan Perlindungan Anak RI.</p>
    </div>
</footer>

<!-- Engineering Scripts Engine Core -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
    AOS.init({ once: true });
    const body = document.body;
    const themeBtn = document.querySelector('#themeBtn i');
    
    function toggleTheme() {
        body.classList.toggle('dark');
        const isDark = body.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        themeBtn.className = isDark ? 'bi bi-sun-fill text-warning fs-5' : 'bi bi-moon-stars-fill fs-5';
    }
    
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark');
        themeBtn.className = 'bi bi-sun-fill text-warning fs-5';
    }
</script>

</body>
</html>