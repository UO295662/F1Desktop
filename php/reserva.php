<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\Reserva.php
require_once 'database.php';

class Reserva {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function crear($usuarioId, $recursoId, $horarioId, $numeroPersonas, $precioTotal, $comentarios = '') {
        $this->db->beginTransaction();
        
        try {
            // Verificar disponibilidad
            $horario = $this->obtenerHorario($horarioId);
            if (!$horario || $horario['plazas_disponibles'] < $numeroPersonas) {
                throw new Exception("No hay suficientes plazas disponibles");
            }
            
            // Crear reserva
            $sql = "INSERT INTO reservas (usuario_id, recurso_id, horario_id, numero_personas, precio_total, comentarios) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuarioId, $recursoId, $horarioId, $numeroPersonas, $precioTotal, $comentarios]);
            
            // Actualizar plazas disponibles
            $sql = "UPDATE horarios_recursos SET plazas_disponibles = plazas_disponibles - ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$numeroPersonas, $horarioId]);
            
            $this->db->commit();
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function obtenerPorUsuario($usuarioId) {
        $sql = "SELECT r.*, rt.nombre as recurso_nombre, rt.descripcion as recurso_descripcion,
                       h.fecha_inicio, h.fecha_fin, t.nombre as tipo_nombre
                FROM reservas r
                JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                JOIN horarios_recursos h ON r.horario_id = h.id
                LEFT JOIN tipos_recursos t ON rt.tipo_recurso_id = t.id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_reserva DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }
    
    public function cancelar($reservaId, $usuarioId) {
        $this->db->beginTransaction();
        
        try {
            // Obtener datos de la reserva
            $sql = "SELECT * FROM reservas WHERE id = ? AND usuario_id = ? AND estado != 'cancelada'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId, $usuarioId]);
            $reserva = $stmt->fetch();
            
            if (!$reserva) {
                throw new Exception("Reserva no encontrada o ya cancelada");
            }
            
            // Cancelar reserva con timestamp
            $sql = "UPDATE reservas SET estado = 'cancelada', fecha_cancelacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId]);
            
            // Devolver plazas
            $sql = "UPDATE horarios_recursos SET plazas_disponibles = plazas_disponibles + ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reserva['numero_personas'], $reserva['horario_id']]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function obtenerHorario($horarioId) {
        $sql = "SELECT * FROM horarios_recursos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$horarioId]);
        return $stmt->fetch();
    }
}
?>