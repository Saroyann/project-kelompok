<?php
include_once __DIR__ . '/../app/config/config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM employees WHERE id_karyawan = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

header("Location: karyawan.php");
exit;
?>