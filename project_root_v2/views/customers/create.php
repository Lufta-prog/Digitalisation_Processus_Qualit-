<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-plus me-2"></i> Nouveau Client
    </h1>
    <div>
        <a href="index.php?controller=customers" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations du client
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form method="POST" action="index.php?controller=customers&action=store">
            <!-- Basic Information Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nom du client <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="industry_id" class="font-weight-bold">Secteur d'activité</label>
                        <select class="form-control" id="industry_id" name="industry_id">
                            <option value="">Sélectionner un secteur</option>
                            <?php foreach ($industries as $industry): ?>
                                <option value="<?= $industry['ID_Industry'] ?>"
                                    <?= (isset($_POST['industry_id']) && $_POST['industry_id'] == $industry['ID_Industry']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($industry['Industry_Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Créer le client
                </button>
                <a href="index.php?controller=customers" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>