class MapHandler {
    constructor() {
        this.map = null;
        this.infoWindow = null;
        this.kmlInput = document.querySelector('section:nth-of-type(1) input[type="file"]');
        this.mapDiv = document.querySelector('section:nth-of-type(1) div');
        this.svgInput = document.querySelector('section:nth-of-type(2) input[type="file"]');
        this.xmlInput = document.querySelector('section:nth-of-type(3) input[type="file"]');
        this.xmlContent = document.querySelector('section:nth-of-type(3)'); 
        
        this.initialize();
    }

    // Método para inicializar el mapa
    initMap() {
        this.map = new google.maps.Map(this.mapDiv, {
            center: { lat: 44.342189, lng: 11.712222 },
            zoom: 14,
            mapTypeId: 'terrain',
        });
    }

    // Método para procesar archivos KML
    handleKMLInput(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = (e) => {
            const kmlText = e.target.result;
            this.initMap();

            const parser = new DOMParser();
            const kmlDoc = parser.parseFromString(kmlText, "application/xml");
            const coordinates = kmlDoc.querySelectorAll("coordinates");
            let path = [];

            coordinates.forEach((coordElement) => {
                const coordsArray = coordElement.textContent.trim().split(/\s+/);

                coordsArray.forEach((coord) => {
                    const [lng, lat] = coord.split(",").map(Number);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        path.push({ lat, lng });
                    }
                });
            });

            if (path.length === 0) {
                console.error("No se encontraron coordenadas válidas.");
                return;
            }

            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 1.0,
                strokeWeight: 2,
            });

            polyline.setMap(this.map);
            polyline.addListener("click", () => {
                if (!this.infoWindow) {
                    this.infoWindow = new google.maps.InfoWindow();
                }
                this.infoWindow.setContent("Polilínea");
                this.infoWindow.setPosition(polyline.getPath().getAt(0));
                this.infoWindow.open(this.map);
            });
        };

        reader.readAsText(file);
    }

    // Método para procesar archivos SVG
    handleSVGInput(event) {
        const file = event.target.files[0];
        const reader = new FileReader();
    
        reader.onload = (e) => {
            const svgContent = e.target.result;
            const svgElement = new DOMParser().parseFromString(svgContent, "image/svg+xml").documentElement;
            const header = document.createElement('h3');
            header.textContent = "SVG Cargado";  
            const section = this.svgInput.closest('section');
            section.appendChild(header);
            section.appendChild(svgElement); 
        };
    
        reader.readAsText(file);
    }

    // Método para procesar archivos XML
    handleXMLInput(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = (e) => {
            try {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(e.target.result, "text/xml");
                const xmlString = new XMLSerializer().serializeToString(xmlDoc);
                this.convertirXMLaHTML(xmlString);
            } catch (error) {
                console.log(error);
                this.xmlContent.innerHTML = "<p>Error al procesar el archivo XML.</p>";
            }
        };

        reader.readAsText(file);
    }

    // Convertir XML a HTML
    convertirXMLaHTML(xmlString) {
        var parser = new DOMParser();
        var xmlDoc = parser.parseFromString(xmlString, "application/xml");

        function recorrerXML(xmlElement) {
            var htmlContent = '';

            $(xmlElement).children().each(function() {
                var tagName = this.tagName;
                var content = $(this).text();
                var $this = $(this);

                // Aquí se procesan los nodos XML como en tu código original
                // (Código para cada tag en XML)
            });

            return htmlContent;
        }

        var html = recorrerXML(xmlDoc.documentElement);
        this.xmlContent.innerHTML = html;
        this.xmlContent.style.display = "block";
    }

    // Método para inicializar los eventos de archivo
    initialize() {
        this.kmlInput.addEventListener("change", (event) => this.handleKMLInput(event));
        this.svgInput.addEventListener("change", (event) => this.handleSVGInput(event));
        this.xmlInput.addEventListener("change", (event) => this.handleXMLInput(event));
    }
}

// Inicializar la clase MapHandler cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function() {
    new MapHandler();
});