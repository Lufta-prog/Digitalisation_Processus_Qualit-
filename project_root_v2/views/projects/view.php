<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-eye me-2"></i> Détails du Projet
    </h1>
    <div>
        <a href="index.php?controller=projects" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations générales
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Nom du projet</strong></label>
                    <p class="form-control-static"><?= htmlspecialchars($project['Name_Project']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Client</strong></label>
                    <p class="form-control-static"><?= htmlspecialchars($project['Name_Customer']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Code contrat</strong></label>
                    <p class="form-control-static"><?= htmlspecialchars($project['contract_code']) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Niveau du projet</strong></label>
                    <p class="form-control-static"><?= $project['Project_Level'] == 'FO' ? 'Front Office' : 'Back Office' ?></p>
                </div>
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Type d'engagement</strong></label>
                    <p class="form-control-static"><?= $project['Type_Engagement'] == 'AT' ? 'Assistance Technique' : 'Work Package' ?></p>
                </div>
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Statut</strong></label>
                    <p class="form-control-static">
                        <span class="badge bg-<?= $project['Status_Project'] == 'Active' ? 'success' : 'secondary' ?>">
                            <?= $project['Status_Project'] == 'Active' ? 'Actif' : 'Inactif' ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Date de début</strong></label>
                    <p class="form-control-static"><?= date('d/m/Y', strtotime($project['Starting_Date'])) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Date de fin prévue</strong></label>
                    <p class="form-control-static"><?= date('d/m/Y', strtotime($project['Expected_Ending_Date'])) ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="font-weight-bold"><strong>Date de création</strong></label>
                    <p class="form-control-static"><?= date('d/m/Y H:i', strtotime($project['Created_At'])) ?></p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>