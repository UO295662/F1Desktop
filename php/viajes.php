<?php
class Carrusel {
    private $pais;
    private $capital;
    private $fotos = [];

    public function __construct($capital, $pais) {
        $this->capital = $capital;
        $this->pais = $pais;
    }

    public function obtenerFotos($apiKey) {
        $endpointSearch = "https://api.flickr.com/services/rest/";
        $searchParams = [
            "method" => "flickr.photos.search",
            "api_key" => $apiKey,
            "text" => $this->pais,
            "format" => "json",
            "nojsoncallback" => 1,
            "per_page" => 10,
            "sort" => "relevance"
        ];
    
        $urlSearch = $endpointSearch . '?' . http_build_query($searchParams);
        $responseSearch = $this->makeHttpRequest($urlSearch);
    
        if ($responseSearch === false) {
            echo "<p>Error al obtener las fotos de Flickr.</p>";
            return;
        }
    
        $dataSearch = json_decode($responseSearch, true);
    
        if (isset($dataSearch['photos']['photo'])) {
            foreach ($dataSearch['photos']['photo'] as $photo) {
                $this->fotos[] = [
                    "url" => "https://live.staticflickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_t.jpg",
                    "title" => $photo['title'],
                    "author" => $photo['owner'], 
                    "date" => null
                ];
            }
        }
    }
    private function makeHttpRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error en la solicitud HTTP: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
    
        curl_close($ch);
        return $response;
    }
   
    public function getImagenes(){
        return $this->fotos;
    }
}

class Moneda {
    private $monedaLocal;
    private $monedaObjetivo;

    public function __construct($monedaLocal, $monedaObjetivo) {
        $this->monedaLocal = $monedaLocal;
        $this->monedaObjetivo = $monedaObjetivo;
    }
    public function getMonedaLocal() {
        return $this->monedaLocal;
    }

    public function getMonedaObjetivo() {
        return $this->monedaObjetivo;
    }
    public function convertir($cantidad) {
        $url = "https://open.er-api.com/v6/latest/{$this->monedaLocal}";
        $response = file_get_contents($url);
        
        if ($response === false) {
            echo 'Error al obtener la tasa de cambio.';
            return null;
        }
        $data = json_decode($response, true);
        if (isset($data['rates'][$this->monedaObjetivo])) {
            $tasaDeCambio = $data['rates'][$this->monedaObjetivo];
            $resultado = $cantidad * $tasaDeCambio;
            return $resultado;
        } else {
            echo "Error: La tasa de cambio no está disponible.";
            return null;
        }
    }
}

$apiKeyFlickr = "6f0341683ea3275017c15c42e61ea7a5";
$apiKeyExchange = "2dd697efa0a1cf3e598d54cea4caef21";

$carrusel1 = new Carrusel("Roma", "Italia");
$carrusel1->obtenerFotos($apiKeyFlickr);

$conversion = new Moneda("USD", "EUR");
$monedaLocal=$conversion->getMonedaLocal();
$monedaObjetivo=$conversion->getMonedaObjetivo();
$cantidad=1;
$resultadoConversion = $conversion->convertir($cantidad);
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>F1 Desktop</title>
    <meta name="author" content="Gael Horta Calzada">
    <meta name="description" content="Documento basado en la F1 y Daniel Ricciardo">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="F1, viajes, mapas, carrusel">
    <link rel="icon" href="../multimedia/imagenes/favicon.ico">
    <link rel="stylesheet" href="../estilo/estilo.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" async></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAQz2BOIGZarND4L4WVBfRCqCigjJ2f4PU&callback"  defer></script>
</head>
<body>
<header>
        <h1><a href="../index.html" title="Ir a la página principal">F1 Desktop</a></h1>
        <nav>
            <a href="../index.html" title="Inicio F1Dekstop">Inicio</a>
            <a href="../juegos.html" title="Juegos F1Dekstop">Juegos</a>
            <a href="../calendario.html" title="Calendario F1Dekstop">Calendario</a>
            <a href="../circuito.html" title="Circuito F1Dekstop">Circuito</a>
            <a href="viajes.php" title="Viajes F1Dekstop" class="active">Viajes</a>
            <a href="../meteorologia.html" title="Meteorología F1Dekstop">Meteorología</a>
            <a href="../noticias.html" title="Noticias F1Dekstop">Noticias</a>
            <a href="../piloto.html" title="Piloto F1Dekstop">Piloto</a>
        </nav>
</header>

<p>Estás en: <a href="../index.html">Inicio</a> >> Viajes</p>
    
<main>
    <h2>Viajes</h2>
    <section>
        <h3>Tasa de Cambio</h3>
        <p>
        <?php echo htmlspecialchars($cantidad); ?> 
        <?php echo htmlspecialchars($monedaLocal); ?> 
        equivale a 
        <?php echo htmlspecialchars($resultadoConversion); ?> 
        <?php echo htmlspecialchars($monedaObjetivo); ?>.
        </p>
    </section> 
    <section>
        <h3>Mapas</h3>
        <p>Haz clic en el botón para mostrar el mapa estático basado en tu ubicación.</p>
        <input type="button" value="Obtener mapa estático" onclick="viaje.getMapaEstaticoGoogle();">
        <article><h3>Mapa Estático</h3></article>
        
        <h3>Mapa Dinámico</h3>
        <p>Haz clic en el botón para mostrar el mapa dinámico basado en tu ubicación.</p>
        <input type="button" value="Obtener mapa dinámico" onclick="viaje.getMapaDinamico();">
    </section>
    <article>
   <h4>Carrusel de imágenes</h4>
       <?php foreach ($carrusel1->getImagenes() as $index => $imagen): ?>
           <img src="<?= htmlspecialchars($imagen['url']) ?>" alt="Imagen <?= $index + 1 ?>" >
       <?php endforeach; ?>
   <button>&lt;</button>
   <button>&gt;</button>
    </article>     
</main>
<script src="../js/viajes.js" async></script>
</body>
</html>