<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\registro.php
session_start();
require_once 'usuario.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario = new Usuario();
        $usuario->setNombre($_POST['nombre']);
        $usuario->setApellidos($_POST['apellidos']);
        $usuario->setEmail($_POST['email']);
        $usuario->setTelefono($_POST['telefono']);
        $usuario->setFechaNacimiento($_POST['fecha_nacimiento']);
        $usuario->setCiudad($_POST['ciudad']);
        $usuario->setCodigoPostal($_POST['codigo_postal']);
        
        if ($usuario->registrar($_POST['password'])) {
            $mensaje = "Usuario registrado correctamente. Ya puedes <a href='login.php'>iniciar sesión</a>";
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
        <a href="viajes.php">Viajes</a>
        <a href="login.php">Iniciar Sesión</a>
    </nav>
</header>

<main>
    <h2>Registro de Usuario</h2>
    
    <?php if ($mensaje): ?>
        <p><?= $mensaje ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <fieldset>
            <legend>Datos Personales</legend>
            
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required 
                   value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
            
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required
                   value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : '' ?>">
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono"
                   value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>">
            
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                   value="<?= isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '' ?>">
            
            <label for="ciudad">Ciudad:</label>
            <input type="text" id="ciudad" name="ciudad"
                   value="<?= isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : '' ?>">
            
            <label for="codigo_postal">Código Postal:</label>
            <input type="text" id="codigo_postal" name="codigo_postal"
                   value="<?= isset($_POST['codigo_postal']) ? htmlspecialchars($_POST['codigo_postal']) : '' ?>">
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Registrarse">
        </fieldset>
    </form>
    
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    <p><a href="viajes.php">Volver a la página principal</a></p>
</main>
</body>
</html>