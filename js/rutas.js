function MapHandler() {
    this.map = null;
    this.currentVectorLayer = null;
    this.popup = null;
    this.rutasData = null;
    this.mapSection = null;
    this.mapInitialized = false;

    this.findMapContainer();
    this.mapSection && (this.mapSection.hidden = true);
    this.configurarInputFile();
    this.pointStyle = new ol.style.Style({
        image: new ol.style.Circle({
            radius: 10,
            fill: new ol.style.Fill({ color: '#8b3a3a' }),
            stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
        })
    });
    this.lineStyle = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#5a1f1f',
            width: 3,
            lineDash: [5, 5]
        })
    });
}

MapHandler.prototype.configurarInputFile = function() {
    const fileInput = document.querySelector('input[type="file"]');

    if (!fileInput) return;

    // Cargar automáticamente cuando se selecciona un archivo
    fileInput.onchange = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        if (!file.name.toLowerCase().endsWith('.xml')) {
            alert('Por favor, selecciona un archivo XML válido');
            fileInput.value = ''; // Limpiar la selección
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => this.procesarRutasXML(e.target.result);
        reader.readAsText(file);
    };
};

MapHandler.prototype.findMapContainer = function() {
    const sections = document.querySelectorAll('section');
    for (let section of sections) {
        const h2 = section.querySelector('h2');
        if (h2?.textContent.includes('Mapa de Rutas')) {
            this.mapSection = section;
            // Añadir atributo data para identificar el mapa
            this.mapSection.setAttribute('data-map-container', 'true');
            return;
        }
    }
    // Si no encuentra la sección, crear una nueva
    this.mapSection = document.createElement('section');
    this.mapSection.innerHTML = '<h2>Mapa de Rutas</h2>';
    this.mapSection.setAttribute('data-map-container', 'true');
    document.body.appendChild(this.mapSection);
};

MapHandler.prototype.mostrarMapa = function() {
    if (!this.mapSection) return;

    this.mapSection.hidden = false;

    if (!this.mapInitialized) {
        setTimeout(() => this.initMap(), 100);
        this.mapInitialized = true;
    } else if (this.map) {
        setTimeout(() => this.map.updateSize(), 100);
    }
};

MapHandler.prototype.initMap = function() {
    // Crear elemento popup
    const popupElement = document.createElement('div');
    popupElement.innerHTML = `
        <div>
            <div>
                <h6>Información</h6>
                <a href="#">&times;</a>
            </div>
            <div>
                <h6>Punto</h6>
                <p>Información del punto</p>
            </div>
        </div>
    `;

    this.popup = new ol.Overlay({
        element: popupElement,
        autoPan: {
            animation: { duration: 250 }
        },
        positioning: 'bottom-center',
        stopEvent: false,
        offset: [0, -10]
    });

    // Inicializar el mapa directamente en la section
    this.map = new ol.Map({
        target: this.mapSection,
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            })
        ],
        overlays: [this.popup],
        view: new ol.View({
            center: ol.proj.fromLonLat([-5.8593, 43.3614]),
            zoom: 14
        }),
        controls: [
            new ol.control.Zoom(),
            new ol.control.Rotate(),
            new ol.control.Attribution(),
            new ol.control.FullScreen()
        ]
    });

    // Configurar el cierre del popup
    const closeButton = popupElement.querySelector('a');
    closeButton.onclick = (e) => {
        e.preventDefault();
        this.popup.setPosition(undefined);
        return false;
    };

    // Manejar clicks en el mapa
    this.map.on('singleclick', (evt) => {
        const feature = this.map.forEachFeatureAtPixel(evt.pixel, f => f);
        if (feature) {
            const content = popupElement.querySelector('div > div:last-child');
            const name = feature.get('name') || 'Punto';
            const description = feature.get('description') || 'Sin descripción';

            content.innerHTML = `
                <h6>${name}</h6>
                <p>${description}</p>
            `;
            this.popup.setPosition(evt.coordinate);
        } else {
            this.popup.setPosition(undefined);
        }
    });

    setTimeout(() => {
        this.map.updateSize();
    }, 250);

    console.log('Mapa inicializado correctamente');
};

