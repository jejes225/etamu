<?php
// admin.php – Dashboard Admin + Rekap Bulanan e-Tamu DPRD Provinsi Lampung
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// ── Mode: dashboard | rekap | export_csv ─────────────────────────────────────
$mode = $_GET['mode'] ?? 'dashboard';

// ════════════════════════════════════════════════════════════════════════════
// MODE: EXPORT CSV — kirim file langsung, lalu exit
// ════════════════════════════════════════════════════════════════════════════
if ($mode === 'export_csv') {
    $tahun = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
    $bulan = isset($_GET['bulan']) && is_numeric($_GET['bulan']) && $_GET['bulan'] >= 1 && $_GET['bulan'] <= 12
        ? (int)$_GET['bulan'] : (int)date('n');

    $bulan_str  = sprintf('%02d', $bulan);
    $range_from = "$tahun-$bulan_str-01";
    $range_to   = date('Y-m-t', mktime(0, 0, 0, $bulan, 1, $tahun));

    $nama_bulan_arr = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $label_slug = $nama_bulan_arr[$bulan] . '_' . $tahun;

    $res = mysqli_query($conn,
        "SELECT * FROM tamu
         WHERE tanggal_daftar >= '$range_from 00:00:00'
           AND tanggal_daftar <= '$range_to 23:59:59'
         ORDER BY tanggal_daftar ASC"
    );

    $stat_q = mysqli_query($conn,
        "SELECT COUNT(*) AS total,
                SUM(status='pending')  AS pending,
                SUM(status='approved') AS approved,
                SUM(status='rejected') AS rejected,
                SUM(COALESCE(jumlah_rombongan,0)) AS total_rombongan
         FROM tamu
         WHERE tanggal_daftar >= '$range_from 00:00:00'
           AND tanggal_daftar <= '$range_to 23:59:59'"
    );
    $stat = mysqli_fetch_assoc($stat_q);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="rekap_tamu_' . $label_slug . '.csv"');
    header('Pragma: no-cache');
    header('Cache-Control: no-store, no-cache');
    header('Expires: 0');

    echo "\xEF\xBB\xBF"; // BOM UTF-8 — agar Excel Windows tidak rusak karakter

    $out = fopen('php://output', 'w');
    fputcsv($out, ['REKAP TAMU BULANAN - DPRD PROVINSI LAMPUNG']);
    fputcsv($out, ['Periode: ' . $nama_bulan_arr[$bulan] . ' ' . $tahun]);
    fputcsv($out, ['Dicetak: ' . date('d M Y H:i') . ' WIB']);
    fputcsv($out, ['Total Tamu: ' . (int)$stat['total'] . ' orang']);
    fputcsv($out, []);
    fputcsv($out, ['No','Tanggal Daftar','Jam','Nama Lengkap','Instansi / Organisasi','No. HP',
                   'Tujuan','Keperluan','Janji Temu','Jml Rombongan','Anggota Rombongan','Status']);

    $no = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $anggota_str = '';
        if (!empty($row['anggota_rombongan'])) {
            $arr = json_decode($row['anggota_rombongan'], true);
            if (is_array($arr)) $anggota_str = implode(' | ', $arr);
        }
        $janji_str = '';
        if (!empty($row['appointment_date']) && $row['appointment_date'] !== '0000-00-00 00:00:00') {
            $ts = strtotime($row['appointment_date']);
            $bl = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $janji_str = date('d', $ts) . ' ' . $bl[(int)date('n',$ts)-1] . ' ' . date('Y H:i', $ts) . ' WIB';
        }
        $sl = ['approved'=>'Disetujui','rejected'=>'Ditolak','pending'=>'Pending'];
        fputcsv($out, [
            $no++,
            date('d M Y', strtotime($row['tanggal_daftar'])),
            date('H:i',   strtotime($row['tanggal_daftar'])),
            $row['full_name'],
            $row['organization'],
            $row['phone'] ?? '',
            $row['destination'] ?? '',
            $row['purpose'],
            $janji_str,
            $row['jumlah_rombongan'] ?? 0,
            $anggota_str,
            $sl[$row['status']] ?? $row['status'],
        ]);
    }
    fclose($out);
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// MODE: DASHBOARD — aksi status / hapus
// ════════════════════════════════════════════════════════════════════════════
if ($mode === 'dashboard') {
    if (isset($_GET['aksi']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $aksi = $_GET['aksi'];
        $id   = (int)$_GET['id'];
        if (in_array($aksi, ['approved', 'rejected', 'pending'])) {
            mysqli_query($conn, "UPDATE tamu SET status='$aksi' WHERE id=$id");
            header("Location: admin.php?msg=berhasil");
        } elseif ($aksi === 'hapus') {
            mysqli_query($conn, "DELETE FROM tamu WHERE id=$id");
            header("Location: admin.php?msg=hapus");
        } else {
            header("Location: admin.php");
        }
        exit();
    }
}

// ════════════════════════════════════════════════════════════════════════════
// DATA DASHBOARD
// ════════════════════════════════════════════════════════════════════════════
$filter = $_GET['filter'] ?? 'semua';
$where  = '';
if (in_array($filter, ['pending', 'approved', 'rejected'])) {
    $where = "WHERE status = '$filter'";
}

$result   = mysqli_query($conn, "SELECT * FROM tamu $where ORDER BY tanggal_daftar DESC");
$total    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu"))['c'];
$pending  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='pending'"))['c'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='approved'"))['c'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='rejected'"))['c'];

// ════════════════════════════════════════════════════════════════════════════
// DATA REKAP BULANAN
// ════════════════════════════════════════════════════════════════════════════
$tahun_skrg  = (int)date('Y');
$bulan_skrg  = (int)date('n');
$rek_tahun   = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? (int)$_GET['tahun'] : $tahun_skrg;
$rek_bulan   = isset($_GET['bulan']) && is_numeric($_GET['bulan']) && $_GET['bulan'] >= 1 && $_GET['bulan'] <= 12
               ? (int)$_GET['bulan'] : $bulan_skrg;
$rek_bstr    = sprintf('%02d', $rek_bulan);
$rek_from    = "$rek_tahun-$rek_bstr-01";
$rek_to      = date('Y-m-t', mktime(0, 0, 0, $rek_bulan, 1, $rek_tahun));

$nama_bulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$label_bulan_rekap = $nama_bulan[$rek_bulan] . ' ' . $rek_tahun;

$rek_result = mysqli_query($conn,
    "SELECT * FROM tamu
     WHERE tanggal_daftar >= '$rek_from 00:00:00'
       AND tanggal_daftar <= '$rek_to 23:59:59'
     ORDER BY tanggal_daftar ASC"
);
$rek_stat_q = mysqli_query($conn,
    "SELECT COUNT(*) AS total,
            SUM(status='pending')  AS pending,
            SUM(status='approved') AS approved,
            SUM(status='rejected') AS rejected,
            SUM(COALESCE(jumlah_rombongan,0)) AS total_rombongan
     FROM tamu
     WHERE tanggal_daftar >= '$rek_from 00:00:00'
       AND tanggal_daftar <= '$rek_to 23:59:59'"
);
$rek_stat = mysqli_fetch_assoc($rek_stat_q);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php if ($mode === 'dashboard'): ?>
  <meta http-equiv="refresh" content="5" />
  <?php endif; ?>
  <title>Admin – e-Tamu DPRD Provinsi Lampung</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Public Sans', sans-serif; }

    /* Tab active underline */
    .tab-active { border-bottom: 3px solid #fff; font-weight: 700; }

    /* Hanya tampil saat cetak */
    .print-only { display: none; }

    @media print {
      .no-print  { display: none !important; }
      .print-only { display: block !important; }
      nav, footer { display: none !important; }
      body { background: white; font-size: 11px; }
      .shadow, .rounded-xl { box-shadow: none !important; }

      /* Hilangkan header & footer bawaan browser (URL, tanggal, judul) */
      @page {
        margin: 10mm 12mm;
        size: A4 landscape;
        /* Chrome/Edge/Firefox: kosongkan string header-footer */
        margin-top: 10mm;
        margin-bottom: 10mm;
      }

      /* Hilangkan scrollbar horizontal wrapper tabel */
      .overflow-x-auto { overflow: visible !important; }

      /* Tabel tidak terpotong, melebar penuh */
      table { width: 100% !important; table-layout: auto; }
      td, th { white-space: normal !important; word-break: break-word; }
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- ══════════════════════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════════════════════ -->
<nav class="bg-yellow-700 text-white px-6 py-0 flex items-center justify-between shadow no-print">
  <div class="flex items-center gap-3 py-3">
    <img src="img/logodrpd.jpg" alt="Logo" class="w-10 h-10 object-contain rounded-full bg-white p-1" />
    <div>
      <p class="font-bold text-lg leading-tight">Dashboard Admin</p>
      <p class="text-xs text-yellow-200">e-Tamu DPRD Provinsi Lampung</p>
    </div>
  </div>

  <!-- TAB NAVIGASI -->
  <div class="flex items-center gap-0 h-full">
    <a href="admin.php?mode=dashboard"
      class="px-5 py-5 text-sm font-semibold hover:bg-yellow-800 transition <?= $mode === 'dashboard' ? 'tab-active bg-yellow-800' : '' ?>">
      Dashboard
    </a>
    <a href="admin.php?mode=rekap"
      class="px-5 py-5 text-sm font-semibold hover:bg-yellow-800 transition <?= $mode === 'rekap' ? 'tab-active bg-yellow-800' : '' ?>">
      Rekap Bulanan
    </a>
    <a href="logout.php"
      class="ml-4 mr-2 text-sm bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg font-semibold transition">
      Logout
    </a>
  </div>
</nav>

<?php if ($mode === 'dashboard'): ?>
<!-- ══════════════════════════════════════════════════════════════════════════
     HALAMAN: DASHBOARD
══════════════════════════════════════════════════════════════════════════ -->

  <!-- COUNTDOWN REFRESH -->
  <div class="bg-yellow-50 border-b border-yellow-200 px-6 py-2 flex items-center justify-between text-xs text-yellow-700 no-print">
    <span>🔄 Halaman refresh otomatis setiap 5 detik</span>
    <span>Refresh dalam: <strong id="countdown">5</strong> detik</span>
  </div>

  <div class="max-w-7xl mx-auto px-4 py-6">

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['msg'])): ?>
    <div class="mb-4 p-3 rounded-lg text-sm <?= $_GET['msg'] === 'hapus' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
      <?= $_GET['msg'] === 'hapus' ? 'Data tamu berhasil dihapus.' : '✅ Status berhasil diperbarui.' ?>
    </div>
    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-3xl font-bold text-gray-800"><?= $total ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Tamu</p>
      </div>
      <div class="bg-yellow-50 rounded-xl shadow p-4 text-center border border-yellow-200">
        <p class="text-3xl font-bold text-yellow-700"><?= $pending ?></p>
        <p class="text-sm text-yellow-600 mt-1">⏳ Pending</p>
      </div>
      <div class="bg-green-50 rounded-xl shadow p-4 text-center border border-green-200">
        <p class="text-3xl font-bold text-green-700"><?= $approved ?></p>
        <p class="text-sm text-green-600 mt-1">✅ Disetujui</p>
      </div>
      <div class="bg-red-50 rounded-xl shadow p-4 text-center border border-red-200">
        <p class="text-3xl font-bold text-red-700"><?= $rejected ?></p>
        <p class="text-sm text-red-600 mt-1">❌ Ditolak</p>
      </div>
    </div>

    <!-- FILTER -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <?php
      $filters = ['semua'=>'Semua','pending'=>'⏳ Pending','approved'=>'✅ Disetujui','rejected'=>'❌ Ditolak'];
      foreach ($filters as $key => $label):
        $active = ($filter === $key) ? 'bg-yellow-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
      ?>
      <a href="admin.php?filter=<?= $key ?>"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= $active ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TABEL DASHBOARD -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-yellow-700 text-white">
            <tr>
              <th class="px-4 py-3 text-left whitespace-nowrap">No</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Nama</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Instansi</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">No. HP</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Tujuan</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Keperluan</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Janji Temu</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Rombongan</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
              <th class="px-4 py-3 text-left whitespace-nowrap">Tanggal Daftar</th>
              <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php
            $no = 1;
            $ada_data = false;
            while ($row = mysqli_fetch_assoc($result)):
              $ada_data = true;

              $anggota_arr = [];
              if (!empty($row['anggota_rombongan'])) {
                  $decoded = json_decode($row['anggota_rombongan'], true);
                  if (is_array($decoded)) $anggota_arr = $decoded;
              }
              $jumlah_romb = (int)($row['jumlah_rombongan'] ?? 0);
            ?>
            <tr class="hover:bg-gray-50 transition align-top">
              <td class="px-4 py-3 text-gray-500"><?= $no++ ?></td>

              <td class="px-4 py-3 font-semibold text-gray-800 whitespace-nowrap">
                <?= htmlspecialchars($row['full_name']) ?>
              </td>

              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                <?= htmlspecialchars($row['organization']) ?>
              </td>

              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                <?php if (!empty($row['phone'])): ?>
                  <a href="tel:<?= htmlspecialchars($row['phone']) ?>"
                    class="text-yellow-700 hover:underline font-medium">
                    📞 <?= htmlspecialchars($row['phone']) ?>
                  </a>
                <?php else: ?>
                  <span class="text-gray-300">—</span>
                <?php endif; ?>
              </td>

              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                <?= htmlspecialchars($row['destination'] ?? '—') ?>
              </td>

              <td class="px-4 py-3 text-gray-600 max-w-[180px] truncate">
                <?= htmlspecialchars($row['purpose']) ?>
              </td>

              <!-- JANJI TEMU -->
              <td class="px-4 py-3 whitespace-nowrap">
                <?php if (!empty($row['appointment_date']) && $row['appointment_date'] !== '0000-00-00 00:00:00'): ?>
                  <?php
                    $ts    = strtotime($row['appointment_date']);
                    $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    $bln   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    $lhari = $hari[date('w', $ts)];
                    $ltgl  = date('d', $ts) . ' ' . $bln[(int)date('n',$ts)-1] . ' ' . date('Y', $ts);
                    $lwkt  = date('H:i', $ts);
                    $ts0   = strtotime('today');
                    $ts1   = strtotime('tomorrow') - 1;
                    if ($ts < $ts0)       { $bc = 'bg-red-50 text-red-700 border-red-200';     $dc = 'bg-red-400'; }
                    elseif ($ts <= $ts1)  { $bc = 'bg-green-50 text-green-700 border-green-200'; $dc = 'bg-green-400'; }
                    else                  { $bc = 'bg-blue-50 text-blue-700 border-blue-200';   $dc = 'bg-blue-400'; }
                  ?>
                  <div class="inline-flex flex-col gap-0.5 border rounded-lg px-2 py-1.5 text-xs <?= $bc ?>">
                    <div class="flex items-center gap-1.5 font-semibold">
                      <span class="w-1.5 h-1.5 rounded-full <?= $dc ?>"></span>
                      <?= $lhari ?>, <?= $ltgl ?>
                    </div>
                    <div class="pl-3 font-bold text-sm"><?= $lwkt ?> WIB</div>
                  </div>
                <?php else: ?>
                  <span class="text-gray-300 text-xs">—</span>
                <?php endif; ?>
              </td>

              <!-- ROMBONGAN -->
              <td class="px-4 py-3">
                <?php if ($jumlah_romb > 0 || count($anggota_arr) > 0): ?>
                  <div class="flex flex-col gap-1">
                    <div class="inline-flex items-center gap-1.5 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-2 py-1 text-xs font-semibold w-fit">
                      👥 <?= $jumlah_romb > 0 ? $jumlah_romb : count($anggota_arr) ?> Orang
                    </div>
                    <?php if (count($anggota_arr) > 0): ?>
                      <button type="button" onclick="toggleAnggota(<?= $row['id'] ?>)"
                        class="inline-flex items-center gap-1 text-xs text-yellow-700 hover:text-yellow-900 font-medium text-left w-fit">
                        <span id="icon-<?= $row['id'] ?>">▶</span> Lihat nama
                      </button>
                      <div id="anggota-<?= $row['id'] ?>" class="hidden mt-1">
                        <ol class="list-decimal list-inside space-y-0.5">
                          <?php foreach ($anggota_arr as $na): ?>
                            <li class="text-xs text-gray-700 leading-snug"><?= htmlspecialchars($na) ?></li>
                          <?php endforeach; ?>
                        </ol>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <span class="text-gray-300 text-xs">—</span>
                <?php endif; ?>
              </td>

              <!-- STATUS -->
              <td class="px-4 py-3 whitespace-nowrap">
                <?php if ($row['status'] === 'approved'): ?>
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Disetujui</span>
                <?php elseif ($row['status'] === 'rejected'): ?>
                  <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>
                <?php else: ?>
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Pending</span>
                <?php endif; ?>
              </td>

              <!-- TANGGAL DAFTAR -->
              <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                <?= date('d M Y', strtotime($row['tanggal_daftar'])) ?><br>
                <span class="text-gray-400"><?= date('H:i', strtotime($row['tanggal_daftar'])) ?></span>
              </td>

              <!-- AKSI -->
              <td class="px-4 py-3">
                <div class="flex gap-1 justify-center flex-wrap">
                  <?php if ($row['status'] !== 'approved'): ?>
                  <a href="admin.php?aksi=approved&id=<?= $row['id'] ?>&filter=<?= $filter ?>"
                    class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs font-semibold transition"
                    onclick="return confirm('Setujui tamu ini?')">Setujui</a>
                  <?php endif; ?>
                  <?php if ($row['status'] !== 'rejected'): ?>
                  <a href="admin.php?aksi=rejected&id=<?= $row['id'] ?>&filter=<?= $filter ?>"
                    class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold transition"
                    onclick="return confirm('Tolak tamu ini?')">Tolak</a>
                  <?php endif; ?>
                  <?php if ($row['status'] !== 'pending'): ?>
                  <a href="admin.php?aksi=pending&id=<?= $row['id'] ?>&filter=<?= $filter ?>"
                    class="bg-gray-400 hover:bg-gray-500 text-white px-2 py-1 rounded text-xs font-semibold transition"
                    onclick="return confirm('Reset ke pending?')">Reset</a>
                  <?php endif; ?>
                  <a href="admin.php?aksi=hapus&id=<?= $row['id'] ?>&filter=<?= $filter ?>"
                    class="bg-gray-700 hover:bg-gray-900 text-white px-2 py-1 rounded text-xs font-semibold transition"
                    onclick="return confirm('Yakin hapus data tamu ini? Tidak bisa dibatalkan!')">🗑️ Hapus</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>

            <?php if (!$ada_data): ?>
            <tr>
              <td colspan="11" class="px-4 py-10 text-center text-gray-400">
                Tidak ada data tamu.
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div><!-- /dashboard container -->

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════════════════════
     HALAMAN: REKAP BULANAN
══════════════════════════════════════════════════════════════════════════ -->
  <div class="max-w-7xl mx-auto px-4 py-6">

    <!-- FILTER BULAN -->
    <div class="bg-white rounded-xl shadow p-5 mb-6 no-print">
      <h2 class="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">Pilih Periode Rekap</h2>
      <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="mode" value="rekap" />
        <div>
          <label class="block text-xs text-gray-500 mb-1">Bulan</label>
          <select name="bulan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <?php foreach ($nama_bulan as $k => $v): ?>
              <option value="<?= $k ?>" <?= $k == $rek_bulan ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tahun</label>
          <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <?php for ($y = $tahun_skrg; $y >= $tahun_skrg - 5; $y--): ?>
              <option value="<?= $y ?>" <?= $y == $rek_tahun ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <button type="submit"
          class="bg-yellow-700 hover:bg-yellow-800 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
          🔍 Tampilkan
        </button>
      </form>
    </div>

    <!-- JUDUL REKAP -->
    <div class="bg-yellow-700 text-white rounded-xl shadow p-5 mb-5 flex items-center gap-4 no-print">
      <div>
        <p class="text-xs uppercase tracking-widest text-yellow-200 mb-0.5">Rekap Tamu Bulanan</p>
        <h1 class="text-2xl font-bold"><?= $label_bulan_rekap ?></h1>
        <p class="text-xs text-yellow-200 mt-0.5">DPRD Provinsi Lampung &nbsp;·&nbsp; Dicetak: <?= date('d M Y H:i') ?> WIB</p>
      </div>
    </div>

    <!-- STATISTIK REKAP -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6 no-print">
      <?php
      $rcards = [
        ['Total Tamu',          $rek_stat['total'],           'bg-white',                              'text-gray-800'],
        ['⏳ Pending',           $rek_stat['pending'],          'bg-yellow-50 border border-yellow-200', 'text-yellow-700'],
        ['✅ Disetujui',         $rek_stat['approved'],         'bg-green-50 border border-green-200',   'text-green-700'],
        ['❌ Ditolak',           $rek_stat['rejected'],         'bg-red-50 border border-red-200',       'text-red-700'],
      ];
      foreach ($rcards as [$lbl, $val, $bg, $fg]):
      ?>
      <div class="<?= $bg ?> rounded-xl shadow p-4 text-center">
        <p class="text-3xl font-bold <?= $fg ?>"><?= (int)$val ?></p>
        <p class="text-xs mt-1 text-gray-500"><?= $lbl ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- TOMBOL EKSPOR -->
    <div class="flex gap-3 mb-4 no-print flex-wrap">
      <a href="admin.php?mode=export_csv&bulan=<?= $rek_bulan ?>&tahun=<?= $rek_tahun ?>"
        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow">
        Export CSV
      </a>
      <button onclick="downloadPDF()"
        class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow">
        Download PDF
      </button>
    </div>

    <!-- HEADER KHUSUS CETAK (hanya muncul saat print) -->
    <div class="print-only mb-4">
      <p class="text-xs uppercase tracking-widest text-gray-400 mb-0.5">Rekap Tamu Bulanan</p>
      <h1 class="text-xl font-bold text-gray-800"><?= $label_bulan_rekap ?></h1>
      <p class="text-xs text-gray-500">DPRD Provinsi Lampung &nbsp;·&nbsp; Dicetak: <?= date('d M Y H:i') ?> WIB</p>
    </div>

    <!-- TABEL REKAP -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table id="tabel-rekap" class="w-full text-sm">
          <thead class="bg-yellow-700 text-white">
            <tr>
              <th class="px-3 py-3 text-left whitespace-nowrap">No</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Tgl Daftar</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Nama</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Instansi</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">No. HP</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Tujuan</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Keperluan</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Janji Temu</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Rombongan</th>
              <th class="px-3 py-3 text-left whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php
            $rno = 1;
            $rada = false;
            while ($rrow = mysqli_fetch_assoc($rek_result)):
              $rada = true;
              $rjml = (int)($rrow['jumlah_rombongan'] ?? 0);

              $rjanji = '—';
              if (!empty($rrow['appointment_date']) && $rrow['appointment_date'] !== '0000-00-00 00:00:00') {
                  $rts = strtotime($rrow['appointment_date']);
                  $rbl = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                  $rjanji = date('d', $rts) . ' ' . $rbl[(int)date('n',$rts)-1] . ' ' . date('Y H:i', $rts) . ' WIB';
              }

              $rstatus = [
                  'approved' => ['✅ Disetujui', 'bg-green-100 text-green-800'],
                  'rejected' => ['❌ Ditolak',   'bg-red-100 text-red-800'],
                  'pending'  => ['⏳ Pending',    'bg-yellow-100 text-yellow-800'],
              ][$rrow['status']] ?? ['—', 'bg-gray-100 text-gray-500'];
            ?>
            <tr class="hover:bg-gray-50 align-top">
              <td class="px-3 py-2.5 text-gray-400 text-xs"><?= $rno++ ?></td>
              <td class="px-3 py-2.5 text-xs text-gray-500 whitespace-nowrap">
                <?= date('d M Y', strtotime($rrow['tanggal_daftar'])) ?><br>
                <span class="text-gray-400"><?= date('H:i', strtotime($rrow['tanggal_daftar'])) ?></span>
              </td>
              <td class="px-3 py-2.5 font-semibold text-gray-800 whitespace-nowrap">
                <?= htmlspecialchars($rrow['full_name']) ?>
              </td>
              <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap">
                <?= htmlspecialchars($rrow['organization']) ?>
              </td>
              <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap text-xs">
                <?= htmlspecialchars($rrow['phone'] ?? '—') ?>
              </td>
              <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap text-xs">
                <?= htmlspecialchars($rrow['destination'] ?? '—') ?>
              </td>
              <td class="px-3 py-2.5 text-gray-600 text-xs max-w-[160px]">
                <?= htmlspecialchars($rrow['purpose']) ?>
              </td>
              <td class="px-3 py-2.5 text-xs text-gray-600 whitespace-nowrap">
                <?= htmlspecialchars($rjanji) ?>
              </td>
              <td class="px-3 py-2.5 text-xs text-gray-600 whitespace-nowrap">
                <?= $rjml > 0 ? '👥 ' . $rjml . ' orang' : '—' ?>
              </td>
              <td class="px-3 py-2.5 whitespace-nowrap">
                <span class="<?= $rstatus[1] ?> px-2 py-0.5 rounded text-xs font-semibold">
                  <?= $rstatus[0] ?>
                </span>
              </td>
            </tr>
            <?php endwhile; ?>

            <?php if (!$rada): ?>
            <tr>
              <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                Tidak ada data tamu untuk periode <?= $label_bulan_rekap ?>.
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- GRAFIK STATUS -->
    <?php if ((int)$rek_stat['total'] > 0): ?>
    <div class="mt-6 bg-white rounded-xl shadow p-5 no-print">
      <h3 class="font-bold text-gray-700 mb-4 text-sm uppercase tracking-wide">Distribusi Status – <?= $label_bulan_rekap ?></h3>
      <div class="flex items-end gap-4 h-36">
        <?php
        $rmax = max(1, (int)$rek_stat['total']);
        $rbars = [
            ['Disetujui', $rek_stat['approved'], 'bg-green-500'],
            ['Pending',   $rek_stat['pending'],  'bg-yellow-500'],
            ['Ditolak',   $rek_stat['rejected'], 'bg-red-500'],
        ];
        foreach ($rbars as [$rl, $rv, $rc]):
            $rp = round(($rv / $rmax) * 100);
        ?>
        <div class="flex flex-col items-center gap-1 flex-1">
          <span class="text-xs font-bold text-gray-700"><?= (int)$rv ?></span>
          <div class="w-full <?= $rc ?> rounded-t-md" style="height: <?= max(4, $rp) ?>%; min-height:4px"></div>
          <span class="text-xs text-gray-500"><?= $rl ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /rekap container -->

  <!-- jsPDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
  <script>
    function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
      const W = 297, H = 210;

      // ── Warna tema ────────────────────────────────────────────────────────
      const GOLD_D  = [120, 53, 15];   // kuning-tua (yellow-900)
      const GOLD    = [146, 64, 14];   // yellow-800
      const GOLD_L  = [217, 119, 6];   // yellow-600
      const WHITE   = [255, 255, 255];
      const DARK    = [17,  24,  39];  // gray-900
      const MID     = [75,  85,  99];  // gray-600
      const LIGHT   = [249, 250, 251]; // gray-50
      const LINE    = [229, 231, 235]; // gray-200

      // ════════════════════════════════════════════════════════════════════
      // FUNGSI HELPER — gambar tiap halaman
      // ════════════════════════════════════════════════════════════════════
      function drawPageFrame(pageNum, totalPages) {
        // Strip atas – gradien simulasi dengan 2 rect
        doc.setFillColor(...GOLD_D);
        doc.rect(0, 0, W, 18, 'F');
        doc.setFillColor(...GOLD_L);
        doc.rect(0, 16, W, 2, 'F');

        // Teks header kanan
        doc.setTextColor(...WHITE);
        doc.setFontSize(7);
        doc.setFont('helvetica', 'normal');
        doc.text('Sekretariat DPRD PROVINSI LAMPUNG', W - 8, 7, { align: 'right' });
        doc.text('Dokumen Rekap Tamu Resmi', W - 8, 12, { align: 'right' });

        // Judul kiri header (halaman pertama lebih besar)
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(pageNum === 1 ? 11 : 9);
        doc.text('REKAP KUNJUNGAN TAMU BULANAN', 10, 8);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.text('Periode: <?= $label_bulan_rekap ?>  |  Total Tamu: <?= (int)$rek_stat['total'] ?> orang', 10, 14);

        // Strip bawah
        doc.setFillColor(...GOLD);
        doc.rect(0, H - 10, W, 10, 'F');

        // Teks footer
        doc.setTextColor(...WHITE);
        doc.setFontSize(7);
        doc.setFont('helvetica', 'normal');
        doc.text('Sekretariat DPRD Provinsi Lampung  |  e-Tamu Digital', 10, H - 3.5);
        doc.text('Dicetak: <?= date('d M Y, H:i') ?> WIB  |  Hal. ' + pageNum + ' / ' + totalPages, W - 10, H - 3.5, { align: 'right' });

        // Garis dekoratif kiri
        doc.setFillColor(...GOLD_L);
        doc.rect(0, 18, 3, H - 28, 'F');
      }

      // ════════════════════════════════════════════════════════════════════
      // HALAMAN 1 — Cover + ringkasan + tabel
      // ════════════════════════════════════════════════════════════════════
      drawPageFrame(1, 1); // akan diupdate setelah autoTable

      // Kotak info di bawah header
      doc.setFillColor(...LIGHT);
      doc.setDrawColor(...LINE);
      doc.setLineWidth(0.3);
      doc.roundedRect(8, 21, W - 16, 14, 2, 2, 'FD');

      doc.setTextColor(...DARK);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(9);
      doc.text('LAPORAN REKAP KUNJUNGAN TAMU', 16, 27);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8);
      doc.setTextColor(...MID);
      doc.text('Sekretariat DPRD Provinsi Lampung  -  Periode: <?= $label_bulan_rekap ?>  -  Jumlah Tamu: <?= (int)$rek_stat['total'] ?> orang', 16, 32);

      // Garis pemisah tipis
      doc.setDrawColor(...LINE);
      doc.setLineWidth(0.3);
      doc.line(8, 37, W - 8, 37);

      // ── Ambil data tabel dari HTML ────────────────────────────────────
      const tbl   = document.getElementById('tabel-rekap');
      const heads = [];
      tbl.querySelectorAll('thead th').forEach(th => heads.push(th.innerText.trim()));
      const rows = [];
      tbl.querySelectorAll('tbody tr').forEach(tr => {
        const cells = [];
        tr.querySelectorAll('td').forEach(td => {
          cells.push(td.innerText.trim().replace(/\n/g,' ').replace(/[✅❌⏳👥📞]/gu,'').trim());
        });
        if (cells.length > 1) rows.push(cells);
      });

      // ── autoTable ─────────────────────────────────────────────────────
      doc.autoTable({
        head: [heads],
        body: rows.length > 0 ? rows : [['', 'Tidak ada data tamu untuk periode ini.', '', '', '', '', '', '', '', '']],
        startY: 39,
        margin: { left: 8, right: 8, bottom: 14 },
        tableWidth: 'auto',
        styles: {
          fontSize: 7.2,
          cellPadding: { top: 2.5, bottom: 2.5, left: 3, right: 3 },
          font: 'helvetica',
          textColor: DARK,
          lineColor: LINE,
          lineWidth: 0.2,
          overflow: 'linebreak',
          valign: 'middle',
        },
        headStyles: {
          fillColor: GOLD,
          textColor: WHITE,
          fontStyle: 'bold',
          fontSize: 7.5,
          halign: 'center',
          cellPadding: { top: 3, bottom: 3, left: 3, right: 3 },
        },
        alternateRowStyles: {
          fillColor: [255, 251, 235], // yellow-50
        },
        columnStyles: {
          0:  { halign: 'center', cellWidth: 8 },   // No
          1:  { cellWidth: 22 },                     // Tgl Daftar
          2:  { cellWidth: 32, fontStyle: 'bold' },  // Nama
          3:  { cellWidth: 32 },                     // Instansi
          4:  { cellWidth: 22 },                     // No. HP
          5:  { cellWidth: 22 },                     // Tujuan
          6:  { cellWidth: 40 },                     // Keperluan
          7:  { cellWidth: 28 },                     // Janji Temu
          8:  { cellWidth: 18, halign: 'center' },   // Rombongan
          9:  { cellWidth: 20, halign: 'center' },   // Status
        },
        didParseCell(data) {
          // Warnai kolom status
          if (data.section === 'body' && data.column.index === 9) {
            const v = (data.cell.raw || '').toString().toLowerCase();
            if (v.includes('disetujui')) {
              data.cell.styles.textColor = [22, 101, 52];
              data.cell.styles.fillColor = [220, 252, 231];
            } else if (v.includes('ditolak')) {
              data.cell.styles.textColor = [153, 27, 27];
              data.cell.styles.fillColor = [254, 226, 226];
            } else if (v.includes('pending')) {
              data.cell.styles.textColor = [120, 53, 15];
              data.cell.styles.fillColor = [254, 249, 195];
            }
          }
        },
        didDrawPage(data) {
          // Gambar frame di setiap halaman baru
          const cur = doc.internal.getCurrentPageInfo().pageNumber;
          drawPageFrame(cur, 1); // total diupdate setelah selesai
        },
      });

      // ── Update total halaman di semua halaman ──────────────────────────
      const totalPages = doc.internal.getNumberOfPages();
      for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        // Hapus teks footer lama dengan rect putih, lalu tulis ulang dengan total yg benar
        doc.setFillColor(...GOLD);
        doc.rect(0, H - 10, W, 10, 'F');
        doc.setTextColor(...WHITE);
        doc.setFontSize(7);
        doc.setFont('helvetica', 'normal');
        doc.text('Sekretariat DPRD Provinsi Lampung  |  e-Tamu Digital', 10, H - 3.5);
        doc.text('Dicetak: <?= date('d M Y, H:i') ?> WIB  |  Hal. ' + i + ' / ' + totalPages, W - 10, H - 3.5, { align: 'right' });
      }

      doc.save('Rekap_Tamu_<?= $rek_tahun ?>_<?= $rek_bstr ?>.pdf');
    }
  </script>
<?php endif; ?>

<!-- ── FOOTER ──────────────────────────────────────────────────────────────── -->
<footer class="text-center py-6 no-print">
  <p class="text-xs text-gray-400">© 2026 DPRD Provinsi Lampung. Seluruh hak cipta dilindungi.</p>
</footer>

<script>
  // Blokir tombol back browser setelah logout
  history.pushState(null, null, location.href);
  window.addEventListener('popstate', () => history.pushState(null, null, location.href));

  // Toggle daftar nama anggota rombongan (dashboard)
  function toggleAnggota(id) {
    const el   = document.getElementById('anggota-' + id);
    const icon = document.getElementById('icon-' + id);
    if (el.classList.contains('hidden')) {
      el.classList.remove('hidden');
      icon.textContent = '▼';
    } else {
      el.classList.add('hidden');
      icon.textContent = '▶';
    }
  }

  // Countdown auto-refresh (dashboard saja)
  <?php if ($mode === 'dashboard'): ?>
  let sisa = 5;
  const elCD = document.getElementById('countdown');
  setInterval(() => { sisa--; if (elCD) elCD.textContent = sisa; }, 1000);
  <?php endif; ?>
</script>
</body>
</html>