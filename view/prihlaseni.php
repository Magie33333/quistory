<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přihlášení</title>
    <!-- Link k CSS souboru -->
</head>
<body>
    <h2>Přihlášení</h2>

    <form action="../controller/UzivatelController.php" method="post">
        <div>
            <label for="username">Uživatelské jméno:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <input type="hidden" name="action" value="login">
        <button type="submit">Přihlásit se</button>
    </form>
</body>
</html>