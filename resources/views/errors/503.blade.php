<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Tidak Tersedia - Lembaga Bahasa UM Metro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            padding: 20px;
        }
        
        .container {
            text-align: center;
            max-width: 520px;
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 10px;
        }
        
        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        
        .icon-container i {
            font-size: 36px;
            color: white;
            animation: spin 3s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        
        p {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }
        
        .btn i {
            font-size: 16px;
        }
        
        .info {
            margin-top: 24px;
            padding: 16px 20px;
            background: #f8fafc;
            border-radius: 12px;
            font-size: 13px;
            color: #64748b;
        }
        
        .info i {
            color: #f59e0b;
            margin-right: 8px;
        }
        
        .logo-section {
            margin-top: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .logo-section img {
            height: 52px;
            width: 52px;
            object-fit: contain;
        }
        
        .logo-text {
            text-align: left;
        }
        
        .logo-text .brand {
            font-weight: 800;
            font-size: 22px;
            color: #1e293b;
        }
        
        .logo-text .brand span {
            color: #f59e0b;
        }
        
        .logo-text .sub {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <i class="fa-solid fa-gear"></i>
        </div>
        <div class="error-code">503</div>
        <h1>Layanan Tidak Tersedia</h1>
        <p>
            Maaf, kami sedang melakukan pembaruan sistem. 
            Silakan coba lagi dalam beberapa saat.
        </p>
        <button onclick="window.location.reload();" class="btn">
            <i class="fa-solid fa-rotate-right"></i>
            Coba Lagi
        </button>
        
        <div class="info">
            <i class="fa-solid fa-clock"></i>
            Terima kasih atas kesabaran Anda
        </div>
        
        <div class="logo-section">
            <img src="{{ asset('images/logo-um.png') }}" alt="Logo UM Metro">
            <div class="logo-text">
                <div class="brand">Lembaga<span>Bahasa</span></div>
                <div class="sub">Universitas Muhammadiyah Metro</div>
            </div>
        </div>
    </div>
</body>
</html>