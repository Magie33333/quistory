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

    // Další potřebné metody... VYTVOŘIT KVÍZ, PŘIDAT OTÁZKY ATD.
}
?>