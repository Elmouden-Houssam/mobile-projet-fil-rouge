<?php
require_once "connexion.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch article with category, user and bids info
$stmt = $pdo->prepare("
    SELECT a.*, 
           t.nom_type, 
           u.nom AS nom_vendeur, 
           u.email AS email_vendeur,
           (SELECT MAX(o.montant) FROM offre o WHERE o.id_article = a.id_article) AS offre_actuelle,
           (SELECT COUNT(*) FROM offre o WHERE o.id_article = a.id_article) AS nb_offres
    FROM article a
    LEFT JOIN type_lot t ON a.id_type = t.id_type
    LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
    WHERE a.id_article = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: index.php");
    exit;
}

$now = new DateTime();
$end = new DateTime($article['date_fin']);
$start = new DateTime($article['date_debut']);
$isEnded = $now >= $end;
$endTimestamp = $end->format('c');

$error = null;

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

// Handle new bid submission
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'bid') {
    $montant = isset($_POST['montant']) ? (float)$_POST['montant'] : 0;
    $minBid = !empty($article['offre_actuelle']) ? (float)$article['offre_actuelle'] : (float)$article['prix_depart'];

    if ($isEnded) {
        $error = "Cette enchère est terminée. Il n'est plus possible de soumettre une offre.";
    } elseif ($montant <= $minBid) {
        $error = "Votre offre doit être strictement supérieure à " . number_format($minBid, 2, ',', ' ') . " DH.";
    } else {
        $stmtInsert = $pdo->prepare("
            INSERT INTO offre (montant, date_offre, id_utilisateur, id_article)
            VALUES (?, NOW(), 1, ?)
        ");
        $stmtInsert->execute([$montant, $id]);

        header("Location: article.php?id=" . $id . "&success=bid");
        exit;
    }
}

// Fetch offer history
$stmtOffers = $pdo->prepare("
    SELECT o.*, u.nom AS nom_encherisseur
    FROM offre o
    LEFT JOIN utilisateur u ON o.id_utilisateur = u.id_utilisateur
    WHERE o.id_article = ?
    ORDER BY o.montant DESC, o.date_offre DESC
");
$stmtOffers->execute([$id]);
$offres = $stmtOffers->fetchAll(PDO::FETCH_ASSOC);

// Refresh current bid
$currentBid = !empty($article['offre_actuelle']) ? (float)$article['offre_actuelle'] : (float)$article['prix_depart'];
$minNextBid = $currentBid + 10;

$success = isset($_GET['success']) && $_GET['success'] === 'bid' ? 'Votre offre a été enregistrée avec succès !' : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre']) ?> — Plateforme d'Enchères</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Toast message -->
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

<main class="detail-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">← Retour aux enchères</a>
        <span class="breadcrumb-separator">/</span>
        <span><?= htmlspecialchars($article['titre']) ?></span>
    </nav>

    <?php if ($error): ?>
    <div class="toast toast-error" style="position:static; margin-bottom:24px;">
        <span>✕</span>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="detail-grid">
        <!-- Left Column: Full Image & Description -->
        <div class="detail-media">
            <div class="detail-image-box">
                <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['titre']) ?>" class="detail-image">
                    <a href="<?= htmlspecialchars($article['image']) ?>" target="_blank" class="detail-image-zoom-btn">
                        🔍 Voir l'image en taille réelle
                    </a>
                <?php else: ?>
                    <div class="detail-image-placeholder">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                        <p style="margin-top:12px; color:var(--text-muted);">Aucune image fournie</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Description Card -->
            <div class="detail-card" style="margin-top: 24px;">
                <h3 class="detail-card-title">Description détaillée</h3>
                <div class="detail-description-text">
                    <?php if (!empty($article['description'])): ?>
                        <?= nl2br(htmlspecialchars($article['description'])) ?>
                    <?php else: ?>
                        <p style="color:var(--text-muted); font-style:italic;">Aucune description renseignée pour cet article.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Info, Countdown, Pricing, Bids -->
        <div class="detail-info">
            <div class="detail-card">
                <!-- Status and Category Badges -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px; gap:8px;">
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

                <h1 class="detail-title"><?= htmlspecialchars($article['titre']) ?></h1>

                <div class="detail-meta-list">
                    <?php if (!empty($article['nom_type'])): ?>
                    <div class="detail-meta-item">
                        <span class="meta-label">Catégorie :</span>
                        <span class="meta-value"><?= getCategoryIcon($article['nom_type'], $categoryIcons) ?> <?= htmlspecialchars($article['nom_type']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($article['nom_vendeur'])): ?>
                    <div class="detail-meta-item">
                        <span class="meta-label">Vendeur :</span>
                        <span class="meta-value"><?= htmlspecialchars($article['nom_vendeur']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="detail-meta-item">
                        <span class="meta-label">Début :</span>
                        <span class="meta-value"><?= date('d/m/Y à H:i', strtotime($article['date_debut'])) ?></span>
                    </div>

                    <div class="detail-meta-item">
                        <span class="meta-label">Fin :</span>
                        <span class="meta-value"><?= date('d/m/Y à H:i', strtotime($article['date_fin'])) ?></span>
                    </div>
                </div>

                <!-- Prominent Countdown Box -->
                <div class="detail-countdown-card">
                    <span class="detail-countdown-title">TEMPS RESTANT</span>
                    <?php if ($isEnded): ?>
                        <div class="countdown-ended" style="font-size:1.1rem; padding:16px;">Cette enchère a pris fin</div>
                    <?php else: ?>
                        <div class="countdown" data-end-date="<?= $endTimestamp ?>" style="margin-bottom:0;">
                            <div class="countdown-timer" style="justify-content:center;">
                                <div class="countdown-unit" style="padding:10px 14px; min-width:64px;">
                                    <span class="countdown-value" data-unit="days" style="font-size:1.5rem;">--</span>
                                    <span class="countdown-suffix">Jours</span>
                                </div>
                                <div class="countdown-unit" style="padding:10px 14px; min-width:64px;">
                                    <span class="countdown-value" data-unit="hours" style="font-size:1.5rem;">--</span>
                                    <span class="countdown-suffix">Heures</span>
                                </div>
                                <div class="countdown-unit" style="padding:10px 14px; min-width:64px;">
                                    <span class="countdown-value" data-unit="minutes" style="font-size:1.5rem;">--</span>
                                    <span class="countdown-suffix">Min</span>
                                </div>
                                <div class="countdown-unit" style="padding:10px 14px; min-width:64px;">
                                    <span class="countdown-value" data-unit="seconds" style="font-size:1.5rem;">--</span>
                                    <span class="countdown-suffix">Sec</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Price Box -->
                <div class="detail-price-box">
                    <div class="detail-price-row">
                        <div>
                            <span class="price-label">Prix de départ</span>
                            <div class="price-value" style="font-size:1.2rem;"><?= number_format($article['prix_depart'], 2, ',', ' ') ?> DH</div>
                        </div>
                        <div style="text-align: right;">
                            <span class="price-label"><?= !empty($article['offre_actuelle']) ? 'Offre la plus haute' : 'Offre actuelle' ?></span>
                            <div class="price-value current-bid" style="font-size:1.75rem;">
                                <?= !empty($article['offre_actuelle']) ? number_format($article['offre_actuelle'], 2, ',', ' ') . ' DH' : 'Aucune offre' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bid Form (if active) -->
                <?php if (!$isEnded): ?>
                <div class="bid-action-box">
                    <h3 style="font-size:1rem; margin-bottom:10px;">Participer à l'enchère</h3>
                    <form method="POST" action="article.php?id=<?= $article['id_article'] ?>" class="bid-form">
                        <input type="hidden" name="action" value="bid">
                        <div class="bid-input-group">
                            <input type="number" 
                                   step="0.01" 
                                   min="<?= htmlspecialchars($minNextBid) ?>" 
                                   name="montant" 
                                   placeholder="Ex: <?= htmlspecialchars($minNextBid) ?>" 
                                   value="<?= htmlspecialchars($minNextBid) ?>" 
                                   required 
                                   class="bid-input">
                            <span class="bid-currency">DH</span>
                            <button type="submit" class="btn btn-primary btn-lg" style="white-space: nowrap;">
                                🔨 Placer l'offre
                            </button>
                        </div>
                        <p class="bid-help-text">
                            L'offre doit être supérieure à <?= number_format($currentBid, 2, ',', ' ') ?> DH.
                        </p>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Actions: Modify & Delete -->
                <div class="detail-actions">
                    <a href="modifier.php?id=<?= $article['id_article'] ?>" class="btn btn-secondary">
                        ✏️ Modifier l'article
                    </a>
                    <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $article['id_article'] ?>, '<?= addslashes(htmlspecialchars($article['titre'])) ?>')">
                        🗑️ Supprimer
                    </button>
                </div>
            </div>

            <!-- Bids History Card -->
            <div class="detail-card" style="margin-top:24px;">
                <h3 class="detail-card-title">
                    Historique des offres
                    <span class="badge" style="background:var(--background); color:var(--text); font-size:0.75rem; vertical-align:middle; margin-left:8px;">
                        <?= count($offres) ?> offre<?= count($offres) > 1 ? 's' : '' ?>
                    </span>
                </h3>

                <?php if (empty($offres)): ?>
                    <p style="color:var(--text-muted); font-size:0.9rem; padding:16px 0;">
                        Aucune offre n'a encore été proposée pour cet article.
                    </p>
                <?php else: ?>
                    <div class="bids-list">
                        <?php foreach ($offres as $index => $o): ?>
                        <div class="bid-item <?= $index === 0 ? 'bid-item-highest' : '' ?>">
                            <div class="bid-item-user">
                                <span class="bid-user-avatar">👤</span>
                                <div>
                                    <div class="bid-user-name">
                                        <?= htmlspecialchars($o['nom_encherisseur'] ?? 'Enchérisseur') ?>
                                        <?php if ($index === 0): ?>
                                            <span class="badge badge-active" style="padding:2px 8px; font-size:0.65rem;">Meilleure offre</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="bid-item-date">
                                        <?= date('d/m/Y à H:i:s', strtotime($o['date_offre'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="bid-item-amount">
                                <?= number_format($o['montant'], 2, ',', ' ') ?> DH
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="footer">
    <p>© 2026 Plateforme d'Enchères — Tous droits réservés</p>
</footer>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Supprimer cet article ?</h3>
        <p class="modal-text">Cette action est irréversible. L'article ainsi que toutes ses offres associées seront définitivement supprimés.</p>
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

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// Toast dismiss
var toast = document.getElementById('toast');
if (toast) {
    setTimeout(function() {
        toast.classList.add('fade-out');
        setTimeout(function() { toast.remove(); }, 300);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', initCountdowns);
</script>

</body>
</html>
