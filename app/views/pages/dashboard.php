<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Dashboard</title>

    <style>
        body {
            margin-left: 220px;
        }

        strong {
            color: #E6E6FA;
        }
    </style>
</head>

<body>



    <!-- components -->
    <?php
    require_once __DIR__ . '/../components/navbar.php';
    require_once __DIR__ . '/../components/footer.php';

    include_once __DIR__ . '/../../config/config.php';

    require_once __DIR__ . '/../../controllers/laporanPekerjaanController.php';
    $todolistController = new ToDoListController($conn);
    $todolistController->handleRequest($id_karyawan);
    $todolist = $todolistController->getAll($id_karyawan);

    $display_name = '';
    $jam_kerja = 0;
    $status_kehadiran = "Belum Absen";
    $gaji_terakhir = 0;
    $todolist = [];
    $id_karyawan = null; // Tambahkan inisialisasi

    if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
        $username = $_SESSION['user'];
        $role = $_SESSION['role'];

        if ($role === 'admin') {
            $display_name = $username;
        } else {
            // Ambil nama dan id_karyawan dari tabel employees
            $stmt = $conn->prepare("SELECT nama, id_karyawan FROM employees WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $display_name = $row['nama'];
                $id_karyawan = $row['id_karyawan'];

                // Cek status kehadiran hari ini
                $stmt2 = $conn->prepare("SELECT status FROM kehadiran WHERE id_karyawan = ? AND tanggal = CURDATE() LIMIT 1");
                $stmt2->bind_param("s", $id_karyawan);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($row2 = $result2->fetch_assoc()) {
                    if ($row2['status'] === 'Diterima') {
                        $status_kehadiran = "<span class='text-success fw-bold'>Telah Absen</span>";
                    } elseif ($row2['status'] === 'Hadir') {
                        $status_kehadiran = "<span class='text-warning fw-bold'>Menunggu Konfirmasi</span>";
                    } elseif ($row2['status'] === 'Ditolak') {
                        $status_kehadiran = "<span class='text-danger fw-bold'>Absensi Ditolak</span>";
                    } else {
                        $status_kehadiran = htmlspecialchars($row2['status']);
                    }
                } else {
                    $status_kehadiran = "Belum Absen";
                }
            } else {
                $display_name = $username;
            }
        }
    } else {
        header("Location: index.php");
        exit();
    }

    ?>

    <?php if ($role === 'admin'): ?>
        <div class="d-flex justify-content-center align-items-center" style="height:100vh;">
            <h1 class="text-white text-center">
                Selamat datang, <strong style="margin-left:10px;"><?php echo htmlspecialchars($display_name) ?></strong>
            </h1>
        </div>
    <?php else: ?>
        <h1 class="text-white text-center d-flex justify-content-center align-items-center" style="height:10dvh;">
            Selamat datang, <strong style="margin-left:10px;"><?php echo htmlspecialchars($display_name) ?></strong>
        </h1>
    <?php endif; ?>

    <?php if ($role !== 'admin'): ?>
        <?php
        // Ambil jam_datang dan jam_pulang hari ini
        $stmt3 = $conn->prepare("SELECT jam_datang, jam_pulang FROM kehadiran WHERE id_karyawan = ? AND tanggal = CURDATE() AND status = 'Diterima'");
        $stmt3->bind_param("s", $id_karyawan);
        $stmt3->execute();
        $stmt3->bind_result($jam_datang, $jam_pulang);
        if ($stmt3->fetch() && $jam_datang && $jam_pulang) {
            $start = new DateTime($jam_datang);
            $end = new DateTime($jam_pulang);
            $interval = $start->diff($end);
            $jam_kerja = $interval->h;
        } else {
            $jam_kerja = 0;
        }
        $stmt3->close();

        ?>
        <div class="container mt-4">

            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <span>Laporan Pekerjaan</span>
                <a href="export_laporan.php" class="btn btn-outline-light btn-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </a>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-header bg-primary text-white">Jam Kerja Hari Ini</div>
                        <div class="card-body">
                            <h3><?= (int)$jam_kerja ?> Jam</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-header bg-info text-white">Status Kehadiran</div>
                        <div class="card-body">
                            <h3><?= $status_kehadiran ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">Laporan Pekerjaan</div>
                        <div class="card-body">
                            <form method="POST" class="d-flex mb-3">
                                <input type="text" name="laporan" class="form-control me-2" placeholder="Tambah laporan pekerjaan..." required>
                                <button type="submit" name="tambah_laporan" class="btn btn-primary">Tambah</button>
                            </form>
                            <ul class="list-group">
                                <?php foreach ($todolist as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <form method="POST" class="d-flex flex-grow-1">
                                            <input type="hidden" name="id_laporan" value="<?= $item['id'] ?>">
                                            <input type="text" name="laporan" class="form-control me-2 <?= $item['status'] == 'selesai' ? 'text-decoration-line-through text-secondary' : '' ?>"
                                                value="<?= htmlspecialchars($item['pekerjaan']) ?>" <?= $item['status'] == 'selesai' ? 'readonly' : '' ?> required>
                                            <?php if ($item['status'] != 'selesai'): ?>
                                                <button type="submit" name="edit_laporan" class="btn btn-warning btn-sm me-2">Edit</button>
                                            <?php endif; ?>
                                        </form>
                                        <?php if ($item['status'] != 'selesai'): ?>
                                            <a href="?selesai_laporan=<?= $item['id'] ?>" class="btn btn-success btn-sm me-2">Selesai</a>
                                        <?php endif; ?>
                                        <a href="?hapus_laporan=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus laporan ini?')">Hapus</a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($todolist)): ?>
                                    <li class="list-group-item text-center text-muted">Belum ada laporan pekerjaan</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>

            <!-- Laporan Pekerjaan -->

</body>

</html>