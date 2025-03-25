<?php

// registrar_accion.php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('No autorizado');
}

require_once 'config/database.php';

$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
$nivel = filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_STRING);

if (!$accion || !$nivel) {
    http_response_code(400);
    exit('Datos incompletos');
}

if (!in_array($nivel, ['AVISO', 'MOVIMIENTO', 'ATAQUE'])) {
    http_response_code(400);
    exit('Nivel no válido');
}

registrarAccion($_SESSION['username'], $accion, $nivel);
echo 'OK';
