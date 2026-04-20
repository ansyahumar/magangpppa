<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   @include('layouts.fav')
   <title>KemenPPPA - Pemberdayaan & Perlindungan</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #ff6f61;
            --secondary-color: #4e54c8;
            --dark-bg: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.8);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }

        body.dark {
            background-color: var(--dark-bg);
            color: #f1f5f9;
        }

       
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding: 1rem 0;
            transition: 0.3s;
        }

        body.dark .navbar {
            background-color: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .navbar-brand img {
            transition: transform 0.3s ease;
        }
        .navbar-brand:hover img {
            transform: rotate(-5deg) scale(1.1);
        }

        .nav-link {
            font-weight: 600;
            color: #1e293b !important;
            margin: 0 10px;
            position: relative;
        }

        body.dark .nav-link { color: #f1f5f9 !important; }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: 0.3s;
        }

        .nav-link:hover::after { width: 100%; }

        
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), 
                       url('{{ asset('images/hero-bg.png') }}') center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding-top: 80px;
        }

        .hero-section h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.1;
        }

       
        .service-card {
            background: #ffffff;
            border: none;
            border-radius: 24px;
            padding: 40px 30px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        body.dark .service-card {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
        }

        body.dark .service-card:hover {
            background: linear-gradient(145deg, #1e293b, #334155);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: rgba(255, 111, 97, 0.1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: var(--primary-color);
        }

      
        .cta-button {
            background: var(--primary-color);
            border: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.4s;
            box-shadow: 0 10px 20px rgba(255, 111, 97, 0.3);
        }

        .cta-button:hover {
            transform: scale(1.05) translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 111, 97, 0.4);
            color: white;
        }

     
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: var(--primary-color); border-radius: 5px; }

        footer {
            background: #0f172a;
            padding: 60px 0 30px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="#">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" height="45">
            <span class="fw-bold fs-4 tracking-tight">KemenPPPA</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <div class="navbar-nav align-items-center">
                <a class="nav-link px-3" href="#about">Tentang</a>
                <a class="nav-link px-3" href="#services">Layanan</a>
                <a class="nav-link px-3" href="#contact">Kontak</a>
                
                <button class="ms-lg-3 theme-toggle btn btn-outline-secondary rounded-circle" onclick="toggleTheme()" id="themeIcon">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container" data-aos="zoom-out" data-aos-duration="1200">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <span class="badge rounded-pill bg-warning text-dark mb-3 px-3 py-2 fw-bold">#IndonesiaEmas2045</span>
                <h1>Perempuan Berdaya Anak Terlindungi <span style="color: var(--primary-color);">Indonesia Maju</span></h1>
                <p></p>
                <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                    <button class="btn cta-button text-white" onclick="window.location.href='{{ route('login') }}'">
                        Mulai Sekarang <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="py-5 my-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&q=80" class="img-fluid rounded-5 shadow-lg" alt="About Us">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h6 class="text-uppercase text-primary fw-bold mb-3">Tentang Kami</h6>
                <h2 class="display-5 fw-bold mb-4">Misi Kami Melindungi & Memberdayakan</h2>
                <p class="text-muted fs-5">Kami adalah garda terdepan dalam memastikan setiap perempuan memiliki kesempatan yang sama dan setiap anak mendapatkan hak perlindungan yang layak di seluruh pelosok negeri.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-3 fs-4"></i> Advokasi Kebijakan Publik</li>
                    <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-3 fs-4"></i> Pendampingan Hukum & Sosial</li>
                    <li class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-3 fs-4"></i> Edukasi Berkelanjutan</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section id="services" class="py-5" style="background: rgba(0,0,0,0.02);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-6 fw-bold">Layanan Prioritas</h2>
            <div class="mx-auto mt-3" style="width: 60px; height: 4px; background: var(--primary-color); border-radius: 2px;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="service-card text-center">
                    <div class="icon-box">
                        <i class="bi bi-book"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Pendidikan Wanita</h4>
                    <p class="text-muted">Program literasi digital dan finansial untuk kemandirian ekonomi perempuan Indonesia.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="service-card text-center border-primary" style="border-width: 2px !important;">
                    <div class="icon-box" style="background: var(--primary-color); color: white;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Perlindungan Anak</h4>
                    <p class="text-muted">Layanan pengaduan 24 jam dan rumah aman bagi anak korban kekerasan dan eksploitasi.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="service-card text-center">
                    <div class="icon-box">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Kesetaraan Gender</h4>
                    <p class="text-muted">Mendorong keterwakilan perempuan di sektor strategis dan pengambilan keputusan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer id="contact">
    <div class="container text-center">
        <div class="mb-4">
            <a href="#" class="text-white fs-2 mx-3"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-white fs-2 mx-3"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-white fs-2 mx-3"><i class="bi bi-twitter-x"></i></a>
        </div>
        <p class="mb-0">&copy; 2026 Kementerian Pemberdayaan Perempuan & Perlindungan Anak.</p>
        <small class="opacity-50">Dibuat dengan dedikasi untuk Indonesia.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
    AOS.init({ 
        duration: 1000, 
        once: true,
        offset: 100
    });

    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const savedTheme = localStorage.getItem('theme');

    function setIcon() {
        themeIcon.innerHTML = body.classList.contains('dark')
            ? '<i class="bi bi-sun-fill text-warning"></i>'
            : '<i class="bi bi-moon-stars-fill"></i>';
    }

    if (savedTheme === 'dark') body.classList.add('dark');
    setIcon();

    function toggleTheme() {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
        setIcon();
    }

    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            nav.classList.add('py-2', 'shadow');
        } else {
            nav.classList.remove('py-2', 'shadow');
        }
    });
</script>

</body>
</html>