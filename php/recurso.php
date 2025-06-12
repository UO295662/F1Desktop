<?php
require_once 'database.php';

class RecursoTuristico {
    private $conn;
    private $table_name = "recursos_turisticos";

    public $id;
    public $nombre;
    public $descripcion;
    public $tipo_recurso_id;
    public $ubicacion;
    public $direccion;
    public $latitud;
    public $longitud;
    public $capacidad_maxima;
    public $precio;
    public $duracion_horas;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leerTodos() {
        $query = "SELECT 
                    r.id, r.nombre, r.descripcion, r.tipo_recurso_id, 
                    r.ubicacion, r.direccion, r.latitud, r.longitud,
                    r.capacidad_maxima, r.precio, r.duracion_horas, r.activo,
                    t.nombre as tipo_nombre, t.icono as tipo_icono
                  FROM " . $this->table_name . " r
                  LEFT JOIN tipos_recursos t ON r.tipo_recurso_id = t.id
                  WHERE r.activo = 1
                  ORDER BY r.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno() {
        $query = "SELECT 
                    r.id, r.nombre, r.descripcion, r.tipo_recurso_id,
                    r.ubicacion, r.direccion, r.latitud, r.longitud,
                    r.capacidad_maxima, r.precio, r.duracion_horas, r.activo,
                    t.nombre as tipo_nombre, t.icono as tipo_icono
                  FROM " . $this->table_name . " r
                  LEFT JOIN tipos_recursos t ON r.tipo_recurso_id = t.id
                  WHERE r.id = ? AND r.activo = 1
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->tipo_recurso_id = $row['tipo_recurso_id'];
            $this->ubicacion = $row['ubicacion'];
            $this->direccion = $row['direccion'];
            $this->latitud = $row['latitud'];
            $this->longitud = $row['longitud'];
            $this->capacidad_maxima = $row['capacidad_maxima'];
            $this->precio = $row['precio'];
            $this->duracion_horas = $row['duracion_horas'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }
}
?>