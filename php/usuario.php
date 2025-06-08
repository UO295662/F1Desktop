<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\usuario.php

// ==================== CONFIGURACIÓN DE BASE DE DATOS ====================
class Database {
    private $host = 'localhost';
    private $dbname = 'turismo_oviedo';
    private $username = 'DBUSER2025';
    private $password = 'DBPWD2025';
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection = null;
    }
}

// ==================== CLASE USUARIO ====================
class Usuario {
    private $db;
    private $idUsuario;
    private $nombre;
    private $apellidos;
    private $email;
    private $telefono;
    private $fechaNacimiento;
    private $ciudad;
    private $codigoPostal;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Getters y Setters
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setEmail($email) { $this->email = $email; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setFechaNacimiento($fecha) { $this->fechaNacimiento = $fecha; }
    public function setCiudad($ciudad) { $this->ciudad = $ciudad; }
    public function setCodigoPostal($codigo) { $this->codigoPostal = $codigo; }
    
    public function getIdUsuario() { return $this->idUsuario; }
    public function getNombre() { return $this->nombre; }
    public function getEmail() { return $this->email; }

    public function registrar($password) {
        try {
            if ($this->existeEmail($this->email)) {
                throw new Exception("El email ya está registrado");
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre, apellidos, email, telefono, fecha_nacimiento, ciudad, codigo_postal, password_hash) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->nombre, $this->apellidos, $this->email, $this->telefono,
                $this->fechaNacimiento, $this->ciudad, $this->codigoPostal, $passwordHash
            ]);

            if ($result) {
                $this->idUsuario = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Error al registrar usuario: " . $e->getMessage());
        }
    }

    public function iniciarSesion($email, $password) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                $this->idUsuario = $usuario['id_usuario'];
                $this->nombre = $usuario['nombre'];
                $this->apellidos = $usuario['apellidos'];
                $this->email = $usuario['email'];
                $this->telefono = $usuario['telefono'];
                $this->fechaNacimiento = $usuario['fecha_nacimiento'];
                $this->ciudad = $usuario['ciudad'];
                $this->codigoPostal = $usuario['codigo_postal'];
                
                session_start();
                $_SESSION['usuario_id'] = $this->idUsuario;
                $_SESSION['usuario_nombre'] = $this->nombre;
                $_SESSION['usuario_email'] = $this->email;
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Error al iniciar sesión: " . $e->getMessage());
        }
    }

    private function existeEmail($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public static function cerrarSesion() {
        session_start();
        session_destroy();
    }

    public static function estaLogueado() {
        session_start();
        return isset($_SESSION['usuario_id']);
    }

    public static function obtenerUsuarioSesion() {
        session_start();
        if (isset($_SESSION['usuario_id'])) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email']
            ];
        }
        return null;
    }
}

