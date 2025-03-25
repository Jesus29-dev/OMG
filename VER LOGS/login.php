<?php

session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['username'] = $username;
        registrarAccion($username, 'Inicio de sesión exitoso', 'AVISO');
        header('Location: index.php');
    } else {
        registrarAccion('anónimo', "Intento fallido de inicio de sesión - usuario: $username", 'ATAQUE');
        $error = "Credenciales inválidas";
    }
}
