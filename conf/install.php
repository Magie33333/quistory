<?php
include 'connect.php'; // Připojení k databázi

if (!$conn) {
    die("Nelze se připojit k databázi: " . mysqli_connect_error());
}

// Funkce pro vytvoření tabulky
function createTable($conn, $sql, $tableName) {
    try {
        $conn->exec($sql);
        echo "Tabulka '$tableName' byla úspěšně vytvořena.";
    } catch (PDOException $e) {
        echo "Chyba při vytváření tabulky '$tableName': " . $e->getMessage();
    }
}

// Vytvoření tabulky uzivatele
createTable($conn, "CREATE TABLE IF NOT EXISTS uzivatele (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                mozkaky INT DEFAULT 0,
                datum_registrace TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )", 'uzivatele');

// Vytvoření tabulky kvizy
createTable($conn, "CREATE TABLE IF NOT EXISTS kvizy (
                kviz_id INT AUTO_INCREMENT PRIMARY KEY,
                nazev VARCHAR(255) NOT NULL,
                popis TEXT,
                cena_mozkaky INT NOT NULL
            )", 'kvizy');

// Vytvoření tabulky otazky
createTable($conn, "CREATE TABLE IF NOT EXISTS otazky (
                otazka_id INT AUTO_INCREMENT PRIMARY KEY,
                kviz_id INT,
                otazka_text TEXT NOT NULL,
                FOREIGN KEY (kviz_id) REFERENCES kvizy(kviz_id)
            )", 'otazky');

// Vytvoření tabulky moznosti
createTable($conn, "CREATE TABLE IF NOT EXISTS moznosti (
                moznost_id INT AUTO_INCREMENT PRIMARY KEY,
                otazka_id INT,
                moznost_text TEXT NOT NULL,
                je_spravna BOOLEAN,
                FOREIGN KEY (otazka_id) REFERENCES otazky(otazka_id)
            )", 'moznosti');

// Vytvoření tabulky vysledky
createTable($conn, "CREATE TABLE IF NOT EXISTS vysledky (
                vysledek_id INT AUTO_INCREMENT PRIMARY KEY,
                uzivatel_id INT,
                kviz_id INT,
                skore INT NOT NULL,
                datum_spocteni TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (uzivatel_id) REFERENCES uzivatele(id),
                FOREIGN KEY (kviz_id) REFERENCES kvizy(kviz_id)
            )", 'vysledky');

createTable($conn, "CREATE TABLE IF NOT EXISTS transakce (
    uzivatel_id INT NOT NULL,
    kviz_id INT NOT NULL,
    datum_koupe TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (uzivatel_id, kviz_id),
    FOREIGN KEY (uzivatel_id) REFERENCES uzivatele(id),
    FOREIGN KEY (kviz_id) REFERENCES kvizy(kviz_id)
);", 'transakce');



// Kontrola, zda již uživatel 'admin' existuje
try {
    $stmt = $conn->prepare("SELECT id FROM uzivatele WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Hash hesla
        $hash = password_hash("admin", PASSWORD_DEFAULT);

        // Vytvoření uživatele 'admin'
        $sql = "INSERT INTO uzivatele (username, password) VALUES ('admin', :hash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hash', $hash);
        $stmt->execute();

        echo "Uživatel 'admin' byl úspěšně vytvořen.";
    } else {
        echo "Uživatel 'admin' již existuje.";
    }
} catch (PDOException $e) {
    echo "Chyba při vytváření uživatele 'admin': " . $e->getMessage();
}

//vložení dat
function createQuiz($conn, $nazev, $popis, $cenaMozkaky) {
    try {
        $sql = "INSERT INTO kvizy (nazev, popis, cena_mozkaky) VALUES (:nazev, :popis, :cenaMozkaky)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nazev', $nazev);
        $stmt->bindParam(':popis', $popis);
        $stmt->bindParam(':cenaMozkaky', $cenaMozkaky);
        $stmt->execute();

        echo "Kvíz '$nazev' byl úspěšně vytvořen.";
    } catch (PDOException $e) {
        echo "Chyba při vytváření kvízu: " . $e->getMessage();
    }
}

