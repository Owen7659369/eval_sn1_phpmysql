<?php
session_start();
require 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Récupérer l'événement
$sql = "SELECT * FROM event WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$event = $stmt->fetch();

// Si l'événement n'existe pas -> Erreur 404
if (!$event) {
    http_response_code(404);
    include '404.html';
    exit;
}

// 2. Traitement de l'inscription / désinscription
$message = '';
$error = '';

if (isset($_POST['action']) && isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    
    if ($_POST['action'] === 'inscrire') {
        // Vérifier si déjà inscrit et s'il reste des places
        $stmtCheck = $pdo->prepare("SELECT * FROM reservation WHERE user_id = ? AND event_id = ?");
        $stmtCheck->execute([$userId, $id]);
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE event_id = ?");
        $stmtCount->execute([$id]);
        $nbInscrits = $stmtCount->fetchColumn();

        if ($stmtCheck->rowCount() > 0) {
            $error = "Vous êtes déjà inscrit à cet événement.";
        } elseif ($nbInscrits >= $event['places']) {
            $error = "Désolé, cet événement est complet.";
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO reservation (user_id, event_id, created_at) VALUES (?, ?, NOW())");
            $stmtIns->execute([$userId, $id]);
            header("Location: evenement.php?id=$id");
            exit;
        }
    } elseif ($_POST['action'] === 'desinscrire') {
        $stmtDel = $pdo->prepare("DELETE FROM reservation WHERE user_id = ? AND event_id = ?");
        $stmtDel->execute([$userId, $id]);
        header("Location: evenement.php?id=$id");
        exit;
    }
}

// 3. Calculer les places restantes et vérifier si l'user connecté est inscrit
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE event_id = ?");
$stmtCount->execute([$id]);
$nbInscrits = $stmtCount->fetchColumn();
$placesRestantes = $event['places'] - $nbInscrits;
$pourcentage = ($event['places'] > 0) ? ($nbInscrits / $event['places']) * 100 : 0;

$isUserInscribed = false;
if (isset($_SESSION['user'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM reservation WHERE user_id = ? AND event_id = ?");
    $stmtUser->execute([$_SESSION['user']['id'], $id]);
    $isUserInscribed = ($stmtUser->rowCount() > 0);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($event['title']) ?> · Agenda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.php">Agenda<span>.</span></a>
            <nav class="nav">
                <a href="index.php">Événements</a>
                <?php if (isset($_SESSION['user'])): ?>
                    <a class="btn btn-small" href="deconnexion.php">Se déconnecter</a>
                <?php else: ?>
                    <a href="connexion.php">Connexion</a>
                    <a class="btn btn-small" href="inscription.php">Créer un compte</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if ($error): ?>
            <div class="flash flash-error"><?= $error ?></div>
        <?php endif; ?>

        <a class="back-link" href="index.php">← Retour aux événements</a>

        <article class="cat-<?= htmlspecialchars($event['category']) ?>">
            <header class="detail-hero" style="background-image: url('<?= htmlspecialchars($event['image'] ? $event['image'] : 'images/concert.jpg') ?>')">
                <div class="detail-hero-content">
                    <span class="badge"><?= ucfirst(htmlspecialchars($event['category'])) ?></span>
                    <h1><?= htmlspecialchars($event['title']) ?></h1>
                    <p><?= date('d/m/Y · H\hi', strtotime($event['date'])) ?></p>
                </div>
            </header>

            <div class="detail-grid">
                <section class="card">
                    <h2>Informations</h2>
                    <p><?= nl2br(htmlspecialchars($event['information'])) ?></p>
                </section>

                <aside class="card booking">
                    <?php if ($placesRestantes > 0): ?>
                        <p class="places"><strong><?= $placesRestantes ?></strong> places restantes sur <?= $event['places'] ?></p>
                        <div class="progress"><span style="width: <?= $pourcentage ?>%"></span></div>

                        <?php if (!isset($_SESSION['user'])): ?>
                            <a class="btn btn-block" href="connexion.php">Connectez-vous pour vous inscrire</a>
                        <?php elseif ($isUserInscribed): ?>
                            <p class="muted">Vous êtes inscrit à cet événement.</p>
                            <form method="post">
                                <input type="hidden" name="action" value="desinscrire">
                                <button type="submit" class="btn btn-secondary btn-block">Se désinscrire</button>
                            </form>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="action" value="inscrire">
                                <button type="submit" class="btn btn-block">S'inscrire</button>
                            </form>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="places"><span class="badge badge-full">Complet</span></p>
                        <button class="btn btn-block" disabled>Complet</button>
                    <?php endif; ?>
                </aside>
            </div>
        </article>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>