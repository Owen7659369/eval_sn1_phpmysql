<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute(array($email));
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = array(
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            );
            header('Location: index.php');
            exit;
        } else {
            $error = "Identifiants invalides.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Agenda</title>
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
                <h1>Connexion</h1>
                <?php if (!empty($error)): ?>
                    <div class="flash flash-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form class="form" method="post">
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autofocus>
                    </div>
                    <div>
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Se connecter</button>
                </form>
                <p class="form-footer">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
            </div>
        </div>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>