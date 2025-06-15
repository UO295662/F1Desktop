class TestConocimientos {
    constructor() {
        this.preguntas = [
            {
                pregunta: "¿Qué tipos de recursos turísticos maneja la aplicación?",
                opciones: [
                    "Solo museos",
                    "Solo restaurantes",
                    "Museos, rutas, restaurantes, hoteles, monumentos y parques",
                    "Solo actividades al aire libre",
                    "Solo alojamientos"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué información meteorológica puedes consultar en la aplicación?",
                opciones: [
                    "Solo la temperatura actual",
                    "Solo si llueve o no",
                    "Previsión de 7 días, y tiempo actual",
                    "Solo la humedad",
                    "Solo alertas meteorológicas"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué formato de archivo necesitas para cargar rutas en el sistema?",
                opciones: [
                    "Archivos PDF",
                    "Archivos de imagen JPG",
                    "Archivos XML con datos de rutas",
                    "Archivos de texto TXT",
                    "Archivos de video MP4"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Cuál es el plato más típico de la gastronomía asturiana que encontrarías en los restaurantes de la aplicación?",
                opciones: [
                    "Paella valenciana",
                    "Fabada asturiana",
                    "Gazpacho andaluz",
                    "Cocido madrileño",
                    "Pulpo a la gallega"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué información incluye cada ruta cargada en el sistema?",
                opciones: [
                    "Solo el nombre de la ruta",
                    "Solo la distancia total",
                    "Planimetría, altimetría, coordenadas y puntos de interés",
                    "Solo las fotografías",
                    "Solo la dificultad"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Cuándo puedes cancelar una reserva en el sistema?",
                opciones: [
                    "Siempre, sin restricciones",
                    "Solo antes de la fecha de inicio y si no está cancelada",
                    "Solo el mismo día",
                    "Nunca se puede cancelar",
                    "Solo si pagas una penalización"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué datos meteoro​lógicos son más importantes para planificar una ruta de senderismo?",
                opciones: [
                    "Solo la temperatura",
                    "Temperatura, viento y probabilidad de lluvia",
                    "Solo la humedad",
                    "Solo la presión atmosférica",
                    "Solo las horas de sol"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué tipos de recursos turísticos maneja la aplicación?",
                opciones: [
                    "Solo museos",
                    "Solo restaurantes",
                    "Museos, rutas, restaurantes, hoteles, monumentos y parques",
                    "Solo actividades al aire libre",
                    "Solo alojamientos"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Cómo puedes visualizar las rutas en el mapa de la aplicación?",
                opciones: [
                    "Solo como puntos individuales",
                    "Como líneas de colores sobre el mapa interactivo",
                    "Solo como fotografías",
                    "Como texto descriptivo únicamente",
                    "No se pueden visualizar en el mapa"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué bebida tradicional asturiana es más probable encontrar en los establecimientos gastronómicos de la aplicación?",
                opciones: [
                    "Sangría",
                    "Tinto de verano",
                    "Sidra asturiana",
                    "Cerveza alemana",
                    "Vino de Rioja"
                ],
                respuestaCorrecta: 2
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
                    <fieldset>
                        <legend>Selecciona tu respuesta:</legend>
                        ${pregunta.opciones.map((opcion, index) => `
                            <p>
                                <input type="radio" id="respuesta_${index}" name="respuesta" value="${index}">
                                <label for="respuesta_${index}">${opcion}</label>
                            </p>
                        `).join('')}
                    </fieldset>
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
        
        const resultadoContainer=document.querySelector('main section');
        
        let mensaje = '';
        
        if (puntuacion >= 9) {
            mensaje = '¡Excelente! Conoces muy bien el patrimonio y cultura de Oviedo.';
        } else if (puntuacion >= 7) {
            mensaje = '¡Muy bien! Tienes un buen conocimiento sobre Oviedo y Asturias.';
        } else if (puntuacion >= 5) {
            mensaje = 'Bien. Conoces algunos aspectos de Oviedo, pero puedes aprender más.';
        } else {
            mensaje = 'Te recomendamos explorar más sobre la rica historia y cultura de Oviedo.';
        }

        resultadoContainer.innerHTML = `
            <h3>Resultados del Test</h3>
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
                                    Pregunta ${index + 1}: ${esCorrecta ? '✓ Correcta' : '✗ Incorrecta'}
                                    ${!esCorrecta ? `<p>Tu respuesta: ${respuestaUsuario}</p><p>Respuesta correcta: ${respuestaCorrecta}</p>` : ''}
                                </li>
                            `;
                        }).join('')}
                    </ul>
                </section>
                
                <section>
                    <button onclick="location.reload()">Repetir Test</button>
                    <button onclick="window.location.href='index.html'">Volver al Inicio</button>
                </section>
            </article>
        `;
    }
}