<?php
// filepath: c:\xampp2\htdocs\F1Desktop\php\reservar.php
session_start();
require_once 'database.php';
require_once 'recurso.php';
require_once 'reserva.php';
require_once 'horarios.php';

class Reservar {
    private $db;
    private $recursoId;
    private $recurso_data;
    private $horarios;
    private $mensaje;
    private $error;
    private $precio_calculado;
    private $horario_seleccionado;
    private $numero_personas_seleccionado;

    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        $this->recursoId = $_GET['recurso'] ?? null;
        if (!$this->recursoId) {
            header('Location: lista.php');
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->mensaje = '';
        $this->error = '';
        $this->precio_calculado = 0.00;
        $this->horario_seleccionado = $_POST['horario_id'] ?? '';
        $this->numero_personas_seleccionado = $_POST['numero_personas'] ?? 1;
    }

    public function inicializar() {
        $this->cargarRecurso();
        $this->cargarHorarios();
        $this->calcularPrecio();
        $this->procesarReserva();
    }

    private function cargarRecurso() {
        $recursos = new RecursoTuristico($this->db);
        $recursos->id = $this->recursoId;

        if ($recursos->leerUno()) {
            $this->recurso_data = [
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

        if (!$this->recurso_data) {
            header('Location: lista.php');
            exit;
        }
    }

    private function cargarHorarios() {
        $horarios_obj = new HorarioRecurso($this->db);
        $horarios_obj->recurso_id = $this->recursoId;
        $horarios_stmt = $horarios_obj->leerPorRecurso();
        $this->horarios = $horarios_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calcularPrecio() {
        if ($this->horario_seleccionado && $this->numero_personas_seleccionado > 0) {
            // Buscar el horario seleccionado
            foreach ($this->horarios as $horario) {
                if ($horario['id'] == $this->horario_seleccionado) {
                    $precio_unitario = $horario['precio_especial'] ?: $this->recurso_data['precio'];
                    $this->precio_calculado = $precio_unitario * $this->numero_personas_seleccionado;
                    
                    // Verificar límite de plazas
                    if ($this->numero_personas_seleccionado > $horario['plazas_disponibles']) {
                        $this->numero_personas_seleccionado = $horario['plazas_disponibles'];
                        $this->precio_calculado = $precio_unitario * $this->numero_personas_seleccionado;
                        $this->error = "Solo hay {$horario['plazas_disponibles']} plazas disponibles para este horario.";
                    }
                    break;
                }
            }
        }
    }

    private function procesarReserva() {
        // CAMBIO CLAVE: Solo procesar reserva si se presiona el botón específico de confirmar
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reserva']) && $_POST['confirmar_reserva'] === 'confirmar') {
            try {
                $reserva = new Reserva($this->db);
                $horarioId = $_POST['horario_id'];
                $numeroPersonas = (int)$_POST['numero_personas'];
                
                // Verificar que el horario existe y tiene plazas disponibles
                $horario_verificar = new HorarioRecurso($this->db);
                $horario_verificar->id = $horarioId;
                if (!$horario_verificar->leerUno()) {
                    throw new Exception("Horario no encontrado");
                }
                
                if ($horario_verificar->plazas_disponibles < $numeroPersonas) {
                    throw new Exception("No hay suficientes plazas disponibles");
                }
                
                // Calcular precio total
                $precioFinal = $horario_verificar->precio_especial ?: $this->recurso_data['precio'];
                $precioTotal = $numeroPersonas * $precioFinal;
                
                // Iniciar transacción
                $this->db->beginTransaction();
                
                try {
                    // Crear reserva
                    $reserva->usuario_id = $_SESSION['usuario_id'];
                    $reserva->recurso_id = $this->recursoId;
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
                    
                    $this->db->commit();
                    header('Location: mis_reservas.php?reserva=1');
                    exit;
                    
                } catch (Exception $e) {
                    $this->db->rollback();
                    throw $e;
                }
                
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    public function getRecursoData() {
        return $this->recurso_data;
    }

    public function getHorarios() {
        return $this->horarios;
    }

    public function getError() {
        return $this->error;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function getPrecioCalculado() {
        return $this->precio_calculado;
    }

    public function getHorarioSeleccionado() {
        return $this->horario_seleccionado;
    }

    public function getNumeroPersonasSeleccionado() {
        return $this->numero_personas_seleccionado;
    }

    public function getPlazasMaximas($horario_id) {
        foreach ($this->horarios as $horario) {
            if ($horario['id'] == $horario_id) {
                return $horario['plazas_disponibles'];
            }
        }
        return $this->recurso_data['capacidad_maxima'];
    }
}

// Instanciar y ejecutar la clase
$reservar = new Reservar();
$reservar->inicializar();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar - <?= htmlspecialchars($reservar->getRecursoData()['nombre']) ?></title>
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
    <h2>Reservar: <?= htmlspecialchars($reservar->getRecursoData()['nombre']) ?></h2>
    
    <?php if ($reservar->getError()): ?>
        <p><strong>Error:</strong> <?= htmlspecialchars($reservar->getError()) ?></p>
    <?php endif; ?>
    
    <section>
        <h3>Información del Recurso</h3>
        <p><strong>Descripción:</strong> <?= htmlspecialchars($reservar->getRecursoData()['descripcion']) ?></p>
        <p><strong>Ubicación:</strong> <?= htmlspecialchars($reservar->getRecursoData()['ubicacion']) ?></p>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($reservar->getRecursoData()['direccion']) ?></p>
        <p><strong>Duración:</strong> <?= $reservar->getRecursoData()['duracion_horas'] ?> hora(s)</p>
        <p><strong>Precio:</strong> €<?= number_format($reservar->getRecursoData()['precio'], 2) ?> por persona</p>
        <p><strong>Capacidad máxima:</strong> <?= $reservar->getRecursoData()['capacidad_maxima'] ?> personas</p>
    </section>
    
    <?php if (empty($reservar->getHorarios())): ?>
        <p>No hay horarios disponibles para este recurso en este momento.</p>
        <p><a href="lista.php">← Volver al catálogo</a></p>
    <?php else: ?>
        <form method="POST" action="">
            <fieldset>
                <legend>Datos de la Reserva</legend>
                
                <label for="horario_id">Horario disponible:</label>
                <select id="horario_id" name="horario_id" required onchange="this.form.submit()">
                    <option value="">-- Selecciona un horario --</option>
                    <?php foreach ($reservar->getHorarios() as $horario): ?>
                        <option value="<?= $horario['id'] ?>" 
                                <?= $reservar->getHorarioSeleccionado() == $horario['id'] ? 'selected' : '' ?>>
                            <?= date('d/m/Y H:i', strtotime($horario['fecha_inicio'])) ?> - 
                            <?= date('H:i', strtotime($horario['fecha_fin'])) ?>
                            (<?= $horario['plazas_disponibles'] ?> plazas disponibles)
                            <?php if ($horario['precio_especial']): ?>
                                - Precio especial: €<?= number_format($horario['precio_especial'], 2) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="numero_personas">Número de personas:</label>
                <input type="number" id="numero_personas" name="numero_personas"
                       min="1" 
                       max="<?= $reservar->getHorarioSeleccionado() ? $reservar->getPlazasMaximas($reservar->getHorarioSeleccionado()) : $reservar->getRecursoData()['capacidad_maxima'] ?>" 
                       value="<?= $reservar->getNumeroPersonasSeleccionado() ?>" 
                       required onchange="this.form.submit()">
                
                <p><strong>Precio total: €<?= number_format($reservar->getPrecioCalculado(), 2) ?></strong></p>
            </fieldset>
        </form>
        
        <?php if ($reservar->getHorarioSeleccionado() && $reservar->getPrecioCalculado() > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="horario_id" value="<?= $reservar->getHorarioSeleccionado() ?>">
                <input type="hidden" name="numero_personas" value="<?= $reservar->getNumeroPersonasSeleccionado() ?>">
                <input type="hidden" name="confirmar_reserva" value="confirmar">
                <input type="submit" value="Confirmar Reserva">
            </form>
        <?php endif; ?>
    <?php endif; ?>
    
    <p><a href="lista.php">← Volver al catálogo de recursos</a></p>
</main>
</body>
</html>