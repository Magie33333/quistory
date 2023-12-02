<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Registrace</title>
    <link rel="stylesheet" href="../css/form.css">
</head>
<body>
    <h2>Registrace</h2>
    <?php include "./statusMessage.php"; displayStatusMessage(); ?>
    <form action="../controller/UzivatelController.php" method="post">
        <div>
            <label for="username">Uživatelské jméno:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="password_confirm">Potvrdit heslo:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <div style="display:none;">
            <label for="confirm_password_honeypot">Potvrzení hesla (nechte prázdné):</label>
            <input type="text" id="confirm_password_honeypot" name="confirm_password_honeypot" value="">
        </div>
        <input type="hidden" name="action" value="register">
        <button type="submit">Registrovat</button>
    </form>
</body>
</html>