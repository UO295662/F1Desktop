<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\login.php
session_start();
require_once 'usuario.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario = new Usuario();
        if ($usuario->login($_POST['email'], $_POST['password'])) {
            $_SESSION['usuario_id'] = $usuario->getId();
            $_SESSION['usuario_nombre'] = $usuario->getNombre();
            $_SESSION['usuario_email'] = $usuario->getEmail();
            header('Location: recursos.php');
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
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
    <title>Iniciar Sesión - Oviedo Turismo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
</head>
<body>
<header>
    <h1><a href="../index.html">Oviedo</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="recursos.php">Viajes</a>
        <a href="reservas.php">Registro</a>
    </nav>
</header>

<main>
    <h2>Iniciar Sesión</h2>
    
    <?php if ($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <fieldset>
            <legend>Acceso al Sistema</legend>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Iniciar Sesión">
        </fieldset>
    </form>
    
    <p>¿No tienes cuenta? <a href="reservas.php">Regístrate aquí</a></p>
    <p><a href="recursos.php">Volver a la página principal</a></p>
</main>
</body>
</html>