<?php
session_start();
require_once 'database.php';
require_once 'reserva.php';

class MisReservas {
    private $db;
    private $usuario_id;
    private $mensaje;
    private $error;
    private $reservas_array;

    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        $this->usuario_id = $_SESSION['usuario_id'];
        $database = new Database();
        $this->db = $database->getConnection();
        $this->mensaje = '';
        $this->error = '';
        $this->reservas_array = [];
    }

    public function inicializar() {
        $this->verificarMensajeConfirmacion();
        $this->procesarCancelacion();
        $this->cargarReservas();
    }

    private function verificarMensajeConfirmacion() {
        if (isset($_GET['reserva'])) {
            $this->mensaje = "¡Reserva confirmada correctamente! Tu reserva ha sido registrada.";
        }
    }

    private function procesarCancelacion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva']) && isset($_GET['reserva_id'])) {
            try {
                $reserva_cancelar = new Reserva($this->db);
                $reserva_cancelar->id = $_GET['reserva_id'];
                $reserva_cancelar->usuario_id = $this->usuario_id;
                
                if ($reserva_cancelar->cancelar()) {
                    $this->mensaje = "Reserva cancelada correctamente.";
                } else {
                    $this->error = "Error al cancelar la reserva.";
                }
            } catch (Exception $e) {
                $this->error = "Error: " . $e->getMessage();
            }
        }
    }

    private function cargarReservas() {
        try {
            $reservas = new Reserva($this->db);
            $reservas->usuario_id = $this->usuario_id;
            $misReservas = $reservas->leerPorUsuario();
            $this->reservas_array = $misReservas->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = "Error al cargar las reservas: " . $e->getMessage();
            $this->reservas_array = [];
        }
    }

    public function fetchAll($tipo = PDO::FETCH_ASSOC) {
        return $this->reservas_array;
    }

    public function tieneReservas() {
        return !empty($this->reservas_array);
    }

    public function getReservas() {
        return $this->reservas_array;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function getError() {
        return $this->error;
    }

    public function hayMensaje() {
        return !empty($this->mensaje);
    }

    public function hayError() {
        return !empty($this->error);
    }

    public function puedeSerCancelada($reserva) {
        return $reserva['estado'] !== 'cancelada' && strtotime($reserva['fecha_inicio']) > time();
    }

    public function estaEnCursoOCompletada($reserva) {
        return strtotime($reserva['fecha_inicio']) <= time() && $reserva['estado'] === 'confirmada';
    }

    public function formatearFecha($fecha) {
        return date('d/m/Y H:i', strtotime($fecha));
    }

    public function formatearPrecio($precio) {
        return number_format($precio, 2);
    }

    public function obtenerEstadoFormateado($estado) {
        return ucfirst($estado);
    }

    public function obtenerTipoFormateado($tipo) {
        return $tipo ?: 'Sin categoría';
    }

    public function calcularPrecioTotal() {
        $total = 0;
        foreach ($this->reservas_array as $reserva) {
            if ($reserva['estado'] !== 'cancelada') {
                $total += $reserva['precio_total'];
            }
        }
        return $total;
    }
}

$misReservas = new MisReservas();
$misReservas->inicializar();

$mensaje = $misReservas->getMensaje();
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
        <a href="lista.php">Recursos Turísticos</a>
        <a href="mis_reservas.php" class="active">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a>
    </nav>
</header>

<p>Estás en: <a href="../index.html">Inicio</a> >> <a href="login.php">Iniciar Sesión</a> >> Mis Reservas</p>
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
        <h3>Precio total de reservas activas: €<?= number_format($misReservas->calcularPrecioTotal(), 2) ?></h3>
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
                       <p>
                           <?= ucfirst($reserva['estado']) ?>
                        </p>
                    </p>
                    <p>Fecha de reserva: <?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></p>
                    
                    <?php if ($reserva['estado'] !== 'cancelada' && strtotime($reserva['fecha_inicio']) > time()): ?>
                        <form method="POST" action="mis_reservas.php?reserva_id=<?= $reserva['id'] ?>"
                              onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reserva? Esta acción no se puede deshacer.');">
                            <input type="submit" name="cancelar_reserva" value="Cancelar Reserva">
                        </form>
                    <?php elseif (strtotime($reserva['fecha_inicio']) <= time() && $reserva['estado'] === 'confirmada'): ?>
                        <p>Reserva en curso o completada</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
    <p><a href="lista.php">← Volver al catálogo de recursos</a></p>
</main>
</body>
</html>