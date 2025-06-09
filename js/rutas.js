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
}

MapHandler.prototype.configurarInputFile = function() {
    const fileInput = document.querySelector('input[type="file"]');
    const loadButton = document.querySelector('button[type="button"]');
    
    if (!fileInput || !loadButton) return;
    
    loadButton.onclick = () => {
        const file = fileInput.files[0];
        if (!file) return alert('Selecciona un archivo XML');
        if (!file.name.toLowerCase().endsWith('.xml')) return alert('Archivo XML inválido');
        
        const reader = new FileReader();
        reader.onload = (e) => this.procesarRutasXML(e.target.result);
        reader.readAsText(file);
    };
    
    fileInput.onchange = (e) => {
        const file = e.target.files[0];
        loadButton.textContent = file?.name.toLowerCase().endsWith('.xml') ? `Cargar: ${file.name}` : 'Cargar Rutas';
    };
};

MapHandler.prototype.findMapContainer = function() {
    const sections = document.querySelectorAll('section');
    for (let section of sections) {
        const h2 = section.querySelector('h2');
        if (h2?.textContent.includes('Mapa de Rutas')) {
            this.mapSection = section;
            // Crear un div específico para el mapa dentro de la sección
            let mapDiv = section.querySelector('#map');
            if (!mapDiv) {
                mapDiv = document.createElement('div');
                mapDiv.id = 'map';
                mapDiv.style.width = '100%';
                mapDiv.style.height = '400px';
                mapDiv.style.border = '1px solid #ccc';
                mapDiv.style.borderRadius = '0.5rem';
                section.appendChild(mapDiv);
            }
            return;
        }
    }
    // Si no encuentra la sección, crear una nueva
    this.mapSection = document.createElement('section');
    this.mapSection.innerHTML = '<h2>Mapa de Rutas</h2>';
    const mapDiv = document.createElement('div');
    mapDiv.id = 'map';
    mapDiv.style.width = '100%';
    mapDiv.style.height = '400px';
    mapDiv.style.border = '1px solid #ccc';
    mapDiv.style.borderRadius = '0.5rem';
    this.mapSection.appendChild(mapDiv);
    document.body.appendChild(this.mapSection);
};

MapHandler.prototype.mostrarMapa = function() {
    if (!this.mapSection) return;
    
    this.mapSection.hidden = false;
    
    if (!this.mapInitialized) {
        // Dar más tiempo para que se renderice el contenedor
        setTimeout(() => this.initMap(), 100);
        this.mapInitialized = true;
    } else if (this.map) {
        // Si el mapa ya existe, solo actualizar el tamaño
        setTimeout(() => this.map.updateSize(), 100);
    }
};

