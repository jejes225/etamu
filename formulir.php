<?php
// formulir.php - Form Pendaftaran Tamu DPRD Provinsi Lampung

include 'koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $nama      = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
  $instansi  = trim(mysqli_real_escape_string($conn, $_POST['organization'] ?? ''));
  $keperluan = trim(mysqli_real_escape_string($conn, $_POST['purpose'] ?? ''));
  $tujuan    = trim(mysqli_real_escape_string($conn, $_POST['destination'] ?? ''));
  $no_hp     = trim(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));

  // VALIDASI WAJIB ISI
  if (
    empty($nama) ||
    empty($instansi) ||
    empty($keperluan) ||
    empty($tujuan) ||
    empty($no_hp)
  ) {

    $error = "Semua data wajib diisi!";
  } else {

    $foto_identitas = null;

    // VALIDASI FOTO
    if (empty($_FILES['foto_identitas']['name'])) {

      $error = "Foto identitas wajib diunggah!";
    } else {

      $file      = $_FILES['foto_identitas'];
      $ekstensi  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $allowed   = ['jpg', 'jpeg', 'png', 'pdf'];
      $maks_size = 2 * 1024 * 1024;

      if (!in_array($ekstensi, $allowed)) {

        $error = "Format file harus JPG, PNG, atau PDF.";
      } elseif ($file['size'] > $maks_size) {

        $error = "Ukuran file maksimal 2MB.";
      } else {

        $folder = 'uploads/identitas/';

        if (!is_dir($folder)) {
          mkdir($folder, 0755, true);
        }

        $nama_file = time() . '_' . uniqid() . '.' . $ekstensi;
        $tujuan_upload = $folder . $nama_file;

        if (move_uploaded_file($file['tmp_name'], $tujuan_upload)) {

          $foto_identitas = $nama_file;
        } else {

          $error = "Gagal upload file.";
        }
      }
    }

    // INSERT DATABASE
    if (empty($error)) {

      $foto_db = mysqli_real_escape_string($conn, $foto_identitas);

      $sql = "INSERT INTO tamu (
                        full_name,
                        organization,
                        purpose,
                        destination,
                        phone,
                        foto_identitas,
                        status,
                        tanggal_daftar
                    ) VALUES (
                        '$nama',
                        '$instansi',
                        '$keperluan',
                        '$tujuan',
                        '$no_hp',
                        '$foto_db',
                        'pending',
                        NOW()
                    )";

      if (mysqli_query($conn, $sql)) {

        $id_tamu = mysqli_insert_id($conn);

        header("Location: status.php?id=$id_tamu");
        exit();
      } else {

        $error = "Database error: " . mysqli_error($conn);
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

  <meta charset="UTF-8">

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

  <title>
    Formulir Tamu DPRD Lampung
  </title>

  <!-- FONT -->
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">

  <!-- ICON -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --gold: #C49A22;
      --gold-dark: #8B6B0F;
      --gold-light: #E8C96A;
      --gold-soft: #FFF7E5;

      --cream: #F9F5ED;
      --white: #ffffff;

      --text: #1A1208;
      --text-soft: #6f634d;

      --border: rgba(196, 154, 34, .25);

      --shadow:
        0 10px 35px rgba(0, 0, 0, .08);
    }

    html,
    body {
      width: 100%;
      overflow-x: hidden;
    }

    body {
      background: var(--cream);
      font-family: 'Outfit', sans-serif;
      color: var(--text);
      min-height: 100vh;
      position: relative;
      padding: 20px;
    }

    body::before {

      content: '';

      position: fixed;
      inset: 0;

      background-image:
        radial-gradient(circle at top, rgba(196, 154, 34, .08), transparent 40%),
        linear-gradient(rgba(196, 154, 34, .03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(196, 154, 34, .03) 1px, transparent 1px);

      background-size:
        100% 100%,
        45px 45px,
        45px 45px;

      z-index: -1;
    }

    .container {
      width: 100%;
      max-width: 920px;
      margin: auto;
    }

    .back-btn {

      display: inline-flex;
      align-items: center;
      gap: 8px;

      text-decoration: none;

      color: var(--gold-dark);

      font-size: 14px;
      font-weight: 600;

      margin-bottom: 20px;
    }

    .back-btn:hover {
      opacity: .8;
    }

    .card {

      background: var(--white);

      border: 1px solid var(--border);

      border-radius: 30px;

      overflow: hidden;

      box-shadow: var(--shadow);
    }

    /* HERO */

    .hero {

      position: relative;

      padding: 45px;

      background:
        linear-gradient(rgba(20, 12, 0, .82),
          rgba(20, 12, 0, .88)),
        url('img/gedung.jpg') center/cover no-repeat;
    }

    .hero::after {

      content: '';

      position: absolute;
      inset: 0;

      background:
        linear-gradient(135deg,
          rgba(255, 255, 255, .06),
          transparent);

      pointer-events: none;
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .logo-wrap {

      display: flex;
      align-items: center;
      gap: 18px;

      margin-bottom: 24px;
    }

    .logo-box {

      width: 74px;
      height: 74px;

      border-radius: 50%;

      background: rgba(255, 255, 255, .12);

      border: 1px solid rgba(255, 255, 255, .18);

      display: flex;
      align-items: center;
      justify-content: center;

      backdrop-filter: blur(8px);
    }

    .logo-box img {

      width: 48px;
      height: 48px;

      object-fit: contain;
    }

    .hero h1 {

      color: #fff;

      font-size: 34px;

      line-height: 1.1;

      font-family: 'Cormorant Garamond', serif;

      margin-bottom: 10px;
    }

    .hero p {

      color: rgba(255, 255, 255, .72);

      font-size: 14px;

      line-height: 1.7;

      max-width: 560px;
    }

    .clock-box {

      margin-top: 24px;

      display: inline-flex;
      align-items: center;
      gap: 12px;

      padding: 14px 18px;

      border-radius: 14px;

      background: rgba(255, 255, 255, .1);

      border: 1px solid rgba(255, 255, 255, .15);

      backdrop-filter: blur(8px);
    }

    .clock-time {

      color: #fff;

      font-size: 26px;

      font-weight: 700;

      letter-spacing: 2px;
    }

    .clock-date {

      color: rgba(255, 255, 255, .72);

      font-size: 12px;
    }

    .tapis {

      height: 5px;

      background:
        repeating-linear-gradient(90deg,
          var(--gold-dark) 0 10px,
          var(--gold) 10px 20px,
          transparent 20px 28px);
    }

    /* BODY */

    .form-body {
      padding: 42px;
    }

    .section-title {

      font-size: 12px;

      font-weight: 700;

      color: var(--gold-dark);

      letter-spacing: .2em;

      text-transform: uppercase;

      margin-bottom: 28px;

      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title::before {

      content: '';

      width: 8px;
      height: 8px;

      border-radius: 50%;

      background: var(--gold);
    }

    .error-box {

      background: #fff2f2;

      border: 1px solid #ffc7c7;

      color: #c0392b;

      border-radius: 14px;

      padding: 15px 18px;

      margin-bottom: 24px;

      font-size: 14px;

      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .form-grid {

      display: grid;

      grid-template-columns: 1fr 1fr;

      gap: 20px;
    }

    .full {
      grid-column: 1/-1;
    }

    .field {
      display: flex;
      flex-direction: column;
    }

    .field label {

      font-size: 12px;

      font-weight: 700;

      letter-spacing: .08em;

      text-transform: uppercase;

      color: #6e6047;

      margin-bottom: 9px;
    }

    .required {
      color: #d63031;
    }

    .input,
    .textarea {

      width: 100%;

      border: 1px solid #e6ddcf;

      background: #fcfbf8;

      border-radius: 14px;

      padding: 15px 16px;

      font-size: 14px;

      font-family: 'Outfit', sans-serif;

      transition: .2s;
    }

    .input:focus,
    .textarea:focus {

      outline: none;

      border-color: var(--gold);

      background: #fff;

      box-shadow:
        0 0 0 4px rgba(196, 154, 34, .12);
    }

    .textarea {

      resize: none;

      min-height: 120px;

      line-height: 1.7;
    }

    .upload {

      border: 2px dashed #d9ccb7;

      border-radius: 18px;

      background: #fcfbf8;

      padding: 35px 20px;

      text-align: center;

      cursor: pointer;

      transition: .2s;
    }

    .upload:hover {

      border-color: var(--gold);

      background: var(--gold-soft);
    }

    .upload i {

      font-size: 48px;

      color: var(--gold-dark);

      margin-bottom: 12px;
    }

    .upload-title {
      font-weight: 600;
      margin-bottom: 6px;
    }

    .upload-sub {

      font-size: 13px;

      color: #8a7d65;
    }

    .preview {

      display: none;

      align-items: center;

      gap: 14px;

      background: #fffaf0;

      border: 1px solid #ecd59d;

      padding: 14px;

      border-radius: 14px;
    }

    .preview.show {
      display: flex;
    }

    .preview-icon {

      width: 52px;
      height: 52px;

      border-radius: 12px;

      background: #f5e2ae;

      display: flex;
      align-items: center;
      justify-content: center;

      color: #8B6B0F;

      font-size: 24px;
    }

    .submit-btn {

      width: 100%;

      border: none;

      border-radius: 16px;

      padding: 16px;

      margin-top: 10px;

      background:
        linear-gradient(135deg,
          #5e4309,
          var(--gold-dark),
          var(--gold));

      color: #fff;

      font-size: 15px;
      font-weight: 600;

      cursor: pointer;

      transition: .2s;

      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;

      box-shadow:
        0 10px 24px rgba(139, 107, 15, .25);
    }

    .submit-btn:hover {

      transform: translateY(-2px);

      opacity: .95;
    }

    .privacy {

      margin-top: 16px;

      text-align: center;

      font-size: 12px;

      color: #8f8169;
    }

    footer {

      margin-top: 22px;

      text-align: center;

      font-size: 12px;

      color: #8f8169;
    }

    /* RESPONSIVE */

    @media(max-width:768px) {

      body {
        padding: 14px;
      }

      .hero {
        padding: 28px 22px;
      }

      .hero h1 {
        font-size: 28px;
      }

      .hero p {
        font-size: 13px;
      }

      .form-body {
        padding: 28px 22px;
      }

      .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }

      .clock-box {
        width: 100%;
        justify-content: flex-start;
      }

      .clock-time {
        font-size: 22px;
      }
    }

    @media(max-width:480px) {

      .hero {
        padding: 24px 18px;
      }

      .form-body {
        padding: 24px 18px;
      }

      .hero h1 {
        font-size: 24px;
      }

      .clock-time {
        font-size: 20px;
      }

      .upload {
        padding: 28px 16px;
      }

      .input,
      .textarea {
        font-size: 13px;
        padding: 14px;
      }

      .submit-btn {
        font-size: 14px;
      }
    }
  </style>

</head>

<body>

  <div class="container">

    <a href="index.php" class="back-btn">
      <i class="ti ti-arrow-left"></i>
      Kembali ke Beranda
    </a>

    <div class="card">

      <!-- HERO -->
      <div class="hero">

        <div class="hero-content">

          <div class="logo-wrap">

            <div class="logo-box">
              <img src="img/logodrpd.jpg" alt="">
            </div>

            <div>

              <div
                style="
                                color:#fff;
                                font-size:14px;
                                font-weight:600;
                                letter-spacing:.12em;
                                text-transform:uppercase;
                            ">
                DPRD Provinsi Lampung
              </div>

              <div
                style="
                                color:rgba(255,255,255,.6);
                                font-size:12px;
                                margin-top:4px;
                            ">
                Sistem Buku Tamu Digital
              </div>

            </div>

          </div>

          <h1>
            Formulir<br>
            Pendaftaran Tamu
          </h1>

          <p>
            Silakan lengkapi seluruh data kunjungan Anda untuk proses verifikasi administrasi tamu DPRD Provinsi Lampung.
          </p>

          <div class="clock-box">

            <i
              class="ti ti-clock"
              style="font-size:22px;color:#fff;"></i>

            <div>

              <div
                class="clock-time"
                id="clock">
                --:--:--
              </div>

              <div
                class="clock-date"
                id="date">
                Memuat tanggal...
              </div>

            </div>

          </div>

        </div>

      </div>

      <div class="tapis"></div>

      <!-- FORM -->
      <div class="form-body">

        <div class="section-title">
          Data Identitas Tamu
        </div>

        <?php if ($error): ?>

          <div class="error-box">

            <i
              class="ti ti-alert-circle"
              style="font-size:20px;"></i>

            <div>
              <?= htmlspecialchars($error) ?>
            </div>

          </div>

        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

          <div class="form-grid">

            <!-- NAMA -->
            <div class="field full">

              <label>
                Nama Lengkap
                <span class="required">*</span>
              </label>

              <input
                type="text"
                name="full_name"
                class="input"
                required
                minlength="3"
                autocomplete="off"
                placeholder="Masukkan nama lengkap"
                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">

            </div>

            <!-- INSTANSI -->
            <div class="field">

              <label>
                Instansi / Organisasi
                <span class="required">*</span>
              </label>

              <input
                type="text"
                name="organization"
                class="input"
                required
                minlength="3"
                autocomplete="off"
                placeholder="Nama instansi"
                value="<?= htmlspecialchars($_POST['organization'] ?? '') ?>">

            </div>

            <!-- HP -->
            <div class="field">

              <label>
                No. Telepon / HP
                <span class="required">*</span>
              </label>

              <input
                type="tel"
                name="phone"
                class="input"
                required
                pattern="[0-9]{10,15}"
                autocomplete="off"
                placeholder="08xxxxxxxxxx"
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

            </div>

            <!-- TUJUAN -->
            <div class="field full">

              <label>
                Tujuan yang Dituju
                <span class="required">*</span>
              </label>

              <input
                type="text"
                name="destination"
                class="input"
                required
                minlength="3"
                autocomplete="off"
                placeholder="Contoh: Ketua DPRD, Sekretaris Dewan"
                value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">

            </div>

            <!-- KEPERLUAN -->
            <div class="field full">

              <label>
                Keperluan Kunjungan
                <span class="required">*</span>
              </label>

              <textarea
                name="purpose"
                class="textarea"
                required
                minlength="3"
                placeholder="Jelaskan secara singkat keperluan kunjungan..."><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>

            </div>

            <!-- UPLOAD -->
            <div class="field full">

              <label>
                Upload Identitas
                <span class="required">*</span>
              </label>

              <div
                class="upload"
                id="uploadArea"
                onclick="document.getElementById('foto_identitas').click()">

                <div id="uploadPlaceholder">

                  <i class="ti ti-id-badge-2"></i>

                  <div class="upload-title">
                    Upload Foto Identitas
                  </div>

                  <div class="upload-sub">
                    JPG, PNG, PDF • Maksimal 2MB
                  </div>

                </div>

                <div
                  class="preview"
                  id="previewBox">

                  <div class="preview-icon">
                    <i class="ti ti-file-check"></i>
                  </div>

                  <div>

                    <div
                      id="previewName"
                      style="
                                            font-size:14px;
                                            font-weight:600;
                                        "></div>

                    <div
                      style="
                                            font-size:12px;
                                            color:#8B6B0F;
                                            margin-top:5px;
                                        ">
                      File berhasil dipilih
                    </div>

                  </div>

                </div>

              </div>

              <input
                type="file"
                name="foto_identitas"
                id="foto_identitas"
                hidden
                required
                accept=".jpg,.jpeg,.png,.pdf">

            </div>

          </div>

          <button type="submit" class="submit-btn">

            <i class="ti ti-send"></i>

            Kirim Pendaftaran

          </button>

          <div class="privacy">
            Data Anda aman dan hanya digunakan untuk administrasi kunjungan DPRD Provinsi Lampung.
          </div>

        </form>

      </div>

    </div>

    <footer>
      © 2026 Sekretariat DPRD Provinsi Lampung · Seluruh hak cipta dilindungi
    </footer>

  </div>

  <script>
    // PREVIEW FILE

    const inputFile =
      document.getElementById('foto_identitas');

    inputFile.addEventListener('change', function() {

      if (this.files.length > 0) {

        document.getElementById('uploadPlaceholder').style.display =
          'none';

        document.getElementById('previewBox').classList.add('show');

        document.getElementById('previewName').innerText =
          this.files[0].name;
      }
    });

    // CLOCK

    const hari = [
      'Minggu',
      'Senin',
      'Selasa',
      'Rabu',
      'Kamis',
      'Jumat',
      'Sabtu'
    ];

    const bulan = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
    ];

    function updateClock() {

      const now = new Date();

      const hh =
        String(now.getHours()).padStart(2, '0');

      const mm =
        String(now.getMinutes()).padStart(2, '0');

      const ss =
        String(now.getSeconds()).padStart(2, '0');

      document.getElementById('clock').innerHTML =
        hh + ':' + mm + ':' + ss;

      document.getElementById('date').innerHTML =
        hari[now.getDay()] + ', ' +
        now.getDate() + ' ' +
        bulan[now.getMonth()] + ' ' +
        now.getFullYear();
    }

    updateClock();

    setInterval(updateClock, 1000);
  </script>

</body>

</html>