<?php
session_start();
require 'config.php';

// Sécurité : réservé aux administrateurs
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Validation d'un événement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $eventId = intval($_POST['id']);
    $stmt = $pdo->prepare("UPDATE event SET is_validated = 1 WHERE id = ?");
    $stmt->execute([$eventId]);
    header('Location: validation.php');
    exit;
}

// Récupérer les événements non validés
$stmt = $pdo->query("SELECT * FROM event WHERE is_validated = 0 ORDER BY date ASC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation des événements · Agenda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.php">Agenda<span>.</span></a>
            <nav class="nav">
                <a href="index.php">Événements</a>
                <a href="validation.php">Validation</a>
                <a href="inscrits.php">Inscrits</a>
                <a class="btn btn-small" href="deconnexion.php">Se déconnecter</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="section-head">
            <div>
                <h1>Événements à valider</h1>
                <p class="muted"><?= count($events) ?> événement(s) en attente de validation.</p>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <p class="empty">Aucun événement en attente 🎉</p>
        <?php else: ?>
            <div class="card table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>Date</th>
                            <th>Catégorie</th>
                            <th>Places</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><a href="evenement.php?id=<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></a></td>
                                <td><?= date('d/m/Y H\hi', strtotime($event['date'])) ?></td>
                                <td><span class="badge cat-<?= htmlspecialchars($event['category']) ?>"><?= ucfirst(htmlspecialchars($event['category'])) ?></span></td>
                                <td><?= $event['places'] ?></td>
                                <td><span class="badge badge-pending">Non validé</span></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-success">Valider</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>