MapHandler.prototype.initMap = function() {
    // Crear elemento popup
    const popupElement = document.createElement('div');
    popupElement.innerHTML = `
        <div style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); min-width: 200px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h6 style="margin: 0; color: #8b3a3a;">Información</h6>
                <a href="#" style="text-decoration: none; font-size: 18px; color: #999;">&times;</a>
            </div>
            <div class="popup-content">
                <h6 style="margin: 0 0 5px 0;">Punto</h6>
                <p style="margin: 0; font-size: 14px;">Información del punto</p>
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
    
    // Inicializar el mapa
    this.map = new ol.Map({
        target: 'map', // Usar el ID del div específico
        layers: [
            new ol.layer.Tile({ 
                source: new ol.source.OSM() 
            })
        ],
        overlays: [this.popup],
        view: new ol.View({ 
            center: ol.proj.fromLonLat([-5.8593, 43.3614]), // Coordenadas de Oviedo
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
            const content = popupElement.querySelector('.popup-content');
            const name = feature.get('name') || 'Punto';
            const description = feature.get('description') || 'Sin descripción';
            
            content.innerHTML = `
                <h6 style="margin: 0 0 5px 0; color: #8b3a3a;">${name}</h6>
                <p style="margin: 0; font-size: 14px; line-height: 1.4;">${description}</p>
            `;
            this.popup.setPosition(evt.coordinate);
        } else {
            this.popup.setPosition(undefined);
        }
    });
    
    // Forzar actualización del tamaño después de inicializar
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
            // Si falla, mostrar puntos de ejemplo para Oviedo
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
        
        feature.setStyle(new ol.style.Style({
            image: new ol.style.Circle({
                radius: 10,
                fill: new ol.style.Fill({ color: '#8b3a3a' }),
                stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
            })
        }));
        
        return feature;
    });
    
    // Crear línea conectando los puntos
    const coords = puntosOviedo.map(p => ol.proj.fromLonLat(p.coords));
    const routeLine = new ol.Feature({
        geometry: new ol.geom.LineString(coords),
        name: `Ruta: ${nombreRuta}`,
        type: 'route'
    });
    
    routeLine.setStyle(new ol.style.Style({
        stroke: new ol.style.Stroke({ 
            color: '#5a1f1f', 
            width: 3, 
            lineDash: [5, 5] 
        })
    }));
    
    features.push(routeLine);
    
    const vectorSource = new ol.source.Vector({ features });
    this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
    this.map.addLayer(this.currentVectorLayer);
    
    // Ajustar vista a los puntos
    this.map.getView().fit(vectorSource.getExtent(), { 
        padding: [50, 50, 50, 50], 
        maxZoom: 16 
    });
};

MapHandler.prototype.procesarKMLPorRuta = function(kmlText, nombreRuta) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "application/xml");
    
    // Verificar si hay errores de parsing
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
    
    // Si no encuentra la ruta específica, cargar todos los puntos
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
                
                feature.setStyle(new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 10, 
                        fill: new ol.style.Fill({ color: '#8b3a3a' }),
                        stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
                    })
                }));
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
        routeLine.setStyle(new ol.style.Style({
            stroke: new ol.style.Stroke({ 
                color: '#5a1f1f', 
                width: 3, 
                lineDash: [5, 5] 
            })
        }));
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
        // Si no hay features del KML, mostrar puntos de ejemplo
        this.mostrarPuntosEjemplo(nombreRuta);
    }
};

MapHandler.prototype.cargarTodosPuntosKML = function(kmlText) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "application/xml");
    const features = [];
    
    kmlDoc.querySelectorAll("coordinates").forEach((coordElement, index) => {
        const coordsText = coordElement.textContent?.trim();
        if (coordsText?.includes(',')) {
            const lines = coordsText.split(/[\s\n]+/).filter(line => line.trim() && line.includes(','));
            
            lines.forEach((line, pointIndex) => {
                const [lng, lat, alt] = line.trim().split(',').map(Number);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const feature = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([lng, lat])),
                        name: `Punto ${index + 1}-${pointIndex + 1}`,
                        description: `${lat.toFixed(6)}, ${lng.toFixed(6)}\n${alt || 0}m`
                    });
                    feature.setStyle(new ol.style.Style({
                        image: new ol.style.Circle({
                            radius: 8, 
                            fill: new ol.style.Fill({ color: '#8b3a3a' }),
                            stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
                        })
                    }));
                    features.push(feature);
                }
            });
        }
    });

    if (features.length > 0) {
        const vectorSource = new ol.source.Vector({ features });
        this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
        this.map.addLayer(this.currentVectorLayer);
        this.map.getView().fit(vectorSource.getExtent(), { 
            padding: [50, 50, 50, 50], 
            maxZoom: 16 
        });
    } else {
        // Si no hay puntos, mostrar ejemplo de Oviedo
        this.mostrarPuntosEjemplo("Ruta por defecto");
    }
};

MapHandler.prototype.procesarRutasXML = function(xmlString) {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, "application/xml");
    
    // Verificar errores de parsing
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
    
    // Limpiar selectores existentes
    const selectoresAnteriores = main.querySelectorAll('section[data-tipo="selector-rutas"], section[data-tipo="contenedor-ruta"]');
    selectoresAnteriores.forEach(el => el.remove());

    const selectorSection = document.createElement('section');
    selectorSection.setAttribute('data-tipo', 'selector-rutas');
    selectorSection.innerHTML = `
        <h3>Selecciona una Ruta</h3>
        <label for="selector-rutas">Elige una ruta:</label>
        <select id="selector-rutas">
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
    contenedorRuta.setAttribute('data-tipo', 'contenedor-ruta');
    contenedorRuta.innerHTML = '<h3>Información de la Ruta</h3><p>Selecciona una ruta para ver su información detallada.</p>';
    
    main.appendChild(selectorSection);
    main.appendChild(contenedorRuta);
    
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
    const contenedorRuta = document.querySelector('section[data-tipo="contenedor-ruta"]');
    
    const nombreRuta = ruta.querySelector("nombre")?.textContent || "";
    const planimetria = ruta.querySelector("planimetria")?.getAttribute("archivo");
    const altimetria = ruta.querySelector("altimetria")?.getAttribute("archivo");
    
    if (nombreRuta && planimetria) this.cargarKMLPorRuta(nombreRuta, `xml/${planimetria}`);
    
    // Mantener el encabezado de la sección y añadir el contenido
    contenedorRuta.innerHTML = '<h3>Información de la Ruta</h3>' + this.convertirRutaAHTML(ruta);
    if (altimetria) this.cargarSVGRuta(contenedorRuta, altimetria);
    contenedorRuta.scrollIntoView({ behavior: 'smooth' });
};

