$(document).ready(function() {
    class Turismo {
        constructor() {
            this.API_KEY = 'pub_17947a3446e14d7a898089f0b7221bef';
            this.QUERY = 'Oviedo Asturias';
            this.API_URL = `https://newsdata.io/api/1/news?apikey=${this.API_KEY}&q=${this.QUERY}&language=es&country=es`;
            
            this.imagenes = [
                {src: "multimedia/Situacion_Oviedo.jpg", alt: "Mapa de situación del concejo de Oviedo", caption: "Mapa de situación del concejo de Oviedo"},
                {src: "multimedia/oviedo-catedral.jpg", alt: "Catedral de Oviedo", caption: "Catedral de Oviedo"},
                {src: "multimedia/naranco.jpg", alt: "Santa María del Naranco", caption: "Santa María del Naranco"},
                {src: "multimedia/san_julian_delosprados.jpg", alt: "San Julián de Los Prados", caption: "San Julián de Los Prados"},
                {src: "multimedia/la_foncalada.jpg", alt: "La Foncalada", caption: "La Foncalada"}
            ];

            this.indice = 0;
        }

        inicializar() {
            this.inicializarCarrusel();
            this.cargarNoticias();
            this.configurarActualizacionNoticias();
        }

        inicializarCarrusel() {
            const self = this;
            
            $('section nav button:first').on('click', function() {
                self.indice = (self.indice - 1 + self.imagenes.length) % self.imagenes.length;
                self.mostrarImagen(self.indice);
            });

            $('section nav button:last').on('click', function() {
                self.indice = (self.indice + 1) % self.imagenes.length;
                self.mostrarImagen(self.indice);
            });

            this.mostrarImagen(this.indice);
        }
        
        mostrarImagen(i) {
            const $contenedor = $('figure');
            const $imagen = $contenedor.find('img');
            const $texto = $contenedor.find('figcaption');
            
            $imagen.attr({
                'src': this.imagenes[i].src,
                'alt': this.imagenes[i].alt
            });
            $texto.text(this.imagenes[i].caption);
        }

        mostrarNoticia(noticia) {
            const fecha = new Date(noticia.pubDate).toLocaleDateString('es-ES');
            const titulo = noticia.title;
            const descripcion = noticia.description || noticia.content || 'Descripción no disponible';
            const descripcionCorta = descripcion.length > 150 ? 
                                    descripcion.substring(0, 150) + '...' : 
                                    descripcion;
            
            return $('<article>').append(
                $('<h3>').text(titulo),
                $('<p>').append(
                    $('<time>')
                        .attr('datetime', noticia.pubDate)
                        .text(fecha)
                ),
                $('<p>').text(descripcionCorta),
                $('<a>')
                    .attr({
                        'href': noticia.link,
                        'target': '_blank',
                        'rel': 'noopener noreferrer'
                    })
                    .text('Leer más'),
                noticia.source_id ? $('<p>').text('Fuente: ' + this.limpiarTexto(noticia.source_id)) : ''
            );
        }

        limpiarTexto(texto) {
            return texto ? texto.replace(/[^\w\s]/gi, '') : '';
        }

        cargarNoticias() {
            const self = this;
            
            $.ajax({
                url: this.API_URL,
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
                headers: {
                    'Accept': 'application/json; charset=utf-8',
                    'Content-Type': 'application/json; charset=utf-8'
                }
            })
            .done(function(datos) {
                const $contenedor = $('article');
                $contenedor.empty();

                if (datos.status === 'success' && datos.results && datos.results.length > 0) {
                    $contenedor.append('<h3>Noticias recientes sobre Oviedo</h3>');
                    
                    const noticias = datos.results.slice(0, 5);
                    
                    noticias.forEach(function(noticia) {
                        if (noticia.title && noticia.link) {
                            try {
                                $contenedor.append(self.mostrarNoticia(noticia));
                            } catch (error) {
                                console.warn('Error procesando noticia:', error);
                            }
                        }
                    });
                    
                    if ($contenedor.children().length <= 1) {
                        $contenedor.html('<h3>Noticias sobre Oviedo</h3><p>No se encontraron noticias relevantes sobre Oviedo.</p>');
                    }
                } else {
                    $contenedor.html('<h3>Noticias sobre Oviedo</h3><p>No se encontraron noticias recientes sobre Oviedo.</p>');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error al cargar las noticias:', error);
                const $contenedor = $('article');
                
                if (xhr.status === 429) {
                    $contenedor.html('<h3>Noticias sobre Oviedo</h3><p>Se ha alcanzado el límite de consultas de la API de noticias. Inténtalo más tarde.</p>');
                } else if (xhr.status === 401) {
                    $contenedor.html('<h3>Noticias sobre Oviedo</h3><p>Error de autenticación con la API de noticias.</p>');
                } else {
                    $contenedor.html('<h3>Noticias sobre Oviedo</h3><p>Lo sentimos, no se pudieron cargar las noticias en este momento. Verifica tu conexión a internet.</p>');
                }
            });
        }

        configurarActualizacionNoticias() {
            const self = this;
            setInterval(function() {
                self.cargarNoticias();
            }, 7200000);
        }
    }
    const turismo = new Turismo();
    turismo.inicializar();
});