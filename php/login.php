<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\login.php
session_start();
require_once 'database.php';
require_once 'usuario.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $usuario = new Usuario($db);
        $usuario->email = $_POST['email'];
        $usuario->password_hash = $_POST['password'];
        
        if ($usuario->login()) {
            $_SESSION['usuario_id'] = $usuario->id;
            $_SESSION['usuario_nombre'] = $usuario->nombre . ' ' . $usuario->apellidos;
            $_SESSION['usuario_email'] = $usuario->email;
            header('Location: lista.php');
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
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
    <title>Login - Oviedo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Página de inicio de sesión para el sistema de reservas de Oviedo">
    <meta name="keywords" content="login, acceso, reservas, Oviedo">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/favicon.ico">
</head>
<body>
<header>
    <h1><a href="../index.html">Oviedo</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="registro.php">Registro</a>
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
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            
            <input type="submit" value="Iniciar Sesión">
        </fieldset>
    </form>
    
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    <p><a href="lista.php">Volver a la página principal</a></p>
</main>
</body>
</html>