<?php
$host = 'localhost';
$dbname = 'sistema_log';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

// Función para registrar acciones
function registrarAccion($usuario, $accion, $nivel) {
    global $pdo;
    $sql = "INSERT INTO registro_acciones (usuario, accion, nivel, fecha) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario, $accion, $nivel]);
    
    // También guardamos en archivo log
    $logMessage = date('Y-m-d H:i:s') . " - Usuario: $usuario - Acción: $accion - Nivel: $nivel\n";
    file_put_contents('logs/sistema.log', $logMessage, FILE_APPEND);
}
?>