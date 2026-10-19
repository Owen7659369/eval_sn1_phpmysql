<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['date'] ?? '';
    $information = trim($_POST['information'] ?? '');
    $category = $_POST['category'] ?? 'concert';
    $places = intval($_POST['places'] ?? 0);
    $image = null;

    // Vérifications exigées par le sujet (en PHP)
    if (empty($title) || empty($date) || empty($information)) {
        $error = "Le titre, la date et les informations sont obligatoires.";
    } elseif ($places <= 0) {
        $error = "Le nombre de places doit être supérieur à 0.";
    } else {
        // Gestion de l'upload d'image (fourni dans les astuces du sujet)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $extensionsAutorisees)) {
                // Créer le dossier uploads s'il n'existe pas
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $image = 'uploads/' . uniqid() . '.' . $extension;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $image);
            } else {
                $error = "L'image doit être au format JPG, PNG ou WebP.";
            }
        }

        if (empty($error)) {
            // Insertion avec is_validated = 0 (non validé par défaut)
            $sql = "INSERT INTO event (title, date, information, category, places, image, is_validated, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $date, $information, $category, $places, $image]);

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
    <title>Ajouter un événement · Agenda</title>
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
        <div class="narrow">
            <a class="back-link" href="index.php">← Retour aux événements</a>

            <div class="card">
                <h1>Ajouter un événement</h1>
                <p class="muted">Votre événement sera visible après validation par un administrateur.</p>

                <?php if ($error): ?>
                    <div class="flash flash-error"><?= $error ?></div>
                <?php endif; ?>

                <form class="form" method="post" enctype="multipart/form-data">
                    <div>
                        <label for="title" class="required">Titre</label>
                        <input type="text" id="title" name="title" placeholder="Ex. : Soirée jazz au Kiosque" required>
                    </div>

                    <div>
                        <label for="date" class="required">Date</label>
                        <input type="datetime-local" id="date" name="date" required>
                    </div>

                    <div>
                        <label for="information" class="required">Informations</label>
                        <textarea id="information" name="information" placeholder="Lieu, programme, tarif..." required></textarea>
                    </div>

                    <div>
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="concert">Concert</option>
                            <option value="conference">Conférence</option>
                            <option value="atelier">Atelier</option>
                            <option value="sport">Sport</option>
                        </select>
                    </div>

                    <div>
                        <label for="places">Nombre de places</label>
                        <input type="number" id="places" name="places" min="1" value="20">
                    </div>

                    <div>
                        <label for="image">Image à la une</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="help-text">JPG, PNG ou WebP.</div>
                    </div>

                    <button type="submit">Enregistrer l'événement</button>
                </form>
            </div>
        </div>
    </main>
    <footer class="site-footer">Agenda · Projet de formation PHP / MySQL</footer>
</body>
</html>