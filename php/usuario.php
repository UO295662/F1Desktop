<?php
require_once 'database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $apellidos;
    public $email;
    public $password_hash;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
                SET nombre=:nombre, apellidos=:apellidos, email=:email, password_hash=:password_hash, activo=1";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, nombre, apellidos, email, password_hash, activo
                FROM " . $this->table_name . "
                WHERE email = ? AND activo = 1
                LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($this->password_hash, $row['password_hash'])) {
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->apellidos = $row['apellidos'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    public function emailExiste() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>