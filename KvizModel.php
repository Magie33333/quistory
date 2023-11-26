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

    /*public function ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky) {
        $stmt = $this->db->prepare("SELECT * FROM otazky WHERE kviz_id = ? AND otazka_id NOT IN (" . implode(',', array_fill(0, count($vylouceneOtazky), '?')) . ")");
        $stmt->execute(array_merge([$kviz_id], $vylouceneOtazky));
        $otazky = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($otazky) > 0) {
            return $otazky[array_rand($otazky)];
        }
        return null;
    }*/

    public function ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky) {
        if (empty($vylouceneOtazky)) {
            // Handle the case where there are no excluded question IDs.
            $stmt = $this->db->prepare("SELECT * FROM otazky WHERE kviz_id = ?");
            $stmt->execute([$kviz_id]);
        } else {
            $questionIds = implode(',', array_fill(0, count($vylouceneOtazky), '?'));
    
            $stmt = $this->db->prepare("SELECT * FROM otazky WHERE kviz_id = ? AND otazka_id NOT IN ($questionIds)");
            $stmt->execute(array_merge([$kviz_id], $vylouceneOtazky));
        }
    
        $otazky = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($otazky) > 0) {
            return $otazky[array_rand($otazky)];
        }
        return null;
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