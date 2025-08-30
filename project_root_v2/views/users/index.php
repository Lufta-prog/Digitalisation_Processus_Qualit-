<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-users me-2"></i> Gestion des Utilisateurs
    </h1>
    <div>
        <a href="index.php?controller=users&action=create" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i> Nouvel Utilisateur
        </a>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row mb-4">
    <!-- Total Users -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Utilisateurs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['totalUsers'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users by Level -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Consultants</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['usersByLevel']['Consultant'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrateurs -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Administrateurs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['usersByLevel']['Admin'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list me-2"></i> Liste des Utilisateurs
        </h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-white"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Actions:</div>
                <a class="dropdown-item" href="index.php?controller=users&action=create">
                    <i class="fas fa-user-plus fa-sm fa-fw me-2 text-gray-400"></i> Ajouter un utilisateur
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="exportUsersExcel">
                    <i class="fas fa-file-excel fa-sm fa-fw me-2 text-gray-400"></i> Exporter vers Excel
                </a>
                <a class="dropdown-item" href="#" id="exportUsersCSV">
                    <i class="fas fa-file-csv fa-sm fa-fw me-2 text-gray-400"></i> Exporter vers CSV
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Fonction</th>
                        <th>BU</th>
                        <th>Niveau</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['ID_User'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2 bg-primary rounded-circle">
                                        <div class="avatar-content text-white">
                                            <?= strtoupper(substr($user['Fname_User'], 0, 1) . substr($user['Lname_User'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <?= htmlspecialchars($user['Fname_User'] . ' ' . $user['Lname_User']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['Email_User']) ?></td>
                            <td><?= htmlspecialchars($user['Name_Function'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['Business_Unit'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($user['User_Level'] === 'Manager'): ?>
                                    <span class="badge bg-primary">Manager</span>
                                <?php elseif ($user['User_Level'] === 'Pilot'): ?>
                                    <span class="badge bg-info">Pilot</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Consultant</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['User_Type'] === 'Admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="index.php?controller=users&action=view&id=<?= $user['ID_User'] ?>" class="btn btn-sm btn-info" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?controller=users&action=edit&id=<?= $user['ID_User'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['ID_User'] != $_SESSION['user_id']): ?>
                                        <a href="#" class="btn btn-sm btn-danger delete-confirm" 
                                           data-name="<?= htmlspecialchars($user['Fname_User'] . ' ' . $user['Lname_User']) ?>"
                                           data-href="index.php?controller=users&action=delete&id=<?= $user['ID_User'] ?>" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Vous ne pouvez pas supprimer votre propre compte">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-primary',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-sm btn-secondary',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }
        ]
    });

    $('.delete-confirm').on('click', function(e) {
        e.preventDefault();
        
        const name = $(this).data('name');
        const url = $(this).data('href');
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${name}" ?`)) {
            window.location.href = url;
        }
    });
});
</script>

<style>
    .avatar {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>

<?php include_once 'views/includes/footer.php'; ?>