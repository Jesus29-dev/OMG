<?php

$host = 'localhost';
$dbname = 'sistema_log';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configuración adicional recomendada
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Desactivar emulación de consultas preparadas
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Configurar modo de obtención por defecto
} catch(PDOException $e) {
    // Evitar mostrar información sensible en producción
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    echo "Error de conexión: Contacte al administrador del sistema";
    exit; // Terminar la ejecución para evitar procesamiento adicional
}

// Función para registrar acciones
function registrarAccion($usuario, $accion, $nivel)
{
    global $pdo;

    // Validación de entradas
    if (empty($usuario) || empty($accion) || empty($nivel)) {
        error_log("Intento de registro con datos inválidos");
        return false;
    }

    try {
        $sql = "INSERT INTO registro_acciones (usuario, accion, nivel, fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);

        // Ejecutar con valores tipados
        $stmt->execute([$usuario, $accion, $nivel]);

        // También guardamos en archivo log
        $logMessage = date('Y-m-d H:i:s') . " - Usuario: " . htmlspecialchars($usuario) .
                      " - Acción: " . htmlspecialchars($accion) .
                      " - Nivel: " . intval($nivel) . "\n";

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
            return true;
        } else {
            error_log("No se puede escribir en el directorio de logs");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al registrar acción: " . $e->getMessage());
        return false;
    }
}
