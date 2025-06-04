<?php
require_once __DIR__ . '/../components/navbar.php';
require_once __DIR__ . '/../components/footer.php';
include_once __DIR__ . '/../../config/config.php';
session_start();

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'karyawan';
$username = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Pastikan folder uploads ada dan bisa ditulis
$upload_dir = __DIR__ . '/../../../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Proses Absensi Karyawan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen'])) {
    try {
        // Ambil data karyawan
        $stmt = $conn->prepare("SELECT id_karyawan, nama FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $karyawan = $result->fetch_assoc();

        if (!$karyawan) {
            throw new Exception("Data karyawan tidak ditemukan");
        }

        $id_karyawan = $karyawan['id_karyawan'];
        $nama = $karyawan['nama'];
        $status = 'Hadir';
        $lokasi = $_POST['lokasi'] ?? '-';

        // Upload foto
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['foto']['type'];

            // Validasi tipe file
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Tipe file tidak diizinkan. Gunakan JPG, PNG, atau GIF");
            }

            // Validasi ukuran file (maksimal 5MB)
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 5MB");
            }

            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'selfie_' . $id_karyawan . '_' . date('YmdHis') . '.' . $ext;
            $foto_path = $upload_dir . $filename;

            if (!file_exists($_FILES['foto']['tmp_name'])) {
                throw new Exception("File sementara tidak ditemukan: " . $_FILES['foto']['tmp_name']);
            }
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                $foto = 'http://localhost/projek_kelompok/uploads/' . $filename;
                error_log("File uploaded successfully: " . $foto);
            } else {
                throw new Exception("Gagal mengupload foto. Periksa permission folder uploads");
            }

            // Debug: Tampilkan path untuk debugging
            error_log("Upload path: " . $foto_path);
            error_log("Temp file: " . $_FILES['foto']['tmp_name']);
        } else {
            // Handle upload errors
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form)',
                UPLOAD_ERR_PARTIAL => 'Upload tidak lengkap',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
            ];

            $error_code = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
            throw new Exception($upload_errors[$error_code] ?? 'Error upload tidak diketahui');
        }

        // Simpan ke database
        $stmt = $conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("sssss", $id_karyawan, $nama, $status, $foto, $lokasi);

        if ($stmt->execute()) {
            $success = "Absensi berhasil! Foto telah disimpan.";
        } else {
            throw new Exception("Gagal menyimpan data ke database");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Attendance error: " . $error);
    }
}

// Ambil data absensi untuk admin
$absensi = [];
if ($role === 'admin') {
    $result = $conn->query("SELECT * FROM kehadiran WHERE tanggal = CURDATE() ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $absensi[] = $row;
    }
}

