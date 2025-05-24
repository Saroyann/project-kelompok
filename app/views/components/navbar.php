<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
    <div class="sidebar-header p-3">
        <h1 style="margin-left:15px;" class="fw-bold fixed-top mt-5">PayKaryawan</h1>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item my-2">
            <a class="nav-link fs-5 <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
        </li>
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
        <li class="nav-item my-2">
            <a class="nav-link fs-5 <?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>" href="pengaturan.php">Pengaturan</a>
        </li>
        <li class="nav-item my-2 logout">
            <a class="nav-link fs-5" style="color: red;" href="logout.php">Logout</a>
        </li>
    </ul>
</nav>