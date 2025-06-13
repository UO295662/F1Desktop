<?php
require_once 'database.php';

class Reserva {
    private $conn;
    private $table_name = "reservas";

    public $id;
    public $usuario_id;
    public $recurso_id;
    public $horario_id;
    public $numero_personas;
    public $precio_total;
    public $estado;
    public $fecha_reserva;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (usuario_id, recurso_id, horario_id, numero_personas, precio_total, estado) 
                  VALUES (?, ?, ?, ?, ?, 'confirmada')";
        
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([
            $this->usuario_id,
            $this->recurso_id,
            $this->horario_id,
            $this->numero_personas,
            $this->precio_total
        ])) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function leerPorUsuario() {
        $query = "SELECT 
                    r.id, r.usuario_id, r.recurso_id, r.horario_id,
                    r.numero_personas, r.precio_total, r.estado, r.fecha_reserva,
                    rt.nombre as recurso_nombre, rt.ubicacion,
                    h.fecha_inicio, h.fecha_fin,
                    t.nombre as tipo_nombre
                  FROM " . $this->table_name . " r
                  INNER JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                  INNER JOIN horarios_recursos h ON r.horario_id = h.id
                  LEFT JOIN tipos_recursos t ON rt.tipo_recurso_id = t.id
                  WHERE r.usuario_id = ?
                  ORDER BY r.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->usuario_id);
        $stmt->execute();
        return $stmt;
    }

    public function cancelar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = 'cancelada'
                  WHERE id = ? AND usuario_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->usuario_id);
        
        return $stmt->execute();
    }
}
?>