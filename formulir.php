<?php
include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama           = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $instansi       = trim(mysqli_real_escape_string($conn, $_POST['organization'] ?? ''));
    $keperluan      = trim(mysqli_real_escape_string($conn, $_POST['purpose'] ?? ''));
    $tujuan         = trim(mysqli_real_escape_string($conn, $_POST['destination'] ?? ''));
    $no_hp          = trim(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));
    $tanggal_janji  = trim(mysqli_real_escape_string($conn, $_POST['appointment_date'] ?? ''));
    // Rombongan: ambil array nama anggota, filter kosong
    $anggota_raw    = $_POST['anggota'] ?? [];
    $anggota_bersih = array_filter(array_map('trim', $anggota_raw));
    $anggota_json   = mysqli_real_escape_string($conn, json_encode(array_values($anggota_bersih), JSON_UNESCAPED_UNICODE));
    $jumlah_romb    = intval($_POST['jumlah_rombongan'] ?? 0);

    if (
        empty($nama) ||
        empty($instansi) ||
        empty($keperluan) ||
        empty($tujuan) ||
        empty($no_hp) ||
        empty($tanggal_janji)
    ) {
        $error = "Semua data wajib diisi!";
    } else {

        $tanggal_daftar = date('Y-m-d H:i:s');

        $sql = "INSERT INTO tamu (
                    full_name,
                    organization,
                    purpose,
                    destination,
                    phone,
                    appointment_date,
                    anggota_rombongan,
                    jumlah_rombongan,
                    status,
                    tanggal_daftar
                ) VALUES (
                    '$nama',
                    '$instansi',
                    '$keperluan',
                    '$tujuan',
                    '$no_hp',
                    '$tanggal_janji',
                    '$anggota_json',
                    '$jumlah_romb',
                    'pending',
                    '$tanggal_daftar'
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

// Kembalikan nilai anggota untuk re-populate form jika error
$anggota_lama = $_POST['anggota'] ?? ['', '', ''];
?>

<!DOCTYPE html>
<html lang="id">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulir Tamu DPRD Lampung</title>

  <!-- FONT -->
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- ICON -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

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
      --shadow: 0 10px 35px rgba(0, 0, 0, .08);
    }

    html, body { width: 100%; overflow-x: hidden; }

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
      background-size: 100% 100%, 45px 45px, 45px 45px;
      z-index: -1;
    }

    .container { width: 100%; max-width: 920px; margin: auto; }

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
    .back-btn:hover { opacity: .8; }

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
        linear-gradient(rgba(20, 12, 0, .82), rgba(20, 12, 0, .88)),
        url('img/gedung.jpg') center/cover no-repeat;
    }
    .hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.06), transparent);
      pointer-events: none;
    }
    .hero-content { position: relative; z-index: 2; }

    .logo-wrap { display: flex; align-items: center; gap: 18px; margin-bottom: 24px; }
    .logo-box {
      width: 74px; height: 74px;
      border-radius: 50%;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.18);
      display: flex; align-items: center; justify-content: center;
      backdrop-filter: blur(8px);
    }
    .logo-box img { width: 48px; height: 48px; object-fit: contain; }

    .hero h1 {
      color: #fff;
      font-size: 34px;
      line-height: 1.1;
      font-family: 'Cormorant Garamond', serif;
      margin-bottom: 10px;
    }
    .hero p { color: rgba(255,255,255,.72); font-size: 14px; line-height: 1.7; max-width: 560px; }

    .clock-box {
      margin-top: 24px;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 14px 18px;
      border-radius: 14px;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.15);
      backdrop-filter: blur(8px);
    }
    .clock-time { color: #fff; font-size: 26px; font-weight: 700; letter-spacing: 2px; }
    .clock-date { color: rgba(255,255,255,.72); font-size: 12px; }

    .tapis {
      height: 5px;
      background: repeating-linear-gradient(90deg,
        var(--gold-dark) 0 10px,
        var(--gold) 10px 20px,
        transparent 20px 28px);
    }

    /* FORM BODY */
    .form-body { padding: 42px; }

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
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--gold);
    }

    .section-divider {
      border: none;
      border-top: 1px dashed rgba(196, 154, 34, .3);
      margin: 32px 0 28px;
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

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full { grid-column: 1/-1; }
    .field { display: flex; flex-direction: column; }

    .field label {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #6e6047;
      margin-bottom: 9px;
    }
    .required { color: #d63031; }

    .input, .textarea {
      width: 100%;
      border: 1px solid #e6ddcf;
      background: #fcfbf8;
      border-radius: 14px;
      padding: 15px 16px;
      font-size: 14px;
      font-family: 'Outfit', sans-serif;
      transition: .2s;
    }
    .input:focus, .textarea:focus {
      outline: none;
      border-color: var(--gold);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(196, 154, 34, .12);
    }
    .textarea { resize: none; min-height: 120px; line-height: 1.7; }

    /* ROMBONGAN */
    .group-box {
      border: 1px solid #e6ddcf;
      border-radius: 18px;
      background: #fcfbf8;
      overflow: hidden;
    }
    .group-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      background: linear-gradient(135deg, #fffaf0, #fdf5e2);
      border-bottom: 1px solid #e6ddcf;
    }
    .group-count-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--gold);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      padding: 5px 12px;
      border-radius: 20px;
      letter-spacing: .05em;
    }
    .group-list { padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; }

    .group-row {
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideIn .25s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .group-number {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: var(--gold-soft);
      border: 1px solid var(--border);
      color: var(--gold-dark);
      font-size: 12px;
      font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .group-row .input { flex: 1; padding: 12px 14px; border-radius: 10px; }

    .btn-remove-member {
      width: 32px; height: 32px;
      border-radius: 50%;
      border: 1px solid #f5c6c6;
      background: #fff5f5;
      color: #c0392b;
      font-size: 16px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      transition: .2s;
    }
    .btn-remove-member:hover { background: #fde8e8; }
    .btn-remove-member:disabled { opacity: .3; cursor: not-allowed; }

    .group-footer { padding: 12px 18px; border-top: 1px dashed #e6ddcf; }
    .btn-add-member {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      border: 1.5px dashed var(--gold);
      background: transparent;
      color: var(--gold-dark);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 10px;
      cursor: pointer;
      transition: .2s;
    }
    .btn-add-member:hover { background: var(--gold-soft); }

    .input[type="datetime-local"] { color: var(--text); }

    .submit-btn {
      width: 100%;
      border: none;
      border-radius: 16px;
      padding: 16px;
      margin-top: 10px;
      background: linear-gradient(135deg, #5e4309, var(--gold-dark), var(--gold));
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 10px 24px rgba(139, 107, 15, .25);
    }
    .submit-btn:hover { transform: translateY(-2px); opacity: .95; }

    .privacy { margin-top: 16px; text-align: center; font-size: 12px; color: #8f8169; }

    footer { margin-top: 22px; text-align: center; font-size: 12px; color: #8f8169; }

    /* RESPONSIVE */
    @media(max-width:768px) {
      body { padding: 14px; }
      .hero { padding: 28px 22px; }
      .hero h1 { font-size: 28px; }
      .hero p { font-size: 13px; }
      .form-body { padding: 28px 22px; }
      .form-grid { grid-template-columns: 1fr; gap: 16px; }
      .clock-box { width: 100%; justify-content: flex-start; }
      .clock-time { font-size: 22px; }
    }
    @media(max-width:480px) {
      .hero { padding: 24px 18px; }
      .form-body { padding: 24px 18px; }
      .hero h1 { font-size: 24px; }
      .clock-time { font-size: 20px; }
      .input, .textarea { font-size: 13px; padding: 14px; }
      .submit-btn { font-size: 14px; }
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
              <div style="color:#fff;font-size:14px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;">
                DPRD Provinsi Lampung
              </div>
              <div style="color:rgba(255,255,255,.6);font-size:12px;margin-top:4px;">
                Sistem Buku Tamu Digital
              </div>
            </div>
          </div>

          <h1>Formulir<br>Pendaftaran Tamu</h1>

          <p>
            Silakan lengkapi seluruh data kunjungan Anda untuk proses verifikasi administrasi tamu DPRD Provinsi Lampung.
          </p>

          <div class="clock-box">
            <i class="ti ti-clock" style="font-size:22px;color:#fff;"></i>
            <div>
              <div class="clock-time" id="clock">--:--:--</div>
              <div class="clock-date"  id="date">Memuat tanggal...</div>
            </div>
          </div>

        </div>
      </div>

      <div class="tapis"></div>

      <!-- FORM -->
      <div class="form-body">

        <?php if ($error): ?>
          <div class="error-box">
            <i class="ti ti-alert-circle" style="font-size:20px;"></i>
            <div><?= htmlspecialchars($error) ?></div>
          </div>
        <?php endif; ?>

        <form method="POST">

          <!-- ══════════════════════════════
               SECTION 1 · DATA IDENTITAS
          ══════════════════════════════ -->
          <div class="section-title">Data Identitas Tamu</div>

          <div class="form-grid">

            <div class="field full">
              <label>Nama Lengkap <span class="required">*</span></label>
              <input type="text" name="full_name" class="input" required minlength="3"
                autocomplete="off" placeholder="Masukkan nama lengkap"
                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="field">
              <label>Instansi / Organisasi <span class="required">*</span></label>
              <input type="text" name="organization" class="input" required minlength="3"
                autocomplete="off" placeholder="Nama instansi"
                value="<?= htmlspecialchars($_POST['organization'] ?? '') ?>">
            </div>

            <div class="field">
              <label>No. Telepon / HP <span class="required">*</span></label>
              <input type="tel" name="phone" class="input" required pattern="[0-9]{10,15}"
                autocomplete="off" placeholder="08xxxxxxxxxx"
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="field full">
              <label>Tujuan yang Dituju <span class="required">*</span></label>
              <input type="text" name="destination" class="input" required minlength="3"
                autocomplete="off" placeholder="Contoh: Ketua DPRD, Sekretaris Dewan"
                value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
            </div>

            <div class="field full">
              <label>Keperluan Kunjungan <span class="required">*</span></label>
              <textarea name="purpose" class="textarea" required minlength="3"
                placeholder="Jelaskan secara singkat keperluan kunjungan..."><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
            </div>

          </div>

          <!-- ══════════════════════════════
               SECTION 2 · JANJI TEMU
          ══════════════════════════════ -->
          <hr class="section-divider">
          <div class="section-title">Jadwal Janji Temu</div>

          <div class="form-grid">
            <div class="field full">
              <label>Tanggal &amp; Waktu Janji Temu <span class="required">*</span></label>
              <input type="datetime-local" name="appointment_date" class="input" required
                min="<?= date('Y-m-d\TH:i') ?>"
                value="<?= htmlspecialchars($_POST['appointment_date'] ?? '') ?>">
            </div>
          </div>

          <!-- ══════════════════════════════
               SECTION 3 · ROMBONGAN
          ══════════════════════════════ -->
          <hr class="section-divider">
          <div class="section-title">Data Rombongan</div>

          <div class="form-grid">

            <div class="field" style="max-width:260px;">
              <label>Jumlah Anggota Rombongan</label>
              <div style="position:relative;">
                <input
                  type="number"
                  name="jumlah_rombongan"
                  id="jumlahInput"
                  class="input"
                  min="0"
                  placeholder="0"
                  style="padding-right:90px;"
                  value="<?= htmlspecialchars($_POST['jumlah_rombongan'] ?? '0') ?>"
>
                <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--gold-dark);font-weight:600;pointer-events:none;">orang</span>
              </div>
              <div style="font-size:12px;color:#8f8169;margin-top:6px;">Terisi otomatis dari daftar nama &middot; bisa diubah manual</div>
            </div>

            <div class="field full">
              <label>Daftar Anggota Rombongan</label>

              <div class="group-box">

                <div class="group-header">
                  <span style="font-size:13px;font-weight:600;color:var(--gold-dark);">
                    <i class="ti ti-users" style="margin-right:4px;"></i>
                    Nama-Nama Anggota
                  </span>
                  <span class="group-count-badge" id="countBadge">
                    <i class="ti ti-user"></i>
                    <span id="countText">0 Orang</span>
                  </span>
                </div>

                <div class="group-list" id="memberList">
                  <?php
                  $anggota_tampil = !empty($_POST['anggota']) ? $_POST['anggota'] : ['', '', ''];
                  foreach ($anggota_tampil as $i => $nama_anggota):
                  ?>
                  <div class="group-row">
                    <div class="group-number"><?= $i + 1 ?></div>
                    <input type="text" name="anggota[]" class="input"
                      placeholder="Contoh: Andre, Bima, Yunus"
                      autocomplete="off"
                      value="<?= htmlspecialchars($nama_anggota) ?>"
                      oninput="updateCount()">
                    <button type="button" class="btn-remove-member" onclick="removeMember(this)" title="Hapus anggota">
                      <i class="ti ti-x"></i>
                    </button>
                  </div>
                  <?php endforeach; ?>
                </div>

                <div class="group-footer">
                  <button type="button" class="btn-add-member" onclick="addMember()">
                    <i class="ti ti-plus"></i>
                    Tambah Anggota
                  </button>
                </div>

              </div>

              <div style="font-size:12px;color:#8f8169;margin-top:8px;">
                Kosongkan jika tidak ada rombongan. Jumlah orang dihitung otomatis dari nama yang terisi.
              </div>
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
    /* ── CLOCK ── */
    const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    function updateClock() {
      const now = new Date();
      const hh  = String(now.getHours()).padStart(2, '0');
      const mm  = String(now.getMinutes()).padStart(2, '0');
      const ss  = String(now.getSeconds()).padStart(2, '0');
      document.getElementById('clock').innerHTML = hh + ':' + mm + ':' + ss;
      document.getElementById('date').innerHTML  =
        hari[now.getDay()] + ', ' + now.getDate() + ' ' + bulan[now.getMonth()] + ' ' + now.getFullYear();
    }
    updateClock();
    setInterval(updateClock, 1000);

    /* ── ROMBONGAN ── */
    function updateCount() {
      const inputs = document.querySelectorAll('#memberList input[name="anggota[]"]');
      let isi = 0;
      inputs.forEach(inp => { if (inp.value.trim() !== '') isi++; });

      // Sinkron badge
      document.getElementById('countText').innerText = isi + ' Orang';


      document.querySelectorAll('#memberList .group-number').forEach((el, i) => {
        el.innerText = i + 1;
      });

      const removeButtons = document.querySelectorAll('.btn-remove-member');
      removeButtons.forEach(btn => {
        btn.disabled = (removeButtons.length <= 1);
      });
    }


    function addMember() {
      const list  = document.getElementById('memberList');
      const rows  = list.querySelectorAll('.group-row');
      const nomor = rows.length + 1;

      const row = document.createElement('div');
      row.className = 'group-row';
      row.innerHTML = `
        <div class="group-number">${nomor}</div>
        <input type="text" name="anggota[]" class="input"
          placeholder="Contoh: Andre, Bima, Yunus"
          autocomplete="off"
          oninput="updateCount()">
        <button type="button" class="btn-remove-member" onclick="removeMember(this)" title="Hapus anggota">
          <i class="ti ti-x"></i>
        </button>
      `;
      list.appendChild(row);
      row.querySelector('input').focus();
      updateCount();
    }

    function removeMember(btn) {
      const list = document.getElementById('memberList');
      if (list.querySelectorAll('.group-row').length <= 1) return;
      btn.closest('.group-row').remove();
      updateCount();
    }

    updateCount();
  </script>

</body>
</html>