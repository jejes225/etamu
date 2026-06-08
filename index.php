<?php
// index.php - Halaman Utama e-Tamu DPRD Provinsi Lampung
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>e-Tamu DPRD Provinsi Lampung</title>

  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --gold: #c49a22;
      --gold-light: #e8c96a;
      --gold-dark: #8b6b0f;
      --cream: #f8f4eb;
      --white: #ffffff;
      --dark: #1f1608;
      --dark-soft: #4a3820;
      --text: #6f624f;
      --border: rgba(196, 154, 34, 0.25);
      --shadow: 0 10px 40px rgba(70, 45, 0, .12);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Outfit', sans-serif;
      background: var(--cream);
      color: var(--dark);
      overflow-x: hidden;
      min-height: 100vh;
      position: relative;
    }

    /* BACKGROUND */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(circle at top, rgba(196, 154, 34, .12), transparent 40%),
        repeating-linear-gradient(90deg,
          rgba(196, 154, 34, .03) 0px,
          rgba(196, 154, 34, .03) 1px,
          transparent 1px,
          transparent 60px),
        repeating-linear-gradient(0deg,
          rgba(196, 154, 34, .03) 0px,
          rgba(196, 154, 34, .03) 1px,
          transparent 1px,
          transparent 60px);
      z-index: -2;
    }

    /* TOP BAR */
    .top-bar {
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg,
          var(--gold-dark),
          var(--gold),
          var(--gold-light),
          var(--gold-dark));
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
    }

    .container {
      width: 100%;
      max-width: 1200px;
      margin: auto;
      padding: 40px 20px 100px;
    }

    /* HERO */
    .hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      align-items: center;
      min-height: 100vh;
      padding-top: 50px;
    }

    /* LEFT */
    .hero-left {
      animation: fadeUp 1s ease;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(196, 154, 34, .12);
      border: 1px solid var(--border);
      color: var(--gold-dark);
      padding: 10px 18px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 28px;
      backdrop-filter: blur(10px);
    }

    /* LOGO */
    .logo-box {
      margin-bottom: 30px;
    }

    .logo-box img {
      width: 120px;
      height: 120px;
      object-fit: contain;
      background: white;
      border-radius: 28px;
      padding: 14px;
      border: 1px solid var(--border);
      box-shadow: 0 10px 35px rgba(0, 0, 0, .12);
    }

    .title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 72px;
      line-height: 1;
      font-weight: 700;
      margin-bottom: 20px;
      color: var(--dark);
    }

    .title span {
      color: var(--gold);
      font-style: italic;
    }

    .subtitle {
      font-size: 17px;
      line-height: 1.8;
      color: var(--text);
      max-width: 580px;
      margin-bottom: 35px;
    }

    /* BUTTONS */
    .button-group {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 40px;
    }

    .btn {
      position: relative;
      overflow: hidden;
      text-decoration: none;
      border: none;
      cursor: pointer;
      border-radius: 18px;
      padding: 18px 28px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .05em;
      transition: .3s ease;
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 240px;
    }

    .btn-primary {
      background: linear-gradient(135deg,
          var(--dark),
          var(--gold-dark));
      color: #fff;
      box-shadow: 0 12px 30px rgba(139, 107, 15, .25);
    }

    .btn-primary:hover {
      transform: translateY(-4px);
      box-shadow: 0 18px 40px rgba(139, 107, 15, .35);
    }

    .btn-secondary {
      background: #fff;
      color: var(--dark-soft);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
    }

    .btn-secondary:hover {
      transform: translateY(-4px);
      background: var(--gold-light);
      color: var(--dark);
    }

    .btn-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .btn-primary .btn-icon {
      background: rgba(255, 255, 255, .12);
    }

    .btn-secondary .btn-icon {
      background: rgba(196, 154, 34, .12);
    }

    .btn-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }

    .btn-text strong {
      font-size: 14px;
      margin-bottom: 2px;
    }

    .btn-text span {
      font-size: 12px;
      opacity: .8;
      font-weight: 400;
      text-transform: none;
      letter-spacing: 0;
    }

    /* INFO */
    .info-card {
      background: rgba(255, 255, 255, .75);
      border: 1px solid var(--border);
      backdrop-filter: blur(14px);
      border-radius: 24px;
      padding: 24px;
      display: flex;
      gap: 18px;
      align-items: flex-start;
      max-width: 580px;
      box-shadow: var(--shadow);
    }

    .info-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      background: rgba(196, 154, 34, .12);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .info-card p {
      font-size: 14px;
      line-height: 1.8;
      color: var(--text);
    }

    .info-card strong {
      color: var(--dark);
    }

    /* RIGHT */
    .hero-right {
      position: relative;
      animation: fadeUp 1s ease .2s both;
    }

    .image-wrap {
      position: relative;
      border-radius: 32px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
      border: 1px solid rgba(255, 255, 255, .4);
    }

    .image-wrap img {
      width: 100%;
      height: 720px;
      object-fit: cover;
      display: block;
    }

    .overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top,
          rgba(0, 0, 0, .72),
          rgba(0, 0, 0, .12));
    }

    .overlay-content {
      position: absolute;
      bottom: 40px;
      left: 40px;
      right: 40px;
      color: #fff;
    }

    .overlay-content h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 42px;
      margin-bottom: 14px;
    }

    .overlay-content p {
      font-size: 15px;
      line-height: 1.8;
      color: rgba(255, 255, 255, .85);
    }

    /* FLOAT CARD */
    .floating-card {
      position: absolute;
      top: 40px;
      left: -40px;
      background: rgba(255, 255, 255, .95);
      backdrop-filter: blur(12px);
      border: 1px solid var(--border);
      border-radius: 22px;
      padding: 22px;
      width: 250px;
      box-shadow: var(--shadow);
    }

    .floating-card h3 {
      font-size: 15px;
      margin-bottom: 8px;
      color: var(--dark);
    }

    .floating-card p {
      font-size: 13px;
      line-height: 1.7;
      color: var(--text);
    }

    /* FOOTER */
    footer {
      margin-top: 70px;
      text-align: center;
      color: var(--text);
      font-size: 13px;
      opacity: .8;
    }

    footer .line {
      width: 100px;
      height: 1px;
      background: var(--border);
      margin: 0 auto 20px;
    }

    /* ANIMATION */
    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* RESPONSIVE */
    @media(max-width:980px) {

      .hero {
        grid-template-columns: 1fr;
        min-height: auto;
        gap: 50px;
      }

      .hero-right {
        order: -1;
      }

      .title {
        font-size: 56px;
      }

      .image-wrap img {
        height: 500px;
      }

      .floating-card {
        left: 20px;
        top: 20px;
      }
    }

    @media(max-width:640px) {

      .container {
        padding: 30px 18px 80px;
      }

      .title {
        font-size: 44px;
      }

      .subtitle {
        font-size: 15px;
      }

      .button-group {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        min-width: 100%;
      }

      .image-wrap img {
        height: 420px;
      }

      .overlay-content {
        left: 24px;
        right: 24px;
        bottom: 24px;
      }

      .overlay-content h2 {
        font-size: 30px;
      }

      .floating-card {
        position: relative;
        width: 100%;
        left: 0;
        top: 0;
        margin-top: 18px;
      }

      .info-card {
        flex-direction: column;
      }

      .logo-box img {
        width: 100px;
        height: 100px;
      }
    }
  </style>
