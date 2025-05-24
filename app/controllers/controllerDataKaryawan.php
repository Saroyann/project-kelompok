<?php
include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../models/UserModel.php';

$userModel = new UserModel($conn);

if ($userModel->checkAdmin($username, $password)) {

} elseif ($userModel->checkEmployee($username, $password)) {

}
?>