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
            align-items: center;
            padding: 0;
            z-index: 1;
        }

        .sidebar .nav-link {
            color: #888;
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

    <?php
    session_start();
    require_once __DIR__ . '/../../../app/config/config.php';

    $alert = null;

    // PERBAIKAN 1: Ambil role dan informasi karyawan dengan benar
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'karyawan';
    $username = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    // PERBAIKAN 2: Ambil id_karyawan dari database berdasarkan username
    $id_karyawan = null;
    if ($username) {
        $stmt = $conn->prepare("SELECT id_karyawan FROM employees WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();
            if ($employee) {
                $id_karyawan = $employee['id_karyawan'];
            }
            $stmt->close();
        }
    }

    // PERBAIKAN 3: Proses form pengajuan cuti/ijin dengan error handling yang lebih baik
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jenis'])) {
        try {
            // Debug: Tampilkan data yang diterima
            error_log("POST Data: " . print_r($_POST, true));
            error_log("FILES Data: " . print_r($_FILES, true));
            error_log("ID Karyawan: " . $id_karyawan);

            $jenis = $_POST['jenis'];
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_selesai = $_POST['tanggal_selesai'];
            $file_lampiran = null;

            // Validasi input
            if (!$id_karyawan) {
                throw new Exception("ID Karyawan tidak ditemukan. Silakan login ulang.");
            }

            if (empty($jenis) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
                throw new Exception("Semua field harus diisi.");
            }

            // Validasi tanggal
            if (strtotime($tanggal_mulai) > strtotime($tanggal_selesai)) {
                throw new Exception("Tanggal mulai tidak boleh lebih besar dari tanggal selesai.");
            }

            // PERBAIKAN 4: Upload file dengan error handling yang lebih baik
            if (isset($_FILES['keterangan_file']) && $_FILES['keterangan_file']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $file_type = $_FILES['keterangan_file']['type'];
                $file_size = $_FILES['keterangan_file']['size'];
                $max_size = 2 * 1024 * 1024; // 2MB

                // Validasi tipe file
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("Tipe file tidak diizinkan. Gunakan PDF atau DOCX.");
                }

                // Validasi ukuran file
                if ($file_size > $max_size) {
                    throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                }

                // Buat direktori jika belum ada
                $uploadDir = __DIR__ . '/../../../uploads/cuti/';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new Exception("Gagal membuat direktori upload.");
                    }
                }

                // Generate nama file unik
                $file_extension = pathinfo($_FILES['keterangan_file']['name'], PATHINFO_EXTENSION);
                $fileName = 'cuti_' . $id_karyawan . '_' . time() . '.' . $file_extension;
                $targetFile = $uploadDir . $fileName;

                // Upload file
                if (move_uploaded_file($_FILES['keterangan_file']['tmp_name'], $targetFile)) {
                    $file_lampiran = 'http://localhost/projek_kelompok/uploads/cuti/' . $fileName;
                    error_log("File uploaded successfully: " . $file_lampiran);
                } else {
                    throw new Exception("Gagal mengupload file. Periksa permission folder.");
                }
            } else {
                // Handle upload errors
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini)',
                    UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form)',
                    UPLOAD_ERR_PARTIAL => 'Upload tidak lengkap',
                    UPLOAD_ERR_NO_FILE => 'File lampiran harus diunggah',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
                ];

                $error_code = $_FILES['keterangan_file']['error'] ?? UPLOAD_ERR_NO_FILE;
                throw new Exception($upload_errors[$error_code] ?? 'Error upload tidak diketahui');
            }

            // PERBAIKAN 5: Simpan ke database dengan prepared statement yang benar
            if ($file_lampiran) {
                $stmt = $conn->prepare("INSERT INTO cuti (id_karyawan, jenis, tanggal_mulai, tanggal_selesai, file_lampiran, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("sssss", $id_karyawan, $jenis, $tanggal_mulai, $tanggal_selesai, $file_lampiran);

                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $alert = ['success', 'Pengajuan cuti/ijin berhasil dikirim!'];
                            error_log("Data berhasil disimpan ke database");
                        } else {
                            throw new Exception("Data tidak tersimpan ke database.");
                        }
                    } else {
                        throw new Exception("Gagal mengirim pengajuan. Database Error: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception("Gagal menyiapkan query database. Error: " . $conn->error);
                }
            } else {
                throw new Exception("File lampiran gagal diupload.");
            }
        } catch (Exception $e) {
            $alert = ['danger', $e->getMessage()];
            error_log("Cuti submission error: " . $e->getMessage());
        }
    }
    ?>

    <?php if ($role === 'admin'): ?>
        <div class="container d-flex justify-content-center align-items-center" style="min-height:90vh;">
            <div class="col-lg-10 col-md-12 mx-auto">
                <div class="card" style="max-width: 1100px; margin: 0 auto;">
                    <div class="card-header bg-secondary text-white text-center">
                        Rekap Pengajuan Cuti / Ijin
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" style="font-size:1.1rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:60px;">No</th>
                                        <th>Nama Karyawan</th>
                                        <th>ID Karyawan</th>
                                        <th>Jenis</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Lampiran</th>
                                        <th>Tanggal Pengajuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Ambil data pengajuan cuti/ijin dari database
                                    $result = $conn->query("SELECT c.*, e.nama FROM cuti c JOIN employees e ON c.id_karyawan = e.id_karyawan ORDER BY c.id DESC");
                                    $no = 1;
                                    if ($result && $result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                                <td><?= htmlspecialchars($row['id_karyawan']) ?></td>
                                                <td><?= htmlspecialchars($row['jenis']) ?></td>
                                                <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                                                <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                                                <td>
                                                    <?php if (!empty($row['file_lampiran'])): ?>
                                                        <a href="<?= htmlspecialchars($row['file_lampiran']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            Lihat Lampiran
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Tidak ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at'] ?? ''))) ?></td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
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

    <?php if ($role !== 'admin'): ?>
        <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
            <div class="row justify-content-center w-100">
                <div class="col-md-7" style="max-width: 450px;">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Form Pengajuan Cuti / Ijin</h5>
                        </div>
                        <div class="card-body py-4">
                            <!-- PERBAIKAN 6: Tampilkan alert/notifikasi -->
                            <?php if ($alert): ?>
                                <div class="alert alert-<?= $alert[0] ?> alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($alert[1]) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- PERBAIKAN 7: Tampilkan informasi debug untuk karyawan -->
                            <?php if (!$id_karyawan): ?>
                                <div class="alert alert-warning">
                                    <strong>Peringatan:</strong> ID Karyawan tidak ditemukan. Silakan login ulang.
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" enctype="multipart/form-data" id="cutiForm">
                                <div class="mb-3">
                                    <label for="jenis" class="form-label">Jenis Pengajuan</label>
                                    <select class="form-select" id="jenis" name="jenis" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="Cuti" <?= (isset($_POST['jenis']) && $_POST['jenis'] == 'Cuti') ? 'selected' : '' ?>>Cuti</option>
                                        <option value="Ijin" <?= (isset($_POST['jenis']) && $_POST['jenis'] == 'Ijin') ? 'selected' : '' ?>>Ijin</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                        value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai"
                                        value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="keterangan_file" class="form-label">File Permohonan (PDF/DOCX) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="keterangan_file" name="keterangan_file"
                                        accept=".pdf,.docx" required>
                                    <small class="form-text text-muted">Unggah file permohonan ijin/cuti (PDF atau DOCX, max 2MB)</small>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <span id="submitText">Ajukan</span>
                                        <span id="submitLoading" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Mengajukan...
                                        </span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // PERBAIKAN 9: JavaScript untuk validasi form dan UX
        document.getElementById('cutiForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            const fileInput = document.getElementById('keterangan_file');
            const tanggalMulai = document.getElementById('tanggal_mulai').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai').value;

            // Validasi tanggal
            if (tanggalMulai && tanggalSelesai && new Date(tanggalMulai) > new Date(tanggalSelesai)) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai!');
                return false;
            }

            // Validasi file
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar. Maksimal 2MB!');
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Tipe file tidak diizinkan. Gunakan PDF atau DOCX!');
                    return false;
                }
            }

            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitLoading.classList.remove('d-none');
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal_mulai').min = today;
        document.getElementById('tanggal_selesai').min = today;

        // Update minimum end date when start date changes
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            document.getElementById('tanggal_selesai').min = this.value;
        });
    </script>

</body>

</html>