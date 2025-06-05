<?php

require_once '/../../config/config.php';
require_once '/../models/Employee.php';
require_once '/../models/Attendance.php';
require_once '/../models/FileUpload.php';

class AttendanceController {
    private $employee_model;
    private $attendance_model;
    private $file_upload_model;
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
        $this->employee_model = new Employee($database);
        $this->attendance_model = new Attendance($database);
        $this->file_upload_model = new FileUpload();
    }
    
    /**
     * Proses absensi datang
     */
    public function clockIn($username, $post_data, $files_data) {
        try {
            // Ambil data karyawan
            $karyawan = $this->employee_model->getByUsername($username);
            if (!$karyawan) {
                throw new Exception("Data karyawan tidak ditemukan");
            }
            
            $id_karyawan = $karyawan['id_karyawan'];
            $nama = $karyawan['nama'];
            $status = 'Hadir';
            $lokasi = $post_data['lokasi'] ?? '-';
            $tanggal = date('Y-m-d');
            $foto = null;
            
            // Upload foto jika ada
            if (isset($files_data['foto']) && $files_data['foto']['error'] === UPLOAD_ERR_OK) {
                $foto = $this->file_upload_model->uploadSelfie($files_data['foto'], $id_karyawan);
            }
            
            // Simpan absensi datang
            if ($this->attendance_model->clockIn($id_karyawan, $nama, $status, $foto, $lokasi, $tanggal)) {
                return ['success' => true, 'message' => 'Absen Datang berhasil!'];
            } else {
                throw new Exception("Gagal menyimpan data absensi datang");
            }
            
        } catch (Exception $e) {
            error_log("Clock in error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Proses absensi pulang
     */
    public function clockOut($username) {
        try {
            // Ambil data karyawan
            $karyawan = $this->employee_model->getByUsername($username);
            if (!$karyawan) {
                throw new Exception("Data karyawan tidak ditemukan");
            }
            
            $id_karyawan = $karyawan['id_karyawan'];
            $tanggal = date('Y-m-d');
            
            // Update absensi pulang
            if ($this->attendance_model->clockOut($id_karyawan, $tanggal)) {
                return ['success' => true, 'message' => 'Absen Pulang berhasil!'];
            } else {
                throw new Exception("Gagal menyimpan data absensi pulang");
            }
            
        } catch (Exception $e) {
            error_log("Clock out error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Proses absensi (method lama untuk kompatibilitas)
     */
    public function submitAttendance($username, $post_data, $files_data) {
        try {
            // Ambil data karyawan
            $karyawan = $this->employee_model->getByUsername($username);
            if (!$karyawan) {
                throw new Exception("Data karyawan tidak ditemukan");
            }
            
            $id_karyawan = $karyawan['id_karyawan'];
            $nama = $karyawan['nama'];
            $status = 'Hadir';
            $lokasi = $post_data['lokasi'] ?? '-';
            $foto = null;
            
            // Upload foto jika ada
            if (isset($files_data['foto']) && $files_data['foto']['error'] === UPLOAD_ERR_OK) {
                $foto = $this->file_upload_model->uploadSelfie($files_data['foto'], $id_karyawan);
            }
            
            // Simpan absensi
            if ($this->attendance_model->addAttendance($id_karyawan, $nama, $status, $foto, $lokasi)) {
                return ['success' => true, 'message' => 'Absensi berhasil! Foto telah disimpan.'];
            } else {
                throw new Exception("Gagal menyimpan data ke database");
            }
            
        } catch (Exception $e) {
            error_log("Attendance error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Ambil status absensi hari ini
     */
    public function getTodayAttendanceStatus($username) {
        try {
            $karyawan = $this->employee_model->getByUsername($username);
            if (!$karyawan) {
                return null;
            }
            
            $id_karyawan = $karyawan['id_karyawan'];
            $tanggal = date('Y-m-d');
            
            return $this->attendance_model->getTodayAttendance($id_karyawan, $tanggal);
            
        } catch (Exception $e) {
            error_log("Get attendance status error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ambil data absensi untuk admin
     */
    public function getTodayAttendances() {
        try {
            return $this->attendance_model->getTodayAttendances();
        } catch (Exception $e) {
            error_log("Get today attendances error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Terima atau tolak kehadiran (admin)
     */
    public function updateAttendanceStatus($post_data) {
        try {
            $id_kehadiran = $post_data['id_kehadiran'];
            $status = $post_data['aksi_kehadiran'] === 'terima' ? 'Diterima' : 'Ditolak';
            
            if ($this->attendance_model->updateStatus($id_kehadiran, $status)) {
                return ['success' => true, 'message' => 'Status kehadiran berhasil diupdate'];
            } else {
                throw new Exception("Gagal mengupdate status kehadiran");
            }
            
        } catch (Exception $e) {
            error_log("Update attendance status error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Ambil riwayat absensi karyawan
     */
    public function getAttendanceHistory($username, $limit = 30) {
        try {
            $karyawan = $this->employee_model->getByUsername($username);
            if (!$karyawan) {
                return [];
            }
            
            $id_karyawan = $karyawan['id_karyawan'];
            return $this->attendance_model->getAttendanceHistory($id_karyawan, $limit);
            
        } catch (Exception $e) {
            error_log("Get attendance history error: " . $e->getMessage());
            return [];
        }
    }
}