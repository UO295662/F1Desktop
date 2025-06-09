<?php
// filepath: c:\xampp\htdocs\F1Desktop\php\Usuario.php
require_once 'Database.php';

class Usuario {
    private $id;
    private $nombre;
    private $apellidos;
    private $email;
    private $telefono;
    private $fechaNacimiento;
    private $ciudad;
    private $codigoPostal;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Setters
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setEmail($email) { $this->email = $email; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setFechaNacimiento($fecha) { $this->fechaNacimiento = $fecha; }
    public function setCiudad($ciudad) { $this->ciudad = $ciudad; }
    public function setCodigoPostal($codigo) { $this->codigoPostal = $codigo; }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getEmail() { return $this->email; }
    
    public function registrar($password) {
        if ($this->existeEmail($this->email)) {
            throw new Exception("El email ya está registrado");
        }
        
        $sql = "INSERT INTO usuarios (nombre, apellidos, email, telefono, fecha_nacimiento, ciudad, codigo_postal, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->nombre,
            $this->apellidos,
            $this->email,
            $this->telefono,
            $this->fechaNacimiento,
            $this->ciudad,
            $this->codigoPostal,
            password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            $this->id = $usuario['id'];
            $this->nombre = $usuario['nombre'];
            $this->apellidos = $usuario['apellidos'];
            $this->email = $usuario['email'];
            return true;
        }
        return false;
    }
    
    private function existeEmail($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
}
?>