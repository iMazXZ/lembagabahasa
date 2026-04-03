<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 | Layanan Tidak Tersedia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #081226;
            --bg-mid: #10294d;
            --panel: rgba(9, 20, 40, 0.78);
            --stroke: rgba(255, 255, 255, 0.14);
            --text-main: #f6f8ff;
            --text-soft: #b7c3de;
            --aqua: #21c7b3;
            --amber: #ffb349;
            --coral: #ff7350;
            --btn: #23b89f;
            --btn-hover: #1da08b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
            color: var(--text-main);
            background:
                radial-gradient(circle at 14% 18%, rgba(33, 199, 179, 0.22), transparent 40%),
                radial-gradient(circle at 88% 20%, rgba(255, 115, 80, 0.24), transparent 34%),
                radial-gradient(circle at 70% 86%, rgba(255, 179, 73, 0.2), transparent 38%),
                linear-gradient(135deg, var(--bg-deep), var(--bg-mid));
            font-family: "Plus Jakarta Sans", "Segoe UI", "Helvetica Neue", sans-serif;
            overflow-x: hidden;
        }

        .aurora {
            position: fixed;
            inset: -25vmax;
            pointer-events: none;
            background:
                radial-gradient(circle, rgba(33, 199, 179, 0.18), transparent 62%),
                radial-gradient(circle, rgba(255, 179, 73, 0.16), transparent 58%);
            filter: blur(36px);
            animation: drift 14s ease-in-out infinite alternate;
            opacity: 0.7;
        }

        .wrapper {
            width: min(860px, 100%);
            position: relative;
            z-index: 2;
        }

        .panel {
            position: relative;
            border-radius: 30px;
            border: 1px solid var(--stroke);
            background: linear-gradient(150deg, rgba(14, 30, 58, 0.95), var(--panel));
            backdrop-filter: blur(14px);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.09);
            padding: 34px 34px 28px;
            overflow: hidden;
        }

        .panel::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -98px;
            top: -98px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(33, 199, 179, 0.18), transparent 64%);
        }

        .panel::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            left: -110px;
            bottom: -130px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 115, 80, 0.18), transparent 62%);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            position: relative;
            z-index: 2;
            margin-bottom: 24px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(33, 199, 179, 0.16);
            color: #8ff1e4;
            border: 1px solid rgba(33, 199, 179, 0.36);
        }

        .chip-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #91f3e8;
            box-shadow: 0 0 0 5px rgba(145, 243, 232, 0.22);
            animation: pulse 1.9s ease-out infinite;
        }

        .status {
            font-size: 13px;
            color: var(--text-soft);
            text-align: right;
            line-height: 1.45;
        }

        .headline {
            position: relative;
            z-index: 2;
            margin-bottom: 18px;
        }

        .code {
            font-family: "Sora", "Segoe UI", "Helvetica Neue", sans-serif;
            font-size: clamp(78px, 16vw, 148px);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 0.95;
            background: linear-gradient(130deg, #ffffff 0%, #d9e6ff 35%, #8fe9dc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 8px 40px rgba(33, 199, 179, 0.28);
        }

        h1 {
            font-family: "Sora", "Segoe UI", "Helvetica Neue", sans-serif;
            margin-top: 6px;
            font-size: clamp(24px, 4.1vw, 36px);
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .desc {
            position: relative;
            z-index: 2;
            color: var(--text-soft);
            font-size: 15px;
            line-height: 1.7;
            max-width: 58ch;
        }

        .meta {
            position: relative;
            z-index: 2;
            margin-top: 22px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .meta-card {
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            padding: 12px 14px;
        }

        .meta-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.64);
            margin-bottom: 4px;
            font-weight: 700;
        }

        .meta-value {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 700;
        }

        .actions {
            position: relative;
            z-index: 2;
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary {
            color: #04141f;
            background: linear-gradient(120deg, var(--aqua), #7ff0e1);
            box-shadow: 0 10px 24px rgba(33, 199, 179, 0.28);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(33, 199, 179, 0.35);
        }

        .btn-secondary {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.13);
        }

        .footer {
            position: relative;
            z-index: 2;
            margin-top: 26px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
        }

        .brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            padding: 4px;
        }

        .brand-name {
            line-height: 1.18;
            font-size: 14px;
            font-weight: 800;
            color: #eef3ff;
        }

        .brand-name span {
            color: var(--amber);
        }

        .brand-sub {
            margin-top: 1px;
            font-size: 11px;
            color: #8fa4cb;
            font-weight: 600;
        }

        .helper {
            font-size: 12px;
            color: #9db0d2;
            text-align: right;
        }

        @keyframes drift {
            from {
                transform: translate3d(-20px, -10px, 0) scale(1);
            }
            to {
                transform: translate3d(22px, 14px, 0) scale(1.06);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.32); opacity: 0.2; }
        }

        @media (max-width: 720px) {
            body {
                padding: 18px;
            }

            .panel {
                border-radius: 22px;
                padding: 24px 18px 20px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 16px;
            }

            .status {
                text-align: left;
            }

            .meta {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
                width: 100%;
            }

            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .helper {
                text-align: left;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .aurora,
            .chip-dot {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <div class="aurora" aria-hidden="true"></div>

    <main class="wrapper">
        <section class="panel" role="alert" aria-live="polite">
            <div class="topbar">
                <div class="chip">
                    <span class="chip-dot"></span>
                    Maintenance Mode
                </div>
                <p class="status">
                    Status server: tidak tersedia sementara<br>
                    Kode error: HTTP 503
                </p>
            </div>

            <div class="headline">
                <div class="code">503</div>
                <h1>Layanan Sedang Kami Perbarui</h1>
            </div>

            <p class="desc">
                Kami sedang melakukan peningkatan sistem agar layanan lebih stabil.
                Silakan coba lagi beberapa saat lagi, data Anda tetap aman dan tidak hilang.
            </p>

            <div class="meta">
                <article class="meta-card">
                    <p class="meta-title">Perkiraan</p>
                    <p class="meta-value">Sementara, mohon tunggu</p>
                </article>
                <article class="meta-card">
                    <p class="meta-title">Dampak</p>
                    <p class="meta-value">Akses halaman dibatasi</p>
                </article>
            </div>

            <div class="actions">
                <button class="btn btn-primary" onclick="window.location.reload();">
                    Muat Ulang Halaman
                </button>
                <a class="btn btn-secondary" href="{{ url('/') }}">
                    Kembali ke Beranda
                </a>
            </div>

            <footer class="footer">
                <div class="brand">
                    <img src="{{ asset('images/logo-um.png') }}" alt="Logo UM Metro">
                    <div>
                        <p class="brand-name">Lembaga<span>Bahasa</span></p>
                        <p class="brand-sub">Universitas Muhammadiyah Metro</p>
                    </div>
                </div>
                <p class="helper">Terima kasih atas kesabaran Anda.</p>
            </footer>
        </section>
    </main>
</body>
</html>
