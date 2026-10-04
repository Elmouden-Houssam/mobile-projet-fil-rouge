<?php
require_once "connexion.php";

$categories = $pdo->query("SELECT * FROM type_lot ORDER BY id_type ASC")->fetchAll(PDO::FETCH_ASSOC);

$selectedCategory = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

$whereClause = "";
$params = [];
if ($selectedCategory > 0) {
    $whereClause = "WHERE a.id_type = ?";
    $params[] = $selectedCategory;
}

// Get articles with highest bid and category name
$sql = "SELECT a.*, t.nom_type,
        (SELECT MAX(o.montant) FROM offre o WHERE o.id_article = a.id_article) AS offre_actuelle
        FROM article a 
        LEFT JOIN type_lot t ON a.id_type = t.id_type
        $whereClause
        ORDER BY a.id_article DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Category icons mapping
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

function getCategoryIcon($name, $icons) {
    return isset($icons[$name]) ? $icons[$name] : '🏷️';
}

// Success messages
$successMessages = [
    'added' => 'Article ajouté avec succès.',
    'modified' => 'Article modifié avec succès.',
    'deleted' => 'Article supprimé avec succès.'
];
$success = isset($_GET['success']) && isset($successMessages[$_GET['success']]) ? $successMessages[$_GET['success']] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme d'Enchères</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Toast message (if success) -->
<?php if ($success): ?>
<div class="toast toast-success" id="toast">
    <span>✓</span>
    <span><?= htmlspecialchars($success) ?></span>
</div>
<?php endif; ?>

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

<!-- Hero -->
<section class="hero">
    <h2>Découvrez les articles aux enchères</h2>
    <p>Parcourez les enchères en cours et trouvez des articles exceptionnels</p>
</section>

<!-- Category Filter Pills -->
<div class="category-filters">
    <a href="index.php" class="category-pill <?= $selectedCategory === 0 ? 'active' : '' ?>">🏷️ Toutes les catégories</a>
    <?php foreach ($categories as $cat): ?>
        <a href="index.php?categorie=<?= $cat['id_type'] ?>" class="category-pill <?= $selectedCategory === (int)$cat['id_type'] ? 'active' : '' ?>">
            <span><?= getCategoryIcon($cat['nom_type'], $categoryIcons) ?></span>
            <span><?= htmlspecialchars($cat['nom_type']) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Articles Grid OR Empty State -->
<?php if (empty($articles)): ?>
<div class="empty-state">
    <div class="empty-state-icon">🏷️</div>
    <h3>Aucune enchère disponible</h3>
    <p><?= $selectedCategory > 0 ? 'Aucun article trouvé dans cette catégorie.' : 'Soyez le premier à mettre un article aux enchères.' ?></p>
    <?php if ($selectedCategory > 0): ?>
        <a href="index.php" class="btn btn-secondary" style="margin-right:8px;">Voir toutes les catégories</a>
    <?php endif; ?>
    <a href="ajouter.php" class="btn btn-primary btn-lg">+ Ajouter un article</a>
