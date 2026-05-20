<?php
// status.php - Status Pendaftaran DPRD Provinsi Lampung
include 'koneksi.php';

$data  = null;
$error = "";

// Ambil data berdasarkan ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = (int) $_GET['id'];

  $sql = "SELECT * FROM tamu WHERE id = $id LIMIT 1";
  $result = mysqli_query($conn, $sql);

  if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
  } else {
    $error = "Data tidak ditemukan.";
  }
} else {
  $error = "ID tidak valid.";
}

$status    = $data['status'] ?? 'pending';
$nama      = $data['full_name'] ?? '-';
$instansi  = $data['organization'] ?? '-';
$tujuan    = $data['destination'] ?? '-';
$keperluan = $data['purpose'] ?? '-';
$tanggal   = $data['tanggal_daftar'] ?? '';

$statusMap = [
  'approved' => [
    'title' => 'Disetujui',
    'desc'  => 'Pendaftaran Anda telah diverifikasi dan disetujui.',
    'class' => 'approved',
    'icon'  => '✓'
  ],
  'rejected' => [
    'title' => 'Ditolak',
    'desc'  => 'Mohon hubungi sekretariat untuk informasi lebih lanjut.',
    'class' => 'rejected',
    'icon'  => '✕'
  ],
  'pending' => [
    'title' => 'Menunggu Persetujuan',
    'desc'  => 'Permintaan Anda sedang diproses oleh admin.',
    'class' => 'pending',
    'icon'  => ''
  ]
];

