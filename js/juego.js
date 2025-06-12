class TestConocimientos {
    constructor() {
        this.preguntas = [
            {
                pregunta: "¿Cuál es uno de los monumentos prerrománicos más importantes de Oviedo?",
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
                pregunta: "¿Cómo se conoce también a la iglesia de San Julián de los Prados?",
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
                pregunta: "¿Qué monumento histórico único se encuentra en Oviedo relacionado con el agua?",
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
                pregunta: "¿Cuál es el plato más representativo de la gastronomía asturiana mencionado en el sitio?",
                opciones: [
                    "Paella",
                    "Cocido",
                    "Fabada asturiana",
                    "Gazpacho",
                    "Tortilla española"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué dulce típico asturiano aparece en la información gastronómica?",
                opciones: [
                    "Turrón",
                    "Carbayones",
                    "Polvorones",
                    "Mantecadas",
                    "Rosquillas"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué bebida tradicional asturiana se menciona en el contenido del sitio?",
                opciones: [
                    "Vino tinto",
                    "Cerveza",
                    "Sidra",
                    "Sangría",
                    "Horchata"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué tipo de arquitectura caracteriza los monumentos más destacados de Oviedo?",
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
                pregunta: "¿En qué zona geográfica se sitúa Oviedo según la información del sitio?",
                opciones: [
                    "Galicia",
                    "Cantabria",
                    "Asturias",
                    "León",
                    "Castilla"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué información meteorológica puedes consultar en el sitio?",
                opciones: [
                    "Solo temperatura actual",
                    "Pronóstico para los próximos días",
                    "Solo humedad",
                    "Solo viento",
                    "Solo precipitaciones"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Cuál es el tema principal del sitio web sobre Oviedo?",
                opciones: [
                    "Historia medieval",
                    "Turismo y patrimonio cultural",
                    "Gastronomía exclusivamente",
                    "Deportes locales",
                    "Comercio y negocios"
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
                    <fieldset>
                        <legend>Selecciona tu respuesta:</legend>
                        ${pregunta.opciones.map((opcion, index) => `
                            <p>
                                <input type="radio" name="respuesta" value="${index}">
                                <label>${opcion}</label>
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
        
        document.querySelector('main section').style.display = 'none';
        const resultadoContainer = document.querySelector('main section:last-of-type');
        resultadoContainer.style.display = 'block';
        
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