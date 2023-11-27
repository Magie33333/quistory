<?php
session_start();

include 'KvizModel.php';
include 'connect.php';

class KvizController {
    private $kvizModel;
    private $pocetSpravnych = 0;

    public function __construct($dbConnection) {
        $this->kvizModel = new KvizModel($dbConnection);
        $this->pocetSpravnych = $_SESSION['pocetSpravnych'] ?? 0;
    }

    public function zobrazKvizy() {
        return $this->kvizModel->ziskatKvizy();
    }

    // Tato metoda bude volána při zahájení kvízu přes AJAX
    public function inicializovatKviz($kviz_id) {
        $_SESSION['zacatekKvizu'] = time();
        $_SESSION['casovyLimit'] = 60; // Nastavte podle potřeby
        $_SESSION['zbývajícíCas'] = $_SESSION['casovyLimit'];
        $_SESSION['kviz_id'] = $kviz_id;
        $_SESSION['pocetSpravnych'] = 0;
        $_SESSION['zobrazenéOtázky'] = [];
    }

    // Tato metoda bude volána při zpracování odpovědí uživatele
    public function zpracujOdpoved($otazka_id, $moznost_id) {
        $jeSpravna = $this->kvizModel->overitOdpoved($otazka_id, $moznost_id);
        if ($jeSpravna) {
            $_SESSION['pocetSpravnych']++;
        }

        $_SESSION['zobrazenéOtázky'][] = $otazka_id;

        // Volání metody pro získání další otázky
        $this->ziskatDalsiOtazkuAjax($_SESSION['kviz_id']);
    }

    // Metoda pro získání další otázky přes AJAX
    public function ziskatDalsiOtazkuAjax($kviz_id) {
        $aktualniCas = time();
        $zacatekKvizu = $_SESSION['zacatekKvizu'];
        $casovyLimit = $_SESSION['casovyLimit'];
        $uplynulyCas = $aktualniCas - $zacatekKvizu;

        if ($uplynulyCas > $casovyLimit) {
            $this->ukoncitKviz();
            return;
        }

        $vylouceneOtazky = $_SESSION['zobrazenéOtázky'];
        $otazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky);

        if ($otazka) {
            $_SESSION['zobrazenéOtázky'][] = $otazka['otazka_id'];
            $odpovedi = $this->kvizModel->ziskatOdpovedi($otazka['otazka_id']);

            echo json_encode([
                'status' => 'success',
                'otazka' => $otazka,
                'odpovedi' => $odpovedi,
                'zbyvajiciCas' => $_SESSION['casovyLimit'] - $uplynulyCas
            ]);
        } else {
            $this->ukoncitKviz();
        }
    }

    // Metoda pro ukončení kvízu
    private function ukoncitKviz() {
        echo json_encode([
            'status' => 'completed',
            'pocetSpravnych' => $_SESSION['pocetSpravnych'],
            'pocetZobrazenych' => count($_SESSION['zobrazenéOtázky']),
            'message' => "Kvíz skončil. Máte " . $_SESSION['pocetSpravnych'] . " správných odpovědí z " . count($_SESSION['zobrazenéOtázky']) . " otázek."
        ]);

        // Reset session dat pro kvíz
        $_SESSION['zacatekKvizu'] = null;
        $_SESSION['casovyLimit'] = null;
        $_SESSION['zbývajícíCas'] = null;
        $_SESSION['kviz_id'] = null;
        $_SESSION['pocetSpravnych'] = null;
        $_SESSION['zobrazenéOtázky'] = null;
    }
}

$controller = new KvizController($conn);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'zahajitKviz':
            if (isset($_GET['kviz_id'])) {
                $controller->inicializovatKviz($_GET['kviz_id']);
            }
            break;
        case 'zpracujOdpoved':
            if (isset($_GET['otazka_id']) && isset($_GET['moznost_id'])) {
                $controller->zpracujOdpoved($_GET['otazka_id'], $_GET['moznost_id']);
            }
            break;
        case 'ziskatDalsiOtazku':
            if (isset($_GET['kviz_id'])) {
                $controller->ziskatDalsiOtazkuAjax($_GET['kviz_id']);
            }
            break;
        // Další případy...
    }
}
?>