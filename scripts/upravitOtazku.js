class UpravitOtazku {
    constructor() {
        this.init();
    }

    init() {
        const selectOtazka = document.getElementById('otazka_id');
        if (selectOtazka && selectOtazka.value) {
            this.nacistDataOtazky(selectOtazka.value);
        }

        selectOtazka.addEventListener('change', (e) => this.nacistDataOtazky(e.target.value));
    }

    nacistDataOtazky(otazkaId) {
        fetch('../controller/KvizController.php?action=ziskatDetailOtazky&otazka_id=' + otazkaId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('otazka_text').value = data.otazka.otazka_text;
                    document.getElementById('kviz_id').value = data.otazka.kviz_id;
                    document.getElementById('hidden_otazka_id').value = otazkaId;

                    let moznostiHtml = '';
                    data.moznosti.forEach((moznost, index) => {
                        moznostiHtml += `<input type="radio" name="spravna_odpoved" value="${moznost.moznost_id}" ${moznost.je_spravna ? 'checked' : ''}>` +
                                        `<input type="text" name="moznosti[${index}][text]" value="${moznost.moznost_text}">` +
                                        `<input type="hidden" name="moznosti[${index}][id]" value="${moznost.moznost_id}"><br>`;
                    });
                    document.getElementById('moznosti').innerHTML = moznostiHtml;
                } else {
                    alert('Data otázky se nepodařilo načíst.');
                }
            })
            .catch(error => {
                console.error('Chyba při načítání dat otázky:', error);
            });
    }
}

document.addEventListener('DOMContentLoaded', () => new UpravitOtazku());