function addQuestion($conn, $kvizId, $otazkaText) {
    try {
        $sql = "INSERT INTO otazky (kviz_id, otazka_text) VALUES (:kvizId, :otazkaText)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':kvizId', $kvizId);
        $stmt->bindParam(':otazkaText', $otazkaText);
        $stmt->execute();

        echo "Otázka byla přidána do kvízu.";
    } catch (PDOException $e) {
        echo "Chyba při přidávání otázky: " . $e->getMessage();
    }
}

function addOption($conn, $otazkaId, $moznostText, $jeSpravna) {
    try {
        $sql = "INSERT INTO moznosti (otazka_id, moznost_text, je_spravna) VALUES (:otazkaId, :moznostText, :jeSpravna)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':otazkaId', $otazkaId);
        $stmt->bindParam(':moznostText', $moznostText);
        $stmt->bindParam(':jeSpravna', $jeSpravna, PDO::PARAM_BOOL);
        $stmt->execute();

        echo "Možnost byla přidána k otázce.";
    } catch (PDOException $e) {
        echo "Chyba při přidávání možnosti: " . $e->getMessage();
    }
}

function getLastInsertId($conn) {
    try {
        // Vrátí ID posledně vloženého záznamu
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        echo "Chyba při získávání posledního vloženého ID: " . $e->getMessage();
    }
}

// OBECNÝ KVÍZ
createQuiz($conn, "Obecný kvíz", "Kvíz testující obecné znalosti z různých oblastí.", 0);
$kvizId = getLastInsertId($conn);

