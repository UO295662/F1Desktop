<?php
session_start();
require_once 'database.php';
require_once 'recurso.php';
require_once 'reserva.php';
require_once 'horarios.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$recursoId = $_GET['recurso'] ?? null;
if (!$recursoId) {
    header('Location: lista.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$recursos = new RecursoTuristico($db);
$recursos->id = $recursoId;

$recurso_data = null;
if ($recursos->leerUno()) {
    $recurso_data = [
        'id' => $recursos->id,
        'nombre' => $recursos->nombre,
        'descripcion' => $recursos->descripcion,
        'tipo_recurso_id' => $recursos->tipo_recurso_id,
        'ubicacion' => $recursos->ubicacion,
        'direccion' => $recursos->direccion,
        'precio' => $recursos->precio,
        'duracion_horas' => $recursos->duracion_horas,
        'capacidad_maxima' => $recursos->capacidad_maxima
    ];
}

if (!$recurso_data) {
    header('Location: lista.php');
    exit;
}

// Obtener horarios reales de la base de datos
$horarios_obj = new HorarioRecurso($db);
$horarios_obj->recurso_id = $recursoId;
$horarios_stmt = $horarios_obj->leerPorRecurso();
$horarios = $horarios_stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $reserva = new Reserva($db);
        $horarioId = $_POST['horario_id'];
        $numeroPersonas = (int)$_POST['numero_personas'];
        
        // Verificar que el horario existe y tiene plazas disponibles
        $horario_verificar = new HorarioRecurso($db);
        $horario_verificar->id = $horarioId;
        if (!$horario_verificar->leerUno()) {
            throw new Exception("Horario no encontrado");
        }
        
        if ($horario_verificar->plazas_disponibles < $numeroPersonas) {
            throw new Exception("No hay suficientes plazas disponibles");
        }
        
        // Calcular precio total
        $precioFinal = $horario_verificar->precio_especial ?: $recurso_data['precio'];
        $precioTotal = $numeroPersonas * $precioFinal;
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Crear reserva
            $reserva->usuario_id = $_SESSION['usuario_id'];
            $reserva->recurso_id = $recursoId;
            $reserva->horario_id = $horarioId;
            $reserva->numero_personas = $numeroPersonas;
            $reserva->precio_total = $precioTotal;
            
            if (!$reserva->crear()) {
                throw new Exception("Error al crear la reserva");
            }
            
            // Actualizar plazas disponibles
            $horario_verificar->plazas_disponibles -= $numeroPersonas;
            if (!$horario_verificar->actualizarPlazas()) {
                throw new Exception("Error al actualizar las plazas disponibles");
            }
            
            $db->commit();
            header('Location: mis_reservas.php?reserva=1');
            exit;
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
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
    <title>Reservar - <?= htmlspecialchars($recurso_data['nombre']) ?></title>
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
        <a href="lista.php">Viajes</a>
        <a href="mis_reservas.php">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a>
    </nav>
</header>

<main>
    <h2>Reservar: <?= htmlspecialchars($recurso_data['nombre']) ?></h2>
    
    <?php if ($error): ?>
        <p>Error: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <section>
        <h3>Información del Recurso</h3>
        <p>Descripción: <?= htmlspecialchars($recurso_data['descripcion']) ?></p>
        <p>Ubicación: <?= htmlspecialchars($recurso_data['ubicacion']) ?></p>
        <p>Dirección: <?= htmlspecialchars($recurso_data['direccion']) ?></p>
        <p>Duración: <?= $recurso_data['duracion_horas'] ?> hora(s)</p>
        <p>Precio: €<?= number_format($recurso_data['precio'], 2) ?> por persona</p>
        <p>Capacidad máxima: <?= $recurso_data['capacidad_maxima'] ?> personas</p>
    </section>
    
    <?php if (empty($horarios)): ?>
        <p>No hay horarios disponibles para este recurso en este momento.</p>
        <p><a href="lista.php">← Volver al catálogo</a></p>
    <?php else: ?>
        <form method="POST" action="">
            <fieldset>
                <legend>Datos de la Reserva</legend>
                
                <label>Horario disponible:</label>
                <select name="horario_id" required onchange="calcularPrecio()">
                    <option value="">-- Selecciona un horario --</option>
                    <?php foreach ($horarios as $horario): ?>
                        <option value="<?= $horario['id'] ?>" 
                                data-precio="<?= $horario['precio_especial'] ?: $recurso_data['precio'] ?>"
                                data-plazas="<?= $horario['plazas_disponibles'] ?>">
                            <?= date('d/m/Y H:i', strtotime($horario['fecha_inicio'])) ?> - 
                            <?= date('H:i', strtotime($horario['fecha_fin'])) ?>
                            (<?= $horario['plazas_disponibles'] ?> plazas disponibles)
                            <?php if ($horario['precio_especial']): ?>
                                - Precio especial: €<?= number_format($horario['precio_especial'], 2) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label>Número de personas:</label>
                <input type="number" name="numero_personas" 
                       min="1" max="<?= $recurso_data['capacidad_maxima'] ?>" value="1" required
                       onchange="calcularPrecio()">
                
                <p>Precio total: €0.00</p>
                
                <input type="submit" value="Confirmar Reserva">
            </fieldset>
        </form>
    <?php endif; ?>
    
    <p><a href="lista.php">← Volver al catálogo de recursos</a></p>
</main>

<script>
function calcularPrecio() {
    const horarioSelect = document.querySelector('select[name="horario_id"]');
    const numeroPersonas = document.querySelector('input[name="numero_personas"]').value;
    const precioElement = document.querySelector('p:nth-of-type(1)');
    
    if (horarioSelect.value && numeroPersonas) {
        const option = horarioSelect.selectedOptions[0];
        const precio = parseFloat(option.dataset.precio);
        const total = precio * parseInt(numeroPersonas);
        precioElement.innerHTML = `Precio total: €${total.toFixed(2)}`;
        
        // Verificar plazas disponibles
        const plazasDisponibles = parseInt(option.dataset.plazas);
        const numPersonasInput = document.querySelector('input[name="numero_personas"]');
        numPersonasInput.max = plazasDisponibles;
        
        if (parseInt(numeroPersonas) > plazasDisponibles) {
            numPersonasInput.value = plazasDisponibles;
            calcularPrecio();
            alert(`Solo hay ${plazasDisponibles} plazas disponibles para este horario.`);
        }
    } else {
        precioElement.innerHTML = 'Precio total: €0.00';
    }
}
</script>
</body>
</html>