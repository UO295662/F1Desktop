<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\RecursoTuristico.php
require_once 'database.php';

class RecursoTuristico {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function obtenerTodos() {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r 
                LEFT JOIN tipos_recursos t ON r.tipo_recurso_id = t.id 
                WHERE r.activo = 1 
                ORDER BY r.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function obtenerPorId($id) {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r 
                LEFT JOIN tipos_recursos t ON r.tipo_recurso_id = t.id 
                WHERE r.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function obtenerHorarios($recursoId) {
        $sql = "SELECT * FROM horarios_recursos 
                WHERE recurso_id = ? AND fecha_inicio > NOW() AND plazas_disponibles > 0 
                ORDER BY fecha_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recursoId]);
        return $stmt->fetchAll();
    }
    
    public function obtenerHorarioPorId($horarioId) {
        $sql = "SELECT * FROM horarios_recursos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$horarioId]);
        return $stmt->fetch();
    }
}
?>