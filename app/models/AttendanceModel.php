<?php

class Attendance {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Tambah atau update absensi datang
     */
    public function clockIn($id_karyawan, $nama, $status, $foto, $lokasi, $tanggal, $jam_datang = '08:00') {
        // Cek apakah sudah ada data absensi hari ini
        if ($this->hasAttendanceToday($id_karyawan, $tanggal)) {
            // Update jam_datang dan foto
            $stmt = $this->conn->prepare("UPDATE kehadiran SET jam_datang = ?, foto = ?, lokasi = ?, status = ? WHERE id_karyawan = ? AND tanggal = ?");
            $stmt->bind_param("ssssss", $jam_datang, $foto, $lokasi, $status, $id_karyawan, $tanggal);
        } else {
            // Insert baru
            $stmt = $this->conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal, jam_datang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $id_karyawan, $nama, $status, $foto, $lokasi, $tanggal, $jam_datang);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Update absensi pulang
     */
    public function clockOut($id_karyawan, $tanggal, $jam_pulang = '17:00', $jam_kerja = 8) {
        $stmt = $this->conn->prepare("UPDATE kehadiran SET jam_pulang = ?, jam_kerja = ? WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("siss", $jam_pulang, $jam_kerja, $id_karyawan, $tanggal);
        return $stmt->execute();
    }
    
    /**
     * Cek apakah sudah absen hari ini
     */
    public function hasAttendanceToday($id_karyawan, $tanggal) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("ss", $id_karyawan, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Ambil status absensi hari ini
     */
    public function getTodayAttendance($id_karyawan, $tanggal) {
        $stmt = $this->conn->prepare("SELECT jam_datang, jam_pulang FROM kehadiran WHERE id_karyawan = ? AND tanggal = ?");
        $stmt->bind_param("ss", $id_karyawan, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Ambil semua data absensi hari ini (untuk admin)
     */
    public function getTodayAttendances($tanggal = null) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $result = $this->conn->query("SELECT * FROM kehadiran WHERE tanggal = '$tanggal' ORDER BY id DESC");
        $attendances = [];
        while ($row = $result->fetch_assoc()) {
            $attendances[] = $row;
        }
        return $attendances;
    }
    
    /**
     * Update status kehadiran (terima/tolak) - untuk admin
     */
    public function updateStatus($id_kehadiran, $status) {
        $stmt = $this->conn->prepare("UPDATE kehadiran SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id_kehadiran);
        return $stmt->execute();
    }
    
    /**
     * Tambah absensi (method lama untuk kompatibilitas)
     */
    public function addAttendance($id_karyawan, $nama, $status, $foto, $lokasi) {
        $stmt = $this->conn->prepare("INSERT INTO kehadiran (id_karyawan, nama, status, foto, lokasi, tanggal) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("sssss", $id_karyawan, $nama, $status, $foto, $lokasi);
        return $stmt->execute();
    }
    
    /**
     * Ambil riwayat absensi karyawan
     */
    public function getAttendanceHistory($id_karyawan, $limit = 30) {
        $stmt = $this->conn->prepare("SELECT * FROM kehadiran WHERE id_karyawan = ? ORDER BY tanggal DESC LIMIT ?");
        $stmt->bind_param("si", $id_karyawan, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }
}