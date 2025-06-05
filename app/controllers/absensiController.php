<?php
require_once __DIR__ . '/../models/absensiModel.php';

class AbsensiController {
    private $model;
    public function __construct($conn) { $this->model = new AbsensiModel($conn); }

    public function getStatusKehadiran($id_karyawan) {
        $status = $this->model->getStatusKehadiranHariIni($id_karyawan);
        if ($status === 'Diterima') {
            return "<span class='text-success fw-bold'>Telah Absen</span>";
        } elseif ($status === 'Hadir') {
            return "<span class='text-warning fw-bold'>Menunggu Konfirmasi</span>";
        } elseif ($status === 'Ditolak') {
            return "<span class='text-danger fw-bold'>Absensi Ditolak</span>";
        } elseif ($status) {
            return htmlspecialchars($status);
        } else {
            return "Belum Absen";
        }
    }

    public function getJamKerja($id_karyawan) {
        return $this->model->getJamKerjaHariIni($id_karyawan);
    }
}