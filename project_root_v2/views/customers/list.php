<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-users me-2"></i> Gestion des Clients
    </h1>
    <div>
        <a href="index.php?controller=customers&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nouveau Client
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list me-2"></i> Liste des Clients
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="customersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Secteur d'activité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= htmlspecialchars($customer['Name_Customer']) ?></td>
                        <td><?= htmlspecialchars($customer['Industry_Name'] ?? 'Non spécifié') ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="index.php?controller=customers&action=edit&id=<?= $customer['ID_Customer'] ?>" 
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="index.php?controller=customers&action=delete&id=<?= $customer['ID_Customer'] ?>" 
                                      method="POST" 
                                      style="display:inline;"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?controller=customers&page=<?= $pagination['page'] - 1 ?>&perPage=<?= $pagination['perPage'] ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?controller=customers&page=<?= $i ?>&perPage=<?= $pagination['perPage'] ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?controller=customers&page=<?= $pagination['page'] + 1 ?>&perPage=<?= $pagination['perPage'] ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="text-center text-muted">
                Affichage des clients <?= ($pagination['page'] - 1) * $pagination['perPage'] + 1 ?> 
                à <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> 
                sur <?= $pagination['total'] ?> au total
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        order: [[0, 'asc']],
        paging: false,
        info: false
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>