MapHandler.prototype.clearMap = function() {
    if (this.currentVectorLayer && this.map) {
        this.map.removeLayer(this.currentVectorLayer);
        this.currentVectorLayer = null;
    }
    this.popup?.setPosition(undefined);
};

MapHandler.prototype.cargarKMLPorRuta = function(nombreRuta, archivoKML) {
    if (!this.map) {
        console.log('Mapa no inicializado, esperando...');
        return setTimeout(() => this.cargarKMLPorRuta(nombreRuta, archivoKML), 500);
    }

    this.clearMap();
    const archivo = archivoKML || 'xml/rutas.kml';
    console.log('Cargando archivo KML:', archivo);

    fetch(archivo)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            return r.text();
        })
        .then(kml => this.procesarKMLPorRuta(kml, nombreRuta))
        .catch(e => {
            console.error('Error cargando KML:', e);
            this.mostrarPuntosEjemplo(nombreRuta);
        });
};

MapHandler.prototype.mostrarPuntosEjemplo = function(nombreRuta) {
    console.log('Mostrando puntos de ejemplo para:', nombreRuta);

    const puntosOviedo = [
        { nombre: 'Catedral de Oviedo', coords: [-5.8593, 43.3614], desc: 'Catedral gótica del siglo XIV' },
        { nombre: 'Teatro Campoamor', coords: [-5.8441, 43.3658], desc: 'Teatro histórico de Oviedo' },
        { nombre: 'Universidad de Oviedo', coords: [-5.8448, 43.3547], desc: 'Campus histórico universitario' },
        { nombre: 'Parque de San Francisco', coords: [-5.8506, 43.3625], desc: 'Principal parque urbano' }
    ];

    const features = puntosOviedo.map((punto, i) => {
        const feature = new ol.Feature({
            geometry: new ol.geom.Point(ol.proj.fromLonLat(punto.coords)),
            name: punto.nombre,
            description: punto.desc
        });

        feature.setStyle(this.pointStyle);

        return feature;
    });

    const coords = puntosOviedo.map(p => ol.proj.fromLonLat(p.coords));
    const routeLine = new ol.Feature({
        geometry: new ol.geom.LineString(coords),
        name: `Ruta: ${nombreRuta}`,
        type: 'route'
    });

    routeLine.setStyle(this.lineStyle);

    features.push(routeLine);

    const vectorSource = new ol.source.Vector({ features });
    this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
    this.map.addLayer(this.currentVectorLayer);

    this.map.getView().fit(vectorSource.getExtent(), {
        padding: [50, 50, 50, 50],
        maxZoom: 16
    });
};

MapHandler.prototype.procesarKMLPorRuta = function(kmlText, nombreRuta) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "application/xml");

    const parserError = kmlDoc.querySelector("parsererror");
    if (parserError) {
        console.error('Error parsing KML:', parserError.textContent);
        this.mostrarPuntosEjemplo(nombreRuta);
        return;
    }

    const folders = kmlDoc.querySelectorAll("Folder");

    for (let folder of folders) {
        const folderName = folder.querySelector("name")?.textContent;
        if (folderName?.trim() === nombreRuta?.trim()) {
            return this.mostrarRutaEnMapa(folder, folderName);
        }
    }

    this.cargarTodosPuntosKML(kmlText);
};

