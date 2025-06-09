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
            return;
        }
    }
    this.mapSection = document.createElement('section');
    this.mapSection.innerHTML = '<h2>Mapa de Rutas</h2>';
    document.body.appendChild(this.mapSection);
};

MapHandler.prototype.mostrarMapa = function() {
    if (!this.mapSection) return;
    
    this.mapSection.hidden = false;
    
    if (!this.mapInitialized) {
        setTimeout(() => this.initMap(), 500);
        this.mapInitialized = true;
    }
};

MapHandler.prototype.initMap = function() {
    const popupElement = document.createElement('section');
    popupElement.innerHTML = '<h6>Popup</h6><a href="#">×</a><section><h6>Contenido</h6></section>';
    
    this.popup = new ol.Overlay({ element: popupElement, autoPan: { animation: { duration: 250 } } });
    this.map = new ol.Map({
        target: this.mapSection,
        layers: [new ol.layer.Tile({ source: new ol.source.OSM() })],
        overlays: [this.popup],
        view: new ol.View({ center: ol.proj.fromLonLat([-5.8593, 43.3614]), zoom: 14 }),
        controls: [new ol.control.Zoom(), new ol.control.Rotate(), new ol.control.Attribution(), new ol.control.FullScreen()]
    });
    
    popupElement.querySelector('a').onclick = () => (this.popup.setPosition(undefined), false);
    
    this.map.on('singleclick', (evt) => {
        const feature = this.map.forEachFeatureAtPixel(evt.pixel, f => f);
        if (feature) {
            const content = popupElement.querySelector('section');
            content.innerHTML = `<h6>Info</h6><section><h6>${feature.get('name') || 'Punto'}</h6><p>${feature.get('description') || ''}</p></section>`;
            this.popup.setPosition(evt.coordinate);
        }
    });
    
    setTimeout(() => this.map.updateSize(), 500);
};

MapHandler.prototype.clearMap = function() {
    if (this.currentVectorLayer) {
        this.map?.removeLayer(this.currentVectorLayer);
        this.currentVectorLayer = null;
    }
    this.popup?.setPosition(undefined);
};

MapHandler.prototype.cargarKMLPorRuta = function(nombreRuta, archivoKML) {
    if (!this.map) return setTimeout(() => this.cargarKMLPorRuta(nombreRuta, archivoKML), 1000);
    
    this.clearMap();
    fetch(archivoKML || 'xml/rutas.kml')
        .then(r => r.text())
        .then(kml => this.procesarKMLPorRuta(kml, nombreRuta))
        .catch(e => console.error('Error KML:', e));
};

MapHandler.prototype.procesarKMLPorRuta = function(kmlText, nombreRuta) {
    const parser = new DOMParser();
    const kmlDoc = parser.parseFromString(kmlText, "application/xml");
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
                    name, description: desc || `${lat.toFixed(6)}, ${lng.toFixed(6)}\n${alt || 0}m`
                });
                
                feature.setStyle(new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 10, fill: new ol.style.Fill({ color: '#ff0000' }),
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
                    name, description: desc || `Línea de ruta: ${nombreRuta}`
                });
                lineFeature.setStyle(new ol.style.Style({
                    stroke: new ol.style.Stroke({ color: '#d77a61', width: 4 })
                }));
                features.push(lineFeature);
            }
        }
    });

    if (coords.length > 1) {
        const routeLine = new ol.Feature({
            geometry: new ol.geom.LineString(coords),
            name: `Ruta: ${nombreRuta}`, type: 'route'
        });
        routeLine.setStyle(new ol.style.Style({
            stroke: new ol.style.Stroke({ color: '#2E8B57', width: 3, lineDash: [5, 5] })
        }));
        features.push(routeLine);
    }

    if (features.length > 0) {
        const vectorSource = new ol.source.Vector({ features });
        this.currentVectorLayer = new ol.layer.Vector({ source: vectorSource });
        this.map.addLayer(this.currentVectorLayer);
        this.map.getView().fit(vectorSource.getExtent(), { padding: [50, 50, 50, 50], maxZoom: 16 });
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
                            radius: 8, fill: new ol.style.Fill({ color: '#d77a61' }),
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
        this.map.getView().fit(vectorSource.getExtent(), { padding: [50, 50, 50, 50], maxZoom: 16 });
    }
};

MapHandler.prototype.procesarRutasXML = function(xmlString) {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, "application/xml");
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
    fetch(`xml/${archivoSVG}`)
        .then(r => r.text())
        .then(svg => {
            const section = document.createElement('section');
            section.innerHTML = `<h4>Perfil Altimétrico</h4><section><h5>SVG</h5>${svg}</section>`;
            contenedorRuta.querySelector('article')?.appendChild(section);
        })
        .catch(() => {
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