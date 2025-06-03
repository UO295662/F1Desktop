<?php
class BaseDeDatos {
    private $conexion;
    private $server;
    private $user;
    private $pass;
    private $dbname;

    public function __construct() {
        $this->server = "localhost";
        $this->user = "DBUSER2024"; 
        $this->pass = "DBPSWD2024";
        $this->dbname = "libre";
    }

    private function connect() {
        $this->conexion = new mysqli($this->server, $this->user, $this->pass);
        if ($this->conexion->connect_error) {
            die("Conexión fallida: " . $this->conexion->connect_error);
        }
    }

    public function crearBaseDeDatos() {
        $this->connect();
        $result = $this->conexion->query("SHOW DATABASES LIKE '{$this->dbname}'");
        if ($result->num_rows == 0) {
            $query = "CREATE DATABASE {$this->dbname}";
        }
        $this->conexion->select_db($this->dbname);
    }

    public function eliminarBaseDeDatos() {
        $this->connect();
        $query = "DROP DATABASE IF EXISTS {$this->dbname}";
    }

    public function crearTablas() {
        $this->crearBaseDeDatos();
        $queriesDrop = [
            "DROP TABLE IF EXISTS resultados",
            "DROP TABLE IF EXISTS carreras",
            "DROP TABLE IF EXISTS circuitos",
            "DROP TABLE IF EXISTS pilotos",
            "DROP TABLE IF EXISTS equipos"
        ];
        foreach ($queriesDrop as $query) {
            $this->conexion->query($query);
        }
        $queries = [
            "CREATE TABLE IF NOT EXISTS equipos (
                id_equipo INT AUTO_INCREMENT PRIMARY KEY,
                nombre_equipo VARCHAR(255) NOT NULL,
                pais_origen VARCHAR(100) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS pilotos (
                id_piloto INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                nacionalidad VARCHAR(100),
                fecha_nacimiento DATE,
                id_equipo INT,
                FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo)
            )",
            "CREATE TABLE IF NOT EXISTS circuitos (
                id_circuito INT AUTO_INCREMENT PRIMARY KEY,
                nombre_circuito VARCHAR(255) NOT NULL,
                localizacion VARCHAR(255),
                longitud FLOAT
            )",
            "CREATE TABLE IF NOT EXISTS carreras (
                id_carrera INT AUTO_INCREMENT PRIMARY KEY,
                id_circuito INT,
                fecha DATE NOT NULL,
                nombre_carrera VARCHAR(255) NOT NULL,
                FOREIGN KEY (id_circuito) REFERENCES circuitos(id_circuito)
            )",
            "CREATE TABLE IF NOT EXISTS resultados (
                id_resultado INT AUTO_INCREMENT PRIMARY KEY,
                id_carrera INT,
                id_piloto INT,
                posicion TINYINT NOT NULL,
                FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera),
                FOREIGN KEY (id_piloto) REFERENCES pilotos(id_piloto)
            )"
        ];

        foreach ($queries as $query) {
            $this->conexion->query($query);
        }
    }

    public function insertarDatos() {
        $this->crearBaseDeDatos();
        $this->conexion->query("INSERT INTO equipos (nombre_equipo, pais_origen) VALUES 
            ('Red Bull Racing', 'Austria'), 
            ('Ferrari', 'Italia'),
            ('Mercedes', 'Alemania')");
        $this->conexion->query("INSERT INTO pilotos (nombre, nacionalidad, fecha_nacimiento, id_equipo) VALUES 
            ('Max Verstappen', 'Países Bajos', '1997-09-30', 1), 
            ('Charles Leclerc', 'Mónaco', '1997-10-16', 2),
            ('Lewis Hamilton', 'Reino Unido', '1985-01-07', 3)");
        $this->conexion->query("INSERT INTO circuitos (nombre_circuito, localizacion, longitud) VALUES 
            ('Silverstone Circuit', 'Reino Unido', 5.891), 
            ('Circuit de Monaco', 'Mónaco', 3.337),
            ('Red Bull Ring', 'Austria', 4.318)");
        $this->conexion->query("INSERT INTO carreras (id_circuito, fecha, nombre_carrera) VALUES 
            (1, '2024-07-10', 'Gran Premio de Gran Bretaña'), 
            (2, '2024-05-26', 'Gran Premio de Mónaco'),
            (3, '2024-06-30', 'Gran Premio de Austria')");
        $this->conexion->query("INSERT INTO resultados (id_carrera, id_piloto, posicion) VALUES 
            (1, 3, 1), 
            (2, 2, 1),
            (3, 1, 1)");
    }

    public function importarCSV($tabla, $archivo) {
        $this->crearBaseDeDatos();
        
        if (($handle = fopen($archivo, "r")) !== FALSE) {
            $columnas = fgetcsv($handle, 1000, ";");
    
            if (!$columnas) {
                fclose($handle);
                throw new Exception("El archivo CSV está vacío o mal formateado.");
            }
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) !== count($columnas)) {
                    fclose($handle);
                    throw new Exception("El número de columnas no coincide con la tabla.");
                }
                $valores = implode("', '", array_map([$this->conexion, 'real_escape_string'], $data));
                $columnasSQL = implode(", ", array_map([$this->conexion, 'real_escape_string'], $columnas));
                $query = "INSERT INTO $tabla ($columnasSQL) VALUES ('$valores')";
                $this->conexion->query($query);
            }
            fclose($handle);
        } else {
            throw new Exception("No se pudo abrir el archivo CSV.");
        }
    }

    public function exportarCSV($tabla, $archivo) {
        $this->crearBaseDeDatos();
        $query = "SELECT * FROM $tabla";
        $resultado = $this->conexion->query($query);
        if ($resultado) {
            $fp = fopen($archivo, 'w');
    
            $columnas = $resultado->fetch_fields();
            $headers = array_map(fn($col) => $col->name, $columnas);
            fputcsv($fp, $headers, ',');

            while ($fila = $resultado->fetch_assoc()) {
                fputcsv($fp, $fila, ',');
            }
    
            fclose($fp);
        }
    }
    public function exportarTodoCSV($archivo) {
        $this->crearBaseDeDatos();
        $tablas = ["equipos", "pilotos", "circuitos", "carreras", "resultados"];
    
        $fp = fopen($archivo, 'w');
    
        foreach ($tablas as $tabla) {
            fputcsv($fp, ["#TABLE:$tabla"], ',');

            $query = "SELECT * FROM $tabla";
            $resultado = $this->conexion->query($query);
    
            if ($resultado) {
                $columnas = $resultado->fetch_fields();
                $headers = array_map(fn($col) => $col->name, $columnas);
                fputcsv($fp, $headers, ',');
    
                while ($fila = $resultado->fetch_assoc()) {
                    fputcsv($fp, $fila, ',');
                }
            }
        }
    
        fclose($fp);
    }
    
    public function importarTodoCSV($archivo) {
    $this->crearBaseDeDatos();

    if (!file_exists($archivo)) {
        throw new Exception("El archivo CSV no existe.");
    }
    $this->conexion->query("SET FOREIGN_KEY_CHECKS=0");

    $tablaActual = null;
    $columnas = [];
    if (($handle = fopen($archivo, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($data[0]) && strpos($data[0], "#TABLE:") === 0) {
                $tablaActual = substr($data[0], 7);
                $columnas = [];

                $this->conexion->query("DELETE FROM $tablaActual");
            } elseif ($tablaActual && empty($columnas)) {
                $columnas = $data;
            } elseif ($tablaActual && $columnas) {
                $valores = implode("', '", array_map([$this->conexion, 'real_escape_string'], $data));
                $columnasSQL = implode(", ", $columnas);
                $query = "INSERT INTO $tablaActual ($columnasSQL) VALUES ('$valores')";
                $this->conexion->query($query);
            }
        }
        fclose($handle);
    } else {
        throw new Exception("No se pudo abrir el archivo CSV.");
    }

    $this->conexion->query("SET FOREIGN_KEY_CHECKS=1");
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new BaseDeDatos();
    if ($_POST['accion'] == 'crearBaseDatos') {
        $db->crearTablas();
    } elseif ($_POST['accion'] == 'insertarDatos') {
        $db->insertarDatos();
    } elseif ($_POST['accion'] == 'importarCSV') {
        $db->importarCSV($_POST['tabla'], $_FILES['archivo']['tmp_name']);
    } elseif ($_POST['accion'] == 'exportarCSV') {
        $archivo = "export_" . $_POST['tabla'] . ".csv";
        $db->exportarCSV($_POST['tabla'], $archivo);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $archivo . '"');
        readfile($archivo);
        unlink($archivo);
        exit();
    } elseif ($_POST['accion'] == 'eliminarBaseDatos') {
        $db->eliminarBaseDeDatos();
    }else if ($_POST['accion'] == 'exportarTodoCSV') {
        $archivo = "export_db.csv";
        $db->exportarTodoCSV($archivo);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $archivo . '"');
        readfile($archivo);
        unlink($archivo);
        exit();
    }elseif ($_POST['accion'] == 'importarTodoCSV') {
        if (isset($_FILES['archivo']['tmp_name']) && $_FILES['archivo']['tmp_name']) {
            $db->importarTodoCSV($_FILES['archivo']['tmp_name']);
    }
}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>F1 Desktop-Libre</title>
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Documento basado en la F1 y Daniel Ricciardo">
    <meta name="keywords" content="aplicacion, pilotos, grafica">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="F1, api, ejercicio libre">
    <link rel="stylesheet" type="text/css" href="../estilo/libre_php.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/imagenes/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Ir a la página principal">F1 Desktop</a></h1>
        <nav>
            <a href="../index.html" title="Inicio F1Dekstop">Inicio</a>
            <a href="../juegos.html" title="Juegos F1Dekstop">Juegos</a>
            <a href="../calendario.html" title="Calendario F1Dekstop">Calendario</a>
            <a href="../circuito.html" title="Circuito F1Dekstop">Circuito</a>
            <a href="viajes.php" title="Viajes F1Dekstop">Viajes</a>
            <a href="../meteorologia.html" title="Meteorología F1Dekstop">Meteorología</a>
            <a href="../noticias.html" title="Noticias F1Dekstop">Noticias</a>
            <a href="../piloto.html" title="Piloto F1Dekstop">Piloto</a>
        </nav>
    </header>

    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../juegos.html">Juegos</a> >> Libre</p>
    <h2>Variedad de juegos disponibles</h2>
    <ul>
        <li><a href="../memoria.html" title="Jugar al juego de memoria">Juego de Memoria</a></li>
        <li><a href="semaforo.php" title="Jugar al juego de semáforo">Semáforo</a></li>
        <li><a href="../api.html" title="Aplicacion web">Ejercicio Api</a></li>
        <li><a href="api.php" title="Ejercicio libre" class="active">Ejercicio Libre php</a></li>
    </ul>
    <main>
    <section>
    <h2>Ejercicio Libre PHP</h2>
    <form method="post">
    <button type="submit" name="accion" value="crearBaseDatos">Crear Base de Datos y Tablas</button>
    <button type="submit" name="accion" value="insertarDatos">Insertar Datos Iniciales</button>
    <button type="submit" name="accion" value="eliminarBaseDatos">Eliminar Base de Datos</button>
</form>

<form method="post" enctype="multipart/form-data">
    <label for="tabla-importar">Tabla:</label>
    <select name="tabla" id="tabla-importar">
        <option value="equipos">Equipos</option>
        <option value="pilotos">Pilotos</option>
        <option value="circuitos">Circuitos</option>
        <option value="carreras">Carreras</option>
        <option value="resultados">Resultados</option>
    </select>
    <label for="archivo-importar">Archivo CSV:</label>
    <input type="file" name="archivo" id="archivo-importar">
    <button type="submit" name="accion" value="importarCSV">Importar tabla desde CSV</button>
</form>

<form method="post">
    <label for="tabla-exportar">Tabla:</label>
    <select name="tabla" id="tabla-exportar">
        <option value="equipos">Equipos</option>
        <option value="pilotos">Pilotos</option>
        <option value="circuitos">Circuitos</option>
        <option value="carreras">Carreras</option>
        <option value="resultados">Resultados</option>
    </select>
    <button type="submit" name="accion" value="exportarCSV">Exportar tabla a CSV</button>
</form>

<form method="post">
    <button type="submit" name="accion" value="exportarTodoCSV">Exportar Toda la Base de Datos a CSV</button>
</form>

<form method="post" enctype="multipart/form-data">
    <label for="archivo-importar-todo">Archivo CSV:</label>
    <input type="file" name="archivo" id="archivo-importar-todo" required>
    <button type="submit" name="accion" value="importarTodoCSV">Importar Toda la Base de Datos desde CSV</button>
</form>
</section>
    </main>
</body>
</html>