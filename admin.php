<?php
// admin.php - Dashboard Admin e-Tamu DPRD Provinsi Lampung
session_start();

// Header anti-cache — mencegah browser menyimpan halaman admin
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// Cek login — jika belum login redirect ke halaman login
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Proses approve / reject / hapus
if (isset($_GET['aksi']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $aksi = $_GET['aksi'];
    $id   = (int) $_GET['id'];

    if (in_array($aksi, ['approved', 'rejected', 'pending'])) {
        mysqli_query($conn, "UPDATE tamu SET status='$aksi' WHERE id=$id");
        header("Location: admin.php?msg=berhasil");
    } elseif ($aksi === 'hapus') {
        // Hapus foto juga kalau ada
        $r = mysqli_query($conn, "SELECT foto_identitas FROM tamu WHERE id=$id");
        $d = mysqli_fetch_assoc($r);
        if (!empty($d['foto_identitas'])) {
            $path = 'uploads/identitas/' . $d['foto_identitas'];
            if (file_exists($path)) unlink($path);
        }
        mysqli_query($conn, "DELETE FROM tamu WHERE id=$id");
        header("Location: admin.php?msg=hapus");
    } else {
        header("Location: admin.php");
    }
    exit();
}

// Filter status
$filter = $_GET['filter'] ?? 'semua';
$where  = "";
if (in_array($filter, ['pending', 'approved', 'rejected'])) {
    $where = "WHERE status = '$filter'";
}

$result   = mysqli_query($conn, "SELECT * FROM tamu $where ORDER BY tanggal_daftar DESC");
$total    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu"))['c'];
$pending  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='pending'"))['c'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='approved'"))['c'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tamu WHERE status='rejected'"))['c'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="refresh" content="5" />
  <title>Admin - e-Tamu DPRD Provinsi Lampung</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Public Sans', sans-serif; }
    .modal-bg { background: rgba(0,0,0,0.6); }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- NAVBAR -->
  <nav class="bg-yellow-700 text-white px-6 py-4 flex items-center justify-between shadow">
    <div class="flex items-center gap-3">
      <img src="img/logodrpd.jpg" alt="Logo" class="w-10 h-10 object-contain rounded-full bg-white p-1" />
      <div>
        <p class="font-bold text-lg leading-tight">Dashboard Admin</p>
        <p class="text-xs text-yellow-200">e-Tamu DPRD Provinsi Lampung</p>
      </div>
    </div>
    <a href="logout.php"
      class="text-sm bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition font-semibold">
      🚪 Logout
    </a>
  </nav>

  <!-- COUNTDOWN REFRESH -->
  <div class="bg-yellow-50 border-b border-yellow-200 px-6 py-2 flex items-center justify-between text-xs text-yellow-700">
    <span>🔄 Halaman refresh otomatis setiap 5 detik</span>
    <span>Refresh dalam: <strong id="countdown">5</strong> detik</span>
  </div>

  <div class="max-w-7xl mx-auto px-4 py-6">

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['msg'])): ?>
    <div class="mb-4 p-3 rounded-lg text-sm <?= $_GET['msg'] === 'hapus' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
      <?= $_GET['msg'] === 'hapus' ? '🗑️ Data tamu berhasil dihapus.' : '✅ Status berhasil diperbarui.' ?>
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
      $filters = ['semua' => 'Semua', 'pending' => '⏳ Pending', 'approved' => '✅ Disetujui', 'rejected' => '❌ Ditolak'];
      foreach ($filters as $key => $label):
        $active = ($filter === $key) ? 'bg-yellow-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
      ?>
      <a href="admin.php?filter=<?= $key ?>"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= $active ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TABEL -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-yellow-700 text-white">
            <tr>
              <th class="px-4 py-3 text-left">No</th>
              <th class="px-4 py-3 text-left">Foto ID</th>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">Instansi</th>
              <th class="px-4 py-3 text-left">No. HP</th>
              <th class="px-4 py-3 text-left">Tujuan</th>
              <th class="px-4 py-3 text-left">Keperluan</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Tanggal</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php
            $no = 1;
            $ada_data = false;
            while ($row = mysqli_fetch_assoc($result)):
              $ada_data = true;
              $foto = $row['foto_identitas'] ?? null;
              $foto_path = $foto ? 'uploads/identitas/' . $foto : null;
              $ext = $foto ? strtolower(pathinfo($foto, PATHINFO_EXTENSION)) : '';
            ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 text-gray-500"><?= $no++ ?></td>

              <!-- FOTO IDENTITAS -->
              <td class="px-4 py-3">
                <?php if ($foto && in_array($ext, ['jpg','jpeg','png'])): ?>
                  <img src="<?= htmlspecialchars($foto_path) ?>"
                    alt="Foto ID"
                    class="w-12 h-12 object-cover rounded-lg border cursor-pointer hover:opacity-80 transition"
                    onclick="bukaFoto('<?= htmlspecialchars($foto_path) ?>', '<?= htmlspecialchars($row['full_name']) ?>')" />
                <?php elseif ($foto && $ext === 'pdf'): ?>
                  <a href="<?= htmlspecialchars($foto_path) ?>" target="_blank"
                    class="inline-flex items-center gap-1 text-yellow-700 hover:underline text-xs font-semibold">
                    📄 Lihat PDF
                  </a>
                <?php else: ?>
                  <span class="text-gray-300 text-xs">—</span>
                <?php endif; ?>
              </td>

              <td class="px-4 py-3 font-semibold text-gray-800 whitespace-nowrap"><?= htmlspecialchars($row['full_name']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($row['organization']) ?></td>

              <!-- NO HP -->
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

              <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($row['destination'] ?? '—') ?></td>
              <td class="px-4 py-3 text-gray-600 max-w-xs truncate"><?= htmlspecialchars($row['purpose']) ?></td>

              <td class="px-4 py-3">
                <?php if ($row['status'] === 'approved'): ?>
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Disetujui</span>
                <?php elseif ($row['status'] === 'rejected'): ?>
                  <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>
                <?php else: ?>
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Pending</span>
                <?php endif; ?>
              </td>

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
              <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                Tidak ada data tamu.
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MODAL PREVIEW FOTO -->
  <div id="modalFoto" class="hidden fixed inset-0 modal-bg z-50 flex items-center justify-center p-4"
    onclick="tutupFoto()">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-4" onclick="event.stopPropagation()">
      <div class="flex justify-between items-center mb-3">
        <p id="modalNama" class="font-bold text-gray-800 text-sm"></p>
        <button onclick="tutupFoto()" class="text-gray-400 hover:text-gray-700 text-xl font-bold">✕</button>
      </div>
      <img id="modalImg" src="" alt="Foto Identitas"
        class="w-full max-h-96 object-contain rounded-lg border" />
      <a id="modalLink" href="" target="_blank"
        class="inline-block mt-3 text-xs text-yellow-700 hover:underline">
        🔗 Buka di tab baru
      </a>
    </div>
  </div>

  <footer class="text-center py-6">
    <p class="text-xs text-gray-400">© 2026 DPRD Provinsi Lampung. Seluruh hak cipta dilindungi.</p>
  </footer>

  <script>
    // Blokir tombol back browser setelah logout
    history.pushState(null, null, location.href);
    window.addEventListener('popstate', () => {
      history.pushState(null, null, location.href);
    });
    function bukaFoto(src, nama) {
      document.getElementById('modalImg').src  = src;
      document.getElementById('modalLink').href = src;
      document.getElementById('modalNama').textContent = '🪪 Foto Identitas — ' + nama;
      document.getElementById('modalFoto').classList.remove('hidden');
    }
    function tutupFoto() {
      document.getElementById('modalFoto').classList.add('hidden');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupFoto(); });

    // Countdown auto refresh
    let sisa = 5;
    const el = document.getElementById('countdown');
    setInterval(() => {
      sisa--;
      if (el) el.textContent = sisa;
    }, 1000);
  </script>
</body>
</html>