<?php

// editar_registro.php
session_start();

// Verificar autenticación
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'mensaje' => 'No autorizado']));
}

require_once 'config/database.php';

// Definir la función registrarAccion fuera de cualquier bloque condicional
function registrarAccion($conn, $usuario, $accion, $nivel)
{
    // Validación de entradas
    if (empty($usuario) || empty($accion) || empty($nivel)) {
        error_log("Intento de registro con datos inválidos");
        return false;
    }

    try {
        $logStmt = $conn->prepare("INSERT INTO registro_acciones (usuario, accion, nivel, fecha) VALUES (:usuario, :accion, :nivel, NOW())");
        $logStmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $logStmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $logStmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);

        $result = $logStmt->execute();

        // También guardamos en archivo log
        if ($result) {
            $logMessage = date('Y-m-d H:i:s') . " - Usuario: " . htmlspecialchars($usuario) .
                         " - Acción: " . htmlspecialchars($accion) .
                         " - Nivel: " . htmlspecialchars($nivel) . "\n";

            // Asegurar la ruta del archivo log
            $logPath = dirname(__FILE__) . '/logs/sistema.log';

            // Verificar que el directorio existe
            $logDir = dirname($logPath);
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Verificar permisos de escritura
            if (is_writable($logDir)) {
                file_put_contents($logPath, $logMessage, FILE_APPEND);
            } else {
                error_log("No se puede escribir en el directorio de logs");
            }
        }

        return $result;
    } catch (PDOException $e) {
        error_log("Error al registrar acción: " . $e->getMessage());
        return false;
    }
}

// Función para crear conexión a la base de datos de manera segura
function crearConexion($host, $dbname, $username, $password)
{
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $conn;
    } catch(PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return null;
    }
}

// Función para enviar respuesta JSON
function enviarJSON($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Determinar si es una solicitud GET (obtener datos) o POST (actualizar datos)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Código para obtener y devolver los datos del registro para edición
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        enviarJSON(['success' => false, 'mensaje' => 'ID no válido'], 400);
    }

    $conn = crearConexion($host, $dbname, $username, $password);
    if (!$conn) {
        enviarJSON(['success' => false, 'mensaje' => 'Error de conexión a la base de datos'], 500);
    }

    try {
        $stmt = $conn->prepare("SELECT id, usuario, accion, nivel, fecha FROM registro_acciones WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro) {
            enviarJSON($registro);
        } else {
            enviarJSON(['success' => false, 'mensaje' => 'Registro no encontrado'], 404);
        }
    } catch(PDOException $e) {
        error_log("Error al obtener registro: " . $e->getMessage());
        enviarJSON(['success' => false, 'mensaje' => 'Error al obtener datos del registro'], 500);
    } finally {
        $conn = null;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Código para actualizar el registro
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nivel = filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$id || !$usuario || !$accion || !$nivel) {
        enviarJSON(['success' => false, 'mensaje' => 'Datos incompletos o inválidos'], 400);
    }

    // Validar que nivel sea uno de los valores permitidos
    $nivelesPermitidos = ['AVISO', 'MOVIMIENTO', 'ATAQUE'];
    if (!in_array($nivel, $nivelesPermitidos)) {
        enviarJSON(['success' => false, 'mensaje' => 'Nivel no válido'], 400);
    }

    $conn = crearConexion($host, $dbname, $username, $password);
    if (!$conn) {
        enviarJSON(['success' => false, 'mensaje' => 'Error de conexión a la base de datos'], 500);
    }

    try {
        // Verificar que el registro existe
        $checkStmt = $conn->prepare("SELECT id FROM registro_acciones WHERE id = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            enviarJSON(['success' => false, 'mensaje' => 'El registro no existe'], 404);
        }

        // Iniciar transacción para garantizar la integridad
        $conn->beginTransaction();

        // Actualizar el registro
        $stmt = $conn->prepare("UPDATE registro_acciones SET usuario = :usuario, accion = :accion, nivel = :nivel WHERE id = :id");
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $resultado = $stmt->execute();

        // Registrar la acción de modificación
        $username = htmlspecialchars($_SESSION['username']);
        $accionLog = "Modificación del registro #" . intval($id);
        $nivelLog = 'MOVIMIENTO';

        $registroExitoso = registrarAccion($conn, $username, $accionLog, $nivelLog);

        if ($resultado && $registroExitoso) {
            $conn->commit();

            // Obtener registro actualizado
            $updatedStmt = $conn->prepare("SELECT id, usuario, accion, nivel, fecha FROM registro_acciones WHERE id = :id");
            $updatedStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $updatedStmt->execute();
            $registroActualizado = $updatedStmt->fetch(PDO::FETCH_ASSOC);

            enviarJSON([
                'success' => true,
                'mensaje' => 'Registro actualizado correctamente',
                'registro' => $registroActualizado
            ]);
        } else {
            // Revertir los cambios si algo falla
            $conn->rollBack();
            enviarJSON(['success' => false, 'mensaje' => 'No se pudo actualizar el registro'], 500);
        }
    } catch(PDOException $e) {
        // Revertir los cambios si hay excepción
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error al actualizar registro: " . $e->getMessage());
        enviarJSON(['success' => false, 'mensaje' => 'Error al procesar la solicitud'], 500);
    } finally {
        $conn = null;
    }
} else {
    enviarJSON(['success' => false, 'mensaje' => 'Método no permitido'], 405);
}
