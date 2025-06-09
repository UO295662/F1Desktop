<?php
session_start();
require_once 'reserva.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$reservas = new Reserva();
$misReservas = $reservas->obtenerPorUsuario($_SESSION['usuario_id']);

$mensaje = '';
if (isset($_GET['reserva'])) {
    $mensaje = "¡Reserva confirmada correctamente! Tu reserva ha sido registrada.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva'])) {
    try {
        $reservas->cancelar($_POST['reserva_id'], $_SESSION['usuario_id']);
        $mensaje = "Reserva cancelada correctamente.";
        $misReservas = $reservas->obtenerPorUsuario($_SESSION['usuario_id']);
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas - Oviedo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Gestión de mis reservas en el sistema de Oviedo">
    <meta name="keywords" content="mis reservas, gestión, cancelar, Oviedo">
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
        <a href="mis_reservas.php">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a>
    </nav>
</header>

<main>
    <h2>Mis Reservas</h2>
    
    <?php if ($mensaje): ?>
        <p><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    
    <?php if (empty($misReservas)): ?>
        <section>
            <p>No tienes reservas realizadas.</p>
            <p><a href="viajes.php">Ver recursos disponibles para reservar →</a></p>
        </section>
    <?php else: ?>
        <section>
            <h3>Listado de Reservas</h3>
            <?php foreach ($misReservas as $reserva): ?>
                <article>
                    <h4><?= htmlspecialchars($reserva['recurso_nombre']) ?></h4>
                    
                    <p><strong>Fecha y hora:</strong>
                       <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> - 
                       <?= date('H:i', strtotime($reserva['fecha_fin'])) ?></p>
                    <p><strong>Número de personas:</strong> <?= $reserva['numero_personas'] ?></p>
                    <p><strong>Precio total:</strong> €<?= number_format($reserva['precio_total'], 2) ?></p>
                    
                    <p><strong>Estado:</strong> 
                       <span>
                           <?= ucfirst($reserva['estado']) ?>
                       </span>
                    </p>
                    <p><strong>Fecha de reserva:</strong> <?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></p>
                    
                    <?php if ($reserva['fecha_cancelacion']): ?>
                        <p><strong>Fecha de cancelación:</strong> <?= date('d/m/Y H:i', strtotime($reserva['fecha_cancelacion'])) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($reserva['comentarios']): ?>
                        <p><strong>Comentarios:</strong> <?= htmlspecialchars($reserva['comentarios']) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($reserva['estado'] !== 'cancelada' && strtotime($reserva['fecha_inicio']) > time()): ?>
                        <form method="POST" 
                              onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reserva? Esta acción no se puede deshacer.');">
                            <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                            <input type="submit" name="cancelar_reserva" value="Cancelar Reserva">
                        </form>
                    <?php elseif (strtotime($reserva['fecha_inicio']) <= time() && $reserva['estado'] === 'confirmada'): ?>
                        <p>⏰ Reserva en curso o completada</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
    <p><a href="viajes.php">← Volver al catálogo de recursos</a></p>
</main>
</body>
</html>