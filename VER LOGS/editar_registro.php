<?php
// editar_registro.php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'mensaje' => 'No autorizado']));
}
require_once 'config/database.php';

// Determinar si es una solicitud GET (obtener datos) o POST (actualizar datos)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Código para obtener y devolver los datos del registro para edición
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'ID no válido']);
        exit();
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT id, usuario, accion, nivel, fecha FROM registro_acciones WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            header('Content-Type: application/json');
            echo json_encode($registro);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => 'Registro no encontrado']);
        }
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
    }
    
    $conn = null;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Código para actualizar el registro
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nivel = filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (!$id || !$usuario || !$accion || !$nivel) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos o inválidos']);
        exit();
    }
    
    // Validar que nivel sea uno de los valores permitidos
    if (!in_array($nivel, ['AVISO', 'MOVIMIENTO', 'ATAQUE'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'Nivel no válido']);
        exit();
    }
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar que el registro existe
        $checkStmt = $conn->prepare("SELECT id FROM registro_acciones WHERE id = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => 'El registro no existe']);
            exit();
        }
        
        // Actualizar el registro sin campo de fecha_modificacion
        $stmt = $conn->prepare("UPDATE registro_acciones SET usuario = :usuario, accion = :accion, nivel = :nivel WHERE id = :id");
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $resultado = $stmt->execute();
        
        // Registrar la acción
        function registrarAccion($conn, $usuario, $accion, $nivel) {
            $logStmt = $conn->prepare("INSERT INTO registro_acciones (usuario, accion, nivel, fecha) VALUES (:usuario, :accion, :nivel, NOW())");
            $logStmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $logStmt->bindParam(':accion', $accion, PDO::PARAM_STR);
            $logStmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
            return $logStmt->execute();
        }
        registrarAccion($conn, $_SESSION['username'], "Modificación del registro #$id", 'MOVIMIENTO');
        
        if ($resultado) {
            // Obtener registro actualizado
            $updatedStmt = $conn->prepare("SELECT id, usuario, accion, nivel, fecha FROM registro_acciones WHERE id = :id");
            $updatedStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $updatedStmt->execute();
            $registroActualizado = $updatedStmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Registro actualizado correctamente',
                'registro' => $registroActualizado
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo actualizar el registro']);
        }
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
    }
    
    $conn = null;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
}
?>