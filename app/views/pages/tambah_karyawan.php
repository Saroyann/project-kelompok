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

    include_once __DIR__ . '/../../config/config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_karyawan = $_POST['id_karyawan'];
        $nama = $_POST['nama'];
        $jabatan = $_POST['jabatan'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password
        $gaji = 0; // default

        $stmt = $conn->prepare("INSERT INTO employees (id_karyawan, nama, jabatan, gaji, username, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $id_karyawan, $nama, $jabatan, $gaji, $username, $password);

        if ($stmt->execute()) {
            header("Location: karyawan.php?success=1");
            exit();
        } else {
            header("Location: karyawan.php?error=1");
            exit();
        }
    }
    ?>


</body>

</html>