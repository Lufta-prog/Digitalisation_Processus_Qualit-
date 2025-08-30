<?php
$pageTitle = "Mes Notifications";
include_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <h2>Mes Notifications</h2>

    <!-- Affichage des messages de succès ou d'erreur -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filtres pour les notifications -->
    <div class="mb-3">
        <form method="GET" action="index.php" class="d-flex align-items-center">
            <input type="hidden" name="controller" value="notification">
            <input type="hidden" name="action" value="index">
            <select name="filter" class="form-select me-2" aria-label="Filtrer les notifications">
                <option value="all" <?= (isset($_GET['filter']) && $_GET['filter'] === 'all') ? 'selected' : '' ?>>Toutes</option>
                <option value="unread" <?= (isset($_GET['filter']) && $_GET['filter'] === 'unread') ? 'selected' : '' ?>>Non lues</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>

    <!-- Bouton pour marquer toutes les notifications comme lues -->
    <div class="mb-3">
        <a href="index.php?controller=notification&action=markAllAsRead" class="btn btn-secondary">
            Marquer toutes comme lues
        </a>
    </div>

    <!-- Liste des notifications -->
    <?php if (empty($notifications)): ?>
        <p>Aucune notification disponible.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <li class="list-group-item <?= $notification['is_read'] ? '' : 'fw-bold' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Icône en fonction du type de notification -->
                        <div class="d-flex align-items-center">
                            <?php if ($notification['type'] === 'info'): ?>
                                <i class="bi bi-info-circle text-primary me-2" aria-hidden="true"></i>
                            <?php elseif ($notification['type'] === 'warning'): ?>
                                <i class="bi bi-exclamation-triangle text-warning me-2" aria-hidden="true"></i>
                            <?php elseif ($notification['type'] === 'error'): ?>
                                <i class="bi bi-x-circle text-danger me-2" aria-hidden="true"></i>
                            <?php else: ?>
                                <i class="bi bi-bell text-secondary me-2" aria-hidden="true"></i>
                            <?php endif; ?>
                            <a href="<?= htmlspecialchars($notification['action_url']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($notification['message']) ?>
                            </a>
                        </div>
                        <small class="text-muted">
                            <?= date("d/m/Y H:i", strtotime($notification['created_at'])) ?>
                        </small>
                    </div>
                    <!-- Bouton pour marquer comme lu -->
                    <?php if (!$notification['is_read']): ?>
                        <div class="mt-2">
                            <a href="index.php?controller=notification&action=markAsRead&id=<?= htmlspecialchars($notification['id']) ?>" class="btn btn-sm btn-outline-success">
                                Marquer comme lu
                            </a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Pagination -->
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <nav aria-label="Pagination des notifications">
                <ul class="pagination justify-content-center mt-4">
                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                        <li class="page-item <?= ($i == $pagination['currentPage']) ? 'active' : '' ?>">
                            <a class="page-link" href="index.php?controller=notification&action=index&page=<?= $i ?>&filter=<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>