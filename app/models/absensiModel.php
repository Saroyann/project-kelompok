<?php

class AbsensiModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getKaryawanByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT id_karyawan, nama FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function insertAbsensi($id_karyawan, $nama, $status, $foto, $lokasi)
    {
        $stmt = $this->conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("sssss", $id_karyawan, $nama, $status, $foto, $lokasi);
        return $stmt->execute();
    }

    public function updateAbsenDatang($id_karyawan, $foto, $lokasi, $status, $tanggal, $jam_datang)
    {
        $stmt = $this->conn->prepare("UPDATE kehadiran SET jam_datang = ?, foto = ?, lokasi = ?, status = ? WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("ssssss", $jam_datang, $foto, $lokasi, $status, $id_karyawan, $tanggal);
        return $stmt->execute();
    }

    public function insertAbsenDatang($id_karyawan, $nama, $status, $foto, $lokasi, $tanggal, $jam_datang)
    {
        $stmt = $this->conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal, jam_datang) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_karyawan, $nama, $status, $foto, $lokasi, $tanggal, $jam_datang);
        return $stmt->execute();
    }

    public function updateAbsenPulang($id_karyawan, $tanggal, $jam_pulang, $jam_kerja = 8)
    {
        $stmt = $this->conn->prepare("UPDATE kehadiran SET jam_pulang = ?, jam_kerja = ? WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("siss", $jam_pulang, $jam_kerja, $id_karyawan, $tanggal);
        return $stmt->execute();
    }

    public function cekAbsensiHariIni($id_karyawan, $tanggal)
    {
        $stmt = $this->conn->prepare("SELECT id FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("ss", $id_karyawan, $tanggal);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

public function getAbsensiHariIni($id_karyawan, $tanggal)
{
    $jam_datang = null;
    $jam_pulang = null;
    $stmt = $this->conn->prepare("SELECT jam_datang, jam_pulang FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
    $stmt->bind_param("ss", $id_karyawan, $tanggal);
    $stmt->execute();
    $stmt->bind_result($jam_datang, $jam_pulang);
    $found = $stmt->fetch();
    $stmt->close();
    if ($found) {
        return ['jam_datang' => $jam_datang, 'jam_pulang' => $jam_pulang];
    } else {
        return ['jam_datang' => null, 'jam_pulang' => null];
    }
}

    public function getAbsensiForAdmin($tanggal)
    {
        $result = $this->conn->query("SELECT * FROM kehadiran WHERE tanggal = '$tanggal' ORDER BY id DESC");
        $absensi = [];
        while ($row = $result->fetch_assoc()) {
            $absensi[] = $row;
        }
        return $absensi;
    }

    public function updateStatusKehadiran($id_kehadiran, $aksi)
    {
        $stmt = $this->conn->prepare("UPDATE kehadiran SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $aksi, $id_kehadiran);
        return $stmt->execute();
    }
}