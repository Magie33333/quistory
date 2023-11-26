<?php
session_start();

include 'KvizModel.php';
include 'connect.php';

/*$controller = new KvizController($conn);
$kvizy = $controller->zobrazKvizy();
include 'KvizVyberView.php';*/

class KvizController {
    private $kvizModel;
    private $pocetSpravnych = 0;
    private $zobrazeneOtazky = [];

    public function __construct($dbConnection) {
        $this->kvizModel = new KvizModel($dbConnection);
        $this->pocetSpravnych = $_SESSION['pocetSpravnych'] ?? 0;
    }

    public function zobrazKvizy() {
        //$kvizy = $this->kvizModel->ziskatKvizy();
        //include 'KvizVyberView.php'; // Zahrnutí souboru pro výběr kvízu
        return $this->kvizModel->ziskatKvizy();
    }

    public function spustKviz($kviz_id) {
        $_SESSION['zacatekKvizu'] = time(); // Uložení současného času jako začátek kvízu
        $_SESSION['casovyLimit'] = 15; // 15 sekund
        $_SESSION['zbývajícíCas'] = 15; // 15 sekund

        $nazevKvizu = $this->kvizModel->ziskatNazevKvizu($kviz_id);

        $otazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $_SESSION['zobrazenéOtázky'] ?? []);


        if ($otazka == null) {
            $this->ukoncitKviz();
            return;
        }

        if ($otazka != null) {
            //$_SESSION['zobrazenéOtázky'][] = $otazka['otazka_id'];
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
                    $_SESSION['pocetSpravnych'] = $this->pocetSpravnych;
                    break;
                }
            }
        }

        // Přidání ID otázky do pole zobrazených otázek v session
        $_SESSION['zobrazenéOtázky'][] = $otazka_id;
        
        $aktualniCas = time();
        $zacatekKvizu = $_SESSION['zacatekKvizu'] ?? $aktualniCas;
        $casovyLimit = $_SESSION['casovyLimit'] ?? 15;
        $uplynulyCas = $aktualniCas - $zacatekKvizu;
        $_SESSION['zbývajícíCas'] = max(0, $_SESSION['zbývajícíCas'] - $uplynulyCas);

        if (($aktualniCas - $zacatekKvizu) > $casovyLimit) {
            // Čas vypršel, ukončení kvízu           
            $this->ukoncitKviz();
            return;
        }

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
        $pocetSpravnych = $_SESSION['pocetSpravnych'] ?? 0;
        $pocetZobrazenych = count($_SESSION['zobrazenéOtázky'] ?? []);

        // Zpráva o ukončení kvízu
        echo "<h1>Kvíz skončil</h1>";
        echo "<p>Máte $pocetSpravnych správných odpovědí z $pocetZobrazenych otázek.</p>";

        // Tlačítko pro návrat na výběr kvízů
        echo "<a href='KvizVyber.php' class='btn'>Zpět na výběr kvízů</a>";

        // Reset session dat pro kvíz
        unset($_SESSION['pocetSpravnych'], $_SESSION['zobrazenéOtázky'], $_SESSION['kviz_id']);
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