MapHandler.prototype.mostrarRutaEnMapa = function(folder, nombreRuta) {
    const features = [], coords = [];

    folder.querySelectorAll("Placemark").forEach((placemark, i) => {
        const name = placemark.querySelector("name")?.textContent || `Punto ${i + 1}`;
        const desc = placemark.querySelector("description")?.textContent || "";

        const pointCoords = placemark.querySelector("Point coordinates");
        if (pointCoords) {
            const [lng, lat, alt] = pointCoords.textContent.trim().split(",").map(Number);
            if (!isNaN(lat) && !isNaN(lng)) {
                const point = ol.proj.fromLonLat([lng, lat]);
                coords.push(point);

                const feature = new ol.Feature({
                    geometry: new ol.geom.Point(point),
                    name,
                    description: desc || `${lat.toFixed(6)}, ${lng.toFixed(6)}\n${alt || 0}m`
                });

                feature.setStyle(this.pointStyle);
                features.push(feature);
            }
        }

        const lineCoords = placemark.querySelector("LineString coordinates");
        if (lineCoords) {
            const lines = lineCoords.textContent.trim().split(/[\s\n]+/).filter(line => line.trim() && line.includes(','));
            const linePoints = [];

            lines.forEach(line => {
                const [lng, lat] = line.trim().split(',').map(Number);
                if (!isNaN(lat) && !isNaN(lng)) {
                    linePoints.push(ol.proj.fromLonLat([lng, lat]));
                }
            });

            if (linePoints.length > 1) {
                const lineFeature = new ol.Feature({
                    geometry: new ol.geom.LineString(linePoints),
                    name,
                    description: desc || `Línea de ruta: ${nombreRuta}`
                });
                lineFeature.setStyle(new ol.style.Style({
                    stroke: new ol.style.Stroke({ color: '#8b3a3a', width: 4 })
                }));
                features.push(lineFeature);
            }
        }
    });

    if (coords.length > 1) {
        const routeLine = new ol.Feature({
            geometry: new ol.geom.LineString(coords),
            name: `Ruta: ${nombreRuta}`,
            type: 'route'
        });
        routeLine.setStyle(this.lineStyle);
        features.push(routeLine);
    }

    if (features.length > 0) {
        const vectorSource = new ol.source.Vector({ features });
        this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
        this.map.addLayer(this.currentVectorLayer);
        this.map.getView().fit(vectorSource.getExtent(), {
            padding: [50, 50, 50, 50],
            maxZoom: 16
        });
    } else {
        this.mostrarPuntosEjemplo(nombreRuta);
    }
};

MapHandler.prototype.cargarTodosPuntosKML = function(kmlText) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "application/xml");
    const features = [];
    const coordsList = [];

    kmlDoc.querySelectorAll("coordinates").forEach((coordElement, index) => {
        const coordsText = coordElement.textContent?.trim();
        if (coordsText?.includes(',')) {
            const lines = coordsText.split(/[\s\n]+/).filter(line => line.trim() && line.includes(','));

            lines.forEach((line, pointIndex) => {
                const [lng, lat, alt] = line.trim().split(',').map(Number);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const point = ol.proj.fromLonLat([lng, lat]);
                    coordsList.push(point);

                    const feature = new ol.Feature({
                        geometry: new ol.geom.Point(point),
                        name: `Punto ${index + 1}-${pointIndex + 1}`,
                        description: `${lat.toFixed(6)}, ${lng.toFixed(6)}\n${alt || 0}m`
                    });
                    feature.setStyle(this.pointStyle);
                    features.push(feature);
                }
            });
        }
    });

    if (coordsList.length > 1) {
        const routeLine = new ol.Feature({
            geometry: new ol.geom.LineString(coordsList),
            name: "Ruta Completa",
            type: 'route'
        });
        routeLine.setStyle(this.lineStyle);
        features.push(routeLine);
    }

    if (features.length > 0) {
        const vectorSource = new ol.source.Vector({ features });
        this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
        this.map.addLayer(this.currentVectorLayer);
        this.map.getView().fit(vectorSource.getExtent(), {
            padding: [50, 50, 50, 50],
            maxZoom: 16
        });
    } else {
        this.mostrarPuntosEjemplo("Ruta por defecto");
    }
};

MapHandler.prototype.procesarRutasXML = function(xmlString) {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, "application/xml");

    const parserError = xmlDoc.querySelector("parsererror");
    if (parserError) {
        console.error('Error parsing XML:', parserError.textContent);
        alert('Error al procesar el archivo XML. Verifica que el formato sea correcto.');
        return;
    }

    const rutas = xmlDoc.querySelectorAll("ruta");

    if (rutas.length === 0) return;

    this.rutasData = rutas;
    this.crearSelectorRutas();
};

