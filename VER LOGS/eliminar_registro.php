<?php

// eliminar_registro.php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('No autorizado');
}

require_once 'config/database.php';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensaje' => 'ID no válido']);
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar la consulta DELETE
    $stmt = $conn->prepare("DELETE FROM registro_acciones WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se eliminó algún registro
    if ($stmt->rowCount() > 0) {
        // Registro eliminado exitosamente
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'mensaje' => 'Registro eliminado correctamente']);
    } else {
        // No se encontró ningún registro con ese ID
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró ningún registro con el ID proporcionado']);
    }
} catch(PDOException $e) {
    // Error en la base de datos
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el registro: ' . $e->getMessage()]);
}

// Cerrar la conexión
$conn = null;
