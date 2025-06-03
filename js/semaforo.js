class Semaforo {
    constructor() {
        this.levels = [0.2, 0.5, 0.8];
        this.lights = 4;
        this.lightsElements = [];
        this.unload_moment = null;
        this.clic_moment = null;
        this.difficulty = this.randomDifficulty();
        this.createStructure();
    }

    randomDifficulty() {
        const randomIndex = Math.floor(Math.random() * this.levels.length);
        return this.levels[randomIndex];
    }
    
    createStructure() {
        const main = document.querySelector('main');
    
        if (main.querySelector('div')) {
            return; 
        }
    
        const header = document.createElement('h2');
        header.textContent = 'Juego del Semáforo';
        main.appendChild(header);
    
        this.lightsElements = [];
        for (let i = 0; i < this.lights; i++) {
            const light = document.createElement('div');
            main.appendChild(light);
            this.lightsElements.push(light);
        }
    
        this.startButton = document.createElement('button');
        this.startButton.textContent = 'Arrancar Semáforo';
        this.startButton.addEventListener('click', () => {
            this.initSequence();
        });
        main.appendChild(this.startButton);
    
        this.reactionButton = document.createElement('button');
        this.reactionButton.textContent = 'Obtener Tiempo de Reacción';
        this.reactionButton.disabled = true;
        this.reactionButton.addEventListener('click', () => {
            this.stopReaction();
        });
        main.appendChild(this.reactionButton);
    
        this.reactionParagraph = document.createElement('p');
        this.reactionParagraph.textContent = '';
        main.appendChild(this.reactionParagraph);
    } 
    initSequence() {
        this.startButton.disabled = true;
        const main = document.querySelector('main');
        main.classList.add('load');

        let delay = 0

        const totalDelay = 500 * this.lightsElements.length;
        const randomTimeout = this.difficulty * 1000 + totalDelay;
        console.log(randomTimeout);
        setTimeout(() => {
            this.unload_moment = new Date();
            this.endSequence();
        }, randomTimeout);
    }

    endSequence() {
        const main = document.querySelector('main');
            main.classList.remove('load');
            main.classList.remove('unload');
            this.reactionButton.disabled = false;
            this.startButton.disabled = true;
    }

    stopReaction() {
        this.clic_moment = new Date();

        const reactionTime = ((this.clic_moment - this.unload_moment) / 1000).toFixed(3);

        this.reactionParagraph.textContent = `Tiempo de reacción: ${reactionTime} segundos`;

        const main = document.querySelector('main');
        main.classList.remove('load');
        main.classList.remove('unload');
        this.createRecordForm(reactionTime);
        this.reactionButton.disabled = true;
        this.startButton.disabled = false;
    }

    createRecordForm(reactionTime) {
        const existingForm = document.querySelector('form'); 
        if (existingForm) {
            existingForm.remove(); 
        }
        const form = document.createElement('form');
        form.action = "semaforo.php";
        form.method = "POST";

        form.innerHTML = `
        <section>
            <h3>Información Personal</h3>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </section>
        <section>
            <h3>Información Adicional</h3>
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>
        </section>
        <section>
            <h3>Dificultad del Juego</h3>
            <label for="nivel">Dificultad:</label>
            <input type="text" id="nivel" name="nivel" value="${this.difficulty}" readonly>
        </section>
        <section>
            <h3>Resultado</h3>
            <label for="tiempo">Tiempo de Reacción (s):</label>
            <input type="text" id="tiempo" name="tiempo" value="${reactionTime}" readonly>
        </section>
        <input type="submit" value="Enviar">
        `;

        const main = document.querySelector('main');
        main.appendChild(form);
    }
}

const semaforo = new Semaforo();