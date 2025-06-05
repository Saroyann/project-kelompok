<?php

class AuthModel {
    private $conn;

    public function __construct($database) {
        $this->conn = $database;
    }

    public function findUserByUsername($username) {
        $stmt = $this->conn->prepare("
            SELECT username, password, 'admin' as role FROM admin WHERE username = ?
            UNION
            SELECT username, password, 'employee' as role FROM employees WHERE username = ?
        ");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}