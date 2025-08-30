<?php
$title = 'Tableau de bord QG';
include_once 'views/includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list"></i> Validations Quality Gate
        </h1>
    </div>

    <!-- Alerts -->
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $type): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-4">
                <?= $_SESSION[$key] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Validations en attente -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clock me-2"></i> Validations QG en attente
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($pendingApprovals)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Activité</th>
                                <th>BU</th>
                                <th>Itération</th>
                                <th>Type QG</th>
                                <th>Statut</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApprovals as $approval): ?>
                                <tr>
                                    <td><?= htmlspecialchars($approval['Reference_Unique']) ?></td>
                                    <td><?= htmlspecialchars($approval['Name_Activity']) ?></td>
                                    <td><?= htmlspecialchars($approval['Name_BU']) ?></td>
                                    <td class="text-center"><?= $approval['iteration'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $approval['qg_type'] === 'qg1' ? 'info' : 'warning' ?>">
                                            <?= strtoupper($approval['qg_type']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $approval['status'] ?></span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($approval['created_at'])) ?></td>
                                    <td class="text-nowrap">
                                        <a href="index.php?controller=qualityGate&action=review&clc_id=<?= $approval['clc_id'] ?>&iteration=<?= $approval['iteration'] ?>&qg_type=<?= $approval['qg_type'] ?>" 
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Examiner
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i> Aucune validation en attente.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historique des validations -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-history me-2"></i> Historique des validations
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="historyFilter" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter me-1"></i> Filtres
                </button>
                <ul class="dropdown-menu" aria-labelledby="historyFilter">
                    <li><a class="dropdown-item" href="#">30 derniers jours</a></li>
                    <li><a class="dropdown-item" href="#">3 derniers mois</a></li>
                    <li><a class="dropdown-item" href="#">Tout l'historique</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="historyTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Activité</th>
                            <th>Type QG</th>
                            <th>Itération</th>
                            <th>Statut</th>
                            <th>Validé par</th>
                            <th>Date validation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les données d'historique seraient ajoutées ici -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialisation de DataTable pour l'historique
    $('#historyTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [[6, 'desc']],
        responsive: true,
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
        }
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>