addQuestion($conn, $kvizId, "Kolik je 23+58?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "81", true);
addOption($conn, $otazkaId, "77", false);
addOption($conn, $otazkaId, "85", false);
addOption($conn, $otazkaId, "79", false);

addQuestion($conn, $kvizId, "Kdo napsal knihu 1984?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "George Orwell", true);
addOption($conn, $otazkaId, "Aldous Huxley", false);
addOption($conn, $otazkaId, "Ray Bradbury", false);
addOption($conn, $otazkaId, "Alexandr Solženicyn", false);

addQuestion($conn, $kvizId, "Jaký je chemický symbol pro vodík?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "H", true);
addOption($conn, $otazkaId, "He", false);
addOption($conn, $otazkaId, "V", false);
addOption($conn, $otazkaId, "N", false);

addQuestion($conn, $kvizId, "Jaký kontinent je největší na světě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Asie", true);
addOption($conn, $otazkaId, "Afrika", false);
addOption($conn, $otazkaId, "Antarktida", false);
addOption($conn, $otazkaId, "Severní Amerika", false);

addQuestion($conn, $kvizId, "Kdo popsal gravitační zákony?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Isaac Newton", true);
addOption($conn, $otazkaId, "Albert Einstein", false);
addOption($conn, $otazkaId, "Galileo Galilei", false);
addOption($conn, $otazkaId, "Johannes Kepler", false);

addQuestion($conn, $kvizId, "Které město je hlavním městem Francie?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Paříž", true);
addOption($conn, $otazkaId, "Lyon", false);
addOption($conn, $otazkaId, "Marseille", false);
addOption($conn, $otazkaId, "Nice", false);

addQuestion($conn, $kvizId, "Který vitamín je známý jako 'sluneční vitamín'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Vitamín D", true);
addOption($conn, $otazkaId, "Vitamín C", false);
addOption($conn, $otazkaId, "Vitamín B12", false);
addOption($conn, $otazkaId, "Vitamín A", false);

addQuestion($conn, $kvizId, "Která řeka je nejdelší na světě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Amazonka", false);
addOption($conn, $otazkaId, "Nil", true);
addOption($conn, $otazkaId, "Jang-c’-ťiang", false);
addOption($conn, $otazkaId, "Mississippi", false);

addQuestion($conn, $kvizId, "Kdo napsal divadelní hru 'Romeo a Julie'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "William Shakespeare", true);
addOption($conn, $otazkaId, "Charles Dickens", false);
addOption($conn, $otazkaId, "Jane Austen", false);
addOption($conn, $otazkaId, "Lev Tolstoj", false);

addQuestion($conn, $kvizId, "Jaký plyn je nejvíce zastoupený v zemské atmosféře?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "kyslík", false);
addOption($conn, $otazkaId, "dusík", true);
addOption($conn, $otazkaId, "uhlík", false);
addOption($conn, $otazkaId, "vodík", false);

addQuestion($conn, $kvizId, "Kdo napsal román 'Sto roků samoty'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Gabriel García Márquez", true);
addOption($conn, $otazkaId, "Mario Vargas Llosa", false);
addOption($conn, $otazkaId, "Julio Cortázar", false);
addOption($conn, $otazkaId, "Carlos Fuentes", false);

addQuestion($conn, $kvizId, "Který prvek má atomové číslo 6?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Uhlík", true);
addOption($conn, $otazkaId, "Dusík", false);
addOption($conn, $otazkaId, "Kyslík", false);
addOption($conn, $otazkaId, "Vodík", false);

addQuestion($conn, $kvizId, "Která země hostila letní olympijské hry v roce 2004?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Řecko", true);
addOption($conn, $otazkaId, "Čína", false);
addOption($conn, $otazkaId, "Austrálie", false);
addOption($conn, $otazkaId, "Spojené království", false);

addQuestion($conn, $kvizId, "Která skupina vydala album 'Dark Side of the Moon'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Pink Floyd", true);
addOption($conn, $otazkaId, "The Beatles", false);
addOption($conn, $otazkaId, "Led Zeppelin", false);
addOption($conn, $otazkaId, "The Rolling Stones", false);

addQuestion($conn, $kvizId, "Jaké je hlavní město Kanady?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Ottawa", true);
addOption($conn, $otazkaId, "Toronto", false);
addOption($conn, $otazkaId, "Montreal", false);
addOption($conn, $otazkaId, "Vancouver", false);

addQuestion($conn, $kvizId, "Kdo je autorem teorie relativity?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Albert Einstein", true);
addOption($conn, $otazkaId, "Isaac Newton", false);
addOption($conn, $otazkaId, "Stephen Hawking", false);
addOption($conn, $otazkaId, "Niels Bohr", false);

addQuestion($conn, $kvizId, "Které město je známé jako 'Město sedmi pahorků'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Řím", true);
addOption($conn, $otazkaId, "Lisabon", false);
addOption($conn, $otazkaId, "San Francisco", false);
addOption($conn, $otazkaId, "Istanbul", false);

addQuestion($conn, $kvizId, "Jaké zvíře je národním symbolem Austrálie?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Klokan", true);
addOption($conn, $otazkaId, "Koala", false);
addOption($conn, $otazkaId, "Emu", false);
addOption($conn, $otazkaId, "Platypus", false);

addQuestion($conn, $kvizId, "Kdo je autorem série knih o kouzelníkovi Harry Potterovi?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "J.K. Rowlingová", true);
addOption($conn, $otazkaId, "J.R.R. Tolkien", false);
addOption($conn, $otazkaId, "C.S. Lewis", false);
addOption($conn, $otazkaId, "Philip Pullman", false);

addQuestion($conn, $kvizId, "Který film získal Oscara za nejlepší film v roce 1994?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Forrest Gump", true);
addOption($conn, $otazkaId, "Pulp Fiction", false);
addOption($conn, $otazkaId, "Vykoupení z věznice Shawshank", false);
addOption($conn, $otazkaId, "Lví král", false);

addQuestion($conn, $kvizId, "Kdo napsal slavnou operu 'Madam Butterfly'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Giacomo Puccini", true);
addOption($conn, $otazkaId, "Wolfgang Amadeus Mozart", false);
addOption($conn, $otazkaId, "Ludwig van Beethoven", false);
addOption($conn, $otazkaId, "Richard Wagner", false);

addQuestion($conn, $kvizId, "V kterém roce padla Berlínská zeď?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "1989", true);
addOption($conn, $otazkaId, "1991", false);
addOption($conn, $otazkaId, "1987", false);
addOption($conn, $otazkaId, "1990", false);

addQuestion($conn, $kvizId, "Které město bylo původní hlavní město Spojených států amerických?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "New York", true);
addOption($conn, $otazkaId, "Washington, D.C.", false);
addOption($conn, $otazkaId, "Filadelfie", false);
addOption($conn, $otazkaId, "Boston", false);

addQuestion($conn, $kvizId, "Kdo objevil Ameriku v roce 1492?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Kryštof Kolumbus", true);
addOption($conn, $otazkaId, "Vasco da Gama", false);
addOption($conn, $otazkaId, "Amerigo Vespucci", false);
addOption($conn, $otazkaId, "Ferdinand Magellan", false);

addQuestion($conn, $kvizId, "Která planeta je známá jako 'Rudá planeta'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Venuše", false);
addOption($conn, $otazkaId, "Mars", true);
addOption($conn, $otazkaId, "Jupiter", false);
addOption($conn, $otazkaId, "Saturn", false);

addQuestion($conn, $kvizId, "Která země se podle projekcí OSN stala v dubnu 2023 nejlidnatější zemí na světě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Indie", true);
addOption($conn, $otazkaId, "Spojené státy americké", false);
addOption($conn, $otazkaId, "Čína", false);
addOption($conn, $otazkaId, "Indonésie", false);

addQuestion($conn, $kvizId, "Jaké je hlavní město Japonska?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Kyoto", false);
addOption($conn, $otazkaId, "Osaka", false);
addOption($conn, $otazkaId, "Tokyo", true);
addOption($conn, $otazkaId, "Sapporo", false);

addQuestion($conn, $kvizId, "Který rok proběhl první let raketoplánu?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "1975", false);
addOption($conn, $otazkaId, "1981", true);
addOption($conn, $otazkaId, "1986", false);
addOption($conn, $otazkaId, "1990", false);

addQuestion($conn, $kvizId, "Kdo je autorem teorie evoluce?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Charles Darwin", true);
addOption($conn, $otazkaId, "Isaac Newton", false);
addOption($conn, $otazkaId, "Albert Einstein", false);
addOption($conn, $otazkaId, "Gregor Mendel", false);

addQuestion($conn, $kvizId, "Kterým jazykem se mluví nejvíc na světě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Angličtina", false);
addOption($conn, $otazkaId, "Mandarínština", true);
addOption($conn, $otazkaId, "Španělština", false);
addOption($conn, $otazkaId, "Hindština", false);

addQuestion($conn, $kvizId, "Která zvířata jsou známá pro svůj echolokační systém?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Netopýři", true);
addOption($conn, $otazkaId, "Velryby", false);
addOption($conn, $otazkaId, "Delfíni", false);
addOption($conn, $otazkaId, "Psi", false);

addQuestion($conn, $kvizId, "Který hudební skladatel napsal Devátou symfonii?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Wolfgang Amadeus Mozart", false);
addOption($conn, $otazkaId, "Johann Sebastian Bach", false);
addOption($conn, $otazkaId, "Ludwig van Beethoven", true);
addOption($conn, $otazkaId, "Pyotr Ilyich Tchaikovsky", false);

addQuestion($conn, $kvizId, "Který stát je známý jako 'Země tisíců jezer'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Norsko", false);
addOption($conn, $otazkaId, "Finsko", true);
addOption($conn, $otazkaId, "Švédsko", false);
addOption($conn, $otazkaId, "Kanada", false);

addQuestion($conn, $kvizId, "Který z těchto vitaminů je rozpustný ve vodě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Vitamín A", false);
addOption($conn, $otazkaId, "Vitamín C", true);
addOption($conn, $otazkaId, "Vitamín D", false);
addOption($conn, $otazkaId, "Vitamín K", false);

addQuestion($conn, $kvizId, "Který oceán je největší na světě?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Atlantický oceán", false);
addOption($conn, $otazkaId, "Indický oceán", false);
addOption($conn, $otazkaId, "Tichý oceán", true);
addOption($conn, $otazkaId, "Severní ledový oceán", false);

addQuestion($conn, $kvizId, "Který stát vyhrál mistrovství světa ve fotbale v roce 2022?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Francie", false);
addOption($conn, $otazkaId, "Argentina", true);
addOption($conn, $otazkaId, "Chorvatsko", false);
addOption($conn, $otazkaId, "Maroko", false);

addQuestion($conn, $kvizId, "Který stát má hlavní město se jménem Oslo?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Švédsko", false);
addOption($conn, $otazkaId, "Dánsko", false);
addOption($conn, $otazkaId, "Norsko", true);
addOption($conn, $otazkaId, "Finsko", false);

addQuestion($conn, $kvizId, "V kterém roce člověk poprvé přistál na Měsíci?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "1965", false);
addOption($conn, $otazkaId, "1969", true);
addOption($conn, $otazkaId, "1973", false);
addOption($conn, $otazkaId, "1970", false);

addQuestion($conn, $kvizId, "Který hudební nástroj má černé a bílé klávesy?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Klavír", true);
addOption($conn, $otazkaId, "Kytara", false);
addOption($conn, $otazkaId, "Violoncello", false);
addOption($conn, $otazkaId, "Flétna", false);

addQuestion($conn, $kvizId, "Který herec ztvárnil postavu Severuse Snapea ve filmové sérii o Harry Potterovi?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Alan Rickman", true);
addOption($conn, $otazkaId, "Adam Driver", false);
addOption($conn, $otazkaId, "Kenneth Branagh", false);
addOption($conn, $otazkaId, "Daniel Radcliffe", false);



//STŘEDOVĚKÝ KVÍZ
createQuiz($conn, "Středověký kvíz", "Kvíz testující znalosti z dějin středověku.", 20);
$kvizId = getLastInsertId($conn);

addQuestion($conn, $kvizId, "Jaký letopočet se tradičně označuje za počátek středověku?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "0", false);
addOption($conn, $otazkaId, "117", false);
addOption($conn, $otazkaId, "395", false);
addOption($conn, $otazkaId, "476", true);

addQuestion($conn, $kvizId, "Jaký letopočet se tradičně označuje za konec středověku?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "1370", false);
addOption($conn, $otazkaId, "1385", false);
addOption($conn, $otazkaId, "1410", false);
addOption($conn, $otazkaId, "1453", true);

addQuestion($conn, $kvizId, "Co byla Magna Charta?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Zákoník císaře Justiniána", false);
addOption($conn, $otazkaId, "Papežská bula", false);
addOption($conn, $otazkaId, "Listina zaručující práva šlechty", true);
addOption($conn, $otazkaId, "Smlouva mezi Anglií a Francií", false);

addQuestion($conn, $kvizId, "Kdo byl autorem 'Božské komedie'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Geoffrey Chaucer", false);
addOption($conn, $otazkaId, "William Shakespeare", false);
addOption($conn, $otazkaId, "Dante Alighieri", true);
addOption($conn, $otazkaId, "Miguel de Cervantes", false);

addQuestion($conn, $kvizId, "Co znamená termín 'feudalismus'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Ekonomický systém založený na obchodu", false);
addOption($conn, $otazkaId, "Politický systém demokracie", false);
addOption($conn, $otazkaId, "Sociální a ekonomický systém založený na lenních vztazích", true);
addOption($conn, $otazkaId, "Náboženský řád středověku", false);

addQuestion($conn, $kvizId, "Jaký byl hlavním důvodem křížových výprav?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Obchodní expanze", false);
addOption($conn, $otazkaId, "Osvobození Jeruzaléma", true);
addOption($conn, $otazkaId, "Boj proti pohanům v Evropě", false);
addOption($conn, $otazkaId, "Rozšíření křesťanství", false);

addQuestion($conn, $kvizId, "Který středověký filozof byl známý jako 'Učitel církve'?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Svatý Augustin", false);
addOption($conn, $otazkaId, "Svatý Tomáš Akvinský", true);
addOption($conn, $otazkaId, "Svatý Anselm", false);
addOption($conn, $otazkaId, "Svatý Jeroným", false);

addQuestion($conn, $kvizId, "Kdo to byl Karel Veliký?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "anglický král", false);
addOption($conn, $otazkaId, "německý císař", false);
addOption($conn, $otazkaId, "franský král", true);
addOption($conn, $otazkaId, "vikingský náčelník", false);

addQuestion($conn, $kvizId, "Které město bylo během středověku centrem Islámského světa?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Mekka", false);
addOption($conn, $otazkaId, "Jeruzalém", false);
addOption($conn, $otazkaId, "Bagdád", true);
addOption($conn, $otazkaId, "Káhira", false);

addQuestion($conn, $kvizId, "Jak se jmenoval první český král?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Břetislav", false);
addOption($conn, $otazkaId, "Vladislav", false);
addOption($conn, $otazkaId, "Vratislav", true);
addOption($conn, $otazkaId, "Přemysl Otakar", false);

addQuestion($conn, $kvizId, "V jakém roce proběhla bitva u Kresčaku, ve které zemřel český král Jan Lucemburský?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "1322", false);
addOption($conn, $otazkaId, "1346", true);
addOption($conn, $otazkaId, "1331", false);
addOption($conn, $otazkaId, "1340", false);

addQuestion($conn, $kvizId, "Nad hrobem jakého svatého údajně vyhlásil Břetislav tzv. dekreta?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "sv. Vojtěcha", true);
addOption($conn, $otazkaId, "sv. Václava", false);
addOption($conn, $otazkaId, "sv. Víta", false);
addOption($conn, $otazkaId, "sv. Mikuláše", false);

addQuestion($conn, $kvizId, "Jak se jmenoval zakladatel Franské říše?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Odoaker", false);
addOption($conn, $otazkaId, "Chlodvík", true);
addOption($conn, $otazkaId, "Thibalt", false);
addOption($conn, $otazkaId, "Chlodomer", false);

addQuestion($conn, $kvizId, "Na jakém poloostrově se rozkládalo království Vizigótů?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Pyrenejském", true);
addOption($conn, $otazkaId, "Apeninském", false);
addOption($conn, $otazkaId, "Balkánském", false);
addOption($conn, $otazkaId, "Skandinávském", false);

addQuestion($conn, $kvizId, "Jako stoletá válka se označuje konflikt mezi Anglií a ...?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Francií", true);
addOption($conn, $otazkaId, "Španělskem", false);
addOption($conn, $otazkaId, "Nizozemskem", false);
addOption($conn, $otazkaId, "Skotskem", false);

addQuestion($conn, $kvizId, "Moravu k Čechám natrvalo připojil jaký kníže?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Václav", false);
addOption($conn, $otazkaId, "Břetislav", false);
addOption($conn, $otazkaId, "Vratislav", false);
addOption($conn, $otazkaId, "Oldřich", true);

addQuestion($conn, $kvizId, "Jak se jmenoval poslední český král z rodu Přemyslovců?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Václav I.", false);
addOption($conn, $otazkaId, "Václav II.", false);
addOption($conn, $otazkaId, "Václav III.", true);
addOption($conn, $otazkaId, "Václav IV.", false);

addQuestion($conn, $kvizId, "Roku 1310 se českým králem stal člen které dynastie?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Lucemburkové", true);
addOption($conn, $otazkaId, "Arpádovci", false);
addOption($conn, $otazkaId, "Habsburkové", false);
addOption($conn, $otazkaId, "Anjouovci", false);

addQuestion($conn, $kvizId, "Karel IV. připojil ke Koruně české na počátku 70. let 14. století kterou zemi?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Braniborsko", true);
addOption($conn, $otazkaId, "Sasko", false);
addOption($conn, $otazkaId, "Horní Lužici", false);
addOption($conn, $otazkaId, "Horní Falc", false);

addQuestion($conn, $kvizId, "Roku 1453 došlo k pádu kterého města?");
$otazkaId = getLastInsertId($conn);
addOption($conn, $otazkaId, "Řím", false);
addOption($conn, $otazkaId, "Konstantinopol", true);
addOption($conn, $otazkaId, "Paříž", false);
addOption($conn, $otazkaId, "Granada", false);

$conn = null; // Uzavření připojení k databázi
?>

