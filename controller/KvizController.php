<?php
session_start();

include '../model/KvizModel.php';
include '../conf/connect.php';

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
        $_SESSION['casovyLimit'] = 60; 
        $_SESSION['zbývajícíCas'] = $_SESSION['casovyLimit'];
        $_SESSION['kviz_id'] = $kviz_id;
        $_SESSION['pocetSpravnych'] = 0;
        $_SESSION['zobrazenéOtázky'] = [];
    
        $prvniOtazka = $this->kvizModel->ziskatNahodnouOtazku($kviz_id, $_SESSION['zobrazenéOtázky']);
        if ($prvniOtazka) {
            $_SESSION['zobrazenéOtázky'][] = $prvniOtazka['otazka_id'];
            $odpovedi = $this->kvizModel->ziskatOdpovedi($prvniOtazka['otazka_id']);
    
            echo json_encode([
                'status' => 'success',
                'otazka' => $prvniOtazka,
                'odpovedi' => $odpovedi,
                'zbyvajiciCas' => $_SESSION['casovyLimit']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Žádné otázky nebyly nalezeny.']);
        }
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

    public function ziskatNazevKvizuAjax($kviz_id) {
        header('Content-Type: application/json');
        $nazevKvizu = $this->kvizModel->ziskatNazevKvizu($kviz_id);
    
        if ($nazevKvizu) {
            echo json_encode(['status' => 'success', 'nazev' => $nazevKvizu]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kvíz nenalezen']);
        }
    }
    

// Metoda pro získání další otázky přes AJAX
public function ziskatDalsiOtazkuAjax($kviz_id) {
    header('Content-Type: application/json');
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
        // Pokud není nalezena další otázka, označíme kvíz za dokončený
        $this->ukoncitKviz();
    }
}


    // Metoda pro ukončení kvízu
private function ukoncitKviz() {
    header('Content-Type: application/json');

    // Odstranění duplicitních hodnot z pole
    $unikatniOtazky = array_unique($_SESSION['zobrazenéOtázky']);
    $pocetUnikatnichOtazek = count($unikatniOtazky);

    echo json_encode([
        'status' => 'completed',
        'pocetSpravnych' => $_SESSION['pocetSpravnych'],
        'pocetZobrazenych' => $pocetUnikatnichOtazek,
        'message' => "Kvíz skončil. Máte " . $_SESSION['pocetSpravnych'] . " správných odpovědí z " . $pocetUnikatnichOtazek . " otázek."
    ]);

    // Reset session dat pro kvíz
    $_SESSION['zacatekKvizu'] = null;
    $_SESSION['casovyLimit'] = null;
    $_SESSION['zbývajícíCas'] = null;
    $_SESSION['kviz_id'] = null;
    $_SESSION['pocetSpravnych'] = null;
    $_SESSION['zobrazenéOtázky'] = [];
}

public function vytvoritKviz() {
    $nazev = $_POST['nazev'];
    $popis = $_POST['popis'];
    echo $nazev;
    echo $popis;
    // Volání metody modelu pro vytvoření kvízu
    $this->kvizModel->vytvoritKviz($nazev, $popis);
}

public function pridatOtazku() {
    $kviz_id = $_POST['kviz_id'];
    $otazka_text = $_POST['otazka_text'];
    $moznosti = [
        ['text' => $_POST['moznost1'], 'je_spravna' => $_POST['spravna'] == 1],
        ['text' => $_POST['moznost2'], 'je_spravna' => $_POST['spravna'] == 2],
        ['text' => $_POST['moznost3'], 'je_spravna' => $_POST['spravna'] == 3],
        ['text' => $_POST['moznost4'], 'je_spravna' => $_POST['spravna'] == 4],
    ];

    // Volání metody modelu pro přidání otázky do kvízu
    $this->kvizModel->pridatOtazku($kviz_id, $otazka_text, $moznosti);
}

public function upravitKviz() {
    $kviz_id = $_POST['kviz_id'];
    $nazev = $_POST['nazev'];
    $popis = $_POST['popis'];

    // Volání metody modelu pro úpravu kvízu
    $this->kvizModel->upravitKviz($kviz_id, $nazev, $popis);
}

public function ziskatDetailKvizuAjax($kviz_id) {
    header('Content-Type: application/json');
    $kviz = $this->kvizModel->ziskatKvizPodleId($kviz_id);

    if ($kviz) {
        echo json_encode(['status' => 'success', 'kviz' => $kviz]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kvíz nenalezen']);
    }
}

public function smazatKviz($kviz_id) {
    $result = $this->kvizModel->smazatKviz($kviz_id);
    
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Kvíz byl smazán']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nepodařilo se smazat kvíz']);
    }
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
        case 'ziskatNazevKvizu':
            if (isset($_GET['kviz_id'])) {
                $controller->ziskatNazevKvizuAjax($_GET['kviz_id']);
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
        case 'ziskatDetailKvizu':
            if (isset($_GET['kviz_id'])) {
                $controller->ziskatDetailKvizuAjax($_GET['kviz_id']);
            }
            break;
            case 'smazatKviz':
                if (isset($_GET['kviz_id'])) {
                    $controller->smazatKviz($_GET['kviz_id']);
                }
                break;
    }
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'vytvoritKviz':
            $controller->vytvoritKviz();
            break;
        case 'pridatOtazku':
            $controller->pridatOtazku();
            break;
        case 'upravitKviz':
            $controller->upravitKviz();
            break;
    }
}
?>