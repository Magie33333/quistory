<?php
class KvizModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function ziskatKvizy() {
        $stmt = $this->db->prepare("SELECT * FROM kvizy");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ziskatNazevKvizu($kviz_id) {
        $stmt = $this->db->prepare("SELECT nazev FROM kvizy WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);
        $vysledek = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vysledek) {
            return $vysledek['nazev'];
        } else {
            return null; // Nebo vhodná chybová zpráva/hodnota
        }
    }

    public function ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky) {
        try {
            if (count($vylouceneOtazky) > 0) {
                $placeholder = implode(',', array_fill(0, count($vylouceneOtazky), '?'));
                $sql = "SELECT * FROM otazky WHERE kviz_id = ? AND otazka_id NOT IN ($placeholder) ORDER BY RAND() LIMIT 1";
                $params = array_merge([$kviz_id], $vylouceneOtazky);
            } else {
                $sql = "SELECT * FROM otazky WHERE kviz_id = ? ORDER BY RAND() LIMIT 1";
                $params = [$kviz_id];
            }
    
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log chyby a vrácení null nebo vyhození vlastní výjimky
            error_log("Chyba při načítání náhodné otázky: " . $e->getMessage());
            return null;
        }
    }

    public function ziskatOdpovedi($otazka_id) {
        $stmt = $this->db->prepare("SELECT * FROM moznosti WHERE otazka_id = ?");
        $stmt->execute([$otazka_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function overitOdpoved($otazka_id, $moznost_id) {
        $stmt = $this->db->prepare("SELECT je_spravna FROM moznosti WHERE otazka_id = ? AND moznost_id = ?");
        $stmt->execute([$otazka_id, $moznost_id]);
        $odpoved = $stmt->fetch(PDO::FETCH_ASSOC);
        return $odpoved['je_spravna'] == 1;
    }

    public function vytvoritKviz($nazev, $popis, $cena) {
        $sql = "INSERT INTO kvizy (nazev, popis, cena_mozkaky) VALUES (:nazev, :popis, :cena_mozkaky)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nazev', $nazev);
        $stmt->bindParam(':popis', $popis);
        $stmt->bindParam(':cena_mozkaky', $cena);
        $stmt->execute();
    }

    public function pridatOtazku($kviz_id, $otazka_text, $moznosti) {
        // Nejprve vložíme otázku
        $sql = "INSERT INTO otazky (kviz_id, otazka_text) VALUES (:kviz_id, :otazka_text)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':kviz_id', $kviz_id);
        $stmt->bindParam(':otazka_text', $otazka_text);
        $stmt->execute();

        $otazka_id = $this->db->lastInsertId();

        // Nyní vložíme možnosti
        $sql = "INSERT INTO moznosti (otazka_id, moznost_text, je_spravna) VALUES (:otazka_id, :moznost_text, :je_spravna)";
        $stmt = $this->db->prepare($sql);

        foreach ($moznosti as $moznost) {
            $stmt->bindParam(':otazka_id', $otazka_id);
            $stmt->bindParam(':moznost_text', $moznost['text']);
            $stmt->bindParam(':je_spravna', $moznost['je_spravna']);
            $stmt->execute();
        }
    }

    public function upravitKviz($kviz_id, $nazev, $popis) {
        $sql = "UPDATE kvizy SET nazev = :nazev, popis = :popis WHERE kviz_id = :kviz_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':kviz_id', $kviz_id);
        $stmt->bindParam(':nazev', $nazev);
        $stmt->bindParam(':popis', $popis);
        $stmt->execute();
    }

    public function ziskatKvizPodleId($kviz_id) {
        $stmt = $this->db->prepare("SELECT * FROM kvizy WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ziskatOtazkuPodleId($otazka_id) {
        $stmt = $this->db->prepare("SELECT * FROM otazky WHERE otazka_id = ?");
        $stmt->execute([$otazka_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function smazatKviz($kviz_id) {
    $this->db->beginTransaction();
    try {
        // Smažeme všechny záznamy v transakcích spojené s tímto kvízem
        $stmt = $this->db->prepare("DELETE FROM transakce WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);

        // Smažeme všechny možnosti spojené s otázkami tohoto kvízu
        $stmt = $this->db->prepare("DELETE moznosti FROM moznosti 
                                     JOIN otazky ON moznosti.otazka_id = otazky.otazka_id 
                                     WHERE otazky.kviz_id = ?");
        $stmt->execute([$kviz_id]);

        // Smažeme všechny otázky spojené s tímto kvízem
        $stmt = $this->db->prepare("DELETE FROM otazky WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);

        // Smažeme všechny výsledky kvízů spojené s tímto kvízem
        $stmt = $this->db->prepare("DELETE FROM vysledky WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);

        // Nyní můžeme bezpečně smazat samotný kvíz
        $stmt = $this->db->prepare("DELETE FROM kvizy WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);

        $this->db->commit();
        return true;
    } catch (PDOException $e) {
        // Pokud dojde k jakékoliv chybě, transakce se vrátí zpět
        $this->db->rollBack();
        // Logování chyby
        error_log('Chyba při mazání kvízu: ' . $e->getMessage());
        return false;
    }
}

    public function upravitOtazku($otazka_id, $kviz_id, $otazka_text, $moznosti, $spravna_odpoved) {
        // Aktualizovat otázku
        try {
            $sql = "UPDATE otazky SET otazka_text = :otazka_text, kviz_id = :kviz_id WHERE otazka_id = :otazka_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['otazka_text' => $otazka_text, 'kviz_id' => $kviz_id, 'otazka_id' => $otazka_id]);
        } catch (PDOException $e) {
            echo "Chyba při aktualizaci otázky: " . $e->getMessage();
        }
    
        // Aktualizovat možnosti
        foreach ($moznosti as $moznost) {
            $moznost_id = $moznost['id']; // ID možnosti
            $moznost_text = $moznost['text']; // Text možnosti
            $je_spravna = ($moznost_id == $spravna_odpoved) ? 1 : 0;
    
            $sql = "UPDATE moznosti SET moznost_text = :moznost_text, je_spravna = :je_spravna WHERE moznost_id = :moznost_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['moznost_text' => $moznost_text, 'je_spravna' => $je_spravna, 'moznost_id' => $moznost_id]);
        }
    }

    public function ziskatVsechnyOtazky() {
        $stmt = $this->db->prepare("SELECT otazka_id, otazka_text FROM otazky");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function prepare($sql) {
        return $this->db->prepare($sql);
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function pridejMozkaky($uzivatel_id, $ziskane_mozkaky) {
        $stmt = $this->db->prepare("UPDATE uzivatele SET mozkaky = mozkaky + :ziskane_mozkaky WHERE id = :uzivatel_id");
        $stmt->execute([':ziskane_mozkaky' => $ziskane_mozkaky, ':uzivatel_id' => $uzivatel_id]);
    }
    
    public function odecistMozkaky($uzivatel_id, $cena_mozkaky) {
        $stmt = $this->db->prepare("UPDATE uzivatele SET mozkaky = mozkaky - :cena_mozkaky WHERE id = :uzivatel_id AND mozkaky >= :cena_mozkaky");
        $stmt->execute([':cena_mozkaky' => $cena_mozkaky, ':uzivatel_id' => $uzivatel_id]);
    }

    public function ziskatCenuKvizu($kviz_id) {
        $stmt = $this->db->prepare("SELECT cena_mozkaky FROM kvizy WHERE kviz_id = ?");
        $stmt->execute([$kviz_id]);
        $vysledek = $stmt->fetch(PDO::FETCH_ASSOC);
        return $vysledek ? $vysledek['cena_mozkaky'] : 0;
    }

    public function ziskatStavMozkaku($uzivatel_id) {
        $stmt = $this->db->prepare("SELECT mozkaky FROM uzivatele WHERE id = :uzivatel_id");
        $stmt->bindParam(':uzivatel_id', $uzivatel_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['mozkaky'] : 0;
    }

    public function zobrazOdemceneKvizy($uzivatel_id) {
        $sql = "SELECT kviz_id FROM transakce WHERE uzivatel_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uzivatel_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function oznacitKvizJakoKoupeny($uzivatel_id, $kviz_id) {
        $sql = "INSERT INTO transakce (uzivatel_id, kviz_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uzivatel_id, $kviz_id]);
    }
}
?>