// ==================== CLASE RECURSO TURÍSTICO ====================
class RecursoTuristico {
    private $db;
    private $idRecurso;
    private $idTipo;
    private $nombre;
    private $descripcion;
    private $ubicacion;
    private $precio;
    private $plazasDisponibles;
    private $fechaInicio;
    private $fechaFin;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getIdRecurso() { return $this->idRecurso; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getUbicacion() { return $this->ubicacion; }
    public function getPrecio() { return $this->precio; }
    public function getPlazasDisponibles() { return $this->plazasDisponibles; }
    public function getFechaInicio() { return $this->fechaInicio; }
    public function getFechaFin() { return $this->fechaFin; }

    public function obtenerTodos() {
        try {
            $sql = "SELECT r.*, t.nombre_tipo 
                    FROM recursos_turisticos r 
                    INNER JOIN tipos_recursos t ON r.id_tipo = t.id_tipo 
                    WHERE r.activo = 1 AND r.fecha_fin > NOW() 
                    ORDER BY r.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener recursos: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = "SELECT r.*, t.nombre_tipo 
                    FROM recursos_turisticos r 
                    INNER JOIN tipos_recursos t ON r.id_tipo = t.id_tipo 
                    WHERE r.id_recurso = ? AND r.activo = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $recurso = $stmt->fetch();

            if ($recurso) {
                $this->idRecurso = $recurso['id_recurso'];
                $this->idTipo = $recurso['id_tipo'];
                $this->nombre = $recurso['nombre'];
                $this->descripcion = $recurso['descripcion'];
                $this->ubicacion = $recurso['ubicacion'];
                $this->precio = $recurso['precio'];
                $this->plazasDisponibles = $recurso['plazas_disponibles'];
                $this->fechaInicio = $recurso['fecha_inicio'];
                $this->fechaFin = $recurso['fecha_fin'];
                return $recurso;
            }
            return null;
        } catch (Exception $e) {
            throw new Exception("Error al obtener recurso: " . $e->getMessage());
        }
    }

    public function obtenerTipos() {
        try {
            $sql = "SELECT * FROM tipos_recursos ORDER BY nombre_tipo";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener tipos: " . $e->getMessage());
        }
    }

    public function verificarDisponibilidad($numPersonas) {
        return $this->plazasDisponibles >= $numPersonas;
    }

    public function calcularPrecioTotal($numPersonas) {
        return $this->precio * $numPersonas;
    }

    public function reducirPlazas($numPersonas) {
        try {
            $sql = "UPDATE recursos_turisticos SET plazas_disponibles = plazas_disponibles - ? WHERE id_recurso = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$numPersonas, $this->idRecurso]);
        } catch (Exception $e) {
            throw new Exception("Error al reducir plazas: " . $e->getMessage());
        }
    }

    public function aumentarPlazas($numPersonas) {
        try {
            $sql = "UPDATE recursos_turisticos SET plazas_disponibles = plazas_disponibles + ? WHERE id_recurso = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$numPersonas, $this->idRecurso]);
        } catch (Exception $e) {
            throw new Exception("Error al aumentar plazas: " . $e->getMessage());
        }
    }
}

// ==================== CLASE RESERVA ====================
class Reserva {
    private $db;
    private $idReserva;
    private $idUsuario;
    private $idRecurso;
    private $numeroPersonas;
    private $precioTotal;
    private $estado;
    private $observaciones;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function setIdUsuario($id) { $this->idUsuario = $id; }
    public function setIdRecurso($id) { $this->idRecurso = $id; }
    public function setNumeroPersonas($num) { $this->numeroPersonas = $num; }
    public function setPrecioTotal($precio) { $this->precioTotal = $precio; }
    public function setObservaciones($obs) { $this->observaciones = $obs; }
    
    public function getIdReserva() { return $this->idReserva; }
    public function getPrecioTotal() { return $this->precioTotal; }

    public function crear() {
        try {
            $this->db->beginTransaction();

            $recurso = new RecursoTuristico();
            $datosRecurso = $recurso->obtenerPorId($this->idRecurso);
            
            if (!$datosRecurso) {
                throw new Exception("Recurso turístico no encontrado");
            }

            if (!$recurso->verificarDisponibilidad($this->numeroPersonas)) {
                throw new Exception("No hay suficientes plazas disponibles");
            }

            $sql = "INSERT INTO reservas (id_usuario, id_recurso, numero_personas, precio_total, estado, observaciones) 
                    VALUES (?, ?, ?, ?, 'pendiente', ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->idUsuario, $this->idRecurso, $this->numeroPersonas, 
                $this->precioTotal, $this->observaciones
            ]);

            if ($result) {
                $this->idReserva = $this->db->lastInsertId();
                $recurso->reducirPlazas($this->numeroPersonas);
                $this->db->commit();
                return true;
            }
            
            $this->db->rollback();
            return false;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error al crear reserva: " . $e->getMessage());
        }
    }

    public function confirmar() {
        try {
            $sql = "UPDATE reservas SET estado = 'confirmada' WHERE id_reserva = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->idReserva]);
        } catch (Exception $e) {
            throw new Exception("Error al confirmar reserva: " . $e->getMessage());
        }
    }

    public function cancelar() {
        try {
            $this->db->beginTransaction();

            $sql = "SELECT * FROM reservas WHERE id_reserva = ? AND estado != 'cancelada'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->idReserva]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                throw new Exception("Reserva no encontrada o ya cancelada");
            }

            $sql = "UPDATE reservas SET estado = 'cancelada' WHERE id_reserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->idReserva]);

            $recurso = new RecursoTuristico();
            $recurso->obtenerPorId($reserva['id_recurso']);
            $recurso->aumentarPlazas($reserva['numero_personas']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error al cancelar reserva: " . $e->getMessage());
        }
    }

    public function obtenerPorUsuario($idUsuario) {
        try {
            $sql = "SELECT r.*, rt.nombre as recurso_nombre, rt.ubicacion, tr.nombre_tipo 
                    FROM reservas r 
                    INNER JOIN recursos_turisticos rt ON r.id_recurso = rt.id_recurso 
                    INNER JOIN tipos_recursos tr ON rt.id_tipo = tr.id_tipo 
                    WHERE r.id_usuario = ? 
                    ORDER BY r.fecha_reserva DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener reservas: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = "SELECT r.*, rt.nombre as recurso_nombre, rt.ubicacion, tr.nombre_tipo,
                           u.nombre as usuario_nombre, u.email as usuario_email
                    FROM reservas r 
                    INNER JOIN recursos_turisticos rt ON r.id_recurso = rt.id_recurso 
                    INNER JOIN tipos_recursos tr ON rt.id_tipo = tr.id_tipo 
                    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                    WHERE r.id_reserva = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Error al obtener reserva: " . $e->getMessage());
        }
    }
}

