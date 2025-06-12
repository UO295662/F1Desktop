<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\registro.php
session_start();
require_once 'database.php';
require_once 'usuario.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $usuario = new Usuario($db);
        $usuario->nombre = $_POST['nombre'];
        $usuario->apellidos = $_POST['apellidos'];
        $usuario->email = $_POST['email'];
        $usuario->telefono = $_POST['telefono'] ?? '';
        $usuario->password_hash = $_POST['password'];
        
        if ($usuario->emailExiste()) {
            $error = "El email ya está registrado";
        } else if ($usuario->crear()) {
            $mensaje = "Usuario registrado correctamente. Ya puedes <a href='login.php'>iniciar sesión</a>";
        } else {
            $error = "Error al registrar el usuario";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Oviedo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Página de registro para el sistema de reservas de Oviedo">
    <meta name="keywords" content="registro, cuenta, reservas, Oviedo">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/favicon.ico">
</head>
<body>
<header>
    <h1><a href="../index.html">Oviedo</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="lista.php">Viajes</a>
        <a href="login.php">Iniciar Sesión</a>
    </nav>
</header>

<main>
    <h2>Registro de Usuario</h2>
    
    <?php if ($mensaje): ?>
        <p><?= $mensaje ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p ><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="registro.php">
        <fieldset>
            <legend>Datos Personales</legend>
            
            <label>Nombre:</label>
            <input type="text" name="nombre" required 
                   value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
            
            <label>Apellidos:</label>
            <input type="text" name="apellidos" required
                   value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : '' ?>">
            
            <label>Email:</label>
            <input type="email" name="email" required
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            
            <label>Teléfono (opcional):</label>
            <input type="tel" name="telefono"
                   value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>">
            
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            
            <input type="submit" value="Registrarse">
        </fieldset>
    </form>
    
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    <p><a href="lista.php">Volver a la página principal</a></p>
</main>
</body>
</html>