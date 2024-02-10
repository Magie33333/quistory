class KvizEditor {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        const kvizSelect = document.getElementById('kviz_id');
        if (kvizSelect) {
            kvizSelect.addEventListener('change', (event) => this.nacistDetailKvizu(event.target.value));
        }

        const smazatBtn = document.getElementById('smazatKvizBtn');
        if (smazatBtn) {
            smazatBtn.addEventListener('click', () => this.smazatKviz());
        }
    }

    nacistDetailKvizu(kvizId) {
        fetch('../controller/KvizController.php?action=ziskatDetailKvizu&kviz_id=' + kvizId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('nazev').value = data.kviz.nazev;
                    document.getElementById('popis').value = data.kviz.popis;
                } else {
                    alert('Data kvízu se nepodařilo načíst.');
                }
            })
            .catch(error => {
                console.error('Chyba při načítání dat kvízu:', error);
            });
    }

    smazatKviz() {
        var kvizId = document.getElementById('kviz_id').value;
        if(confirm('Opravdu chcete smazat tento kvíz?')) {
            fetch('../controller/KvizController.php?action=smazatKviz&kviz_id=' + kvizId, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Kvíz byl úspěšně smazán.');
                    window.location.reload(); // Reload stránky nebo přesměrování
                } else {
                    alert('Nepodařilo se smazat kvíz: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Chyba při mazání kvízu:', error);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new KvizEditor();
});