</head>

<body>

  <div class="top-bar"></div>

  <div class="container">

    <section class="hero">

      <!-- LEFT -->
      <div class="hero-left">

        <div class="badge">
          ✦ Sistem Informasi Buku Tamu Digital
        </div>

        <!-- LOGO DPRD -->
        <div class="logo-box">
          <img src="img/logodrpd.jpg" alt="Logo DPRD Lampung">
        </div>

        <h1 class="title">
          <span>e</span>-Tamu<br>
          DPRD Provinsi Lampung
        </h1>

        <p class="subtitle">
          Platform layanan tamu digital resmi Sekretariat DPRD Provinsi Lampung
          untuk mempermudah proses registrasi kunjungan, pengecekan status,
          serta pengelolaan administrasi tamu secara modern, cepat, aman,
          dan profesional.
        </p>

        <!-- BUTTON -->
        <div class="button-group">

          <!-- DAFTAR TAMU -->
          <a href="formulir.php" class="btn btn-primary">

            <div class="btn-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
            </div>

            <div class="btn-text">
              <strong>Daftar Tamu</strong>
              <span>Isi formulir kunjungan DPRD</span>
            </div>

          </a>

          <!-- CEK STATUS -->
          <a href="cek_status.php" class="btn btn-secondary">

            <div class="btn-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 13l4 4L19 7" />
              </svg>
            </div>

            <div class="btn-text">
              <strong>Cek Status</strong>
              <span>Lihat status pendaftaran</span>
            </div>

          </a>

          <!-- LOGIN ADMIN -->
          <a href="admin.php" class="btn btn-secondary">

            <div class="btn-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1v10" />
                <path d="M8 5l4-4 4 4" />
                <rect x="4" y="11" width="16" height="10" rx="2" />
              </svg>
            </div>

            <div class="btn-text">
              <strong>Login Admin</strong>
              <span>Masuk ke dashboard admin</span>
            </div>

          </a>

        </div>

        <!-- INFO -->
        <div class="info-card">

          <div class="info-icon">
            <svg width="22" height="22" fill="none" stroke="var(--gold-dark)" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M12 8v4" />
              <circle cx="12" cy="16" r="1" />
            </svg>
          </div>

          <p>
            <strong>Jam Operasional</strong><br>
            Senin – Jumat • 08.00 – 16.00 WIB<br>
            Jl. Wolter Monginsidi No. 69, Teluk Betung Selatan,
            Bandar Lampung.
          </p>

        </div>

      </div>

      <!-- RIGHT -->
      <div class="hero-right">

        <div class="image-wrap">

          <img src="img/gedung.jpg" alt="Gedung DPRD Lampung">

          <div class="overlay"></div>

          <div class="overlay-content">
            <h2>Sai Bumi Ruwa Jurai</h2>
            <p>
              Bersama membangun pelayanan publik DPRD Provinsi Lampung
              yang modern, elegan, dan terintegrasi digital.
            </p>
          </div>

        </div>

        <!-- FLOATING -->
        <div class="floating-card">
          <h3>Pelayanan Digital Modern</h3>
          <p>
            Sistem e-Tamu mempermudah proses administrasi kunjungan tamu
            secara cepat, transparan, dan efisien.
          </p>
        </div>

      </div>

    </section>

    <!-- FOOTER -->
    <footer>
      <div class="line"></div>
      <p>
        © 2026 Sekretariat DPRD Provinsi Lampung · Seluruh Hak Cipta Dilindungi
      </p>
    </footer>

  </div>

</body>

</html>