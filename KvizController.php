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
    }

    public function ziskatDalsiOtazkuAjax($kviz_id) {
        session_start();
        $aktualniCas = time();
        $zacatekKvizu = $_SESSION['zacatekKvizu'] ?? $aktualniCas;
        $casovyLimit = $_SESSION['casovyLimit'] ?? 60; // Nastavení limitu (např. 60 sekund)
        $uplynulyCas = $aktualniCas - $zacatekKvizu;

        // Kontrola, zda nevypršel čas
        if ($uplynulyCas > $casovyLimit) {
            echo json_encode(['status' => 'expired']);
            exit;
        }

        // Získání náhodné otázky, která ještě nebyla zobrazena
        $vylouceneOtazky = $_SESSION['zobrazenéOtázky'] ?? [];
        $otazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky);

        if ($otazka == null) {
            // Všechny otázky byly již zobrazeny nebo došlo k chybě
            echo json_encode(['status' => 'completed']);
            exit;
        }

        // Přidání ID otázky do seznamu zobrazených otázek
        $_SESSION['zobrazenéOtázky'][] = $otazka['otazka_id'];

        // Získání odpovědí pro otázku
        $odpovedi = $this->kvizModel->ziskatOdpovedi($otazka['otazka_id']);

        // Vytvoření pole pro JSON odpověď
        $response = [
            'status' => 'success',
            'otazka' => $otazka,
            'odpovedi' => $odpovedi,
            'zbyvajiciCas' => max(0, $casovyLimit - $uplynulyCas)
        ];

        // Odeslání odpovědi ve formátu JSON
        echo json_encode($response);
        exit;
    }
    
    private function ukoncitKviz() {
        $pocetSpravnych = $_SESSION['pocetSpravnych'] ?? 0;
        $pocetZobrazenych = count($_SESSION['zobrazenéOtázky'] ?? []);
    
        // Příprava dat pro odpověď
        $response = [
            'status' => 'completed',
            'pocetSpravnych' => $pocetSpravnych,
            'pocetZobrazenych' => $pocetZobrazenych,
            'message' => "Kvíz skončil. Máte $pocetSpravnych správných odpovědí z $pocetZobrazenych otázek."
        ];
    
        // Odeslání odpovědi ve formátu JSON
        echo json_encode($response);
    
        // Reset session dat pro kvíz
        unset($_SESSION['pocetSpravnych'], $_SESSION['zobrazenéOtázky'], $_SESSION['kviz_id']);
    }
}

$controller = new KvizController($conn);

if (isset($_POST['action']) && $_POST['action'] === 'zpracujOdpoved') {
    $otazka_id = $_POST['otazka_id'];
    $moznost_id = $_POST['moznost_id'];
    $kviz_id = $_POST['kviz_id'];

    $controller->zpracujOdpoved($otazka_id, $moznost_id, $kviz_id);
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_GET['action']) && $_GET['action'] === 'ziskatDalsiOtazku') {
        $kviz_id = $_GET['kviz_id'];
        $controller->ziskatDalsiOtazkuAjax($kviz_id);
    }
}

?>