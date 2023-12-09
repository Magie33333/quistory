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
            $this->kvizModel->pridejMozkaky($_SESSION['uzivatel_id'], 1);
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
    public function ukoncitKviz() {
        header('Content-Type: application/json');
    
    $pocetSpravnych = $_SESSION['pocetSpravnych'] ?? 0;
    $unikatniOtazky = array_unique($_SESSION['zobrazenéOtázky'] ?? []);
    $pocetUnikatnichOtazek = count($unikatniOtazky);
    $kviz_id = $_SESSION['kviz_id'] ?? null;
    $uzivatel_id = $_SESSION['uzivatel_id'] ?? null; // Předpokládáme, že máte uživatel_id v session
    
    // Zkontrolujte, jestli máte kviz_id a uzivatel_id
    if ($kviz_id === null || $uzivatel_id === null) {
        echo json_encode(['status' => 'error', 'message' => 'Chybí ID kvízu nebo uživatele.']);
        exit();
    }
    
     // Vložení výsledků do databáze
     try {
        $sql = "INSERT INTO vysledky (uzivatel_id, kviz_id, skore, datum_spocteni) VALUES (:uzivatel_id, :kviz_id, :skore, NOW())";
        $stmt = $this->kvizModel->prepare($sql);
        $stmt->execute([
            ':uzivatel_id' => $uzivatel_id,
            ':kviz_id' => $kviz_id,
            ':skore' => $pocetSpravnych
        ]);
        $vysledek_id = $this->kvizModel->lastInsertId();  
    } catch (PDOException $e) {
        error_log("Chyba při ukládání výsledků kvízu: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Nelze uložit výsledky kvízu.']);
        exit();
    }
    
        echo json_encode([
            'status' => 'completed',
            'pocetSpravnych' => $pocetSpravnych,
            'pocetZobrazenych' => $pocetUnikatnichOtazek,
            'message' => "Kvíz skončil. Máte $pocetSpravnych správných odpovědí z $pocetUnikatnichOtazek otázek.",
            'vysledek_id' => $vysledek_id
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
    $cena = $_POST['cena'];

    // Volání metody modelu pro vytvoření kvízu
    try {
        $this->kvizModel->vytvoritKviz($nazev, $popis, $cena);

        // Přesměrování a zobrazení úspěšné zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    } catch (Exception $e) {
        // Přesměrování a zobrazení chybové zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    }
    
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
    
    try {
        $this->kvizModel->pridatOtazku($kviz_id, $otazka_text, $moznosti);

        // Přesměrování a zobrazení úspěšné zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    } catch (Exception $e) {
        // Přesměrování a zobrazení chybové zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    }
}

public function upravitKviz() {
    $kviz_id = $_POST['kviz_id'];
    $nazev = $_POST['nazev'];
    $popis = $_POST['popis'];

    // Volání metody modelu pro úpravu kvízu
    
    try {
        $this->kvizModel->upravitKviz($kviz_id, $nazev, $popis);

        // Přesměrování a zobrazení úspěšné zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    } catch (Exception $e) {
        // Přesměrování a zobrazení chybové zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    }
    
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

public function upravitOtazku() {
    $otazka_id = $_POST['otazka_id'];
    $otazka_text = $_POST['otazka_text'];
    $kviz_id = $_POST['kviz_id']; // ID kvízu získané z formuláře
    $moznosti = $_POST['moznosti'];
    $spravna_odpoved = $_POST['spravna_odpoved'];

    try {
        // Upravit otázku s novými hodnotami
        $this->kvizModel->upravitOtazku($otazka_id, $kviz_id, $otazka_text, $moznosti, $spravna_odpoved);

        // Přesměrování a zobrazení úspěšné zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    } catch (Exception $e) {
        // Přesměrování a zobrazení chybové zprávy
        header('Location: ../view/SpravaKvizuView.php');
        exit();
    }
}

public function ziskatMoznostiOtazkyAjax($otazka_id) {
    $moznosti = $this->kvizModel->ziskatOdpovedi($otazka_id);
    
    header('Content-Type: application/json');
    if ($moznosti) {
        echo json_encode(['status' => 'success', 'moznosti' => $moznosti]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Možnosti nebyly nalezeny']);
    }
}

public function zobrazVsechnyOtazky() {
    return $this->kvizModel->ziskatVsechnyOtazky();
}

public function ziskatDetailOtazkyAjax($otazka_id) {
    header('Content-Type: application/json');
    $otazka = $this->kvizModel->ziskatOtazkuPodleId($otazka_id);
    $moznosti = $this->kvizModel->ziskatOdpovedi($otazka_id);

    if ($otazka) {
        echo json_encode(['status' => 'success', 'otazka' => $otazka, 'moznosti' => $moznosti]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Otázka nenalezena']);
    }
}

public function ziskatStavMozkaku($uzivatel_id) {
    return $this->kvizModel->ziskatStavMozkaku($uzivatel_id);
}

public function zobrazOdemceneKvizy($uzivatel_id) {
    return $this->kvizModel->zobrazOdemceneKvizy($uzivatel_id);
}

public function zobrazKvizyProUzivatele($uzivatel_id) {
    $odemceneKvizy = $this->kvizModel->zobrazOdemceneKvizy($uzivatel_id);
    $vsechnyKvizy = $this->zobrazKvizy();

    $odemcene = [];
    $zamcene = [];

    foreach ($vsechnyKvizy as $kviz) {
        if (in_array($kviz['kviz_id'], $odemceneKvizy)) {
            $odemcene[] = $kviz;
        } else {
            $zamcene[] = $kviz;
        }
    }

    return ['odemcene' => $odemcene, 'zamcene' => $zamcene];
}

public function koupitKviz($uzivatel_id, $kviz_id) {
    // Získejte cenu kvízu
    $cena = $this->kvizModel->ziskatCenuKvizu($kviz_id);

    // Zkontrolujte, jestli má uživatel dostatek Mozkáků
    $stavMozkaku = $this->kvizModel->ziskatStavMozkaku($uzivatel_id);
    if ($stavMozkaku >= $cena) {
        // Odečtení Mozkáků a zapsání transakce
        $this->kvizModel->odecistMozkaky($uzivatel_id, $cena);
        $this->kvizModel->oznacitKvizJakoKoupeny($uzivatel_id, $kviz_id);
        return true;
    } else {
        return false; // Nedostatek Mozkáků
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
        case 'ziskatMoznostiOtazky':
                if (isset($_GET['otazka_id'])) {
                    $controller->ziskatMoznostiOtazkyAjax($_GET['otazka_id']);
                }
                break;
        case 'ziskatDetailOtazky':
                if (isset($_GET['otazka_id'])) {
                    $controller->ziskatDetailOtazkyAjax($_GET['otazka_id']);
                }
                break;
        case 'ukoncitKviz':
            if (isset($_GET['kviz_id'])) {
                $controller->ukoncitKviz($_GET['kviz_id']);
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
        case 'upravitOtazku':
            $controller->upravitOtazku();
            break;
        case 'koupitKviz':
            if (isset($_POST['kviz_id']) && isset($_SESSION['uzivatel_id'])) {
                $uspech = $controller->koupitKviz($_SESSION['uzivatel_id'], $_POST['kviz_id']);
                if ($uspech) {
                    // Nákup byl úspěšný
                    $_SESSION['nákup_stav'] = 'úspěšný';
                } else {
                    // Chyba při nákupu
                    $_SESSION['nákup_stav'] = 'neúspěšný';
                }
                header('Location: ../view/KvizVyber.php');
                exit();
            }
            break;
    }
}
?>