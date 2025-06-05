<?php
require_once __DIR__ . '/../models/laporanPekerjaanModel.php';

class ToDoListController {
    private $model;

    public function __construct($conn) {
        $this->model = new ToDoListModel($conn);
    }

    public function handleRequest($id_karyawan) {
        // Tambah laporan
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_laporan'])) {
            $laporan = trim($_POST['laporan']);
            if ($laporan !== '') {
                $this->model->add($id_karyawan, $laporan);
                header("Location: dashboard.php");
                exit();
            }
        }
        // Edit laporan
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_laporan'])) {
            $id_laporan = $_POST['id_laporan'];
            $laporan = trim($_POST['laporan']);
            if ($laporan !== '') {
                $this->model->edit($id_laporan, $id_karyawan, $laporan);
                header("Location: dashboard.php");
                exit();
            }
        }
        // Hapus laporan
        if (isset($_GET['hapus_laporan'])) {
            $id_laporan = $_GET['hapus_laporan'];
            $this->model->delete($id_laporan, $id_karyawan);
            header("Location: dashboard.php");
            exit();
        }
        // Tandai selesai
        if (isset($_GET['selesai_laporan'])) {
            $id_laporan = $_GET['selesai_laporan'];
            $this->model->selesai($id_laporan, $id_karyawan);
            header("Location: dashboard.php");
            exit();
        }
    }

    public function getAll($id_karyawan) {
        return $this->model->getAllByKaryawan($id_karyawan);
    }
}