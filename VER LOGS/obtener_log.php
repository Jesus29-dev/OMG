<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit('No autorizado');
}

$logContent = file_get_contents('logs/sistema.log');
echo $logContent;
?>