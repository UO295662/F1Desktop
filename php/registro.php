<?php
session_start();
require_once 'database.php';
require_once 'usuario.php';

class Registro {
    private $mensaje = '';
    private $error = '';
    private $db;
    
    public function __construct() {
        $this->procesarFormulario();
    }
    
    private function procesarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_POST['nombre']) || !isset($_POST['apellidos']) || 
                    !isset($_POST['email']) || !isset($_POST['password'])) {
                    $this->error = "Por favor, complete todos los campos requeridos";
                } else {
                    $database = new Database();
                    $this->db = $database->getConnection();
                    
                    $usuario = new Usuario($this->db);
                    $usuario->nombre = trim($_POST['nombre']);
                    $usuario->apellidos = trim($_POST['apellidos']);
                    $usuario->email = trim($_POST['email']);
                    $usuario->password_hash = $_POST['password'];
                    
                    // Validaciones adicionales
                    if (empty($usuario->nombre) || empty($usuario->apellidos) || 
                        empty($usuario->email) || empty($usuario->password_hash)) {
                        $this->error = "Todos los campos son obligatorios";
                    } elseif (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
                        $this->error = "El formato del email no es válido";
                    } elseif (strlen($usuario->password_hash) < 6) {
                        $this->error = "La contraseña debe tener al menos 6 caracteres";
                    } elseif ($usuario->emailExiste()) {
                        $this->error = "El email ya está registrado";
                    } elseif ($usuario->crear()) {
                        $this->mensaje = "Usuario registrado correctamente. Ya puedes <a href='login.php'>iniciar sesión</a>";
                        $_POST = [];
                    } else {
                        $this->error = "Error al registrar el usuario";
                    }
                }
            } catch (Exception $e) {
                $this->error = "Error del servidor: " . $e->getMessage();
            }
        }
    }
    
    public function getMensaje() {
        return $this->mensaje;
    }
    
    public function getError() {
        return $this->error;
    }
    
    public function mostrarFormulario() {
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
                <a href="login.php" >Iniciar Sesión</a>
                <a href="registro.php" class="active">Registro</a>
            </nav>
        </header>   
        <p>Estás en: <a href="../index.html">Inicio</a> >> Registro</p>
        <main>
            <h2>Registro de Usuario</h2>
            
            <?php if ($this->mensaje): ?>
                <p><?= $this->mensaje ?></p>
            <?php endif; ?>
            
            <?php if ($this->error): ?>
                <p><?= htmlspecialchars($this->error) ?></p>
            <?php endif; ?>
            
            <form method="POST" action="registro.php">
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
                   
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    
                    <input type="submit" value="Registrarse">
                </fieldset>
            </form>
            
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            <p><a href="lista.php">Volver a la página principal</a></p>
        </main>
        </body>
        </html>
        <?php
    }
}

$registro = new Registro();
$registro->mostrarFormulario();
?>