// ==================== CLASE PAGO ====================
class Pago {
    private $db;
    private $idPago;
    private $idReserva;
    private $importe;
    private $metodoPago;
    private $estadoPago;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function setIdReserva($id) { $this->idReserva = $id; }
    public function setImporte($importe) { $this->importe = $importe; }
    public function setMetodoPago($metodo) { $this->metodoPago = $metodo; }

    public function procesar() {
        try {
            $sql = "INSERT INTO pagos (id_reserva, importe, metodo_pago, estado_pago) 
                    VALUES (?, ?, ?, 'completado')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$this->idReserva, $this->importe, $this->metodoPago]);

            if ($result) {
                $this->idPago = $this->db->lastInsertId();
                
                // Confirmar la reserva asociada
                $reserva = new Reserva();
                $reserva->setIdReserva($this->idReserva);
                $reserva->confirmar();
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Error al procesar pago: " . $e->getMessage());
        }
    }
}

// ==================== SISTEMA DE INSTALACIÓN ====================
class InstaladorBD {
    private $db;

    public function __construct() {
        try {
            // Conexión inicial sin especificar base de datos
            $this->db = new PDO(
                "mysql:host=localhost;charset=utf8mb4",
                'DBUSER2025',
                'DBPWD2025',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }

    public function instalar() {
        try {
            // Crear base de datos
            $this->db->exec("CREATE DATABASE IF NOT EXISTS turismo_oviedo");
            $this->db->exec("USE turismo_oviedo");

            // Crear tablas
            $this->crearTablas();
            $this->insertarDatosIniciales();
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error en instalación: " . $e->getMessage());
        }
    }

    private function crearTablas() {
        $sqls = [
            "CREATE TABLE IF NOT EXISTS tipos_recursos (
                id_tipo int(11) NOT NULL AUTO_INCREMENT,
                nombre_tipo varchar(100) NOT NULL,
                descripcion text,
                PRIMARY KEY (id_tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS recursos_turisticos (
                id_recurso int(11) NOT NULL AUTO_INCREMENT,
                id_tipo int(11) NOT NULL,
                nombre varchar(255) NOT NULL,
                descripcion text NOT NULL,
                ubicacion varchar(255) NOT NULL,
                precio decimal(10,2) NOT NULL,
                plazas_disponibles int(11) NOT NULL,
                fecha_inicio datetime NOT NULL,
                fecha_fin datetime NOT NULL,
                activo tinyint(1) DEFAULT 1,
                fecha_creacion timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_recurso),
                KEY id_tipo (id_tipo),
                CONSTRAINT recursos_ibfk_1 FOREIGN KEY (id_tipo) REFERENCES tipos_recursos (id_tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS usuarios (
                id_usuario int(11) NOT NULL AUTO_INCREMENT,
                nombre varchar(100) NOT NULL,
                apellidos varchar(150) NOT NULL,
                email varchar(200) NOT NULL UNIQUE,
                telefono varchar(20),
                fecha_nacimiento date,
                ciudad varchar(100),
                codigo_postal varchar(10),
                password_hash varchar(255) NOT NULL,
                fecha_registro timestamp DEFAULT CURRENT_TIMESTAMP,
                activo tinyint(1) DEFAULT 1,
                PRIMARY KEY (id_usuario)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS reservas (
                id_reserva int(11) NOT NULL AUTO_INCREMENT,
                id_usuario int(11) NOT NULL,
                id_recurso int(11) NOT NULL,
                numero_personas int(11) NOT NULL,
                precio_total decimal(10,2) NOT NULL,
                fecha_reserva timestamp DEFAULT CURRENT_TIMESTAMP,
                estado enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
                observaciones text,
                PRIMARY KEY (id_reserva),
                KEY id_usuario (id_usuario),
                KEY id_recurso (id_recurso),
                CONSTRAINT reservas_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
                CONSTRAINT reservas_ibfk_2 FOREIGN KEY (id_recurso) REFERENCES recursos_turisticos (id_recurso)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS pagos (
                id_pago int(11) NOT NULL AUTO_INCREMENT,
                id_reserva int(11) NOT NULL,
                importe decimal(10,2) NOT NULL,
                metodo_pago enum('tarjeta','transferencia','efectivo','paypal') NOT NULL,
                fecha_pago timestamp DEFAULT CURRENT_TIMESTAMP,
                estado_pago enum('pendiente','completado','fallido','reembolsado') DEFAULT 'pendiente',
                referencia_externa varchar(100),
                PRIMARY KEY (id_pago),
                KEY id_reserva (id_reserva),
                CONSTRAINT pagos_ibfk_1 FOREIGN KEY (id_reserva) REFERENCES reservas (id_reserva)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($sqls as $sql) {
            $this->db->exec($sql);
        }
    }

    private function insertarDatosIniciales() {
        // Tipos de recursos
        $this->db->exec("INSERT IGNORE INTO tipos_recursos (id_tipo, nombre_tipo, descripcion) VALUES
            (1, 'Museo', 'Espacios culturales y museos de la ciudad'),
            (2, 'Ruta Turística', 'Recorridos guiados por la ciudad y alrededores'),
            (3, 'Restaurante', 'Experiencias gastronómicas y cenas especiales'),
            (4, 'Hotel', 'Alojamientos y estancias especiales'),
            (5, 'Actividad Cultural', 'Eventos, espectáculos y actividades culturales')");

        // Recursos turísticos
        $this->db->exec("INSERT IGNORE INTO recursos_turisticos (id_recurso, id_tipo, nombre, descripcion, ubicacion, precio, plazas_disponibles, fecha_inicio, fecha_fin) VALUES
            (1, 1, 'Museo de Bellas Artes de Asturias', 'Visita guiada al museo con las mejores obras de arte asturiano', 'Calle Santa Ana, 1', 12.50, 25, '2025-06-06 10:00:00', '2025-12-31 18:00:00'),
            (2, 1, 'Cámara Santa de la Catedral', 'Tour exclusivo por la Cámara Santa, Patrimonio de la Humanidad', 'Plaza Alfonso II el Casto', 8.00, 15, '2025-06-06 09:00:00', '2025-12-31 19:00:00'),
            (3, 2, 'Ruta de los Monumentos Prerrománicos', 'Recorrido por Santa María del Naranco y San Miguel de Lillo', 'Monte Naranco', 15.00, 20, '2025-06-06 09:30:00', '2025-12-31 17:30:00'),
            (4, 2, 'Ruta del Casco Histórico', 'Paseo guiado por el centro histórico de Oviedo', 'Plaza de la Constitución', 10.00, 30, '2025-06-06 10:00:00', '2025-12-31 20:00:00'),
            (5, 3, 'Cena Asturiana Tradicional', 'Experiencia gastronómica con fabada, cachopo y sidra', 'Calle Gascona, 15', 35.00, 12, '2025-06-06 20:00:00', '2025-12-31 23:00:00'),
            (6, 4, 'Hotel de la Reconquista - Noche Especial', 'Estancia de lujo en el hotel más emblemático', 'Calle Gil de Jaz, 16', 180.00, 5, '2025-06-06 15:00:00', '2025-12-31 12:00:00'),
            (7, 5, 'Concierto en el Teatro Campoamor', 'Espectáculo musical en el teatro más importante de Asturias', 'Calle Pelayo, s/n', 25.00, 50, '2025-06-15 20:30:00', '2025-06-15 23:00:00')");
    }
}

// ==================== FUNCIONES DE UTILIDAD ====================
function verificarSesion() {
    if (!Usuario::estaLogueado()) {
        header('Location: login.php');
        exit();
    }
}

function mostrarError($mensaje) {
    return "<p style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid #ff0000; border-radius: 5px;'>Error: " . htmlspecialchars($mensaje) . "</p>";
}

function mostrarExito($mensaje) {
    return "<p style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid #00aa00; border-radius: 5px;'>Éxito: " . htmlspecialchars($mensaje) . "</p>";
}

function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

function formatearPrecio($precio) {
    return number_format($precio, 2, ',', '.') . '€';
}

// ==================== INSTALACIÓN AUTOMÁTICA ====================
// Verificar si la base de datos necesita ser instalada
try {
    $testDB = new Database();
    // Si llegamos aquí, la BD ya existe
} catch (Exception $e) {
    // La BD no existe, intentar instalarla
    try {
        $instalador = new InstaladorBD();
        $instalador->instalar();
        echo "<p style='color: green;'>Base de datos instalada correctamente.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al instalar la base de datos: " . $e->getMessage() . "</p>";
    }
}

?>