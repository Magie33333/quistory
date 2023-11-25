<?php
include 'KvizModel.php';
include 'connect.php';

class KvizController {
    private $kvizModel;
    private $pocetSpravnych = 0;
    private $zobrazeneOtazky = [];
    private $pocetOtazekVKvizu = 5;

    public function __construct($dbConnection) {
        $this->kvizModel = new KvizModel($dbConnection);
    }

    public function zobrazKvizy() {
        $kvizy = $this->kvizModel->ziskatKvizy();
        //include 'KvizVyberView.php'; // Zahrnutí souboru pro výběr kvízu
        return $kvizy;
    }

    public function spustKviz($kviz_id) {
        if (count($this->zobrazeneOtazky) >= $this->pocetOtazekVKvizu) {
            $this->ukoncitKviz();
            return;
        }

        $otazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $this->zobrazeneOtazky);
        if ($otazka != null) {
            $this->zobrazeneOtazky[] = $otazka['otazka_id'];
            $odpovedi = $this->kvizModel->ziskatOdpovedi($otazka['otazka_id']);
            shuffle($odpovedi);
            include 'KvizProbihaView.php';
        }
    }

    // Další potřebné metody...

    public function zpracujOdpoved($otazka_id, $moznost_id, $kviz_id) {
        $odpoved = $this->kvizModel->ziskatOdpovedi($otazka_id);
        foreach ($odpoved as $moznost) {
            if ($moznost['moznost_id'] == $moznost_id && $moznost['je_spravna']) {
                $this->pocetSpravnych++;
                break;
            }
        }
        $this->spustKviz($kviz_id);
    }

    private function ukoncitKviz() {
        echo "Kvíz skončil. Máte $this->pocetSpravnych správných odpovědí z $this->pocetOtazekVKvizu otázek.";
    }
    
}

if (isset($_POST['action']) && $_POST['action'] === 'zpracujOdpoved') {
    $otazka_id = $_POST['otazka_id'];
    $moznost_id = $_POST['moznost_id'];
    $kviz_id = $_POST['kviz_id'];

    //$controller = new KvizController($dbConnection); // Předpokládáme, že máte připojení k DB
    $controller->zpracujOdpoved($otazka_id, $moznost_id, $kviz_id);
}
?>