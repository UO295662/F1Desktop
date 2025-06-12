class Meteorologia {
    constructor() {
        this.ciudad = "Oviedo";
        this.lat = 43.3603;
        this.lon = -5.8448;
        this.url = `https://api.open-meteo.com/v1/forecast?latitude=${this.lat}&longitude=${this.lon}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weathercode,windspeed_10m_max,winddirection_10m_dominant&timezone=Europe/Madrid`;
   }

    obtenerDescripcion(codigo) {
        const weatherCodes = {
            0: "Despejado",
            1: "Mayormente despejado",
            2: "Parcialmente nublado",
            3: "Nublado",
            45: "Niebla",
            48: "Niebla con escarcha",
            51: "Llovizna ligera",
            53: "Llovizna moderada",
            55: "Llovizna intensa",
            61: "Lluvia ligera",
            63: "Lluvia moderada",
            65: "Lluvia fuerte",
            71: "Nevada ligera",
            73: "Nevada moderada",
            75: "Nevada fuerte",
            77: "Aguanieve",
            80: "Lluvia ligera intermitente",
            81: "Lluvia moderada intermitente",
            82: "Lluvia fuerte intermitente",
            85: "Nevada ligera intermitente",
            86: "Nevada fuerte intermitente",
            95: "Tormenta eléctrica",
        };
        return weatherCodes[codigo] || "Desconocido";
    }

    obtenerIcono(codigo) {
        const iconMap = {
            0: "01d", 
            1: "02d", 
            2: "03d", 
            3: "04d", 
            45: "50d", 
            48: "50d", 
            51: "09d", 
            61: "10d", 
            71: "13d", 
            95: "11d", 
        };
        return iconMap[codigo] || "03d";
    }

    mostrarClimaActual(datos) {
        return `
            <section>
                <h3>Clima Actual en ${this.ciudad}</h3>
                <img src="https://openweathermap.org/img/wn/${this.obtenerIcono(datos.current.weather_code)}@2x.png" 
                     alt="${this.obtenerDescripcion(datos.current.weather_code)}">
                <ul>
                    <li>Temperatura: ${Math.round(datos.current.temperature_2m)}°C</li>
                    <li>Humedad Relativa: ${datos.current.relative_humidity_2m}%</li>
                    <li>Temperatura Aparente: ${Math.round(datos.current.apparent_temperature)}°C</li>
                    <li>Precipitación: ${datos.current.precipitation} mm</li>
                    <li>Velocidad del Viento: ${datos.current.wind_speed_10m} km/h</li>
                    <li>Descripción: ${this.obtenerDescripcion(datos.current.weather_code)}</li>
                </ul>
            </section>
        `;
    }

    mostrarPronostico(datos) {
        let pronosticoHTML = `
            <h3>Pronóstico para los próximos días</h3>
            <section>
            <h3 style="display: none;">Pronóstico para los próximos días</h3>`;
        
        datos.daily.time.forEach((fecha, index) => {
            const weatherCode = datos.daily.weathercode[index];
            pronosticoHTML += `
                <article>
                <h4>Pronóstico del día</h4>
                    <p>${new Date(fecha).toLocaleDateString()}</p>
                    <img src="https://openweathermap.org/img/wn/${this.obtenerIcono(weatherCode)}@2x.png" 
                         alt="${this.obtenerDescripcion(weatherCode)}">
                    <ul>
                        <li>Máx: ${Math.round(datos.daily.temperature_2m_max[index])}°C</li>
                        <li>Mín: ${Math.round(datos.daily.temperature_2m_min[index])}°C</li>
                        <li>Lluvia: ${datos.daily.precipitation_probability_max[index]}%</li>
                        <li>Viento: ${datos.daily.windspeed_10m_max[index]} km/h</li>
                        <li>${this.obtenerDescripcion(weatherCode)}</li>
                    </ul>
                </article>
            `;
        });
        pronosticoHTML += '</section>';
        return pronosticoHTML;
    }

    obtenerClima() {
        $.ajax({
            dataType: "json",
            url: this.url,
            method: 'GET',
            success: (datos) => {
                const climaActual = this.mostrarClimaActual(datos);
                const pronostico = this.mostrarPronostico(datos);
                
                $("main > aside").empty();
                
                $("main > aside:first-of-type").html(climaActual);
                $("main > aside:last-of-type").html(pronostico);
            },
            error: (xhr, status, error) => {
                console.error('Error al obtener el pronóstico:', error);
                $("main > aside").html('<p>Error al cargar el pronóstico del tiempo.</p>');
            }
        });
    }
}