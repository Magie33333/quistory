class Kviz {
    constructor(kvizId) {
        this.kvizId = kvizId;
        this.zbyvajiciCas = 60; // Prednastavena hodnota
        this.odpocetIntervalu = null;
        this.init();
    }

    init() {
        if (!this.kvizId) {
            alert('ID kvízu není zadáno.');
            window.location.href = './KvizVyber.php';
            return;
        }

        this.nacistNazevKvizu();
        this.nacistPrvniOtazku();
        this.setupFormularListener();
        this.odpocetCasu();
    }

    nacistNazevKvizu() {
        fetch('../controller/KvizController.php?action=ziskatNazevKvizu&kviz_id=' + this.kvizId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('nazevKvizu').textContent = data.nazev;
            } else {
                console.error('Chyba při načítání názvu kvízu: ', data.message);
            }
        })
        .catch(error => {
            console.error('Chyba při načítání názvu kvízu: ', error);
        });
    }

    nacistPrvniOtazku() {
        fetch('../controller/KvizController.php?action=zahajitKviz&kviz_id=' + this.kvizId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                this.aktualizovatOtazkuAOdpovedi(data.otazka, data.odpovedi);
                this.zbyvajiciCas = data.zbyvajiciCas;
                this.odpocetCasu(true); // Restart odpočtu s aktualizovaným časem
            } else {
                this.ukoncitKviz(data.message);
            }
        })
        .catch(error => {
            console.error('Chyba při načítání první otázky: ', error);
        });
    }   

    odpocetCasu(prvniSpusteni = false) {
        if (prvniSpusteni && this.odpocetIntervalu) {
            clearInterval(this.odpocetIntervalu);
        }
    
        const pocatecniCas = this.zbyvajiciCas;
        const casovacKruh = document.getElementById('casovacKruh');
        const obvod = 2 * Math.PI * casovacKruh.getAttribute('r');
    
        casovacKruh.style.strokeDasharray = `${obvod}`;
        casovacKruh.style.strokeDashoffset = '0';
    
        this.odpocetIntervalu = setInterval(() => {
            if (this.zbyvajiciCas <= 0) {
                clearInterval(this.odpocetIntervalu);
                this.ukoncitKvizCas();
            } else {
                const procentoUplneho = (pocatecniCas - this.zbyvajiciCas) / pocatecniCas;
                
                casovacKruh.style.strokeDashoffset = -(obvod * procentoUplneho);
    
                document.getElementById('zbyvajiciCas').textContent = this.zbyvajiciCas;
                this.zbyvajiciCas--;
            }
        }, 1000);
    }

    setupFormularListener() {
        const kvizFormular = document.getElementById('kvizFormular');
        kvizFormular.onsubmit = (event) => {
            event.preventDefault();
            this.odeslatOdpoved();
        };
    }

    odeslatOdpoved() {
        const otazkaId = document.querySelector('input[name="otazka_id"]').value;
        const moznostId = document.querySelector('input[name="moznost_id"]:checked').value;
    
        fetch('../controller/KvizController.php?action=zpracujOdpoved&otazka_id=' + otazkaId + '&moznost_id=' + moznostId + '&kviz_id=' + this.kvizId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                this.aktualizovatOtazkuAOdpovedi(data.otazka, data.odpovedi);
                this.zbyvajiciCas = data.zbyvajiciCas; // Aktualizuj čas, pokud je to potřeba
            } else if (data.status === 'expired' || data.status === 'completed') {
                this.ukoncitKviz(data.message);
            }
        })
        .catch(error => {
            console.error('Chyba při zpracování odpovědi: ', error);
        });
    }

    aktualizovatOtazkuAOdpovedi(otazka, odpovedi) {
        const otazkaTextElem = document.getElementById('otazkaText');
        otazkaTextElem.textContent = otazka.otazka_text;
    
        const odpovediElem = document.getElementById('odpovedi');
        odpovediElem.innerHTML = ''; // Vyčisti předchozí odpovědi
    
        odpovedi.forEach(odpoved => {
            const label = document.createElement('label');
            label.className = "odpoved-container";
    
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'moznost_id';
            input.value = odpoved.moznost_id;
    
            const span = document.createElement('span');
            span.textContent = odpoved.moznost_text;
            span.className = "odpoved-text";
    
            label.appendChild(input);
            label.appendChild(span);
            odpovediElem.appendChild(label);
        });
    
        // Aktualizuj skryté pole s ID otázky pro další odeslání
        document.querySelector('input[name="otazka_id"]').value = otazka.otazka_id;
        document.querySelector('input[type="submit"]').disabled = false; // Povol odeslání
    }

    ukoncitKvizCas() {
        fetch('../controller/KvizController.php?action=ukoncitKviz&kviz_id=' + this.kvizId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                alert(data.message); // Informuj uživatele o ukončení kvízu
                window.location.href = './KvizVyber.php'; // Přesměruj na výběr kvízu
            }
        })
        .catch(error => {
            console.error('Chyba při ukončování kvízu: ', error);
        });
    }

    ukoncitKviz(message) {
        alert(message); // Zobraz zprávu
        window.location.href = './KvizVyber.php'; // Přesměruj na výběr kvízu
    }
        
}

document.addEventListener('DOMContentLoaded', function() {
    const kvizId = new URLSearchParams(window.location.search).get('kviz_id');
    new Kviz(kvizId);
});
