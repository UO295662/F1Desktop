<?php
session_start();
require_once 'database.php';
require_once 'recurso.php';

class Lista {
    private $db;
    private $recursos;
    private $listaRecursos;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->recursos = new RecursoTuristico($this->db);
        $this->listaRecursos = $this->recursos->leerTodos();
    }
    
    public function getListaRecursos() {
        return $this->listaRecursos;
    }
    
    public function usuarioLogueado() {
        return isset($_SESSION['usuario_id']);
    }
    
    public function getNombreUsuario() {
        return $_SESSION['usuario_nombre'] ?? '';
    }
}

$lista = new Lista();
$listaRecursos = $lista->getListaRecursos();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Viajes - Oviedo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Gestión de recursos turísticos en el sistema de Oviedo">
    <meta name="keywords" content="recursos, gestión, turismo, Oviedo">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/favicon.ico">
</head>
<body>
<header>
    <h1><a href="../index.html">Oviedo</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="lista.php" class="active">Recursos Turísticos</a>
        <?php if ($lista->usuarioLogueado()): ?>
            <a href="mis_reservas.php">Mis Reservas</a>
            <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($lista->getNombreUsuario()) ?>)</a>
        <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
            <a href="registro.php">Registro</a>
        <?php endif; ?>
    </nav>
</header>
<p>Estás en: <a href="../index.html">Inicio</a> >> <a href="login.php">Iniciar Sesión</a> >> Recursos Turísticos</p>
<main>
    <h2>Recursos Turísticos Disponibles</h2>
    
    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <p>Para realizar reservas, necesitas <a href="login.php">iniciar sesión</a> o <a href="registro.php">registrarte</a>.</p>
    <?php endif; ?>
    
    <section>
        <h3>Catálogo de Recursos</h3>
        
        <?php 
        $recursos_array = $listaRecursos->fetchAll(PDO::FETCH_ASSOC);
        if (empty($recursos_array)): ?>
            <p>No hay recursos turísticos disponibles en este momento.</p>
        <?php else: ?>
            <?php foreach ($recursos_array as $recurso): ?>
                <article>
                    <h4><?= htmlspecialchars($recurso['nombre']) ?></h4>
                    <p>Tipo: <?= htmlspecialchars($recurso['tipo_nombre'] ?: 'Sin categoría') ?></p>
                    <p>Descripción: <?= htmlspecialchars($recurso['descripcion']) ?></p>
                    <p>Ubicación: <?= htmlspecialchars($recurso['ubicacion']) ?></p>
                    <p>Dirección: <?= htmlspecialchars($recurso['direccion']) ?></p>
                    <p>Capacidad: <?= $recurso['capacidad_maxima'] ?> personas</p>
                    <p>Precio: €<?= number_format($recurso['precio'], 2) ?> por persona</p>
                    <p>Duración: <?= $recurso['duracion_horas'] ?> hora(s)</p>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="reservar.php?recurso=<?= $recurso['id'] ?>">Reservar</a>
                    <?php else: ?>
                        <p><em>Inicia sesión para reservar</em></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>    
    </section>
</main>
</body>
</html>