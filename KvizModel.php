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

    public function ziskatNahodnouOtazku($kviz_id, $vylouceneOtazky) {
        $stmt = $this->db->prepare("SELECT * FROM otazky WHERE kviz_id = ? AND otazka_id NOT IN (" . implode(',', $vylouceneOtazky) . ")");
        $stmt->execute([$kviz_id]);
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