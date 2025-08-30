<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-plus-circle me-2"></i> Nouveau Projet
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
            <i class="fas fa-info-circle me-2"></i> Informations du projet
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form action="index.php?controller=projects&action=store" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nom du projet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer_id" class="font-weight-bold">Client <span class="text-danger">*</span></label>
                        <select class="form-control" id="customer_id" name="customer_id" required>
                            <option value="">Sélectionnez un client</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['ID_Customer'] ?>">
                                    <?= htmlspecialchars($customer['Name_Customer']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contract_code" class="font-weight-bold">Code contrat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contract_code" name="contract_code" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="project_level" class="font-weight-bold">Niveau du projet <span class="text-danger">*</span></label>
                        <select class="form-control" id="project_level" name="project_level" required>
                            <?php foreach ($projectLevels as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="starting_date" class="font-weight-bold">Date de début <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="starting_date" name="starting_date" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_ending_date" class="font-weight-bold">Date de fin prévue <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expected_ending_date" name="expected_ending_date" required>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Statut <span class="text-danger">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <?php foreach ($statuses as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $key == 'Active' ? 'selected' : '' ?>>
                                    <?= $value ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type_engagement" class="font-weight-bold">Type d'engagement <span class="text-danger">*</span></label>
                        <select class="form-control" id="type_engagement" name="type_engagement" required>
                            <?php foreach ($engagementTypes as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Enregistrer
                </button>
                <a href="index.php?controller=projects" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>