MapHandler.prototype.crearSelectorRutas = function() {
    const main = document.querySelector('main');

    // Limpiar selectores existentes buscando por contenido
    const selectoresAnteriores = Array.from(main.querySelectorAll('section')).filter(section => {
        const h3 = section.querySelector('h3');
        return h3 && (h3.textContent.includes('Selecciona una Ruta') || h3.textContent.includes('Información de la Ruta'));
    });
    selectoresAnteriores.forEach(el => el.remove());

    const selectorSection = document.createElement('section');
    selectorSection.innerHTML = `
        <h3>Selecciona una Ruta</h3>
        <label>Elige una ruta:</label>
        <select>
            <option value="">-- Elige una ruta --</option>
        </select>
    `;

    const select = selectorSection.querySelector('select');
    this.rutasData.forEach((ruta, i) => {
        const nombre = ruta.querySelector("nombre")?.textContent || `Ruta ${i + 1}`;
        const tipo = ruta.querySelector("tipo")?.textContent || "";
        select.innerHTML += `<option value="${i}">${nombre}${tipo ? ` (${tipo})` : ''}</option>`;
    });

    const contenedorRuta = document.createElement('section');
    contenedorRuta.innerHTML = '<h3>Información de la Ruta</h3><p>Selecciona una ruta para ver su información detallada.</p>';

    main.appendChild(selectorSection);
    main.appendChild(contenedorRuta);

    this.contenedorRuta = contenedorRuta;

    select.onchange = () => {
        if (select.value) {
            this.mostrarMapa();
            this.mostrarRutaSeleccionada(parseInt(select.value));
        } else {
            this.mapSection.hidden = true;
            contenedorRuta.innerHTML = '<h3>Información de la Ruta</h3><p>Selecciona una ruta para ver su información detallada.</p>';
            this.clearMap();
        }
    };
};

MapHandler.prototype.mostrarRutaSeleccionada = function(indice) {
    const ruta = this.rutasData[indice];

    const nombreRuta = ruta.querySelector("nombre")?.textContent || "";
    const planimetria = ruta.querySelector("planimetria")?.getAttribute("archivo");
    const altimetria = ruta.querySelector("altimetria")?.getAttribute("archivo");

    if (nombreRuta && planimetria) this.cargarKMLPorRuta(nombreRuta, `xml/${planimetria}`);

    this.contenedorRuta.innerHTML = '<h3>Información de la Ruta</h3>' + this.convertirRutaAHTML(ruta);
    if (altimetria) this.cargarSVGRuta(this.contenedorRuta, altimetria);
    this.contenedorRuta.scrollIntoView({ behavior: 'smooth' });
};

MapHandler.prototype.cargarSVGRuta = function(contenedorRuta, archivoSVG) {
    const timestamp = new Date().getTime();
    const urlConCache = `xml/${archivoSVG}?v=${timestamp}`;

    fetch(urlConCache)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            return r.text();
        })
        .then(svg => {
            const section = document.createElement('section');
            section.innerHTML = `
                <h4>Perfil Altimétrico</h4>
                <section>
                    <h6>Perfil</h6>
                    ${svg}
                </section>
            `;
            contenedorRuta.querySelector('article')?.appendChild(section);
        })
        .catch(e => {
            console.error('Error cargando SVG:', e);
            const error = document.createElement('section');
            error.innerHTML = '<h4>Perfil Altimétrico</h4><p>No disponible</p>';
            contenedorRuta.querySelector('article')?.appendChild(error);
        });
};

