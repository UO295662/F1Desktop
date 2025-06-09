class TestConocimientos {
    constructor() {
        this.preguntas = [
            {
                pregunta: "¿Cuál es uno de los monumentos prerrománicos que aparece en el carrusel de imágenes?",
                opciones: [
                    "Santa María del Naranco",
                    "Catedral de Santiago",
                    "Palacio de Valdecarzana",
                    "Teatro Campoamor",
                    "Universidad de Oviedo"
                ],
                respuestaCorrecta: 0
            },
            {
                pregunta: "¿Qué fuente histórica aparece mencionada en las imágenes del sitio?",
                opciones: [
                    "Fuente de Foncalada",
                    "La Foncalada",
                    "Fuente del Naranco",
                    "Fuente de la Catedral",
                    "Fuente de Santullano"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Cuál es el otro nombre de San Julián de los Prados?",
                opciones: [
                    "Santa María",
                    "San Miguel",
                    "Santullano",
                    "San Pedro",
                    "San Salvador"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué información puedes consultar en la sección de Meteorología?",
                opciones: [
                    "Solo la temperatura actual",
                    "Solo la humedad",
                    "Pronóstico para los próximos días con temperatura y lluvia",
                    "Solo la velocidad del viento",
                    "Solo las condiciones de ayer"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué formatos de archivo se pueden cargar en la sección de Rutas?",
                opciones: [
                    "Solo PDF",
                    "KML, SVG y XML",
                    "Solo imágenes JPG",
                    "Solo archivos de texto",
                    "Solo archivos de audio"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué tipo de información incluye la sección de Gastronomía?",
                opciones: [
                    "Solo recetas",
                    "Solo horarios de restaurantes",
                    "Platos típicos, precios y experiencias culinarias",
                    "Solo bebidas típicas",
                    "Solo festivales gastronómicos"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué puedes planificar en la sección de Viajes?",
                opciones: [
                    "Solo vuelos",
                    "Solo hoteles",
                    "Visitas a Oviedo, incluyendo alojamiento y transporte",
                    "Solo actividades deportivas",
                    "Solo excursiones en bicicleta"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Cuál es el propósito principal de la página de Ayuda?",
                opciones: [
                    "Mostrar precios",
                    "Proporcionar guía de usuario y explicar la navegación",
                    "Mostrar el mapa del sitio",
                    "Contactar con soporte técnico",
                    "Descargar manuales"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué tipo de arquitectura caracteriza a los monumentos más importantes de Oviedo?",
                opciones: [
                    "Gótica",
                    "Barroca",
                    "Prerrománica asturiana",
                    "Renacentista",
                    "Modernista"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Cuál es el tema principal del sitio web F1 Desktop?",
                opciones: [
                    "Deportes en general",
                    "Turismo y cultura de Oviedo",
                    "Gastronomía española",
                    "Historia universal",
                    "Tecnología web"
                ],
                respuestaCorrecta: 1
            }
        ];
        
        this.respuestasUsuario = [];
        this.preguntaActual = 0;
    }

    iniciarTest() {
        this.mostrarPregunta();
    }

    mostrarPregunta() {
        const container = document.querySelector('main section');
        
        if (this.preguntaActual >= this.preguntas.length) {
            this.mostrarResultados();
            return;
        }

        const pregunta = this.preguntas[this.preguntaActual];
        
        container.innerHTML = `
            <h3>Preguntas del Test</h3>
            <article>
                <h3>Pregunta ${this.preguntaActual + 1} de ${this.preguntas.length}</h3>
                <p>${pregunta.pregunta}</p>
                <form>
                    ${pregunta.opciones.map((opcion, index) => `
                        <fieldset>
                            <input type="radio" name="respuesta" value="${index}" id="opcion_${index}">
                            <label for="opcion_${index}">${opcion}</label>
                        </fieldset>
                    `).join('')}
                    <button type="submit" disabled>
                        ${this.preguntaActual === this.preguntas.length - 1 ? 'Finalizar Test' : 'Siguiente Pregunta'}
                    </button>
                </form>
            </article>
        `;

        // Habilitar botón cuando se seleccione una respuesta
        const radios = container.querySelectorAll('input[type="radio"]');
        const siguienteBtn = container.querySelector('button[type="submit"]');
        
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                siguienteBtn.disabled = false;
            });
        });

        // Manejar envío del formulario
        const form = container.querySelector('form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.procesarRespuesta();
        });
    }

    procesarRespuesta() {
        const seleccionada = document.querySelector('input[name="respuesta"]:checked');
        
        if (!seleccionada) {
            alert('Por favor, selecciona una respuesta antes de continuar.');
            return;
        }

        const respuestaUsuario = parseInt(seleccionada.value);
        this.respuestasUsuario.push(respuestaUsuario);
        
        this.preguntaActual++;
        this.mostrarPregunta();
    }

    calcularPuntuacion() {
        let aciertos = 0;
        
        for (let i = 0; i < this.preguntas.length; i++) {
            if (this.respuestasUsuario[i] === this.preguntas[i].respuestaCorrecta) {
                aciertos++;
            }
        }
        
        return aciertos;
    }

    mostrarResultados() {
        const aciertos = this.calcularPuntuacion();
        const puntuacion = aciertos;
        
        document.querySelector('main section').style.display = 'none';
        const resultadoContainer = document.querySelector('main aside');
        resultadoContainer.style.display = 'block';
        
        let mensaje = '';
        
        if (puntuacion >= 9) {
            mensaje = '¡Excelente! Conoces muy bien el contenido del sitio.';
        } else if (puntuacion >= 7) {
            mensaje = '¡Muy bien! Tienes un buen conocimiento del sitio.';
        } else if (puntuacion >= 5) {
            mensaje = 'Bien. Conoces algunos aspectos del sitio.';
        } else {
            mensaje = 'Puedes mejorar. Te recomendamos explorar más el sitio web.';
        }

        resultadoContainer.innerHTML = `
            <article>
                <h3>¡Test Completado!</h3>
                <section>
                    <h3>Tu puntuación: ${puntuacion}/10</h3>
                    <p>(${(puntuacion * 10)}%)</p>
                </section>
                <p>${mensaje}</p>
                
                <section>
                    <h4>Detalle de respuestas:</h4>
                    <ul>
                        ${this.preguntas.map((pregunta, index) => {
                            const esCorrecta = this.respuestasUsuario[index] === pregunta.respuestaCorrecta;
                            const respuestaUsuario = pregunta.opciones[this.respuestasUsuario[index]];
                            const respuestaCorrecta = pregunta.opciones[pregunta.respuestaCorrecta];
                            
                            return `
                                <li>
                                    <strong>Pregunta ${index + 1}:</strong> ${esCorrecta ? '✓ Correcta' : '✗ Incorrecta'}
                                    ${!esCorrecta ? `<small>Tu respuesta: ${respuestaUsuario} - Respuesta correcta: ${respuestaCorrecta}</small>` : ''}
                                </li>
                            `;
                        }).join('')}
                    </ul>
                </section>
                
                <button onclick="location.reload()">Repetir Test</button>
                <button onclick="window.location.href='index.html'">Volver al Inicio</button>
            </article>
        `;
    }
}