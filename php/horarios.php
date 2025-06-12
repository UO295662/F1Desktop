<?php
require_once 'database.php';

class HorarioRecurso {
    private $conn;
    private $table_name = "horarios_recursos";

    public $id;
    public $recurso_id;
    public $fecha_inicio;
    public $fecha_fin;
    public $plazas_totales;
    public $plazas_disponibles;
    public $precio_especial;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leerPorRecurso() {
        $query = "SELECT 
                    h.id, h.recurso_id, h.fecha_inicio, h.fecha_fin,
                    h.plazas_totales, h.plazas_disponibles, h.precio_especial
                  FROM " . $this->table_name . " h
                  WHERE h.recurso_id = ? AND h.fecha_inicio > NOW() AND h.plazas_disponibles > 0
                  ORDER BY h.fecha_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->recurso_id);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno() {
        $query = "SELECT 
                    h.id, h.recurso_id, h.fecha_inicio, h.fecha_fin,
                    h.plazas_totales, h.plazas_disponibles, h.precio_especial
                  FROM " . $this->table_name . " h
                  WHERE h.id = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->recurso_id = $row['recurso_id'];
            $this->fecha_inicio = $row['fecha_inicio'];
            $this->fecha_fin = $row['fecha_fin'];
            $this->plazas_totales = $row['plazas_totales'];
            $this->plazas_disponibles = $row['plazas_disponibles'];
            $this->precio_especial = $row['precio_especial'];
            return true;
        }
        return false;
    }

    public function actualizarPlazas() {
        $query = "UPDATE " . $this->table_name . " 
                  SET plazas_disponibles = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->plazas_disponibles);
        $stmt->bindParam(2, $this->id);
        
        return $stmt->execute();
    }
}
?>