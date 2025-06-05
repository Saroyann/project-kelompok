<?php
require_once __DIR__ . '/../models/authModel.php';

class LoginController {
    private $authModel;
    public $error = "";

    public function __construct($conn) {
        $this->authModel = new AuthModel($conn);
    }

    public function login($username, $password) {
        $user = $this->authModel->findUserByUsername($username);

        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $this->error = "Username atau password salah!";
        }
    }
}