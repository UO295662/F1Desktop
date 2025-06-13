class TiempoHandler {
    constructor() {
        this.weatherIcons = {
            // Mapeo de códigos OpenMeteo a iconos locales
            0: 'multimedia/weather/sunny.png',           // Despejado
            1: 'multimedia/weather/partly-cloudy.png',   // Principalmente despejado
            2: 'multimedia/weather/partly-cloudy.png',   // Parcialmente nublado
            3: 'multimedia/weather/cloudy.png',          // Nublado
            45: 'multimedia/weather/foggy.png',          // Niebla
            48: 'multimedia/weather/foggy.png',          // Niebla con escarcha
            51: 'multimedia/weather/drizzle.png',        // Llovizna ligera
            53: 'multimedia/weather/drizzle.png',        // Llovizna moderada
            55: 'multimedia/weather/drizzle.png',        // Llovizna densa
            56: 'multimedia/weather/frost.png',          // Llovizna helada ligera
            57: 'multimedia/weather/frost.png',          // Llovizna helada densa
            61: 'multimedia/weather/light-rain.png',     // Lluvia ligera
            63: 'multimedia/weather/rainy.png',          // Lluvia moderada
            65: 'multimedia/weather/heavy-rain.png',     // Lluvia intensa
            66: 'multimedia/weather/frost.png',          // Lluvia helada ligera
            67: 'multimedia/weather/frost.png',          // Lluvia helada intensa
            71: 'multimedia/weather/snowy.png',          // Nieve ligera
            73: 'multimedia/weather/snowy.png',          // Nieve moderada
            75: 'multimedia/weather/snowy.png',          // Nieve intensa
            77: 'multimedia/weather/snowy.png',          // Granizo de nieve
            80: 'multimedia/weather/light-rain.png',     // Chubascos ligeros
            81: 'multimedia/weather/rainy.png',          // Chubascos moderados
            82: 'multimedia/weather/heavy-rain.png',     // Chubascos violentos
            85: 'multimedia/weather/snowy.png',          // Chubascos de nieve ligeros
            86: 'multimedia/weather/snowy.png',          // Chubascos de nieve intensos
            95: 'multimedia/weather/thunderstorm.png',   // Tormenta
            96: 'multimedia/weather/thunderstorm.png',   // Tormenta con granizo ligero
            99: 'multimedia/weather/thunderstorm.png'    // Tormenta con granizo intenso
        };
    }

    obtenerDatosTiempo() {
        const url = 'https://api.open-meteo.com/v1/forecast?latitude=43.36&longitude=-5.84&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max,weathercode&timezone=Europe/Madrid&forecast_days=5';
        
        fetch(url)
            .then(response => response.json())
            .then(data => this.mostrarPrediccion(data))
            .catch(error => {
                console.error('Error al obtener datos meteorológicos:', error);
                this.mostrarError();
            });
    }

    mostrarPrediccion(data) {
        const container = document.querySelector('main section');
        
        let html = '<h2>Predicción Meteorológica - Oviedo</h2>';
        html += '<section>';
        
        for (let i = 0; i < data.daily.time.length; i++) {
            const fecha = new Date(data.daily.time[i]);
            const weatherCode = data.daily.weathercode[i];
            const iconPath = this.weatherIcons[weatherCode] || this.weatherIcons[0]; // Default: sunny
            
            html += `
                <article>
                    <h3>${this.formatearFecha(fecha)}</h3>
                    <img src="${iconPath}" alt="${this.obtenerDescripcionTiempo(weatherCode)}">
                    <p>Máxima: ${Math.round(data.daily.temperature_2m_max[i])}°C</p>
                    <p>Mínima: ${Math.round(data.daily.temperature_2m_min[i])}°C</p>
                    <p>Precipitación: ${data.daily.precipitation_sum[i]}mm</p>
                    <p>Viento: ${Math.round(data.daily.windspeed_10m_max[i])}km/h</p>
                    <p>Condición: ${this.obtenerDescripcionTiempo(weatherCode)}</p>
                </article>
            `;
        }
        
        html += '</section>';
        container.innerHTML = html;
    }

    obtenerDescripcionTiempo(weatherCode) {
        const descripciones = {
            0: 'Despejado',
            1: 'Principalmente despejado',
            2: 'Parcialmente nublado',
            3: 'Nublado',
            45: 'Niebla',
            48: 'Niebla con escarcha',
            51: 'Llovizna ligera',
            53: 'Llovizna moderada',
            55: 'Llovizna densa',
            56: 'Llovizna helada ligera',
            57: 'Llovizna helada densa',
            61: 'Lluvia ligera',
            63: 'Lluvia moderada',
            65: 'Lluvia intensa',
            66: 'Lluvia helada ligera',
            67: 'Lluvia helada intensa',
            71: 'Nieve ligera',
            73: 'Nieve moderada',
            75: 'Nieve intensa',
            77: 'Granizo de nieve',
            80: 'Chubascos ligeros',
            81: 'Chubascos moderados',
            82: 'Chubascos violentos',
            85: 'Chubascos de nieve ligeros',
            86: 'Chubascos de nieve intensos',
            95: 'Tormenta',
            96: 'Tormenta con granizo',
            99: 'Tormenta con granizo intenso'
        };
        
        return descripciones[weatherCode] || 'Condición desconocida';
    }

    formatearFecha(fecha) {
        const opciones = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return fecha.toLocaleDateString('es-ES', opciones);
    }

    mostrarError() {
        const container = document.querySelector('main section');
        container.innerHTML = `
            <h2>Predicción Meteorológica - Oviedo</h2>
            <p>No se pudieron cargar los datos meteorológicos. Inténtalo más tarde.</p>
        `;
    }
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    const tiempoHandler = new TiempoHandler();
    tiempoHandler.obtenerDatosTiempo();
});