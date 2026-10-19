<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmtCheck->execute(array($email));
        if ($stmtCheck->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmtIns = $pdo->prepare("INSERT INTO user (email, password, role, created_at) VALUES (?, ?, 'user', NOW())");
            $stmtIns->execute(array($email, $hash));

            $_SESSION['user'] = array(
                'id' => $pdo->lastInsertId(),
                'email' => $email,
                'role' => 'user'
            );
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte · Agenda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.php">Agenda<span>.</span></a>
            <nav class="nav">
                <a href="index.php">Événements</a>
                <a href="connexion.php">Connexion</a>
                <a class="btn btn-small" href="inscription.php">Créer un compte</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="narrow">
            <div class="card">
                <h1>Créer un compte</h1>
                <?php if (!empty($error)): ?>
                    <div class="flash flash-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form class="form" method="post">
                    <div>
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div>
                        <label for="password" class="required">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                        <div class="help-text">6 caractères minimum.</div>
                    </div>
                    <button type="submit">Créer mon compte</button>
                </form>
                <p class="form-footer">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
            </div>
        </div>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>