MapHandler.prototype.cargarSVGRuta = function(contenedorRuta, archivoSVG) {
    // Añadir timestamp para evitar cache
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
                <div class="svg-container">
                    ${svg}
                </div>
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
        if (val) html += `<li><strong>${label}:</strong> ${val}${field === 'recomendacion' ? '/10' : ''}</li>`;
    });
    
    const desc = get("descripcion");
    html += `</ul>${desc ? `<p><strong>Descripción:</strong> ${desc}</p>` : ''}</section>`;
    
    // Punto de inicio
    const inicio = ruta.querySelector("inicio");
    if (inicio) {
        html += '<section><h4>Punto de Inicio</h4><ul>';
        ['lugar', 'direccion'].forEach(field => {
            const val = inicio.querySelector(field)?.textContent;
            if (val) html += `<li><strong>${field === 'lugar' ? 'Lugar' : 'Dirección'}:</strong> ${val}</li>`;
        });
        
        const coords = inicio.querySelector("coordenadas");
        if (coords) {
            const lat = coords.querySelector("latitud")?.textContent;
            const lng = coords.querySelector("longitud")?.textContent;
            const alt = coords.querySelector("altitud")?.textContent;
            if (lat && lng) html += `<li><strong>Coordenadas:</strong> ${lat}, ${lng}${alt ? ` (${alt}m)` : ''}</li>`;
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
            if (dist) html += `<li><strong>Distancia:</strong> ${dist.textContent} ${dist.getAttribute("unidad") || "m"}</li>`;
            
            const coordsH = hito.querySelector("coordenadas");
            if (coordsH) {
                const lat = coordsH.querySelector("latitud")?.textContent;
                const lng = coordsH.querySelector("longitud")?.textContent;
                const alt = coordsH.querySelector("altitud")?.textContent;
                if (lat && lng) html += `<li><strong>Coordenadas:</strong> ${lat}, ${lng}${alt ? ` (${alt}m)` : ''}</li>`;
            }
            html += '</ul>';
            
            const fotos = hito.querySelectorAll("galeria_fotos foto");
            if (fotos.length > 0) {
                html += '<aside><p><strong>Fotos:</strong></p>';
                fotos.forEach(foto => {
                    const archivo = foto.getAttribute("archivo");
                    const desc = foto.textContent;
                    if (archivo) {
                        html += `<figure><img src="${archivo}" alt="${desc}"><figcaption>${desc}</figcaption></figure>`;
                    }
                });
                html += '</aside>';
            }
            
            const videos = hito.querySelectorAll("galeria_videos video");
            if (videos.length > 0) {
                html += '<aside><p><strong>Videos:</strong></p>';
                videos.forEach(video => {
                    const archivo = video.getAttribute("archivo");
                    const desc = video.textContent;
                    if (archivo) {
                        html += `<figure><video controls><source src="${archivo}" type="video/mp4">${desc}</video><figcaption>${desc}</figcaption></figure>`;
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
        if (planimetria) html += `<li><strong>Planimetría:</strong> ${planimetria.getAttribute("archivo")}</li>`;
        if (altimetria) html += `<li><strong>Altimetría:</strong> ${altimetria.getAttribute("archivo")}</li>`;
        html += '</ul></section>';
    }
    
    return html + '</article>';
};

document.addEventListener("DOMContentLoaded", () => new MapHandler());