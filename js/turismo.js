$(document).ready(function() {
    const API_KEY = '44608818046f42e0994e6b9589147ebd';
    const QUERY = 'Oviedo';
    const API_URL = `https://newsapi.org/v2/everything?q=${QUERY}&sortBy=publishedAt&apiKey=${API_KEY}`;
    
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

    $('button[aria-label="Anterior"]').on('click', function() {
        indice = (indice - 1 + imagenes.length) % imagenes.length;
        mostrarImagen(indice);
    });

    $('button[aria-label="Siguiente"]').on('click', function() {
        indice = (indice + 1) % imagenes.length;
        mostrarImagen(indice);
    });

    mostrarImagen(indice);

    function mostrarNoticia(noticia) {
        const fecha = new Date(noticia.publishedAt).toLocaleDateString('es-ES');
        return $('<article>').append(
            $('<h3>').text(noticia.title),
            $('<p>').append(
                $('<time>')
                    .attr('datetime', noticia.publishedAt)
                    .text(fecha)
            ),
            $('<p>').text(noticia.description),
            $('<a>')
                .attr({
                    'href': noticia.url,
                    'target': '_blank',
                    'rel': 'noopener'
                })
                .text('Leer más')
        );
    }

    function cargarNoticias() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            headers: {
                'Authorization': API_KEY,
                'X-Api-Key': API_KEY
            }
        })
        .done(function(datos) {
            const $contenedor = $('article[role="feed"]');
            $contenedor.empty();

            if (datos.articles && datos.articles.length > 0) {
                const noticiasEnEspanol = datos.articles.filter(noticia => 
                    noticia.language === 'es' || 
                    noticia.url.includes('.es') || 
                    noticia.url.includes('/es/')
                );
                
                const noticias = noticiasEnEspanol.slice(0, 5);
                
                if (noticias.length > 0) {
                    noticias.forEach(function(noticia) {
                        $contenedor.append(mostrarNoticia(noticia));
                    });
                } else {
                    $contenedor.html('<p>No se encontraron noticias en español.</p>');
                }
            } else {
                $contenedor.html('<p>No se encontraron noticias recientes.</p>');
            }
        })
        .fail(function(error) {
            console.error('Error al cargar las noticias:', error);
            $('article[role="feed"]')
                .html('<p>Lo sentimos, no se pudieron cargar las noticias en este momento. ' +
                      'Debido a las restricciones de la API, esta funcionalidad solo está disponible ' +
                      'en modo desarrollo.</p>');
        });
    }

    cargarNoticias();
    setInterval(cargarNoticias, 3600000);
});