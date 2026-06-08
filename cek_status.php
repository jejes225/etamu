<?php
include 'koneksi.php';

$searched = false;
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari'])) {

    $keyword = mysqli_real_escape_string($conn, $_POST['cari']);

    $sql = "SELECT *
            FROM tamu
            WHERE full_name LIKE '%$keyword%'
            ORDER BY tanggal_daftar DESC";

    $query = mysqli_query($conn, $sql);

    $searched = true;

    if ($query && mysqli_num_rows($query) > 0) {

        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cek Status Tamu - DPRD Provinsi Lampung</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(212, 175, 55, 0.08), transparent 30%),
                linear-gradient(to bottom, #f8f5ef, #f3efe6);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .gold-line {
            height: 5px;
            width: 100%;
            background:
                repeating-linear-gradient(90deg,
                    #d4af37 0px,
                    #d4af37 10px,
                    #8b6b10 10px,
                    #8b6b10 18px,
                    #e7c96d 18px,
                    #e7c96d 28px);
        }

        .main-card {
            backdrop-filter: blur(18px);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .hero {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg,
                    rgba(15, 23, 42, 0.94),
                    rgba(30, 41, 59, 0.88),
                    rgba(161, 98, 7, 0.72)),
                url('img/gedung.jpg');
            background-size: cover;
            background-position: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);

            background-size: 42px 42px;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            background: rgba(255, 215, 0, 0.18);
            filter: blur(70px);
            top: -120px;
            right: -80px;
        }

        .title-main {
            font-family: 'Cormorant Garamond', serif;
        }

        .glass {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.94);
        }

        .search-input {
            transition: .25s ease;
        }

        .search-input:focus {
            transform: translateY(-1px);
            box-shadow:
                0 0 0 4px rgba(212, 175, 55, 0.15),
                0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .search-btn {
            transition: .25s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(161, 98, 7, 0.25);
        }

        .result-card {
            transition: .25s ease;
        }

        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.08);
        }

        .status-approved {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .status-rejected {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-pending {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .animate-fade {
            animation: fade .7s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #f59e0b;
            position: relative;
        }

        .pulse-dot::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: #f59e0b;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }

            100% {
                transform: scale(2.8);
                opacity: 0;
            }
        }

        .detail-btn{
            transition:.25s ease;
        }

        .detail-btn:hover{
            transform:translateY(-2px);
        }
    </style>
</head>

<body>

    <div class="gold-line"></div>

    <div class="min-h-screen px-4 py-6 sm:px-6">

        <div class="max-w-2xl mx-auto animate-fade">

            <a href="index.php"
                class="inline-flex items-center gap-2 mb-5 text-slate-700 hover:text-yellow-700 transition font-semibold text-sm sm:text-base">
                ← Kembali
            </a>

            <div class="main-card rounded-[30px] overflow-hidden">

                <div class="hero px-6 sm:px-10 pt-10 pb-24 text-white">

                    <div class="relative z-10 text-center">

                        <div class="w-24 h-24 sm:w-28 sm:h-28 bg-white rounded-[28px] shadow-2xl mx-auto flex items-center justify-center border border-white/20">
                            <img src="img/logodrpd.jpg"
                                alt="Logo DPRD"
                                class="w-16 h-16 sm:w-20 sm:h-20 object-contain">
                        </div>

                        <p class="mt-5 uppercase tracking-[4px] text-[11px] sm:text-xs font-semibold text-white/70">
                            Sistem Informasi Digital
                        </p>

                        <h1 class="title-main text-5xl sm:text-6xl font-bold leading-none mt-2">
                            <span style="color:#e7c96d;">e</span>-Tamu
                        </h1>

                        <p class="mt-2 text-white/75 italic text-sm sm:text-base">
                            DPRD Provinsi Lampung
                        </p>

                    </div>

                </div>

                <div class="px-4 sm:px-7 pb-8 -mt-14 relative z-20">

                    <div class="glass rounded-[28px] shadow-2xl border border-white/60 p-5 sm:p-6">

                        <form method="POST" action="" class="space-y-4">

                            <div>

                                <label class="block text-sm font-bold text-slate-700 mb-3">
                                    Cari Berdasarkan Nama Lengkap
                                </label>

                                <div class="relative">

                                    <input type="text"
                                        name="cari"
                                        required
                                        value="<?= htmlspecialchars($_POST['cari'] ?? '') ?>"
                                        placeholder="Masukkan nama lengkap..."
                                        class="search-input w-full h-14 sm:h-16 rounded-2xl border border-slate-200 bg-slate-50 px-5 pr-14 text-sm sm:text-[15px] focus:outline-none">

                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">
                                        🔍
                                    </div>

                                </div>

                            </div>

                            <button type="submit"
                                class="search-btn w-full h-14 sm:h-16 rounded-2xl bg-gradient-to-r from-yellow-600 via-yellow-700 to-amber-700 text-white font-bold tracking-wide shadow-xl">

                                Cari Status
                            </button>

                        </form>

                    </div>

                    <?php if ($searched): ?>

                        <div class="mt-7">

                            <?php if (empty($results)): ?>

                                <div class="glass rounded-[28px] p-8 sm:p-10 text-center shadow-xl border border-white/60">

                                    <div class="text-5xl sm:text-6xl mb-5">
                                        😔
                                    </div>

                                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800">
                                        Data Tidak Ditemukan
                                    </h2>

                                </div>

                            <?php else: ?>

                                <div class="space-y-5">

                                    <?php foreach ($results as $row): ?>

                                        <?php
                                        $status = $row['status'];

                                        if ($status === 'approved') {
                                            $statusClass = 'status-approved';
                                            $statusText = '✅ Disetujui';
                                        } elseif ($status === 'rejected') {
                                            $statusClass = 'status-rejected';
                                            $statusText = '❌ Ditolak';
                                        } else {
                                            $statusClass = 'status-pending';
                                            $statusText = '⏳ Pending';
                                        }
                                        ?>

                                        <div class="result-card glass rounded-[28px] border border-white/60 overflow-hidden shadow-xl">

                                            <div class="p-5 sm:p-6">

                                                <div class="flex items-start justify-between gap-4">

                                                    <div class="flex items-start gap-4 flex-1">

                                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-lg flex-shrink-0">
                                                            <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                                                        </div>

                                                        <div class="flex-1 min-w-0">

                                                            <h3 class="font-extrabold text-slate-800 text-base sm:text-lg break-words">
                                                                <?= htmlspecialchars($row['full_name']) ?>
                                                            </h3>

                                                            <p class="text-sm text-slate-500 mt-1 break-words">
                                                                🏢 <?= htmlspecialchars($row['organization']) ?>
                                                            </p>

                                                            <p class="text-xs text-slate-400 mt-2">
                                                                🕒 <?= date('d F Y - H:i', strtotime($row['tanggal_daftar'])) ?> WIB
                                                            </p>

                                                        </div>

                                                    </div>

                                                    <div
                                                        data-id="<?= $row['id'] ?>"
                                                        class="<?= $statusClass ?> mobile-status px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap status-box">

                                                        <?php if ($status == 'pending'): ?>

                                                            <div class="flex items-center gap-2">
                                                                <div class="pulse-dot"></div>
                                                                <?= $statusText ?>
                                                            </div>

                                                        <?php else: ?>

                                                            <?= $statusText ?>

                                                        <?php endif; ?>

                                                    </div>

                                                </div>

                                                <!-- BUTTON LIHAT STATUS -->
                                                <div class="mt-5">

                                                    <a href="status.php?id=<?= $row['id'] ?>"
                                                        class="detail-btn inline-flex items-center justify-center w-full h-12 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-900 text-white font-bold shadow-lg">

                                                        Lihat Detail Status →
                                                    </a>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

    <script>
        setInterval(() => {

            const statusElements = document.querySelectorAll('[data-id]');

            statusElements.forEach(el => {

                const id = el.dataset.id;

                fetch('get_status.php?id=' + id)
                    .then(res => res.json())
                    .then(data => {

                        if (data.success) {

                            let html = '';
                            let className = '';

                            if (data.status === 'approved') {

                                className = 'status-approved';
                                html = '✅ Disetujui';

                            } else if (data.status === 'rejected') {

                                className = 'status-rejected';
                                html = '❌ Ditolak';

                            } else {

                                className = 'status-pending';

                                html = `
                                <div class="flex items-center gap-2">
                                    <div class="pulse-dot"></div>
                                    ⏳ Pending
                                </div>
                            `;
                            }

                            el.className =
                                className +
                                ' mobile-status px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap status-box';

                            el.innerHTML = html;
                        }
                    });

            });

        }, 3000);
    </script>

</body>

</html>