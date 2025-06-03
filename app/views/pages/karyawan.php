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

    <div class="container mt-2 pt-5">
        <h1 class="text-center mb-4 fw-bold">Data Karyawan</h1>
        <!-- Tombol Tambah Karyawan -->
        <div class="mb-3 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKaryawanModal">
                + Tambah Karyawan
            </button>
        </div>

        <!-- Modal Tambah Karyawan -->
        <div class="modal fade" id="tambahKaryawanModal" tabindex="-1" aria-labelledby="tambahKaryawanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="tambah_karyawan.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahKaryawanModalLabel">Tambah Karyawan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_karyawan" class="form-label">ID</label>
                                <input type="text" class="form-control" name="id_karyawan" id="id_karyawan" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="nama" id="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <select class="form-select" name="jabatan" id="jabatan" required>
                                    <option value="" disabled selected>Pilih Jabatan</option>
                                    <option value="Manajer">Manajer</option>
                                    <option value="Asisten Manajer">Asisten Manajer</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Office Boy/Girl">Office Boy/Girl</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Gaji</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include_once __DIR__ . '/../../config/config.php';
                            $no = 1;
                            $result = $conn->query("SELECT * FROM employees ORDER BY nama ASC");
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                            ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['id_karyawan']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jabatan']); ?></td>
                                        <td>Rp.<?= number_format($row['gaji'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="#"
                                                class="btn btn-warning btn-sm btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editKaryawanModal"
                                                data-id="<?= htmlspecialchars($row['id_karyawan']); ?>"
                                                data-nama="<?= htmlspecialchars($row['nama']); ?>"
                                                data-jabatan="<?= htmlspecialchars($row['jabatan']); ?>"
                                                data-gaji="<?= htmlspecialchars($row['gaji']); ?>">Edit</a>

                                            <a href="hapus_karyawan.php?id=<?= urlencode($row['id_karyawan']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak Ada Data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Edit Karyawan -->
    <div class="modal fade" id="editKaryawanModal" tabindex="-1" aria-labelledby="editKaryawanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="edit_karyawan.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editKaryawanModalLabel">Edit Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_karyawan" id="edit-id_karyawan">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" id="edit-nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select name="jabatan" id="edit-jabatan" class="form-select" required>
                                <option value="Manajer">Manajer</option>
                                <option value="Asisten Manajer">Asisten Manajer</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                                <option value="Office Boy/Girl">Office Boy/Girl</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-edit').forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('edit-id_karyawan').value = this.dataset.id;
                document.getElementById('edit-nama').value = this.dataset.nama;
        document.getElementById('edit-jabatan').value = this.dataset.jabatan;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>