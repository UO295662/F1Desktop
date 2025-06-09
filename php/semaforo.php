<?php
class Record {
    private $server;
    private $user;
    private $pass;
    private $dbname;
    private $conn;

    public function __construct() {
        $this->server = "localhost";
        $this->user = "DBUSER2024";
        $this->pass = "DBPSWD2024";
        $this->dbname = "records";
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->server, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    public function saveRecord($nombre, $apellidos, $nivel, $tiempo) {
        $stmt = $this->conn->prepare("INSERT INTO registro (nombre, apellidos, nivel, tiempo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $nombre, $apellidos, $nivel, $tiempo);
        if ($stmt->execute()) {
            return true; 
        } else {
            return false;
        }
        $stmt->close();
    }

    public function getTopRecords($nivel) {
        $stmt = $this->conn->prepare("SELECT nombre, apellidos, tiempo FROM registro WHERE nivel = ? ORDER BY tiempo ASC LIMIT 10");
        $stmt->bind_param("s", $nivel);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        $stmt->close();
        return $records;
    }

    public function getTopRecordsAsHTML($nivel) {
        $records = $this->getTopRecords($nivel);
        $html = ""; 
        $html .= "<section><h3>Los 10 mejores récords para el nivel $nivel:</h3></section>";
        $html .= "<section><h2>Tabla</h2><table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Tiempo (segundos)</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($records as $record) {
            $html .= "<tr>
                <td>" . htmlspecialchars($record['nombre']) . "</td>
                <td>" . htmlspecialchars($record['apellidos']) . "</td>
                <td>" . number_format($record['tiempo'], 3) . "</td>
              </tr>";
        }
        $html .= "</tbody>
            </table></section>";
        return $html;
    }

    public function __destruct() {
        $this->conn->close();
    }
}

$record = new Record();
$formSubmitted = false;
$recordsHTML = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $nivel = $_POST['nivel'];
    $tiempo = $_POST['tiempo'];
    if ($record->saveRecord($nombre, $apellidos, $nivel, $tiempo)) {
        $formSubmitted = true;
        $recordsHTML = $record->getTopRecordsAsHTML($nivel);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Documento basado en la F1 y Daniel Ricciardo">
    <meta name="keywords" content="F1, juego,semáforo, reaccion">
    <link rel="stylesheet" href="../estilo/semaforo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/imagenes/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Semáforo</title>
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Ir a la página principal">F1 Desktop</a></h1>
        <nav>
            <a href="../index.html" title="Inicio F1Dekstop">Inicio</a>
            <a href="../juegos.html" title="Juegos F1Dekstop">Juegos</a>
            <a href="../calendario.html" title="Calendario F1Dekstop">Calendario</a>
            <a href="../circuito.html" title="Circuito F1Dekstop">Circuito</a>
            <a href="recursos.php" title="Viajes F1Dekstop">Viajes</a>
            <a href="../meteorologia.html" title="Meteorología F1Dekstop">Meteorología</a>
            <a href="../noticias.html" title="Noticias F1Dekstop">Noticias</a>
            <a href="../piloto.html" title="Piloto F1Dekstop">Piloto</a>
        </nav>
    </header>

    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../juegos.html">Juegos</a> >> Semáforo</p>

    <h2>Variedad de juegos disponibles</h2>
    <ul>
        <li><a href="../memoria.html" title="Jugar al juego de memoria">Juego de Memoria</a></li>
        <li><a href="semaforo.php" title="Jugar al juego de semáforo" class="active">Semáforo</a></li>
		<li><a href="../api.html" title="Aplicacion web">Ejercicio Api</a></li>
        <li><a href="api.php" title="Ejercicio libre">Ejercicio Libre php</a></li>
    </ul>

    <main>
        <section>
        <h2>Semáforo</h2>
            <?php if ($formSubmitted): ?>
                <?php echo $recordsHTML; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="../js/semaforo.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const semaforo = new Semaforo();
        });
    </script>
</body>
</html>