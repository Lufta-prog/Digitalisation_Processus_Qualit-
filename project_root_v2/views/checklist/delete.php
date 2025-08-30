<?php
$title = 'Supprimer une Checklist';
include_once 'views/includes/header.php';

$checklists = $data['checklists'] ?? [];
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-trash-alt me-2"></i> Supprimer une Checklist
    </h1>
    <div>
        <a href="index.php?controller=checklist&action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-exclamation-triangle me-2"></i> Confirmation de suppression
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <?php if (empty($checklists)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Aucune checklist disponible pour suppression.
            </div>
        <?php else: ?>
            <form action="index.php?controller=checklist&action=processDelete" method="POST" id="deleteForm">
                <div class="form-group mb-4">
                    <label for="checklist_id" class="font-weight-bold">Sélectionnez une checklist à supprimer <span class="text-danger">*</span></label>
                    <select name="checklist_id" id="checklist_id" class="form-control" required>
                        <option value="">Choisir une checklist </option>
                        <?php foreach ($checklists as $checklist): ?>
                            <option value="<?= htmlspecialchars($checklist['ID_CLC']) ?>">
                                <?= htmlspecialchars($checklist['Reference_Unique']) ?> 
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible. La suppression entraînera la perte définitive de toutes les données associées à cette checklist.
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i> Confirmer la suppression
                    </button>
                    <a href="index.php?controller=checklist&action=index" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Annuler
                    </a>
                </div>
            </form>


        <?php endif; ?>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>