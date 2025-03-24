<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Registro</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/main.js" defer></script>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h3>Menú</h3>
            <ul>
                <li><a href="#" onclick="cargarRegistros()">Ver Registros</a></li>
                <li><a href="#" onclick="cargarEdicionRegistros()">Editar Registros</a></li>
                <li><a href="#" onclick="cargarLog()">Ver Archivo Log</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
        <main id="contenido-principal">
            <h1>Bienvenido, <?php echo $_SESSION['username']; ?></h1>
            <div id="resultados"></div>
        </main>
    </div>
</body>
</html>

