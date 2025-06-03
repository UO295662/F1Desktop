class Memoria {
    constructor() {
        this.hasFlippedCard = false;
        this.lockBoard = false;
        this.firstCard = null;
        this.secondCard = null;

        this.elements = [
            { element: "RedBull", source: "https://upload.wikimedia.org/wikipedia/de/c/c4/Red_Bull_Racing_logo.svg" },
            { element: "RedBull", source: "https://upload.wikimedia.org/wikipedia/de/c/c4/Red_Bull_Racing_logo.svg" },
            { element: "McLaren", source: "https://upload.wikimedia.org/wikipedia/en/6/66/McLaren_Racing_logo.svg" },
            { element: "McLaren", source: "https://upload.wikimedia.org/wikipedia/en/6/66/McLaren_Racing_logo.svg" },
            { element: "Alpine", source: "https://upload.wikimedia.org/wikipedia/fr/b/b7/Alpine_F1_Team_2021_Logo.svg" },
            { element: "Alpine", source: "https://upload.wikimedia.org/wikipedia/fr/b/b7/Alpine_F1_Team_2021_Logo.svg" },
            { element: "AstonMartin", source: "https://upload.wikimedia.org/wikipedia/fr/7/72/Aston_Martin_Aramco_Cognizant_F1.svg" },
            { element: "AstonMartin", source: "https://upload.wikimedia.org/wikipedia/fr/7/72/Aston_Martin_Aramco_Cognizant_F1.svg" },
            { element: "Ferrari", source: "https://upload.wikimedia.org/wikipedia/de/c/c0/Scuderia_Ferrari_Logo.svg" },
            { element: "Ferrari", source: "https://upload.wikimedia.org/wikipedia/de/c/c0/Scuderia_Ferrari_Logo.svg" },
            { element: "Mercedes", source: "https://upload.wikimedia.org/wikipedia/commons/f/fb/Mercedes_AMG_Petronas_F1_Logo.svg" },
            { element: "Mercedes", source: "https://upload.wikimedia.org/wikipedia/commons/f/fb/Mercedes_AMG_Petronas_F1_Logo.svg" },
        ];

        this.shuffleElements();
        this.createElements();
        this.addEventListeners();
    }

    addEventListeners() {
        const allCards = document.querySelectorAll('article');
        allCards.forEach(card => {
            card.addEventListener('click', this.flipCard.bind(this, card));
        });
    }

    createElements() {
        const main = document.querySelector('main');
        if (!main) {
            console.error('Main element is missing in the DOM');
            return;
        }
    
        let cardsContainer = document.querySelector('main section');
        if (!cardsContainer) {
            cardsContainer = document.createElement('section');
            main.appendChild(cardsContainer);
        } else {
            cardsContainer.innerHTML = ''; 
        }
    
        let heading = cardsContainer.querySelector('h2');
        if (!heading) {
            heading = document.createElement('h2');
            heading.innerHTML = "Juego de Memoria";
            cardsContainer.prepend(heading); 
        }
    
        this.elements.forEach(card => {
            const article = document.createElement('article');
            article.dataset.element = card.element;
    
            const h3 = document.createElement('h3');
            h3.innerText = 'Tarjeta de memoria';
            article.appendChild(h3);
    
            const img = document.createElement('img');
            img.src = card.source;
            img.alt = card.element;
            article.appendChild(img);
    
            cardsContainer.appendChild(article);
        });
    }

    flipCard(card) {
        if (this.lockBoard || card === this.firstCard || card.dataset.state === 'revealed') {
            return;
        }
        card.dataset.state = 'flip';
        if (!this.hasFlippedCard) {
            this.hasFlippedCard = true;
            this.firstCard = card;
        } else {
            this.secondCard = card;
            this.lockBoard = true;
            this.checkForMatch();
        }
    }

    checkForMatch() {
        const isMatch = this.firstCard.dataset.element === this.secondCard.dataset.element;
        isMatch ? this.disableCards() : this.unflipCards();
    }

    disableCards() {
        setTimeout(() => {
            this.firstCard.dataset.state = 'revealed';
            this.secondCard.dataset.state = 'revealed';
            this.resetBoard();
        }, 1000);
    }

    unflipCards() {
        setTimeout(() => {
            this.firstCard.dataset.state = '';
            this.secondCard.dataset.state = '';
            this.resetBoard();
        }, 1000);
    }

    resetBoard() {
        this.hasFlippedCard = false;
        this.lockBoard = false;
        this.firstCard = null;
        this.secondCard = null;
    }

    shuffleElements() {
        for (let i = this.elements.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [this.elements[i], this.elements[j]] = [this.elements[j], this.elements[i]];
        }
    }
}

const juegoMemoria = new Memoria();