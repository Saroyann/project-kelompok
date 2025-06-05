<?php
class AbsensiModel
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getStatusKehadiranHariIni($id_karyawan)
    {
        $stmt = $this->conn->prepare("SELECT status FROM kehadiran WHERE id_karyawan = ? AND tanggal = CURDATE() LIMIT 1");
        $stmt->bind_param("s", $id_karyawan);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['status'] ?? null;
    }

    public function getJamKerjaHariIni($id_karyawan)
    {
        $jam_datang = null;
        $jam_pulang = null;
        $stmt = $this->conn->prepare("SELECT jam_datang, jam_pulang FROM kehadiran WHERE id_karyawan = ? AND tanggal = CURDATE() AND status = 'Diterima'");
        $stmt->bind_param("s", $id_karyawan);
        $stmt->execute();
        $stmt->bind_result($jam_datang, $jam_pulang);
        if ($stmt->fetch() && $jam_datang && $jam_pulang) {
            $start = new DateTime($jam_datang);
            $end = new DateTime($jam_pulang);
            $interval = $start->diff($end);
            return $interval->h + ($interval->i / 60);
        }
        return 0;
    }
}
