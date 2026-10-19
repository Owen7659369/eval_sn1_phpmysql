<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM event WHERE is_validated = 1 ORDER BY date ASC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrits aux événements · Agenda</title>
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
                <h1>Inscrits aux événements</h1>
                <p class="muted">Liste des participants pour chaque événement validé.</p>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <p class="empty">Aucun événement validé pour le moment.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <?php
                $stmtInscrits = $pdo->prepare("SELECT user.email, reservation.created_at FROM reservation JOIN user ON user.id = reservation.user_id WHERE reservation.event_id = ? ORDER BY reservation.created_at ASC");
                $stmtInscrits->execute(array($event['id']));
                $inscrits = $stmtInscrits->fetchAll();
                $nbInscrits = count($inscrits);
                $isComplet = ($nbInscrits >= $event['places']);
                ?>
                <section class="card attendees">
                    <div class="attendees-head">
                        <div>
                            <h2><a href="evenement.php?id=<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a></h2>
                            <span class="badge cat-<?php echo htmlspecialchars($event['category']); ?>"><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                            <span class="muted"><?php echo date('d/m/Y · H\hi', strtotime($event['date'])); ?></span>
                        </div>
                        <p class="places">
                            <?php if ($isComplet): ?>
                                <span class="badge badge-full">Complet</span>
                            <?php endif; ?>
                            <strong><?php echo $nbInscrits; ?></strong> inscrits sur <?php echo $event['places']; ?> places
                        </p>
                    </div>

                    <?php if (empty($inscrits)): ?>
                        <p class="empty">Aucun inscrit pour le moment.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Inscrit le</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscrits as $inscrit): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inscrit['email']); ?></td>
                                            <td><?php echo date('d/m/Y H\i', strtotime($inscrit['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>