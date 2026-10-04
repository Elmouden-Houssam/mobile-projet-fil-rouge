<?php
require_once "connexion.php";

$error = null;
$categories = $pdo->query("SELECT * FROM type_lot ORDER BY id_type ASC")->fetchAll(PDO::FETCH_ASSOC);

$categoryIcons = [
    'Électronique' => '⚡',
    'Téléphones'   => '📱',
    'Ordinateurs'  => '💻',
    'Voitures'     => '🚗',
    'Mode'         => '👗',
    'Bijoux'       => '💎',
    'Maison'       => '🏠',
    'Jeux vidéo'   => '🎮',
    'Sport'        => '⚽',
    'Art'          => '🎨',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $imagePath = null;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.");
            }

            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("L'image ne doit pas dépasser 5 Mo.");
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('article_') . '.' . $extension;
            $imagePath = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                throw new Exception("Erreur lors de l'upload de l'image.");
            }
        }

        $id_type = isset($_POST["id_type"]) && !empty($_POST["id_type"]) ? (int)$_POST["id_type"] : 1;

        $sql = "INSERT INTO article 
                (titre, description, prix_depart, date_debut, date_fin, image, id_utilisateur, id_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["titre"],
            $_POST["description"],
            $_POST["prix_depart"],
            $_POST["date_debut"],
            $_POST["date_fin"],
            $imagePath,
            1,
            $id_type
        ]);

        header("Location: index.php?success=added");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article — Plateforme d'Enchères</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-inner">
        <a href="index.php" class="logo">◆ Plateforme d'Enchères</a>
        <nav class="nav">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="ajouter.php" class="btn btn-primary">+ Ajouter un article</a>
        </nav>
    </div>
</header>

<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Ajouter un article</h1>
        <p class="form-subtitle">Remplissez les informations pour mettre un article aux enchères.</p>

        <?php if ($error): ?>
        <div class="toast toast-error" style="position:static; margin-bottom:20px;">
            <span>✕</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" placeholder="Titre de l'article" required 
                       value="<?= isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="id_type">Catégorie</label>
                <select id="id_type" name="id_type" required>
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id_type'] ?>" <?= (isset($_POST['id_type']) && $_POST['id_type'] == $cat['id_type']) ? 'selected' : '' ?>>
                            <?= (isset($categoryIcons[$cat['nom_type']]) ? $categoryIcons[$cat['nom_type']] . ' ' : '🏷️ ') . htmlspecialchars($cat['nom_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Décrivez votre article..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="prix_depart">Prix de départ (DH)</label>
                <input type="number" step="0.01" min="0" id="prix_depart" name="prix_depart" placeholder="0.00" required
                       value="<?= isset($_POST['prix_depart']) ? htmlspecialchars($_POST['prix_depart']) : '' ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_debut">Date de début</label>
                    <input type="datetime-local" id="date_debut" name="date_debut" required
                           value="<?= isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="date_fin">Date de fin</label>
                    <input type="datetime-local" id="date_fin" name="date_fin" required
                           value="<?= isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Image</label>
                <div class="image-upload">
                    <div class="image-upload-area" onclick="document.getElementById('image').click()">
                        <div class="image-upload-icon">📷</div>
                        <div class="image-upload-text">Cliquez pour choisir une image</div>
                        <div class="image-upload-text" style="font-size:0.75rem; margin-top:4px;">JPG, PNG, GIF, WEBP — Max 5 Mo</div>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview" style="display:none">
                        <img id="imagePreviewImg" src="" alt="Aperçu">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-neutral">Annuler</a>
                <button type="submit" class="btn btn-primary">Ajouter l'article</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <p>© 2026 Plateforme d'Enchères — Tous droits réservés</p>
</footer>

<script>
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    var previewImg = document.getElementById('imagePreviewImg');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
        previewImg.src = '';
    }
}
</script>

</body>
</html>