<?php

class Employee {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Ambil data karyawan berdasarkan username
     */
    public function getByUsername($username) {
        $stmt = $this->conn->prepare("SELECT id_karyawan, nama FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Ambil semua data karyawan
     */
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM employees ORDER BY nama ASC");
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        return $employees;
    }
    
    /**
     * Validasi apakah karyawan ada
     */
    public function exists($id_karyawan) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM employees WHERE id_karyawan = ?");
        $stmt->bind_param("s", $id_karyawan);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
}