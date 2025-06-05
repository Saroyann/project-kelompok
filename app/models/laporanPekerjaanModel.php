<?php

class ToDoListModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllByKaryawan($id_karyawan) {
        $stmt = $this->conn->prepare("SELECT * FROM todolist WHERE id_karyawan = ? ORDER BY id DESC");
        $stmt->bind_param("s", $id_karyawan);
        $stmt->execute();
        $result = $stmt->get_result();
        $todolist = [];
        while ($row = $result->fetch_assoc()) {
            $todolist[] = $row;
        }
        return $todolist;
    }

    public function add($id_karyawan, $pekerjaan) {
        $stmt = $this->conn->prepare("INSERT INTO todolist (id_karyawan, pekerjaan, status) VALUES (?, ?, 'proses')");
        $stmt->bind_param("ss", $id_karyawan, $pekerjaan);
        return $stmt->execute();
    }

    public function edit($id_laporan, $id_karyawan, $pekerjaan) {
        $stmt = $this->conn->prepare("UPDATE todolist SET pekerjaan = ? WHERE id = ? AND id_karyawan = ?");
        $stmt->bind_param("sis", $pekerjaan, $id_laporan, $id_karyawan);
        return $stmt->execute();
    }

    public function delete($id_laporan, $id_karyawan) {
        $stmt = $this->conn->prepare("DELETE FROM todolist WHERE id = ? AND id_karyawan = ?");
        $stmt->bind_param("is", $id_laporan, $id_karyawan);
        return $stmt->execute();
    }

    public function selesai($id_laporan, $id_karyawan) {
        $stmt = $this->conn->prepare("UPDATE todolist SET status = 'selesai' WHERE id = ? AND id_karyawan = ?");
        $stmt->bind_param("is", $id_laporan, $id_karyawan);
        return $stmt->execute();
    }
}