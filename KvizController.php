<?php
include 'KvizModel.php';
include 'connect.php';

/*$controller = new KvizController($conn);
$kvizy = $controller->zobrazKvizy();
include 'KvizVyberView.php';*/

class KvizController {
    private $kvizModel;
    private $pocetSpravnych = 0;
    private $zobrazeneOtazky = [];
    private $pocetOtazekVKvizu = 2;

    public function __construct($dbConnection) {
        $this->kvizModel = new KvizModel($dbConnection);
    }

    public function zobrazKvizy() {
        //$kvizy = $this->kvizModel->ziskatKvizy();
        //include 'KvizVyberView.php'; // Zahrnutí souboru pro výběr kvízu
        return $this->kvizModel->ziskatKvizy();
    }

    public function spustKviz($kviz_id) {
        if (count($this->zobrazeneOtazky) >= $this->pocetOtazekVKvizu) {
            $this->ukoncitKviz();
            return;
        }

        $nazevKvizu = $this->kvizModel->ziskatNazevKvizu($kviz_id);

        $otazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $this->zobrazeneOtazky);
        if ($otazka != null) {
            $this->zobrazeneOtazky[] = $otazka['otazka_id'];
            $odpovedi = $this->kvizModel->ziskatOdpovedi($otazka['otazka_id']);
            shuffle($odpovedi);
            include 'KvizProbihaView.php';
        }
        
    }

    // Další potřebné metody...

    /*public function zpracujOdpoved($otazka_id, $moznost_id, $kviz_id) {
        $odpoved = $this->kvizModel->ziskatOdpovedi($otazka_id);
        foreach ($odpoved as $moznost) {
            if ($moznost['moznost_id'] == $moznost_id && $moznost['je_spravna']) {
                $this->pocetSpravnych++;
                break;
            }
        }
        $this->spustKviz($kviz_id);
    }*/

    public function zpracujOdpoved($otazka_id, $moznost_id, $kviz_id) {
        // Získá všechny odpovědi pro danou otázku
        $odpovedi = $this->kvizModel->ziskatOdpovedi($otazka_id);

        // Zkontroluje, zda je vybraná možnost správná
        foreach ($odpovedi as $odpoved) {
            if ($odpoved['moznost_id'] == $moznost_id) {
                if ($odpoved['je_spravna'] == 1) {
                    $this->pocetSpravnych++;
                    break;
                }
            }
        }

        // Uložení počtu správných odpovědí do session
        $_SESSION['pocetSpravnych'] = $this->pocetSpravnych;

        // Přidání ID otázky do pole zobrazených otázek v session
        $_SESSION['zobrazenéOtázky'][] = $otazka_id;

        // Přesměrování na další otázku
        $this->presunNaDalsiOtazku($kviz_id);
    }

    private function presunNaDalsiOtazku($kviz_id) {
        // Zde můžete přidat logiku pro určení ID další otázky
        // a přesměrovat uživatele, nebo zahrnout pohled pro další otázku
        // Příklad přesměrování:
        header("Location: KvizProbiha.php?kviz_id=" . $kviz_id);
        exit;
    }

    private function ukoncitKviz() {
        echo "Kvíz skončil. Máte $this->pocetSpravnych správných odpovědí z $this->pocetOtazekVKvizu otázek.";
    }
    
}

if (isset($_POST['action']) && $_POST['action'] === 'zpracujOdpoved') {
    $otazka_id = $_POST['otazka_id'];
    $moznost_id = $_POST['moznost_id'];
    $kviz_id = $_POST['kviz_id'];

    $controller = new KvizController($conn); // Předpokládáme, že máte připojení k DB
    $controller->zpracujOdpoved($otazka_id, $moznost_id, $kviz_id);
}
?>