</div>
<?php else: ?>
<div class="articles-grid">
    <?php foreach ($articles as $article): ?>
    <?php
        $now = new DateTime();
        $end = new DateTime($article['date_fin']);
        $isEnded = $now >= $end;
        $endTimestamp = $end->format('c'); // ISO 8601 for JS
    ?>
    <div class="card">
        <!-- Image with link to full article details -->
        <a href="article.php?id=<?= $article['id_article'] ?>" class="card-image-link" title="Voir les détails de <?= htmlspecialchars($article['titre']) ?>">
            <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['titre']) ?>" class="card-image">
            <?php else: ?>
                <div class="card-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
            <?php endif; ?>
        </a>

        <div class="card-body">
            <!-- Status and Category Badges -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px; gap:8px;">
                <?php if (!empty($article['nom_type'])): ?>
                    <span class="badge badge-category"><?= getCategoryIcon($article['nom_type'], $categoryIcons) ?> <?= htmlspecialchars($article['nom_type']) ?></span>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <?php if ($isEnded): ?>
                    <span class="badge badge-ended">Enchère terminée</span>
                <?php else: ?>
                    <span class="badge badge-active">En cours</span>
                <?php endif; ?>
            </div>

            <!-- Title (clickable) -->
            <h3 class="card-title">
                <a href="article.php?id=<?= $article['id_article'] ?>" class="card-title-link">
                    <?= htmlspecialchars($article['titre']) ?>
                </a>
            </h3>

            <!-- Description -->
            <p class="card-description"><?= htmlspecialchars($article['description']) ?></p>

            <!-- Price Section -->
            <div class="price-section">
                <div class="price-block">
                    <span class="price-label">Prix de départ</span>
                    <span class="price-value"><?= number_format($article['prix_depart'], 2, ',', ' ') ?> DH</span>
                </div>
                <?php if ($article['offre_actuelle']): ?>
                <div class="price-block">
                    <span class="price-label">Offre actuelle</span>
                    <span class="price-value current-bid"><?= number_format($article['offre_actuelle'], 2, ',', ' ') ?> DH</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Countdown -->
            <?php if ($isEnded): ?>
                <div class="countdown">
                    <div class="countdown-ended">Enchère terminée</div>
                </div>
            <?php else: ?>
                <div class="countdown" data-end-date="<?= $endTimestamp ?>">
                    <div class="countdown-label">Temps restant</div>
                    <div class="countdown-timer">
                        <div class="countdown-unit">
                            <span class="countdown-value" data-unit="days">--</span>
                            <span class="countdown-suffix">J</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-value" data-unit="hours">--</span>
                            <span class="countdown-suffix">H</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-value" data-unit="minutes">--</span>
                            <span class="countdown-suffix">M</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-value" data-unit="seconds">--</span>
                            <span class="countdown-suffix">S</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="card-actions">
                <a href="article.php?id=<?= $article['id_article'] ?>" class="btn btn-primary btn-sm">👁️ Détails</a>
                <a href="modifier.php?id=<?= $article['id_article'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?= $article['id_article'] ?>, '<?= addslashes(htmlspecialchars($article['titre'])) ?>')">🗑️</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="footer">
    <p>© 2026 Plateforme d'Enchères — Tous droits réservés</p>
</footer>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Supprimer cet article ?</h3>
        <p class="modal-text">Cette action est irréversible. L'article sera définitivement supprimé.</p>
        <div class="modal-actions">
            <button class="btn btn-neutral" onclick="closeModal()">Annuler</button>
            <form id="deleteForm" method="POST" action="supprimer.php" style="display:inline;">
                <input type="hidden" name="id" id="deleteArticleId">
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<script>
// === Countdown System ===
function initCountdowns() {
    const countdowns = document.querySelectorAll('.countdown[data-end-date]');
    
    countdowns.forEach(function(el) {
        const endDate = new Date(el.getAttribute('data-end-date')).getTime();
        
        function updateCountdown() {
            const now = Date.now();
            const diff = endDate - now;
            
            if (diff <= 0) {
                el.innerHTML = '<div class="countdown-ended">Enchère terminée</div>';
                // Update badge
                const card = el.closest('.card');
                if (card) {
                    const badge = card.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge badge-ended';
                        badge.textContent = 'Enchère terminée';
                    }
                }
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const daysEl = el.querySelector('[data-unit="days"]');
            const hoursEl = el.querySelector('[data-unit="hours"]');
            const minutesEl = el.querySelector('[data-unit="minutes"]');
            const secondsEl = el.querySelector('[data-unit="seconds"]');
            
            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
            
            // Add urgent class when less than 1 hour
            const timerEl = el.querySelector('.countdown-timer');
            if (diff < 3600000 && timerEl) {
                timerEl.classList.add('countdown-urgent');
            }
            
            requestAnimationFrame(function() {
                setTimeout(updateCountdown, 1000);
            });
        }
        
        updateCountdown();
    });
}

// === Delete Modal ===
function confirmDelete(id, title) {
    document.getElementById('deleteArticleId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal on overlay click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// === Toast auto-dismiss ===
var toast = document.getElementById('toast');
if (toast) {
    setTimeout(function() {
        toast.classList.add('fade-out');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 4000);
}

// Initialize countdowns on page load
document.addEventListener('DOMContentLoaded', initCountdowns);
</script>

</body>
</html>