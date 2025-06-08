$(document).ready(function() {
    const API_KEY = 'pub_17947a3446e14d7a898089f0b7221bef';
    const QUERY = 'Oviedo';
    const API_URL = `https://newsdata.io/api/1/news?apikey=${API_KEY}&q=${QUERY}&language=es&country=es`;
    
    const imagenes = [
        {src: "multimedia/Situacion_Oviedo.jpg", alt: "Mapa de situación del concejo de Oviedo", caption: "Mapa de situación del concejo de Oviedo"},
        {src: "multimedia/oviedo-catedral.jpg", alt: "Catedral de Oviedo", caption: "Catedral de Oviedo"},
        {src: "multimedia/naranco.jpg", alt: "Santa María del Naranco", caption: "Santa María del Naranco"},
        {src: "multimedia/san_julian_delosprados.jpg", alt: "San Julián de Los Prados", caption: "San Julián de Los Prados"},
        {src: "multimedia/la_foncalada.jpg", alt: "La Foncalada", caption: "La Foncalada"}
    ];

    let indice = 0;
    
    function mostrarImagen(i) {
        const $contenedor = $('figure');
        const $imagen = $contenedor.find('img');
        const $texto = $contenedor.find('figcaption');
        
        $imagen.attr({
            'src': imagenes[i].src,
            'alt': imagenes[i].alt
        });
        $texto.text(imagenes[i].caption);
    }

    $('section nav button:first').on('click', function() {
        indice = (indice - 1 + imagenes.length) % imagenes.length;
        mostrarImagen(indice);
    });

    $('section nav button:last').on('click', function() {
        indice = (indice + 1) % imagenes.length;
        mostrarImagen(indice);
    });

    mostrarImagen(indice);

    function mostrarNoticia(noticia) {
        const fecha = new Date(noticia.pubDate).toLocaleDateString('es-ES');
        const descripcion = noticia.description || noticia.content || 'Descripción no disponible';
        
        return $('<article>').append(
            $('<h3>').text(noticia.title),
            $('<p>').append(
                $('<time>')
                    .attr('datetime', noticia.pubDate)
                    .text(fecha)
            ),
            $('<p>').text(descripcion.substring(0, 200) + '...'),
            $('<a>')
                .attr({
                    'href': noticia.link,
                    'target': '_blank',
                    'rel': 'noopener noreferrer'
                })
                .text('Leer más'),
            noticia.source_id ? $('<p>').text(`Fuente: ${noticia.source_id}`) : ''
        );
    }

    function cargarNoticias() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            timeout: 10000
        })
        .done(function(datos) {
            const $contenedor = $('article[role="feed"]');
            $contenedor.empty();

            if (datos.status === 'success' && datos.results && datos.results.length > 0) {
                const noticias = datos.results.slice(0, 5);
                
                noticias.forEach(function(noticia) {
                    if (noticia.title && noticia.link) {
                        $contenedor.append(mostrarNoticia(noticia));
                    }
                });
                
                if ($contenedor.children().length === 0) {
                    $contenedor.html('<p>No se encontraron noticias relevantes sobre Oviedo.</p>');
                }
            } else {
                $contenedor.html('<p>No se encontraron noticias recientes sobre Oviedo.</p>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error al cargar las noticias:', error);
            const $contenedor = $('article[role="feed"]');
            
            if (xhr.status === 429) {
                $contenedor.html('<p>Se ha alcanzado el límite de consultas de la API de noticias. Inténtalo más tarde.</p>');
            } else if (xhr.status === 401) {
                $contenedor.html('<p>Error de autenticación con la API de noticias.</p>');
            } else {
                $contenedor.html('<p>Lo sentimos, no se pudieron cargar las noticias en este momento. ' +
                              'Verifica tu conexión a internet.</p>');
            }
        });
    }

    cargarNoticias();
    setInterval(cargarNoticias, 7200000);
});