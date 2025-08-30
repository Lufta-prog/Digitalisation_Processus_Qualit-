<?php
$title = 'Validation QG - ' . htmlspecialchars($checklist['Reference_Unique']);
include_once 'views/includes/header.php';

$criticalityLevel = $checklist['Criticality_Level'] ?? 'C2';
$qgLabel = ($qgType === 'qg1') ? 'QG1' : 'QG2';
$isReadonly = isset($validation['status']) && in_array($validation['status'], ['Approved', 'Rejected']);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list"></i> Validation <?= $qgLabel ?> - Itération <?= $iteration ?>
        </h1>
        <div>
            <span class="badge bg-<?= $criticalityLevel === 'Haute' ? 'danger' : ($criticalityLevel === 'Moyenne' ? 'warning' : 'success') ?> me-2">
                <?= htmlspecialchars($criticalityLevel) ?>
            </span>
            <a href="index.php?controller=checklist&action=view&id=<?= $checklist['ID_CLC'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $type): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-4">
                <?= $_SESSION[$key] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-info-circle me-2"></i> Détails de la Checklist
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Référence:</label>
                        <p class="form-control-static"><?= htmlspecialchars($checklist['Reference_Unique']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Activité:</label>
                        <p class="form-control-static"><?= htmlspecialchars($checklist['Name_Activity']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Business Unit:</label>
                        <p class="form-control-static"><?= htmlspecialchars($checklist['Name_BU']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Itération:</label>
                        <p class="form-control-static"><?= $iteration ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="checklistForm" action="index.php?controller=qualityGate&action=validate" method="POST">
        <input type="hidden" name="validation_id" value="<?= $validation['id'] ?>">
        <input type="hidden" name="clc_id" value="<?= $checklist['ID_CLC'] ?>">
        <input type="hidden" name="qg_type" value="<?= $qgType ?>">
        <input type="hidden" name="iteration" value="<?= $iteration ?>">

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-list-check me-2"></i> Items de sortie
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px">N°</th>
                                <th>Item</th>
                                <th class="text-center">Statut Consultant</th>
                                <th class="text-center">Statut <?= $qgLabel ?></th>
                                <th class="text-center">Itération</th>
                                <th class="text-center">Commentaire Consultant</th>
                                <th class="text-center">Votre Commentaire <?= $qgLabel ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                               <?php foreach ($items as $index => $item): ?>
                                <?php
                                // Récupération des valeurs correctes
                                $consultantComment = $item['Comment'] ?? '';
                                $qgCommentField = ($qgType === 'qg1') ? 'QG1_Comment' : 'QG2_Comment';
                                $qgStatusField = ($qgType === 'qg1') ? 'QG1_Status' : 'QG2_Status';
                                
                                $qgComment = $item[$qgCommentField] ?? '';
                                $qgStatus = $item[$qgStatusField] ?? 'NA';
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($item['Name_Item']) ?></td>
                                    <td class="text-center">
                                        <span class="badge status-<?= strtolower($item['Status'] ?? 'na') ?>">
                                            <?= htmlspecialchars($item['Status'] ?? 'NA') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <select class="form-select form-select-sm qg-status item-status" 
                                                name="items[<?= $item['ID_Item'] ?>][status]"
                                                <?= $isReadonly ? 'disabled' : '' ?>>
                                            <option value="OK" <?= $qgStatus === 'OK' ? 'selected' : '' ?>>OK</option>
                                            <option value="NOK" <?= $qgStatus === 'NOK' ? 'selected' : '' ?>>NOK</option>
                                            <option value="NA" <?= $qgStatus === 'NA' ? 'selected' : '' ?>>NA</option>
                                        </select>
                                    </td>
                                    <td class="text-center">It<?= $iteration ?></td>
                                    <td>
                                        <div class="comment-readonly small">
                                            <?= htmlspecialchars($consultantComment) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <textarea class="form-control form-control-sm qg-comment comments-field"
                                                name="items[<?= $item['ID_Item'] ?>][comment]"
                                                rows="1"
                                                <?= $isReadonly ? 'readonly' : '' ?>
                                                data-modified="false"><?= htmlspecialchars($qgComment) ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">Aucun item de sortie trouvé pour cette activité</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-comment me-2"></i> Commentaire global <?= $qgLabel ?>
                </h6>
            </div>
            <div class="card-body">
                <textarea id="global_comment" name="global_comment" class="form-control" rows="3" required
                    <?= $isReadonly ? 'readonly' : '' ?>><?= htmlspecialchars($validation['comment'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <?php if (!$isReadonly): ?>
                <div class="d-flex align-items-center w-100">
                    <select id="validationAction" name="action" class="form-select me-2" style="width: auto;" required>
                        <option value=""> Choisir une action </option>
                        <option value="approve">Approuver</option>
                        <option value="reject">Rejeter</option>
                    </select>
                    
                    <button type="submit" id="submitButton" class="btn btn-secondary" disabled>
                        <i class="fas fa-paper-plane me-1"></i> Soumettre
                    </button>
                </div>
            <?php else: ?>
                <div class="alert alert-<?= $validation['status'] === 'Approved' ? 'success' : 'danger' ?> w-100 text-center mb-0">
                    <i class="fas fa-<?= $validation['status'] === 'Approved' ? 'check' : 'times' ?>-circle me-2"></i>
                    Cette validation a déjà été traitée (<?= htmlspecialchars($validation['status']) ?>).
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
/* Styles améliorés */
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.8rem;
    display: inline-block;
    min-width: 40px;
    text-align: center;
}
.status-ok {
    background-color: #d1e7dd;
    color: #0f5132;
}
.status-nok {
    background-color: #f8d7da;
    color: #842029;
}
.status-na {
    background-color: #e2e3e5;
    color: #41464b;
}
.qg-comment {
    background-color: #e8f4fd;
    border-left: 3px solid #0d6efd;
}
.comment-readonly {
    padding: 0.375rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    min-height: 38px;
}

.form-select-sm, .form-control-sm {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}
.form-control-static {
    padding: 0.375rem 0;
    margin-bottom: 0;
    line-height: 1.5;
    background-color: transparent;
    border: solid transparent;
    border-width: 1px 0;
}
/* Style pour la sélection d'action */
#validationAction {
    min-width: 180px;
}

/* Animation pour le bouton */
#submitButton:not(:disabled) {
    transition: all 0.3s ease;
}

/* Indication visuelle pour l'action choisie */
#validationAction option[value="approve"] {
    background-color: #d1e7dd;
    color: #0f5132;
}

#validationAction option[value="reject"] {
    background-color: #f8d7da;
    color: #842029;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
// Fonction pour gérer le changement de statut
function handleStatusChange(select) {
    const row = select.closest('tr');
    if (!row) return;

    const commentField = row.querySelector('.comments-field');
    if (!commentField) return;

    // Réinitialisation complète
    clearFieldError(commentField);
    
    // Réinitialiser l'état modifié lors du changement de statut
    commentField.dataset.modified = 'false';
    
    // Mise à jour des contraintes
    if (select.value === 'OK') {
        commentField.required = false;
        commentField.placeholder = "Commentaire optionnel";
    } else {
        commentField.required = true;
        commentField.placeholder = `Commentaire obligatoire (${
            select.value === 'NOK' ? 'Décrivez le problème' : 'Expliquez la non-applicabilité'
        })`;
    }
}

// Gestion du bouton unique de soumission
document.addEventListener('DOMContentLoaded', function() {
    const actionSelect = document.getElementById('validationAction');
    const submitButton = document.getElementById('submitButton');
    const form = document.querySelector('form');

    if (actionSelect && submitButton) {
        // Changer l'apparence du bouton selon l'action choisie
        actionSelect.addEventListener('change', function() {
            if (this.value) {
                submitButton.disabled = false;
                submitButton.className = this.value === 'approve' 
                    ? 'btn btn-success' 
                    : 'btn btn-danger';
                submitButton.innerHTML = this.value === 'approve'
                    ? '<i class="fas fa-check-circle me-1"></i> Approuver'
                    : '<i class="fas fa-times-circle me-1"></i> Rejeter';
            } else {
                submitButton.disabled = true;
                submitButton.className = 'btn btn-secondary';
                submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Soumettre';
            }
        });

        // Validation avant soumission
        form.addEventListener('submit', function(e) {
            const selectedAction = actionSelect.value;
            
            if (!selectedAction) {
                e.preventDefault();
                alert('Veuillez choisir une action (Approuver ou Rejeter)');
                return;
            }

            // Validation des commentaires
            let isValid = true;
            document.querySelectorAll('.comments-field').forEach(textarea => {
                if (!validateCommentField(textarea)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Veuillez corriger les erreurs dans les commentaires avant de soumettre', 'danger');
                
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Message de confirmation différent selon l'action
            const message = selectedAction === 'approve' 
                ? 'Êtes-vous sûr de vouloir approuver cette validation ?'
                : 'Êtes-vous sûr de vouloir rejeter cette validation ?';

            if (!confirm(message)) {
                e.preventDefault();
                return;
            }

            // Désactiver le bouton pendant le traitement
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Traitement...';
        });
    }
    
    // Ajouter des écouteurs d'événements pour les changements de statut
    document.querySelectorAll('.item-status').forEach(select => {
        select.addEventListener('change', function() {
            handleStatusChange(this);
        });
        
        // Initialiser l'état des champs de commentaire au chargement
        handleStatusChange(select);
    });
    
    // Ajouter des écouteurs d'événements pour les champs de commentaire
    document.querySelectorAll('.comments-field').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.dataset.modified = 'true';
            validateCommentField(this);
        });
        
        textarea.addEventListener('blur', function() {
            validateCommentField(this);
        });
    });
    
    // Initialiser la validation de la checklist
    initChecklistPage({
        formId: 'checklistForm',
        statusClass: 'item-status',
        commentClass: 'comments-field'
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>