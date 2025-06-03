"use strict";

class Viajes {
    constructor() {
        navigator.geolocation.getCurrentPosition(
            this.getPosicion.bind(this),
        );
        document.addEventListener("DOMContentLoaded", () => {
            this.initCarrusel();
        });
    }
    getPosicion(posicion) {
        this.longitud = posicion.coords.longitude;
        this.latitud = posicion.coords.latitude;
    }
    getLongitud() {
        return this.longitud;
    }

    getLatitud() {
        return this.latitud;
    }

    getMapaEstaticoGoogle() {
        let mapaEstatico = document.querySelector("article:nth-of-type(1)");
        if (!mapaEstatico) {
            mapaEstatico = document.createElement("article:nth-of-type(1)");
            ubicacion.appendChild(mapaEstatico);
        }
        const apiKey = "&key=AIzaSyAQz2BOIGZarND4L4WVBfRCqCigjJ2f4PU";
        const url = "https://maps.googleapis.com/maps/api/staticmap?";
        const centro = `center=${this.latitud},${this.longitud}`;
        const zoom = "&zoom=15";
        const tamaño = "&size=250x200";
        const marcador = `&markers=color:red%7Clabel:S%7C${this.latitud},${this.longitud}`;
        const sensor = "&sensor=false";
        this.imagenMapa = url + centro + zoom + tamaño + marcador + sensor + apiKey;
        const contenido = `
            <h3>Mapa Estático</h3>
            <img src="${this.imagenMapa}" alt="Mapa estático de Google">`;
        mapaEstatico.innerHTML = contenido;
    }

    getMapaDinamico() {
        const ubicacion = document.querySelector('section:nth-of-type(2)');;
        if (!ubicacion) {
            console.error("No se encuentra el contenedor para el mapa dinámico.");
            return;
        }
        let mapaDinamico = document.querySelector("section div");
        if (!mapaDinamico) {
            mapaDinamico = document.createElement("div");
            mapaDinamico.innerHTML = `<h3>Mapa Dinámico</h3>`;
            ubicacion.appendChild(mapaDinamico);
        }
        if (this.latitud && this.longitud) {
            this.initMapaDinamico();
        } else {
            setTimeout(() => this.initMapaDinamico(), 1000);
        }
    }

    initMapaDinamico() {
        if (!this.latitud || !this.longitud) {
            console.error("No se han obtenido las coordenadas para el mapa dinámico.");
            return;
        }
        const mapaDinamicoDiv = document.querySelector("section div");
        if (!mapaDinamicoDiv) {
            console.error("No se encuentra el div del mapa dinámico.");
            return;
        }
        var mapa = new google.maps.Map(mapaDinamicoDiv, {
            zoom: 15,
            center: { lat: this.latitud, lng: this.longitud },
            mapTypeId: google.maps.MapTypeId.ROADMAP,
        });
    }
    initCarrusel() {
    const slides = document.querySelectorAll("img");
    const nextSlide = document.querySelector("button:nth-of-type(2)");
    const prevSlide = document.querySelector("button:nth-of-type(1)");

    let curSlide = 0;
    const maxSlide = slides.length - 1;

    nextSlide.addEventListener("click", function () {
        if (curSlide === maxSlide) {
            curSlide = 0;
        } else {
            curSlide++;
        }

        slides.forEach((slide, indx) => {
            var trans = 100 * (indx - curSlide);
            $(slide).css('transform', 'translateX(' + trans + '%)');
        });
    });

    prevSlide.addEventListener("click", function () {
        if (curSlide === 0) {
            curSlide = maxSlide;
        } else {
            curSlide--;
        }

        slides.forEach((slide, indx) => {
            var trans = 100 * (indx - curSlide);
            $(slide).css('transform', 'translateX(' + trans + '%)');
        });
    });
    }
}
const viaje = new Viajes();
