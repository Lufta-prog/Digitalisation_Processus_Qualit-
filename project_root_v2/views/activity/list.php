<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-tasks me-2"></i> Gestion des Activités
    </h1>
    <div>
        <a href="index.php?controller=activity&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nouvelle Activité
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list me-2"></i> Liste des Activités
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="activitiesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Business Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?= htmlspecialchars($activity['Name_Activity']) ?></td>
                        <td><?= htmlspecialchars($activity['Name_BU']) ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="index.php?controller=activity&action=edit&id=<?= $activity['ID_Activity'] ?>" 
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="index.php?controller=activity&action=delete&id=<?= $activity['ID_Activity'] ?>" 
                                      method="POST" 
                                      style="display:inline;"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?');">
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
                            <a class="page-link" href="?controller=activity&page=<?= $pagination['page'] - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?controller=activity&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?controller=activity&page=<?= $pagination['page'] + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="text-center text-muted">
                Affichage des activités <?= ($pagination['page'] - 1) * $pagination['perPage'] + 1 ?> 
                à <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> 
                sur <?= $pagination['total'] ?> au total
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#activitiesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        order: [[1, 'asc']],
        paging: false,
        info: false
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>