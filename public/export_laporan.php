<?php
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../app/config/config.php';
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] === 'admin') {
    die('Akses ditolak.');
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id_karyawan, nama FROM employees WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if (!$row = $result->fetch_assoc()) {
    die('Data karyawan tidak ditemukan.');
}
$id_karyawan = $row['id_karyawan'];
$nama = $row['nama'];

// Ambil laporan pekerjaan
$stmt2 = $conn->prepare("SELECT pekerjaan, status FROM todolist WHERE id_karyawan = ? ORDER BY id DESC");
$stmt2->bind_param("s", $id_karyawan);
$stmt2->execute();
$result2 = $stmt2->get_result();

$html = '<h2>Laporan Pekerjaan - ' . htmlspecialchars($nama) . '</h2>';
$html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%">';
$html .= '<thead><tr><th>No</th><th>Pekerjaan</th><th>Status</th></tr></thead><tbody>';
$no = 1;
while ($item = $result2->fetch_assoc()) {
    $status = $item['status'] == 'selesai' ? 'Selesai' : 'Proses';
    $style = $item['status'] == 'selesai' ? 'style="color:gray;text-decoration:line-through;"' : '';
    $html .= '<tr>';
    $html .= '<td>' . $no++ . '</td>';
    $html .= '<td ' . $style . '>' . htmlspecialchars($item['pekerjaan']) . '</td>';
    $html .= '<td>' . $status . '</td>';
    $html .= '</tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="3" align="center">Belum ada laporan pekerjaan</td></tr>';
}
$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('laporan_pekerjaan.pdf', 'D');
exit;