$current = $statusMap[$status] ?? $statusMap['pending'];
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Status Pendaftaran - DPRD Provinsi Lampung</title>

  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --gold: #d4a63f;
      --gold2: #f5d37a;
      --dark: #111827;
      --dark2: #1f2937;
      --cream: #f9f6ef;
      --text: #1e293b;
      --muted: #64748b;
      --white: #ffffff;
      --border: rgba(212, 166, 63, .25);
    }

    html,
    body {
      min-height: 100%;
      font-family: 'Outfit', sans-serif;
      background:
        radial-gradient(circle at top right, rgba(212, 166, 63, .10), transparent 30%),
        radial-gradient(circle at bottom left, rgba(212, 166, 63, .08), transparent 25%),
        #f8f5ef;
      color: var(--text);
    }

    /* SCROLL */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-thumb {
      background: #d6b15d;
      border-radius: 20px;
    }

    /* TOP BAR */
    .tapis-top {
      width: 100%;
      height: 6px;
      background:
        repeating-linear-gradient(90deg,
          #8B6B0F 0px,
          #8B6B0F 12px,
          #D4A63F 12px,
          #D4A63F 22px,
          #F4D27A 22px,
          #F4D27A 32px);
    }

    /* PAGE */
    .page {
      width: 100%;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 18px 60px;
    }

    /* CARD */
    .card {
      width: 100%;
      max-width: 560px;
      background: rgba(255, 255, 255, .86);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, .45);
      border-radius: 34px;
      overflow: hidden;
      box-shadow:
        0 20px 60px rgba(0, 0, 0, .10),
        inset 0 1px 0 rgba(255, 255, 255, .4);
      animation: fadeUp .7s ease;
    }

    /* HEADER */
    .hero {
      position: relative;
      padding: 40px 28px 110px;
      overflow: hidden;

      background:
        linear-gradient(135deg,
          rgba(15, 23, 42, .94),
          rgba(30, 41, 59, .88),
          rgba(120, 75, 0, .72)),
        url('img/gedung.jpg');

      background-size: cover;
      background-position: center;
    }

    .hero::before {
      content: '';
      position: absolute;
      inset: 0;

      background-image:
        linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);

      background-size: 34px 34px;
    }

    .glow {
      position: absolute;
      top: -60px;
      right: -60px;
      width: 220px;
      height: 220px;
      background: rgba(255, 215, 0, .20);
      filter: blur(70px);
      border-radius: 50%;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: white;
    }

    .logo {
      width: 92px;
      height: 92px;
      background: rgba(255, 255, 255, .15);
      border: 1px solid rgba(255, 255, 255, .25);
      backdrop-filter: blur(8px);
      border-radius: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
    }

    .logo img {
      width: 62px;
      height: 62px;
      object-fit: contain;
    }

    .hero small {
      letter-spacing: 4px;
      text-transform: uppercase;
      font-size: 11px;
      opacity: .8;
    }

    .hero h1 {
      margin-top: 10px;
      font-family: 'Cormorant Garamond', serif;
      font-size: 52px;
      line-height: 1;
      font-weight: 700;
    }

    .hero h1 span {
      color: var(--gold2);
      font-style: italic;
    }

    .hero p {
      margin-top: 12px;
      font-size: 14px;
      opacity: .85;
      line-height: 1.7;
    }

    /* BODY */
    .body {
      position: relative;
      margin-top: -70px;
      padding: 0 24px 28px;
      z-index: 5;
    }

    /* PROFILE */
    .profile {
      background: white;
      border-radius: 28px;
      padding: 24px;
      box-shadow:
        0 14px 40px rgba(0, 0, 0, .08);
      border: 1px solid rgba(255, 255, 255, .7);
    }

    .user {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .avatar {
      width: 72px;
      height: 72px;
      border-radius: 24px;
      background: linear-gradient(135deg, #111827, #374151);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      font-weight: 700;
      flex-shrink: 0;
    }

    .user h2 {
      font-size: 24px;
      font-weight: 700;
      line-height: 1.2;
    }

    .user p {
      font-size: 14px;
      color: var(--muted);
      margin-top: 4px;
    }

    /* DETAIL */
    .detail-list {
      margin-top: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .detail-item {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      padding: 14px;
      border-radius: 18px;
      background: #faf8f4;
    }

    .detail-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
    }

    .detail-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #94a3b8;
      font-weight: 700;
    }

    .detail-value {
      margin-top: 4px;
      font-size: 14px;
      line-height: 1.7;
      color: #334155;
      font-weight: 500;
    }

    /* STATUS */
    .status-card {
      margin-top: 24px;
      border-radius: 30px;
      padding: 28px 24px;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: 0 14px 40px rgba(0, 0, 0, .08);
    }

    .status-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        linear-gradient(120deg,
          rgba(255, 255, 255, .16),
          transparent 50%);
    }

    .status-approved {
      background: linear-gradient(135deg, #ecfdf5, #d1fae5);
      color: #047857;
    }

    .status-rejected {
      background: linear-gradient(135deg, #fef2f2, #fee2e2);
      color: #b91c1c;
    }

    .status-pending {
      background: linear-gradient(135deg, #fff7ed, #ffedd5);
      color: #b45309;
    }

    /* ICON */
    .status-icon {
      width: 96px;
      height: 96px;
      margin: 0 auto 18px;
      border-radius: 50%;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
      position: relative;
    }

    /* PENDING ANIMATION */
    .spinner-wrap {
      position: relative;
      width: 56px;
      height: 56px;
    }

    .spinner {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 5px solid rgba(245, 158, 11, .18);
      border-top: 5px solid #f59e0b;
      animation: spin 1s linear infinite;
    }

    .spinner-dot {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 10px;
      height: 10px;
      background: #f59e0b;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      animation: pulse 1.3s ease infinite;
    }

    .status-title {
      font-size: 30px;
      font-weight: 800;
      margin-bottom: 10px;
    }

    .status-desc {
      max-width: 340px;
      margin: auto;
      line-height: 1.7;
      font-size: 14px;
    }

    /* BUTTONS */
    .actions {
      margin-top: 24px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .btn {
      width: 100%;
      border: none;
      outline: none;
      padding: 16px 20px;
      border-radius: 18px;
      text-decoration: none;
      font-weight: 700;
      font-size: 15px;
      transition: .25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #111827, #374151);
      color: white;
      box-shadow: 0 10px 24px rgba(0, 0, 0, .15);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: white;
      border: 1px solid #e5e7eb;
      color: #374151;
    }

    .btn-secondary:hover {
      border-color: #d4a63f;
      color: #b7791f;
    }

    /* FOOTER */
    .footer {
      margin-top: 28px;
      text-align: center;
      font-size: 12px;
      color: #94a3b8;
    }

    /* ERROR */
    .error-box {
      background: #fff1f2;
      border: 1px solid #fecdd3;
      color: #be123c;
      padding: 24px;
      border-radius: 24px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
    }

    .error-box h2 {
      margin-top: 10px;
      font-size: 24px;
    }

    /* ANIMATION */
    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes pulse {

      0%,
      100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
      }

      50% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: .5;
      }
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(25px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* RESPONSIVE */
    @media(max-width:640px) {

      .page {
        padding: 18px 14px 40px;
      }

      .hero {
        padding: 32px 20px 100px;
      }

      .hero h1 {
        font-size: 42px;
      }

      .body {
        padding: 0 16px 24px;
        margin-top: -60px;
      }

      .profile {
        padding: 20px;
        border-radius: 24px;
      }

      .user {
        flex-direction: column;
        text-align: center;
      }

      .avatar {
        width: 66px;
        height: 66px;
        font-size: 24px;
      }

      .user h2 {
        font-size: 22px;
      }

      .detail-item {
        padding: 12px;
      }

      .detail-icon {
        width: 42px;
        height: 42px;
        font-size: 18px;
      }

      .status-card {
        padding: 24px 18px;
        border-radius: 24px;
      }

      .status-title {
        font-size: 24px;
      }

      .status-desc {
        font-size: 13px;
      }

      .btn {
        font-size: 14px;
        padding: 15px;
      }

    }
  </style>
</head>

<body>

  <div class="tapis-top"></div>

  <div class="page">

    <div class="card">

      <div class="hero">

        <div class="glow"></div>

        <div class="hero-content">

          <div class="logo">
            <img src="img/logodrpd.jpg" alt="Logo DPRD">
          </div>

          <small>Sistem Informasi Digital</small>

          <h1><span>e</span>-Tamu</h1>

          <p>
            Monitoring status pendaftaran tamu DPRD Provinsi Lampung
            secara cepat, aman, dan profesional.
          </p>

        </div>
      </div>

      <div class="body">

        <?php if ($error): ?>

          <div class="error-box">
            <div style="font-size:50px;">⚠️</div>
            <h2>Terjadi Kesalahan</h2>
            <p style="margin-top:10px;">
              <?= htmlspecialchars($error) ?>
            </p>
          </div>

        <?php else: ?>

          <!-- PROFILE -->
          <div class="profile">

            <div class="user">

              <div class="avatar">
                <?= strtoupper(substr($nama, 0, 1)) ?>
              </div>

              <div>
                <h2><?= htmlspecialchars($nama) ?></h2>
                <p><?= htmlspecialchars($instansi) ?></p>
              </div>

            </div>

            <div class="detail-list">

              <div class="detail-item">
                <div class="detail-icon">🎯</div>

                <div>
                  <div class="detail-label">Tujuan</div>
                  <div class="detail-value">
                    <?= htmlspecialchars($tujuan) ?>
                  </div>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-icon">📝</div>

                <div>
                  <div class="detail-label">Keperluan</div>
                  <div class="detail-value">
                    <?= htmlspecialchars($keperluan) ?>
                  </div>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-icon">🕒</div>

                <div>
                  <div class="detail-label">Tanggal Pendaftaran</div>
                  <div class="detail-value">
                    <?= date('d F Y - H:i', strtotime($tanggal)) ?> WIB
                  </div>
                </div>
              </div>

            </div>

          </div>

          <!-- STATUS -->
          <div class="status-card status-<?= $current['class'] ?>">

            <div class="status-icon">

              <?php if ($status == 'pending'): ?>

                <div class="spinner-wrap">
                  <div class="spinner"></div>
                  <div class="spinner-dot"></div>
                </div>

              <?php else: ?>

                <div style="font-size:46px;font-weight:700;">
                  <?= $current['icon'] ?>
                </div>

              <?php endif; ?>

            </div>

            <div class="status-title">
              <?= $current['title'] ?>
            </div>

            <div class="status-desc">
              <?= $current['desc'] ?>
            </div>

          </div>

          <!-- BUTTON -->
          <div class="actions">

            <a href="status.php?id=<?= $id ?>" class="btn btn-primary">
              🔄 Refresh Status
            </a>

            <a href="index.php" class="btn btn-secondary">
              ← Kembali ke Beranda
            </a>

          </div>

        <?php endif; ?>

        <div class="footer">
          © 2026 DPRD Provinsi Lampung · Sistem Buku Tamu Digital
        </div>

      </div>

    </div>

  </div>

</body>

</html>