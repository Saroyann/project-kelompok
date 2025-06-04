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