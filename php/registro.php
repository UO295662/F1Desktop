<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\registro.php
require_once 'Usuario.php';

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
            $mensaje = "Usuario registrado correctamente. Ya puede iniciar sesión.";
        } else {
            $error = "Error al registrar el usuario.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario - Oviedo Turismo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
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
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
        <p><a href="login.php">Ir a iniciar sesión</a></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <?php if (!$mensaje): ?>
    <form method="POST" action="">
        <fieldset>
            <legend>Datos Personales</legend>
            
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono">
            
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            
            <label for="ciudad">Ciudad:</label>
            <input type="text" id="ciudad" name="ciudad">
            
            <label for="codigo_postal">Código Postal:</label>
            <input type="text" id="codigo_postal" name="codigo_postal">
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Registrarse">
        </fieldset>
    </form>
    <?php endif; ?>
    
    <p><a href="viajes.php">Volver a la página principal</a></p>
</main>
</body>
</html>