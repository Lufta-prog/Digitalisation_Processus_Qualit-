<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-edit me-2"></i> Modifier l'activité
    </h1>
    <div>
        <a href="index.php?controller=activity" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations de l'activité
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form method="post" action="index.php?controller=activity&action=update&id=<?= $activity['ID_Activity'] ?>">
            <!-- Basic Information Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nom de l'activité <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" 
                               value="<?= htmlspecialchars($activity['Name_Activity'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bu_id" class="font-weight-bold">Business Unit <span class="text-danger">*</span></label>
                        <select name="bu_id" id="bu_id" class="form-control" required>
                            <option value="">Sélectionner une Business Unit</option>
                            <?php foreach ($businessUnits as $bu): ?>
                                <option value="<?= $bu['ID_BU'] ?>" 
                                    <?= ($activity['BU_ID'] ?? null) == $bu['ID_BU'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($bu['Name_BU'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                </button>
                <a href="index.php?controller=activity" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>