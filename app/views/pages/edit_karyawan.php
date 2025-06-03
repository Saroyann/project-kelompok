<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form modal
    $id = $_POST['id_karyawan'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $gaji = $_POST['gaji'];

    $stmt = $conn->prepare("UPDATE employees SET nama=?, jabatan=?, gaji=? WHERE id_karyawan=?");
    $stmt->bind_param("ssss", $nama, $jabatan, $gaji, $id);
    $stmt->execute();

    header("Location: karyawan.php");
    exit;
} else {
    // Ambil data untuk prefill form jika ada parameter id di GET
    if (!isset($_GET['id'])) {
        header("Location: karyawan.php");
        exit;
    }

    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id_karyawan = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}
?>