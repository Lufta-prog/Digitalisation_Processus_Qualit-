<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-project-diagram me-2"></i> Gestion des Projets
    </h1>
    <div>
        <a href="index.php?controller=projects&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nouveau Projet
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list me-2"></i> Liste des Projets
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="projectsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Client</th>
                        <th>Code Contrat</th>
                        <th>Type</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['Name_Project']) ?></td>
                        <td><?= htmlspecialchars($project['Name_Customer']) ?></td>
                        <td><?= htmlspecialchars($project['contract_code']) ?></td>
                        <td><?= $project['Project_Level'] == 'FO' ? 'Front Office' : 'Back Office' ?></td>
                        <td><?= date('d/m/Y', strtotime($project['Starting_Date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($project['Expected_Ending_Date'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $project['Status_Project'] == 'Active' ? 'success' : 'secondary' ?>">
                                <?= $project['Status_Project'] == 'Active' ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="index.php?controller=projects&action=view&id=<?= $project['ID_Project'] ?>" 
                                   class="btn btn-sm btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="index.php?controller=projects&action=edit&id=<?= $project['ID_Project'] ?>" 
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="index.php?controller=projects&action=delete&id=<?= $project['ID_Project'] ?>" 
                                      method="POST" 
                                      style="display:inline;"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
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
                            <a class="page-link" href="?controller=projects&page=<?= $pagination['page'] - 1 ?>&perPage=<?= $pagination['perPage'] ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?controller=projects&page=<?= $i ?>&perPage=<?= $pagination['perPage'] ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?controller=projects&page=<?= $pagination['page'] + 1 ?>&perPage=<?= $pagination['perPage'] ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="text-center text-muted">
                Affichage des projets <?= ($pagination['page'] - 1) * $pagination['perPage'] + 1 ?> 
                à <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> 
                sur <?= $pagination['total'] ?> au total
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#projectsTable').DataTable({
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