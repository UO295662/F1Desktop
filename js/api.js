class F1App {
    constructor() {
        this.apiUrl = 'https://ergast.com/api/f1/current/drivers.json';  
        this.pilotSelector = document.querySelector('select');  
        this.resultChartCanvas = document.querySelector('canvas'); 
        this.fullscreenButtons = document.querySelectorAll('button');
        this.message = "La página está minimizada"; 
        this.init();
    }

    init() {
        this.loadPilots();

        this.pilotSelector.addEventListener('change', (e) => {
            this.loadResults(e.target.value);
        });

        document.addEventListener("visibilitychange", () => {
            this.handleVisibilityChange();
        });
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.loadPilots();
            const firstPilot = this.pilotSelector.options[0]?.value; 
            if (firstPilot) {
                this.loadResults(firstPilot);
            }
        }
    }

    loadPilots() {
        fetch(this.apiUrl)
            .then(response => response.json())
            .then(data => {
                const pilotos = data.MRData.DriverTable.Drivers;
                this.pilotSelector.innerHTML = ''; 

                pilotos.forEach(piloto => {
                    const option = document.createElement('option');
                    option.value = piloto.driverId;
                    option.textContent = `${piloto.givenName} ${piloto.familyName}`;
                    this.pilotSelector.appendChild(option);
                });

                if (!document.hidden) {
                    const initialPilot = pilotos[0].driverId;
                    this.loadResults(initialPilot); 
                }
            });
    }

    loadResults(driverId) {
        const resultsUrl = `https://ergast.com/api/f1/current/drivers/${driverId}/results.json`;
        const storageKey = `f1Results_${driverId}`;

        const storedResults = localStorage.getItem(storageKey);

        if (storedResults) {
            const data = JSON.parse(storedResults);
            this.processResults(data);
        } else {
            fetch(resultsUrl)
                .then(response => response.json())
                .then(data => {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                    this.processResults(data);
                });
        }
    }

    processResults(data) {
        const results = data.MRData.RaceTable.Races;
        const carreras = results.map(race => race.raceName.replace(" Grand Prix", ""));
        const posiciones = results.map(race => parseInt(race.Results[0].positionText));

        this.drawChart(carreras, posiciones);
    }

    drawChart(carreras, posiciones) {
        const canvas = document.querySelector('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = canvas.offsetWidth;  
        canvas.height = canvas.offsetHeight; 

        const barWidth = canvas.width / (carreras.length * 1.8); 
        const gap = barWidth * 0.8; 
        const topMargin = canvas.height * 0.1; 
        const chartHeight = canvas.height;
        const chartWidth = canvas.width;

        ctx.clearRect(0, 0, chartWidth, chartHeight);

        for (let i = 0; i < carreras.length; i++) {
            const x = (i * (barWidth + gap)) + gap;
            const height = (posiciones[i] / 10) * chartHeight * 0.6; 
            const y = chartHeight - height - topMargin; 

            ctx.fillStyle = '#1f3541';
            ctx.fillRect(x, y, barWidth, height);

            ctx.fillStyle = 'white';
            ctx.font = `${barWidth * 0.4}px Arial`;
            const positionText = posiciones[i];
            const textWidth = ctx.measureText(positionText).width;

            const textX = x + (barWidth / 2) - (textWidth / 2);
            const textY = y + height / 2 + barWidth * 0.2;

            ctx.fillText(positionText, textX, textY);
        }

        ctx.fillStyle = '#1f3541';
        ctx.font = `${barWidth * 0.3}px Arial`; 
        for (let i = 0; i < carreras.length; i++) {
            const x = (i * (barWidth + gap)) + gap;
            const labelWidth = ctx.measureText(carreras[i]).width;

            const labelX = x + (barWidth / 2) - (labelWidth / 2);
            const labelY = chartHeight - topMargin / 4;

            ctx.fillText(carreras[i], labelX, labelY);
        }
    }
}
document.addEventListener('DOMContentLoaded', () => {
    new F1App();
});