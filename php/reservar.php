<?php
session_start();
require_once 'recurso.php';
require_once 'reserva.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$recursoId = $_GET['id'] ?? null;
if (!$recursoId) {
    header('Location: viajes.php');
    exit;
}

$recursos = new RecursoTuristico();
$recurso = $recursos->obtenerPorId($recursoId);
$horarios = $recursos->obtenerHorarios($recursoId);

if (!$recurso) {
    header('Location: viajes.php');
    exit;
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $reserva = new Reserva();
        $horarioId = $_POST['horario_id'];
        $numeroPersonas = (int)$_POST['numero_personas'];
        $comentarios = $_POST['comentarios'] ?? '';
        
        $horario = $recursos->obtenerHorarioPorId($horarioId);
        $precioTotal = $numeroPersonas * ($horario['precio_especial'] ?? $recurso['precio']);
        
        $reservaId = $reserva->crear(
            $_SESSION['usuario_id'],
            $recursoId,
            $horarioId,
            $numeroPersonas,
            $precioTotal,
            $comentarios
        );
        
        header('Location: mis_reservas.php?reserva=' . $reservaId);
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar - <?= htmlspecialchars($recurso['nombre']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Reservar en el sistema de Oviedo">
    <meta name="keywords" content="reservar, gestión, confirmar, Oviedo">
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
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
    <h2>Reservar: <?= htmlspecialchars($recurso['nombre']) ?></h2>
    
    <?php if ($error): ?>
        <p><strong>Error:</strong> <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <section>
        <h3>Información del Recurso</h3>
        <p><strong>Descripción:</strong> <?= htmlspecialchars($recurso['descripcion']) ?></p>
        <p><strong>Tipo:</strong> <?= htmlspecialchars($recurso['tipo_nombre'] ?: 'Sin categoría') ?></p>
        <p><strong>Ubicación:</strong> <?= htmlspecialchars($recurso['ubicacion']) ?></p>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($recurso['direccion']) ?></p>
        <p><strong>Duración:</strong> <?= $recurso['duracion_horas'] ?> hora(s)</p>
        <p><strong>Precio:</strong> €<?= number_format($recurso['precio'], 2) ?> por persona</p>
        <p><strong>Capacidad máxima:</strong> <?= $recurso['capacidad_maxima'] ?> personas</p>
    </section>
    
    <?php if (empty($horarios)): ?>
        <p><strong>No hay horarios disponibles para este recurso en este momento.</strong></p>
        <p><a href="viajes.php">← Volver al catálogo</a></p>
    <?php else: ?>
        <form method="POST" action="reservar.php?id=<?= $recursoId ?>">
            <fieldset>
                <legend>Datos de la Reserva</legend>
                
                <label for="horario_id">Horario disponible:</label>
                <select id="horario_id" name="horario_id" required onchange="calcularPrecio()">
                    <option value="">-- Selecciona un horario --</option>
                    <?php foreach ($horarios as $horario): ?>
                        <?php if ($horario['plazas_disponibles'] > 0): ?>
                            <option value="<?= $horario['id'] ?>" 
                                    data-precio="<?= $horario['precio_especial'] ?? $recurso['precio'] ?>"
                                    data-plazas="<?= $horario['plazas_disponibles'] ?>">
                                <?= date('d/m/Y H:i', strtotime($horario['fecha_inicio'])) ?> - 
                                <?= date('H:i', strtotime($horario['fecha_fin'])) ?>
                                (<?= $horario['plazas_disponibles'] ?> plazas disponibles)
                                <?php if ($horario['precio_especial']): ?>
                                    - Precio especial: €<?= number_format($horario['precio_especial'], 2) ?>
                                <?php endif; ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                
                <label for="numero_personas">Número de personas:</label>
                <input type="number" id="numero_personas" name="numero_personas" 
                       min="1" max="<?= $recurso['capacidad_maxima'] ?>" value="1" required
                       onchange="calcularPrecio()">
                
                <label for="comentarios">Comentarios o solicitudes especiales (opcional):</label>
                <textarea id="comentarios" name="comentarios" rows="3" 
                          placeholder="Ej: Necesidades especiales, preferencias, etc."></textarea>
                
                <p id="precio_total"><strong>Precio total: €0.00</strong></p>
                
                <input type="submit" value="Confirmar Reserva">
            </fieldset>
        </form>
    <?php endif; ?>
    
    <p><a href="viajes.php">← Volver al catálogo de recursos</a></p>
</main>

<script>
function calcularPrecio() {
    const horarioSelect = document.getElementById('horario_id');
    const numeroPersonas = document.getElementById('numero_personas').value;
    const precio = document.getElementById('precio_total');
    
    if (horarioSelect.value && numeroPersonas) {
        const option = horarioSelect.selectedOptions[0];
        const precio = parseFloat(option.dataset.precio);
        const total = precio * parseInt(numeroPersonas);
        precio.innerHTML = `<strong>Precio total: €${total.toFixed(2)}</strong>`;
        
        // Verificar plazas disponibles
        const plazasDisponibles = parseInt(option.dataset.plazas);
        const numPersonasInput = document.getElementById('numero_personas');
        numPersonasInput.max = plazasDisponibles;
        
        if (parseInt(numeroPersonas) > plazasDisponibles) {
            numPersonasInput.value = plazasDisponibles;
            calcularPrecio();
            alert(`Solo hay ${plazasDisponibles} plazas disponibles para este horario.`);
        }
    } else {
        precio.innerHTML = '<strong>Precio total: €0.00</strong>';
    }
}

// Validar formulario antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    const horario = document.getElementById('horario_id').value;
    const personas = document.getElementById('numero_personas').value;
    
    if (!horario) {
        alert('Por favor selecciona un horario.');
        e.preventDefault();
        return;
    }
    
    if (!personas || personas < 1) {
        alert('Por favor indica el número de personas.');
        e.preventDefault();
        return;
    }
    
    if (!confirm('¿Confirmas que deseas realizar esta reserva?')) {
        e.preventDefault();
    }
});
</script>
</body>
</html>