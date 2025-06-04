<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'karyawan'; // default karyawan jika belum login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 340px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-right: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            margin-bottom: 8px;
            padding: 12px 24px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <nav class="sidebar">
    <div class="sidebar-header p-3">
        <h1 style="margin-left:15px;" class="fw-bold fixed-top mt-5">PayKaryawan</h1>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item my-2">
            <a class="nav-link fs-5 <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
        </li>
        <?php if ($role === 'admin'): ?>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'karyawan.php') ? 'active' : ''; ?>" href="karyawan.php">Karyawan</a>
            </li>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'absensi.php') ? 'active' : ''; ?>" href="absensi.php">Absensi</a>
            </li>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'gaji.php') ? 'active' : ''; ?>" href="gaji.php">Gaji</a>
            </li>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'cuti.php') ? 'active' : ''; ?>" href="cuti.php">Cuti dan Ketidakhadiran</a>
            </li>
        <?php else: ?>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'absensi.php') ? 'active' : ''; ?>" href="absensi.php">Absen</a>
            </li>
            <li class="nav-item my-2">
                <a class="nav-link fs-5 <?php echo ($current_page == 'cuti.php') ? 'active' : ''; ?>" href="cuti.php">Pengajuan Cuti dan Ijin</a>
            </li>
        <?php endif; ?>
        <li class="nav-item my-2 logout">
            <a class="nav-link fs-5" style="color: red;" href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
</body>
</html>