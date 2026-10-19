<?php
require 'config.php';
// Récupération de la catégorie filtrée (si elle existe dans l'URL)
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : null;
// Requête de base : événements validés et futurs
if ($categorie) {
    $sql = "SELECT * FROM event WHERE is_validated = 1 AND date > NOW() AND category = ? ORDER BY date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$categorie]);
} else {
    $sql = "SELECT * FROM event WHERE is_validated = 1 AND date > NOW() ORDER BY date ASC";
    $stmt = $pdo->query($sql);
}
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements à venir · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.php">Agenda<span>.</span></a>
            <nav class="nav">
                <a href="index.php">Événements</a>

                <!-- Liens réservés aux administrateurs -->
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="validation.php">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Validation
                    </a>
                    <a href="inscrits.php">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M19 14a5 5 0 0 1 3 5v2"/></svg>
                        Inscrits
                    </a>
                <?php endif; ?>

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
<section class="hero" style="background-image: url('images/rock.jpg')">
        <div class="hero-content">
            <span class="hero-kicker">Agenda local</span>
            <h1>Les événements près de chez vous</h1>
            <p>Concerts, conférences, ateliers et sport : trouvez votre prochaine sortie ou proposez la vôtre.</p>
            <a class="btn" href="ajouter.php">+ Ajouter un événement</a>
        </div>
    </section>
 <div class="section-head">
        <h2>À venir</h2>
        <ul class="legend">
            <li><a class="<?= !$categorie ? 'active' : '' ?>" href="index.php">Toutes</a></li>
            <li class="cat-concert"><a class="<?= $categorie === 'concert' ? 'active' : '' ?>" href="index.php?categorie=concert">Concert</a></li>
            <li class="cat-conference"><a class="<?= $categorie === 'conference' ? 'active' : '' ?>" href="index.php?categorie=conference">Conférence</a></li>
            <li class="cat-atelier"><a class="<?= $categorie === 'atelier' ? 'active' : '' ?>" href="index.php?categorie=atelier">Atelier</a></li>
            <li class="cat-sport"><a class="<?= $categorie === 'sport' ? 'active' : '' ?>" href="index.php?categorie=sport">Sport</a></li>
        </ul>
    </div>
  <?php if (empty($events)): ?>
        <p class="empty">Aucun événement à venir pour le moment.</p>
    <?php else: ?>
        <ul class="event-list">
            <?php foreach ($events as $event): ?>
                <li class="event-card cat-<?= htmlspecialchars($event['category']) ?>">
                    <div class="event-media">
                        <img class="event-image" src="<?= htmlspecialchars($event['image'] ? $event['image'] : 'images/concert.jpg') ?>" alt="">
                        <div class="event-date">
                            <span class="day"><?= date('d', strtotime($event['date'])) ?></span>
                            <span class="month"><?= date('M', strtotime($event['date'])) ?></span>
                        </div>
                        <span class="badge"><?= ucfirst(htmlspecialchars($event['category'])) ?></span>
                    </div>
                    <h3 class="event-title">
                        <a href="evenement.php?id=<?= $event['id'] ?>">
                            <?= htmlspecialchars($event['title']) ?>
                        </a>
                    </h3>
                    <span class="muted"><?= date('d/m/Y · H\hi', strtotime($event['date'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    </main>
  <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>                                                                                                                                       