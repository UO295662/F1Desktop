<?php
session_start();
require_once 'database.php';
require_once 'usuario.php';

class Login {
    private $db;
    private $error;
    private $usuario;

    public function __construct() {
        $this->error = '';
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuario = new Usuario($this->db);
    }

    public function inicializar() {
        $this->procesarLogin();
    }

    private function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                if (empty($email) || empty($password)) {
                    $this->error = "Todos los campos son obligatorios";
                    return;
                }

                $this->usuario->email = $email;
                $this->usuario->password_hash = $password;
                
                if ($this->usuario->login()) {
                    $this->establecerSesion();
                    $this->redirigirUsuario();
                } else {
                    $this->error = "Email o contraseña incorrectos";
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    private function establecerSesion() {
        $_SESSION['usuario_id'] = $this->usuario->id;
        $_SESSION['usuario_nombre'] = $this->usuario->nombre . ' ' . $this->usuario->apellidos;
        $_SESSION['usuario_email'] = $this->usuario->email;
    }

    private function redirigirUsuario() {
        header('Location: lista.php');
        exit;
    }

    public function getError() {
        return $this->error;
    }

    public function hayError() {
        return !empty($this->error);
    }

    public function getEmailAnterior() {
        return $_POST['email'] ?? '';
    }

    public static function cerrarSesion() {
        session_start();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    public static function verificarSesion() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function estaLogueado() {
        return isset($_SESSION['usuario_id']);
    }
}

// Instanciar y ejecutar la clase
$login = new Login();
$login->inicializar();
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
        <a href="login.php" class="active">Iniciar Sesión</a>
        <a href="registro.php">Registro</a>
    </nav>
</header>
<p>Estás en: <a href="../index.html">Inicio</a> >> Iniciar Sesión</p>
<main>
    <h2>Iniciar Sesión</h2>
    
    <?php if ($login->hayError()): ?>
        <p><strong>Error:</strong> <?= htmlspecialchars($login->getError()) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <fieldset>
            <legend>Acceso al Sistema</legend>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($login->getEmailAnterior()) ?>" 
                   required placeholder="tu@email.com">
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" 
                   required placeholder="Tu contraseña">

            <input type="submit" value="Iniciar Sesión">
        </fieldset>
    </form>
    
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    <p><a href="lista.php">Volver a la página principal</a></p>
</main>
</body>
</html>