// Ambil data karyawan
$stmt = $conn->prepare("SELECT id_karyawan, nama FROM employees WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$karyawan = $result->fetch_assoc();
$id_karyawan = $karyawan['id_karyawan'] ?? null;
$nama = $karyawan['nama'] ?? null;

// Cek status absensi hari ini
$tanggal = date('Y-m-d');
$jam_datang = null;
$jam_pulang = null;
$stmt = $conn->prepare("SELECT jam_datang, jam_pulang FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
$stmt->bind_param("ss", $id_karyawan, $tanggal);
$stmt->execute();
$stmt->bind_result($jam_datang, $jam_pulang);
$stmt->fetch();
$stmt->close();

// Proses Absen Datang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen_datang'])) {
    try {
        $status = 'Hadir';
        $lokasi = $_POST['lokasi'] ?? '-';
        $foto = null;

        // Upload foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['foto']['type'];
            if (!in_array($file_type, $allowed_types)) throw new Exception("Tipe file tidak diizinkan.");
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) throw new Exception("Ukuran file terlalu besar.");
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'selfie_' . $id_karyawan . '_' . date('YmdHis') . '.' . $ext;
            $upload_dir = __DIR__ . '/../../../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $foto_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                $foto = 'http://localhost/projek_kelompok/uploads/' . $filename;
            } else {
                throw new Exception("Gagal mengupload foto.");
            }
        }

        // Cek apakah sudah ada data absensi hari ini
        $stmt = $conn->prepare("SELECT id FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("ss", $id_karyawan, $tanggal);
        $stmt->execute();
        $stmt->store_result();

        $jam_datang_val = '08:00';
        if ($stmt->num_rows > 0) {
            // Sudah ada, update jam_datang dan foto jika perlu
            $stmt2 = $conn->prepare("UPDATE kehadiran SET jam_datang = ?, foto = ?, lokasi = ?, status = ? WHERE id_karyawan = ? AND tanggal = ?");
            $stmt2->bind_param("ssssss", $jam_datang_val, $foto, $lokasi, $status, $id_karyawan, $tanggal);
            $stmt2->execute();
            $stmt2->close();
        } else {
            // Insert baru
            $stmt2 = $conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal, jam_datang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssssss", $id_karyawan, $nama, $status, $foto, $lokasi, $tanggal, $jam_datang_val);
            $stmt2->execute();
            $stmt2->close();
        }
        $success = "Absen Datang berhasil!";
        $jam_datang = $jam_datang_val;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Proses Absen Pulang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen_pulang'])) {
    try {
        $jam_pulang_val = '17:00';
        $stmt = $conn->prepare("UPDATE kehadiran SET jam_pulang = ? WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("sss", $jam_pulang_val, $id_karyawan, $tanggal);
        $stmt->execute();
        $stmt->close();
        $success = "Absen Pulang berhasil!";
        $jam_pulang = $jam_pulang_val;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Proses aksi admin: terima/tolak kehadiran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_kehadiran']) && $role === 'admin') {
    $id_kehadiran = $_POST['id_kehadiran'];
    $aksi = $_POST['aksi_kehadiran'] === 'terima' ? 'Diterima' : 'Ditolak';
    $stmt = $conn->prepare("UPDATE kehadiran SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $aksi, $id_kehadiran);
    $stmt->execute();
    $stmt->close();
    // Optional: tampilkan pesan sukses/gagal
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kehadiran Karyawan</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .admin-table-nama {
            color: #000 !important;
        }

        body {
            margin-left: 220px;
        }

        /* Container responsif */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            /* ubah dari flex-start ke center */
            min-height: calc(100vh - 100px);
            padding: 2rem;
        }

        .attendance-card {
            width: 100%;
            max-width: 900px;
            /* atau 1200px jika ingin sama persis dengan admin */
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
        }

        .content-wrapper {
            width: 100%;
            max-width: 100%;
            min-width: 300px;
        }



        /* File upload preview */
        .file-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }

        /* Debug info */
        .debug-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }

            .main-container {
                padding: 1rem;
            }

            .attendance-card {
                min-width: unset;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="content-wrapper">
            <?php if ($role !== 'admin'): ?>
                <div class="card mb-4 attendance-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0">Form Kehadiran Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" id="attendanceForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Foto Selfie <span class="text-danger">*</span></label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/*" <?= !$jam_datang ? 'required' : '' ?>> <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal 5MB</small>
                                <div id="imagePreview"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <input type="text" name="lokasi" id="lokasi" class="form-control" readonly placeholder="Mengambil lokasi..." <?= $jam_datang ? 'disabled' : '' ?>>
                                <small class="form-text text-muted">Lokasi akan diambil secara otomatis</small>
                                <div class="debug-info" id="locationDebug"></div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="absen_datang" class="btn btn-lg <?= $jam_datang ? 'btn-secondary' : 'btn-success' ?>" <?= $jam_datang ? 'disabled' : '' ?>>
                                    Absen Datang <?= $jam_datang ? "($jam_datang)" : '' ?>
                                </button>
                                <button type="submit" name="absen_pulang" class="btn btn-lg <?= ($jam_datang && !$jam_pulang) ? 'btn-primary' : 'btn-secondary' ?>" <?= ($jam_datang && !$jam_pulang) ? '' : 'disabled' ?>>
                                    Absen Pulang <?= $jam_pulang ? "($jam_pulang)" : '' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <div class="attendance-card">
                    <div class="container-fluid card" style="max-width:1200px;">
                        <div class="table-responsive card-header bg-secondary text-white text-center">
                            <h5 class="mb-0">Rekap Kehadiran Hari Ini</h5>
                            <small>Total: <?= count($absensi) ?> karyawan</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-container">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">No</th>
                                            <th>Nama Karyawan</th>
                                            <th>ID Karyawan</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Foto Selfie</th>
                                            <th class="text-center">Lokasi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($absensi)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox"></i><br>
                                                    Belum ada data kehadiran hari ini
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1;
                                            foreach ($absensi as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><span class="admin-table-nama"><?= htmlspecialchars($row['nama']) ?></span></td>
                                                    <td><span class="badge bg-info"><?= htmlspecialchars($row['id_karyawan']) ?></span></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success"><?= htmlspecialchars($row['status']) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($row['foto']): ?>
                                                            <img src="<?= htmlspecialchars($row['foto']) ?>" alt="Selfie"
                                                                width="60" height="60" class="rounded"
                                                                onclick="showImageModal('<?= htmlspecialchars($row['foto']) ?>', '<?= htmlspecialchars($row['nama']) ?>')">
                                                        <?php else: ?>
                                                            <span class="text-muted">Tidak ada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($row['lokasi']) && $row['lokasi'] !== '-'): ?>
                                                            <a href="https://www.google.com/maps?q=<?= htmlspecialchars($row['lokasi']) ?>"
                                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-geo-alt"></i> Lihat
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted"> - </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($row['status'] === 'Hadir'): ?>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="id_kehadiran" value="<?= $row['id'] ?>">
                                                                <button type="submit" name="aksi_kehadiran" value="terima" class="btn btn-success btn-sm" onclick="return confirm('Terima kehadiran ini?')">Terima</button>
                                                                <button type="submit" name="aksi_kehadiran" value="tolak" class="btn btn-danger btn-sm" onclick="return confirm('Tolak kehadiran ini?')">Tolak</button>
                                                            </form>
                                                        <?php elseif ($row['status'] === 'Diterima'): ?>
                                                            <span class="badge bg-success">Diterima</span>
                                                        <?php elseif ($row['status'] === 'Ditolak'): ?>
                                                            <span class="badge bg-danger">Ditolak</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal untuk preview gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalTitle">Foto Selfie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Selfie" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview gambar sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="file-preview img-thumbnail mt-2" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Ambil lokasi otomatis
        function getLocation() {
            const locationInput = document.getElementById('lokasi');
            const debugDiv = document.getElementById('locationDebug');

            if (navigator.geolocation) {
                locationInput.placeholder = "Mengambil lokasi...";
                debugDiv.innerHTML = "Meminta izin akses lokasi...";

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        locationInput.value = lat + ',' + lng;
                        locationInput.placeholder = "Lokasi berhasil diambil";
                        debugDiv.innerHTML = `Akurasi: ${Math.round(accuracy)}m | ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    },
                    function(error) {
                        let errorMsg = "Gagal mengambil lokasi: ";
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg += "Izin lokasi ditolak";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg += "Lokasi tidak tersedia";
                                break;
                            case error.TIMEOUT:
                                errorMsg += "Timeout";
                                break;
                            default:
                                errorMsg += "Error tidak diketahui";
                                break;
                        }
                        locationInput.placeholder = errorMsg;
                        debugDiv.innerHTML = errorMsg;
                    }
                );
            } else {
                locationInput.placeholder = "Geolocation tidak didukung";
                debugDiv.innerHTML = "Browser tidak mendukung geolocation";
            }
        }

        // Show image modal
        function showImageModal(imageSrc, employeeName) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModalTitle').textContent = 'Foto Selfie - ' + employeeName;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Form validation
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('foto');
            const locationInput = document.getElementById('lokasi');
            // Cek tombol mana yang diklik
            const absenDatangBtn = document.querySelector('button[name="absen_datang"]');
            const absenPulangBtn = document.querySelector('button[name="absen_pulang"]');
            // Cek apakah submit karena absen datang
            const isAbsenDatang = document.activeElement === absenDatangBtn;

            // Validasi foto hanya saat absen datang
            if (isAbsenDatang && !fileInput.files.length) {
                e.preventDefault();
                alert('Silakan pilih foto selfie terlebih dahulu');
                return false;
            }

            if (!locationInput.value && (!absenDatangBtn.disabled || isAbsenDatang)) {
                if (!confirm('Lokasi belum terdeteksi. Lanjutkan tanpa lokasi?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            getLocation();
        });
    </script>
</body>

</html>