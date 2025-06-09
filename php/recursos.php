<?php
session_start();
require_once 'recurso.php';

$recursos = new RecursoTuristico();
$listaRecursos = $recursos->obtenerTodos();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recursos Turísticos - Oviedo</title>
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
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="mis_reservas.php">Mis Reservas</a>
            <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
            <a href="reservas.php">Registro</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <h2>Recursos Turísticos Disponibles</h2>
    
    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <p><strong>Para realizar reservas, necesitas <a href="login.php">iniciar sesión</a> o <a href="reservas.php">registrarte</a>.</strong></p>
    <?php endif; ?>
    
    <section>
        <h3>Catálogo de Recursos</h3>
        
        <?php if (empty($listaRecursos)): ?>
            <p>No hay recursos turísticos disponibles en este momento.</p>
        <?php else: ?>
            <?php foreach ($listaRecursos as $recurso): ?>
                <article>
                    <h4><?= htmlspecialchars($recurso['nombre']) ?></h4>
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($recurso['tipo_nombre'] ?: 'Sin categoría') ?></p>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($recurso['descripcion']) ?></p>
                    <p><strong>Capacidad:</strong> <?= $recurso['capacidad_maxima'] ?> personas</p>
                    <p><strong>Precio:</strong> €<?= number_format($recurso['precio'], 2) ?> por persona</p>
                    <p><strong>Duración:</strong> <?= $recurso['duracion_horas'] ?> hora(s)</p>
                    <p><strong>Ubicación:</strong> <?= htmlspecialchars($recurso['ubicacion']) ?></p>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="reservar.php?id=<?= $recurso['id'] ?>">
                           Reservar
                        </a>
                    <?php else: ?>
                        <p>Inicia sesión para reservar</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>