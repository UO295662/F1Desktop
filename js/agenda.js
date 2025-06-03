class Agenda {
    constructor() {
        this.url = 'https://ergast.com/api/f1/current.json';
    }

    obtenerYMostrarCalendario() {
        $.ajax({
            url: this.url,
            method: 'GET',
            dataType: 'json',
            success: (respuesta) => {
                const carreras = respuesta.MRData.RaceTable.Races;
                this.mostrarCarreras(carreras);
            },
            error: (xhr, status, error) => {
                console.error(`Error en la consulta: ${status} - ${error}`);
            }
        });
    }

    mostrarCarreras(carreras) {
        const contenedor = document.querySelector("section");
        contenedor.innerHTML="<h3>Calendario de las Carreras de la Temporada</h3>"; 
        carreras.forEach(carrera => {
            const { raceName, date, time, Circuit } = carrera;
            const { circuitName, Location } = Circuit;
            const { lat, long, locality, country } = Location;

            const carreraHTML = `
            <section>
                <h3>${raceName}</h3>
                <h4>Circuito:</h4> 
                <p>${circuitName}</p>
                <h4>Ubicación:</h4> 
                <p>${locality}, ${country}</p>
                <h4>Coordenadas:</h4> 
                <p>Latitud: ${lat}, Longitud: ${long}</p>
                <h4>Fecha:</h4> 
                <p>${date}</p>
                <h4>Hora:</h4> 
                <p>${time}</p>
            </section>
            <hr>`;

            contenedor.innerHTML+=carreraHTML;
        });
    }
}
