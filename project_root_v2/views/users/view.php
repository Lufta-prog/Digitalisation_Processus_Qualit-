<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user me-2"></i> Détails de l'Utilisateur
    </h1>
    <div>
        <a href="index.php?controller=users&action=edit&id=<?= $user['ID_User'] ?>" class="btn btn-warning me-2">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <a href="index.php?controller=users" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- User Information Cards -->
<div class="row">
    <!-- User Profile Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 primary-gradient">
                <h6 class="m-0 font-weight-bold text-white">Profil Utilisateur</h6>
            </div>
            <div class="card-body text-center">
                <div class="avatar bg-primary rounded-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <div class="avatar-content text-white">
                        <?= strtoupper(substr($user['Fname_User'], 0, 1) . substr($user['Lname_User'], 0, 1)) ?>
                    </div>
                </div>
                <h4 class="mb-0"><?= htmlspecialchars($user['Fname_User'] . ' ' . $user['Lname_User']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($user['Email_User']) ?></p>
                
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-<?= $user['User_Level'] === 'Manager' ? 'primary' : ($user['User_Level'] === 'Pilot' ? 'info' : 'secondary') ?> mx-1 p-2">
                        <?= htmlspecialchars($user['User_Level']) ?>
                    </span>
                    <span class="badge bg-<?= $user['User_Type'] === 'Admin' ? 'danger' : 'secondary' ?> mx-1 p-2">
                        <?= htmlspecialchars($user['User_Type']) ?>
                    </span>
                </div>
                
                <div class="border-top pt-3 mt-3">
                    <div class="row text-start">
                        <div class="col-6 mb-2">
                            <strong>ID:</strong>
                        </div>
                        <div class="col-6 mb-2">
                            <?= $user['ID_User'] ?>
                        </div>
                        
                        <div class="col-6 mb-2">
                            <strong>Créé le:</strong>
                        </div>
                        <div class="col-6 mb-2">
                            <?= date('d/m/Y', strtotime($user['Created_At'])) ?>
                        </div>
                        
                        <div class="col-6 mb-2">
                            <strong>Modifié le:</strong>
                        </div>
                        <div class="col-6 mb-2">
                            <?= date('d/m/Y', strtotime($user['Updated_At'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Professional Information Card -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">Informations Professionnelles</h6>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary">Fonction</h6>
                        <p class="mb-0">
                            <?php if (!empty($user['Name_Function'])): ?>
                                <strong><?= htmlspecialchars($user['Name_Function']) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Activité</h6>
                        <p class="mb-0">
                            <?php if (!empty($user['Name_Activity'])): ?>
                                <?= htmlspecialchars($user['Name_Activity']) ?>
                            <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Business Unit</h6>
                        <p class="mb-0">
                            <?php if (!empty($user['Business_Unit'])): ?>
                                <?= htmlspecialchars($user['Business_Unit']) ?>
                            <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Niveau d'accès</h6>
                        <p class="mb-0">
                            <?php if ($user['User_Level'] === 'Manager'): ?>
                                <span class="badge bg-primary">Manager</span>
                                <small class="d-block mt-1">Accès complet aux fonctionnalités</small>
                            <?php elseif ($user['User_Level'] === 'Pilot'): ?>
                                <span class="badge bg-info">Pilote</span>
                                <small class="d-block mt-1">Gestion des livrables</small>
                            <?php else: ?>
                                <span class="badge bg-secondary">Consultant</span>
                                <small class="d-block mt-1">Accès aux livrables assignés</small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Stats Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">Statistiques Utilisateur</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                            <h1 class="display-4 text-primary mb-0">0</h1>
                            <p class="mb-0">Livrables assignés</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                            <h1 class="display-4 text-success mb-0">0</h1>
                            <p class="mb-0">Livrables complétés</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                            <h1 class="display-4 text-warning mb-0">0</h1>
                            <p class="mb-0">En cours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-dark text-white">
        <h6 class="m-0 font-weight-bold">Actions</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="index.php?controller=users&action=edit&id=<?= $user['ID_User'] ?>" class="btn btn-warning btn-block">
                    <i class="fas fa-edit me-2"></i>Modifier l'utilisateur
                </a>
            </div>
            <?php if ($user['ID_User'] != $_SESSION['user_id']): ?>
                <div class="col-md-3 mb-3">
                    <a href="#" class="btn btn-danger btn-block delete-confirm"
                       data-name="<?= htmlspecialchars($user['Fname_User'] . ' ' . $user['Lname_User']) ?>"
                       data-href="index.php?controller=users&action=delete&id=<?= $user['ID_User'] ?>">
                        <i class="fas fa-trash me-2"></i>Supprimer l'utilisateur
                    </a>
                </div>
            <?php endif; ?>
            <div class="col-md-3 mb-3">
                <a href="index.php?controller=delivrables&filter=user&id=<?= $user['ID_User'] ?>" class="btn btn-info btn-block">
                    <i class="fas fa-clipboard-list me-2"></i>Voir les livrables
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="index.php?controller=users" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmation pour la suppression
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
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .btn-block {
        display: block;
        width: 100%;
    }
</style>

<?php include_once 'views/includes/footer.php'; ?>