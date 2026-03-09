<!-- resources/views/secondLanding.blade.php -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberdayaan Wanita & Perlindungan Anak - Gabung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        }

        .hero-section {
            height: 60vh;
            background: linear-gradient(135deg, #ff7eb3, #ff65a3);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .hero-section h2 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .section-info {
            padding: 50px 20px;
            text-align: center;
        }

        .cta-button {
            padding: 15px 30px;
            background-color: #ff6f61;
            color: white;
            border: none;
            font-size: 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .cta-button:hover {
            background-color: #ff4c3b;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="hero-section">
    <div>
        <h2>Gabung Sekarang!</h2>
        <p>Langkah pertama untuk bergabung dalam pemberdayaan wanita dan perlindungan anak.</p>
        <a href="{{ route('login') }}">
            <button class="cta-button">Login</button>
        </a>
        <a href="{{ route('register') }}">
            <button class="cta-button mt-3">Daftar</button>
        </a>
    </div>
</div>

<div class="section-info">
    <h3>Pemberdayaan Wanita & Perlindungan Anak</h3>
    <p>Di sini, Anda akan menemukan berbagai informasi terkait dengan upaya pemberdayaan wanita dan perlindungan hak anak.</p>
    <p>Daftar untuk mendapatkan akses ke berbagai program dan kesempatan belajar untuk mendukung peran Anda dalam komunitas.</p>
</div>

</body>
</html>
