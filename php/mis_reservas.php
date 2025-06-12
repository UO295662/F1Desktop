<?php
session_start();
require_once 'database.php';
require_once 'reserva.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$reservas = new Reserva($db);
$reservas->usuario_id = $_SESSION['usuario_id'];
$misReservas = $reservas->leerPorUsuario();

$mensaje = '';
if (isset($_GET['reserva'])) {
    $mensaje = "¡Reserva confirmada correctamente! Tu reserva ha sido registrada.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva'])) {
    try {
        $reserva_cancelar = new Reserva($db);
        $reserva_cancelar->id = $_POST['reserva_id'];
        $reserva_cancelar->usuario_id = $_SESSION['usuario_id'];
        
        if ($reserva_cancelar->cancelar()) {
            $mensaje = "Reserva cancelada correctamente.";
            // Recargar las reservas
            $reservas->usuario_id = $_SESSION['usuario_id'];
            $misReservas = $reservas->leerPorUsuario();
        } else {
            $mensaje = "Error al cancelar la reserva.";
        }
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
        <a href="lista.php">Viajes</a>
        <a href="mis_reservas.php">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a>
    </nav>
</header>

<main>
    <h2>Mis Reservas</h2>
    
    <?php if ($mensaje): ?>
        <p><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    
    <?php 
    $reservas_array = $misReservas->fetchAll(PDO::FETCH_ASSOC);
    if (empty($reservas_array)): ?>
        <section>
            <p>No tienes reservas realizadas.</p>
            <p><a href="lista.php">Ver recursos disponibles para reservar →</a></p>
        </section>
    <?php else: ?>
        <section>
            <h3>Listado de Reservas</h3>
            <?php foreach ($reservas_array as $reserva): ?>
                <article>
                    <h4><?= htmlspecialchars($reserva['recurso_nombre']) ?></h4>
                    
                    <p>Tipo: <?= htmlspecialchars($reserva['tipo_nombre'] ?: 'Sin categoría') ?></p>
                    <p>Ubicación: <?= htmlspecialchars($reserva['ubicacion']) ?></p>
                    <p>Fecha y hora:
                       <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> - 
                       <?= date('H:i', strtotime($reserva['fecha_fin'])) ?></p>
                    <p>Número de personas: <?= $reserva['numero_personas'] ?></p>
                    <p>Precio total: €<?= number_format($reserva['precio_total'], 2) ?></p>
                    
                    <p>Estado: 
                       <span>
                           <?= ucfirst($reserva['estado']) ?>
                       </span>
                    </p>
                    <p>Fecha de reserva: <?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></p>
                    
                    <?php if ($reserva['estado'] !== 'cancelada' && strtotime($reserva['fecha_inicio']) > time()): ?>
                        <form method="POST" 
                              onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reserva? Esta acción no se puede deshacer.');">
                            <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                            <input type="submit" name="cancelar_reserva" value="Cancelar Reserva" >
                        </form>
                    <?php elseif (strtotime($reserva['fecha_inicio']) <= time() && $reserva['estado'] === 'confirmada'): ?>
                        <p>⏰ Reserva en curso o completada</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
    <p><a href="lista.php">← Volver al catálogo de recursos</a></p>
</main>
</body>
</html>