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
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 340px;
            background: #ffffff;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Tengah vertikal */
            align-items: center;
            padding: 0;
            z-index: 1;
        }

        .sidebar .nav-link {
            /* abu-abu untuk tidak aktif */
            color: #888;
            /* abu-abu gelap */
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: background 0.2s, color 0.2s;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: #fff !important;
        }

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

    session_start();
    include_once __DIR__ . '/../../config/config.php';

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

                // Ambil ToDo List milik karyawan ini
                $stmt4 = $conn->prepare("SELECT * FROM todolist WHERE id_karyawan = ? ORDER BY id DESC");
                $stmt4->bind_param("s", $id_karyawan);
                $stmt4->execute();
                $result4 = $stmt4->get_result();
                while ($row4 = $result4->fetch_assoc()) {
                    $todolist[] = $row4;
                }
            } else {
                $display_name = $username;
            }
        }
    } else {
        header("Location: index.php");
        exit();
    }

    // CRUD ToDo List

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_laporan'])) {
        $laporan = trim($_POST['laporan']);
        if ($laporan !== '') {
            $stmt = $conn->prepare("INSERT INTO todolist (id_karyawan, pekerjaan, status) VALUES (?, ?, 'proses')");
            $stmt->bind_param("ss", $id_karyawan, $laporan);
            $stmt->execute();
            header("Location: dashboard.php");
            exit();
        }
    }
    if (isset($_GET['hapus_laporan'])) {
        $id_laporan = $_GET['hapus_laporan'];
        $stmt = $conn->prepare("DELETE FROM todolist WHERE id = ? AND id_karyawan = ?");
        $stmt->bind_param("is", $id_laporan, $id_karyawan);
        $stmt->execute();
        header("Location: dashboard.php");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_laporan'])) {
        $id_laporan = $_POST['id_laporan'];
        $laporan = trim($_POST['laporan']);
        if ($laporan !== '') {
            $stmt = $conn->prepare("UPDATE todolist SET pekerjaan = ? WHERE id = ? AND id_karyawan = ?");
            $stmt->bind_param("sis", $laporan, $id_laporan, $id_karyawan);
            $stmt->execute();
            header("Location: dashboard.php");
            exit();
        }
    }
    if (isset($_GET['selesai_laporan'])) {
        $id_laporan = $_GET['selesai_laporan'];
        $stmt = $conn->prepare("UPDATE todolist SET status = 'selesai' WHERE id = ? AND id_karyawan = ?");
        $stmt->bind_param("is", $id_laporan, $id_karyawan);
        $stmt->execute();
        header("Location: dashboard.php");
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
                            <h3><?= htmlspecialchars($status_kehadiran) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-header bg-success text-white">Gaji Terakhir Diterima</div>
                        <div class="card-body">
                            <h3>Rp.<?= number_format($gaji_terakhir, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Pekerjaan -->
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

</body>

</html>