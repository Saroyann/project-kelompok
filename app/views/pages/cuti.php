<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>karyawan</title>
    <!-- Bootstrap -->
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
    ?>

    <?php if ($role === 'admin'): ?>
        <div class="container-fluid d-flex justify-content-center align-items-center" style="min-height:90vh;">
            <div class="col-lg-10 col-md-10 mx-auto">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        Rekap Pengajuan Cuti / Ijin
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:60px;">No</th>
                                        <th>Nama Karyawan</th>
                                        <th>ID Karyawan</th>
                                        <th>Jenis</th>
                                        <th>Lampiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query("SELECT c.*, e.nama FROM cuti c JOIN employees e ON c.id_karyawan = e.id_karyawan ORDER BY c.id DESC");
                                    $no = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                                <td><?= htmlspecialchars($row['id_karyawan']) ?></td>
                                                <td><?= htmlspecialchars($row['jenis']) ?></td>
                                                <td>
                                                    <?php if (!empty($row['file_lampiran'])): ?>
                                                        <a href="<?= htmlspecialchars($row['file_lampiran']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            Lihat Lampiran
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Tidak ada</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox" style="font-size:2rem;"></i><br>
                                                <span class="fw-semibold">Tidak ada pengajuan cuti / ijin dari karyawan</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="row justify-content-center w-100">
            <div class="col-md-7" style="max-width: 450px;">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Form Pengajuan Cuti / Ijin</h5>
                    </div>
                    <div class="card-body py-4">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="jenis" class="form-label">Jenis Pengajuan</label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Ijin">Ijin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                            <div class="mb-3">
                                <label for="keterangan_file" class="form-label">File Permohonan (PDF/DOCX)</label>
                                <input type="file" class="form-control" id="keterangan_file" name="keterangan_file" accept=".pdf,.docx" required>
                                <small class="form-text text-muted">Unggah file permohonan ijin/cuti (PDF atau DOCX, max 2MB)</small>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Ajukan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>