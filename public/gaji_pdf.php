<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Akses ditolak');
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';

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

$html = '
<h2 style="text-align:center;">Rekap Gaji Karyawan</h2>
<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>ID Karyawan</th>
            <th>Jumlah Kehadiran</th>
            <th>Jam Kerja</th>
            <th>Gaji</th>
        </tr>
    </thead>
    <tbody>';
$no = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jabatan = strtolower($row['jabatan']);
        $gaji_jam = $gaji_per_jam[$jabatan] ?? 0;
        $gaji = $gaji_jam * (int)$row['total_jam_kerja'];
        $html .= '<tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['nama']) . '</td>
            <td>' . htmlspecialchars($row['id_karyawan']) . '</td>
            <td align="center">' . htmlspecialchars($row['jumlah_kehadiran']) . '</td>
            <td align="center">' . htmlspecialchars($row['total_jam_kerja']) . '</td>
            <td>Rp ' . number_format($gaji, 0, ',', '.') . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">Tidak ada data gaji karyawan</td></tr>';
}
$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);
$mpdf->WriteHTML($html);
$mpdf->Output('rekap_gaji.pdf', 'D');
exit;