MapHandler.prototype.convertirRutaAHTML = function(ruta) {
    const get = (sel) => ruta.querySelector(sel)?.textContent || "";
    const nombre = get("nombre") || "Ruta sin nombre";

    let html = `<article><h3>${nombre}</h3><section><h4>Información General</h4><ul>`;

    const campos = {tipo: 'Tipo', medio_transporte: 'Transporte', duracion: 'Duración',
                   agencia: 'Agencia', personas: 'Dirigida a', recomendacion: 'Recomendación'};

    Object.entries(campos).forEach(([field, label]) => {
        const val = get(field);
        if (val) html += `<li>${label}: ${val}${field === 'recomendacion' ? '/10' : ''}</li>`;
    });

    const desc = get("descripcion");
    html += `</ul>${desc ? `<p>Descripción: ${desc}</p>` : ''}</section>`;

    // Punto de inicio
    const inicio = ruta.querySelector("inicio");
    if (inicio) {
        html += '<section><h4>Punto de Inicio</h4><ul>';
        ['lugar', 'direccion'].forEach(field => {
            const val = inicio.querySelector(field)?.textContent;
            if (val) html += `<li>${field === 'lugar' ? 'Lugar' : 'Dirección'}: ${val}</li>`;
        });

        const coords = inicio.querySelector("coordenadas");
        if (coords) {
            const lat = coords.querySelector("latitud")?.textContent;
            const lng = coords.querySelector("longitud")?.textContent;
            const alt = coords.querySelector("altitud")?.textContent;
            if (lat && lng) html += `<li>Coordenadas: ${lat}, ${lng}${alt ? ` (${alt}m)` : ''}</li>`;
        }
        html += '</ul></section>';
    }

    const hitos = ruta.querySelectorAll("hito");
    if (hitos.length > 0) {
        html += '<section><h4>Hitos de la Ruta</h4>';
        hitos.forEach(hito => {
            const nombreH = hito.querySelector("nombre")?.textContent || "";
            const descH = hito.querySelector("descripcion")?.textContent || "";
            html += `<article><h5>${nombreH}</h5>${descH ? `<p>${descH}</p>` : ''}`;

            html += '<ul>';
            const dist = hito.querySelector("distancia");
            if (dist) html += `<li>Distancia: ${dist.textContent} ${dist.getAttribute("unidad") || "m"}</li>`;

            const coordsH = hito.querySelector("coordenadas");
            if (coordsH) {
                const lat = coordsH.querySelector("latitud")?.textContent;
                const lng = coordsH.querySelector("longitud")?.textContent;
                const alt = coordsH.querySelector("altitud")?.textContent;
                if (lat && lng) html += `<li>Coordenadas: ${lat}, ${lng}${alt ? ` (${alt}m)` : ''}</li>`;
            }
            html += '</ul>';

            const fotos = hito.querySelectorAll("galeria_fotos foto");
            if (fotos.length > 0) {
                html += '<aside><p>Fotos:</p>';
                fotos.forEach(foto => {
                    const archivo = foto.getAttribute("archivo");
                    const desc = foto.textContent;
                    if (archivo) {
                        html += `<figure><img src="${archivo}" alt="${desc || 'Imagen'}"><figcaption>${desc || ''}</figcaption></figure>`;
                    }
                });
                html += '</aside>';
            }

            const videos = hito.querySelectorAll("galeria_videos video");
            if (videos.length > 0) {
                html += '<aside><p>Videos:</p>';
                videos.forEach(video => {
                    const archivo = video.getAttribute("archivo");
                    const desc = video.textContent;
                    if (archivo) {
                        html += `<figure><video controls><source src="${archivo}" type="video/mp4">Tu navegador no soporta video HTML5.</video><figcaption>${desc || ''}</figcaption></figure>`;
                    }
                });
                html += '</aside>';
            }

            html += '</article>';
        });
        html += '</section>';
    }

    const refs = ruta.querySelectorAll("referencias referencia");
    if (refs.length > 0) {
        html += '<section><h4>Referencias</h4><ul>';
        refs.forEach(ref => html += `<li><a href="${ref.textContent}" target="_blank">${ref.textContent}</a></li>`);
        html += '</ul></section>';
    }

    const planimetria = ruta.querySelector("planimetria");
    const altimetria = ruta.querySelector("altimetria");
    if (planimetria || altimetria) {
        html += '<section><h4>Archivos Adjuntos</h4><ul>';
        if (planimetria) html += `<li>Planimetría: ${planimetria.getAttribute("archivo")}</li>`;
        if (altimetria) html += `<li>Altimetría: ${altimetria.getAttribute("archivo")}</li>`;
        html += '</ul></section>';
    }

    return html + '</article>';
};

document.addEventListener("DOMContentLoaded", () => new MapHandler());
