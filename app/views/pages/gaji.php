<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Akses ditolak');
}
require_once __DIR__ . '/../../../app/config/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Gaji Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./css/style.css">
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
            align-items: center;
            padding: 0;
            z-index: 1;
        }
        .sidebar .nav-link { color: #888; border-radius: 0.5rem; margin-bottom: 0.5rem; transition: background 0.2s, color 0.2s; }
        .sidebar .nav-link.active { background: linear-gradient(135deg, #764ba2, #667eea); color: #fff !important; }
        body { margin-left: 220px; }
        strong { color: #E6E6FA; }
    </style>
</head>

<body>
    <?php
    require_once __DIR__ . '/../components/navbar.php';
    require_once __DIR__ . '/../components/footer.php';
    ?>

    <div class="container mt-4">
        <a href="gaji_pdf.php" target="_blank" class="btn btn-danger mb-3">
            <i class="bi bi-file-earmark-pdf"></i> Download PDF
        </a>

        <div class="card" style="max-width: 1100px; margin: 0 auto;">
            <div class="card-header bg-secondary text-white text-center">
                Rekap Gaji Karyawan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" style="font-size:1.1rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:60px;">No</th>
                                <th>Nama Karyawan</th>
                                <th>ID Karyawan</th>
                                <th>Jumlah Kehadiran</th>
                                <th>Jam Kerja</th>
                                <th>Gaji</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $gaji_per_jam = [
                                'manajer' => 85227,
                                'asisten manajer' => 56818,
                                'supervisor' => 45455,
                                'staff' => 34091,
                                'admn' => 28409,
                                'office boy' => 17045,
                                'office girl' => 17045
                            ];

                            $sql =  "
                                SELECT 
                                    e.id_karyawan, 
                                    e.nama, 
                                    e.jabatan,
                                    COUNT(a.id) AS jumlah_kehadiran,
                                    IFNULL(SUM(a.jam_kerja),0) AS total_jam_kerja
                                FROM employees e
                                LEFT JOIN kehadiran a 
                                    ON e.id_karyawan = a.id_karyawan 
                                    AND a.status = 'Diterima'
                                GROUP BY e.id_karyawan, e.nama, e.jabatan
                                ORDER BY e.nama ASC
                            ";
                            $result = $conn->query($sql);
                            $no = 1;
                            if ($result && $result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $jabatan = strtolower($row['jabatan']);
                                    $gaji_jam = $gaji_per_jam[$jabatan] ?? 0;
                                    $gaji = $gaji_jam * (int)$row['total_jam_kerja'];
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['id_karyawan']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['jumlah_kehadiran']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['total_jam_kerja']) ?></td>
                                    <td>Rp <?= number_format($gaji, 0, ',', '.') ?></td>
                                </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <span class="fw-semibold">Tidak ada data gaji karyawan</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>