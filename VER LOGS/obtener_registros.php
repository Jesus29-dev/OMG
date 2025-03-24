<?php
require_once 'config/database.php';

$stmt = $pdo->query("SELECT * FROM registro_acciones ORDER BY fecha DESC");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($registros);
?>