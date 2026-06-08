<?php
// status.php - Status Pendaftaran DPRD Provinsi Lampung

include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$data  = null;
$error = "";

// AMBIL DATA BERDASARKAN ID
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

// DATA
$status    = $data['status'] ?? 'pending';
$nama      = $data['full_name'] ?? '-';
$instansi  = $data['organization'] ?? '-';
$tujuan    = $data['destination'] ?? '-';
$keperluan = $data['purpose'] ?? '-';
$tanggal   = $data['tanggal_daftar'] ?? '';

// FORMAT TANGGAL INDONESIA
$bulan = [
    1 => 'Januari',
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

if (!empty($tanggal)) {

    $time = strtotime($tanggal);

    $tanggal_indonesia =
        date('d', $time) . ' ' .
        $bulan[(int)date('m', $time)] . ' ' .
        date('Y - H:i:s', $time) . ' WIB';

} else {

    $tanggal_indonesia = '-';
}

// STATUS MAP
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Status Pendaftaran - DPRD Provinsi Lampung
    </title>

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            min-height:100vh;
            font-family:'Outfit',sans-serif;
            background:
            radial-gradient(circle at top right, rgba(212,166,63,.10), transparent 30%),
            radial-gradient(circle at bottom left, rgba(212,166,63,.08), transparent 25%),
            #f8f5ef;
            color:#1e293b;
        }

        .tapis-top{
            width:100%;
            height:6px;
            background:
            repeating-linear-gradient(
            90deg,
            #8B6B0F 0px,
            #8B6B0F 12px,
            #D4A63F 12px,
            #D4A63F 22px,
            #F4D27A 22px,
            #F4D27A 32px
            );
        }

        .page{
            width:100%;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:30px 18px;
        }

        .card{
            width:100%;
            max-width:560px;
            background:rgba(255,255,255,.88);
            backdrop-filter:blur(18px);
            border-radius:34px;
            overflow:hidden;
            box-shadow:
            0 20px 60px rgba(0,0,0,.10),
            inset 0 1px 0 rgba(255,255,255,.4);
            animation:fadeUp .7s ease;
        }

        .hero{
            position:relative;
            overflow:hidden;
            padding:40px 28px 110px;

            background:
            linear-gradient(
            135deg,
            rgba(15,23,42,.94),
            rgba(30,41,59,.88),
            rgba(120,75,0,.72)
            ),
            url('img/gedung.jpg');

            background-size:cover;
            background-position:center;
        }

        .hero::before{
            content:'';
            position:absolute;
            inset:0;

            background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);

            background-size:34px 34px;
        }

        .glow{
            position:absolute;
            top:-60px;
            right:-60px;
            width:220px;
            height:220px;
            border-radius:50%;
            background:rgba(255,215,0,.20);
            filter:blur(70px);
        }

        .hero-content{
            position:relative;
            z-index:2;
            text-align:center;
            color:white;
        }

        .logo{
            width:92px;
            height:92px;
            margin:0 auto 18px;
            border-radius:28px;
            background:rgba(255,255,255,.15);
            display:flex;
            align-items:center;
            justify-content:center;
            backdrop-filter:blur(8px);
        }

        .logo img{
            width:62px;
            height:62px;
            object-fit:contain;
        }

        .hero small{
            letter-spacing:4px;
            text-transform:uppercase;
            font-size:11px;
            opacity:.8;
        }

        .hero h1{
            margin-top:10px;
            font-family:'Cormorant Garamond',serif;
            font-size:52px;
            line-height:1;
        }

        .hero h1 span{
            color:#f5d37a;
            font-style:italic;
        }

        .hero p{
            margin-top:12px;
            font-size:14px;
            line-height:1.7;
            opacity:.86;
        }

        .body{
            position:relative;
            z-index:5;
            margin-top:-70px;
            padding:0 24px 28px;
        }

        .profile{
            background:white;
            border-radius:28px;
            padding:24px;
            box-shadow:0 14px 40px rgba(0,0,0,.08);
        }

        .user{
            display:flex;
            align-items:center;
            gap:16px;
        }

        .avatar{
            width:72px;
            height:72px;
            border-radius:24px;
            background:linear-gradient(135deg,#111827,#374151);
            color:white;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            font-weight:700;
            flex-shrink:0;
        }

        .user h2{
            font-size:24px;
            font-weight:700;
        }

        .user p{
            font-size:14px;
            color:#64748b;
            margin-top:4px;
        }

        .detail-list{
            margin-top:24px;
            display:flex;
            flex-direction:column;
            gap:16px;
        }

        .detail-item{
            display:flex;
            gap:14px;
            align-items:flex-start;
            padding:14px;
            border-radius:18px;
            background:#faf8f4;
        }

        .detail-icon{
            width:46px;
            height:46px;
            border-radius:14px;
            background:white;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:20px;
            flex-shrink:0;
        }

        .detail-label{
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:2px;
            color:#94a3b8;
            font-weight:700;
        }

        .detail-value{
            margin-top:4px;
            font-size:14px;
            line-height:1.7;
            color:#334155;
            font-weight:500;
        }

        .status-card{
            margin-top:24px;
            border-radius:30px;
            padding:28px 24px;
            text-align:center;
            overflow:hidden;
            position:relative;
            box-shadow:0 14px 40px rgba(0,0,0,.08);
            transition:.3s;
        }

        .status-approved{
            background:linear-gradient(135deg,#ecfdf5,#d1fae5);
            color:#047857;
        }

        .status-rejected{
            background:linear-gradient(135deg,#fef2f2,#fee2e2);
            color:#b91c1c;
        }

        .status-pending{
            background:linear-gradient(135deg,#fff7ed,#ffedd5);
            color:#b45309;
        }

        .status-icon{
            width:96px;
            height:96px;
            margin:0 auto 18px;
            border-radius:50%;
            background:white;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
        }

        .spinner-wrap{
            position:relative;
            width:56px;
            height:56px;
        }

        .spinner{
            width:56px;
            height:56px;
            border-radius:50%;
            border:5px solid rgba(245,158,11,.18);
            border-top:5px solid #f59e0b;
            animation:spin 1s linear infinite;
        }

        .spinner-dot{
            position:absolute;
            top:50%;
            left:50%;
            width:10px;
            height:10px;
            border-radius:50%;
            background:#f59e0b;
            transform:translate(-50%,-50%);
            animation:pulse 1.3s ease infinite;
        }

        .status-title{
            font-size:30px;
            font-weight:800;
            margin-bottom:10px;
        }

        .status-desc{
            font-size:14px;
            line-height:1.7;
            max-width:340px;
            margin:auto;
        }

        .btn-secondary{
            width:100%;
            margin-top:20px;
            padding:16px;
            border-radius:18px;
            background:white;
            border:1px solid #e5e7eb;
            text-decoration:none;
            color:#374151;
            font-weight:700;
            display:flex;
            align-items:center;
            justify-content:center;
            transition:.25s;
            cursor:pointer;
        }

        .btn-secondary:hover{
            border-color:#d4a63f;
            color:#b7791f;
        }

        .footer{
            margin-top:24px;
            text-align:center;
            font-size:12px;
            color:#94a3b8;
        }

        @keyframes spin{
            100%{
                transform:rotate(360deg);
            }
        }

        @keyframes pulse{

            0%,100%{
                transform:translate(-50%,-50%) scale(1);
                opacity:1;
            }

            50%{
                transform:translate(-50%,-50%) scale(1.5);
                opacity:.5;
            }
        }

        @keyframes fadeUp{
            from{
                opacity:0;
                transform:translateY(25px);
            }
            to{
                opacity:1;
                transform:translateY(0);
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
                    secara realtime dan profesional.
                </p>

            </div>

        </div>

        <div class="body">

            <?php if($error): ?>

                <div class="profile">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php else: ?>

            <div class="profile">

                <div class="user">

                    <div class="avatar">
                        <?= strtoupper(substr($nama,0,1)) ?>
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
                                <?= $tanggal_indonesia ?>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="status-card status-<?= $current['class'] ?>">

                <div class="status-icon">

                    <?php if($status == 'pending'): ?>

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

            <!-- BUTTON REFRESH -->
            <button
                onclick="refreshPage()"
                class="btn-secondary">

                ↻ Refresh Halaman

            </button>

            <!-- BUTTON KEMBALI -->
            <a href="index.php" class="btn-secondary">
                ← Kembali ke Beranda
            </a>

            <?php endif; ?>

            <div class="footer">
                © 2026 DPRD Provinsi Lampung · Sistem Buku Tamu Digital
            </div>

        </div>

    </div>

</div>

<script>

let currentStatus = "<?= $status ?>";

const currentId = <?= isset($id) ? $id : 0 ?>;

// REFRESH HALAMAN
function refreshPage(){

    location.reload();

}

// AUTO CEK STATUS
async function checkRealtimeStatus(){

    try{

        const response =
        await fetch("status_check.php?id=" + currentId);

        const result = await response.json();

        if(result.success){

            if(result.status !== currentStatus){

                currentStatus = result.status;

                updateStatusUI(result.status);
            }
        }

    }catch(error){

        console.log("Realtime error");

    }

}

function updateStatusUI(status){

    const statusCard  = document.querySelector(".status-card");

    const statusTitle = document.querySelector(".status-title");

    const statusDesc  = document.querySelector(".status-desc");

    const statusIcon  = document.querySelector(".status-icon");

    statusCard.classList.remove(
        "status-approved",
        "status-rejected",
        "status-pending"
    );

    // APPROVED
    if(status === "approved"){

        statusCard.classList.add("status-approved");

        statusTitle.innerHTML = "Disetujui";

        statusDesc.innerHTML =
        "Pendaftaran Anda telah diverifikasi dan disetujui.";

        statusIcon.innerHTML =
        '<div style="font-size:46px;font-weight:700;">✓</div>';
    }

    // REJECTED
    else if(status === "rejected"){

        statusCard.classList.add("status-rejected");

        statusTitle.innerHTML = "Ditolak";

        statusDesc.innerHTML =
        "Mohon hubungi sekretariat untuk informasi lebih lanjut.";

        statusIcon.innerHTML =
        '<div style="font-size:46px;font-weight:700;">✕</div>';
    }

    // PENDING
    else{

        statusCard.classList.add("status-pending");

        statusTitle.innerHTML = "Menunggu Persetujuan";

        statusDesc.innerHTML =
        "Permintaan Anda sedang diproses oleh admin.";

        statusIcon.innerHTML = `
            <div class="spinner-wrap">
                <div class="spinner"></div>
                <div class="spinner-dot"></div>
            </div>
        `;
    }

}

// CEK STATUS REALTIME SETIAP 2 DETIK
setInterval(checkRealtimeStatus, 2000);

</script>

</body>
</html>