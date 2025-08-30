<?php
$title = 'Détails de la Checklist - Données de Sortie';
include_once 'views/includes/header.php';
require_once __DIR__ . '/../../utils/helpers.php';

// Récupération des données de session/post
$post = $_SESSION['old_post'] ?? $_POST ?? [];
unset($_SESSION['old_post']);

// Variables d'itération
$lastValidatedIterationDS = (int)($checklist['Iteration_DS'] ?? 0);
$currentInputIterationDS = $isC3 ? 1 : ($lastValidatedIterationDS + 1); // Pour C3, toujours itération 1

// Pour C3, pas de validation QG
$hasPendingValidation = $isC3 ? false : $this->checklistModel->hasPendingQGApproval($checklist['ID_CLC'], $currentInputIterationDS);
$wasIterationRejected = $isC3 ? false : (
    $this->checklistModel->wasIterationRejected($checklist['ID_CLC'], $currentInputIterationDS-1, 'qg1') || 
    ($checklist['Criticality_Level'] === 'C1' && 
     $this->checklistModel->wasIterationRejected($checklist['ID_CLC'], $currentInputIterationDS-1, 'qg2'))
);
// Statut d'approbation QG
$isApprovedByQG1 = $isIterationApprovedByQG1;
$isApprovedByQG2 = $isIterationApprovedByQG2;
$isApprovedByAllQG = $isApprovedByQG1 && ($criticality !== 'C1' || $isApprovedByQG2);

// Statut de la checklist
$isChecklistCompleted = ($checklist['Status'] === 'Completed');
$isChecklistFullyCompleted = $isC3 ? ($lastValidatedIterationDS >= 1) : ($lastValidatedIterationDS >= 3);

// Déterminer si l'itération peut être modifiée
$canEditCurrentIteration = (
    ($isC3 || !$hasPendingValidation) && // Toujours modifiable en C3
    ($wasIterationRejected || $currentInputIterationDS === 1 || $isC3) && // Pas besoin de rejet précédent en C3
    ($isC3 ? $currentInputIterationDS <= 1 : $currentInputIterationDS <= 3) && // Limite d'itérations différente pour C3
    !$isChecklistFullyCompleted
);

$isWaitingQG = ($hasPendingValidation && !$canEditCurrentIteration);
$iterationDisplayLimit = $isC3 ? 1 : max($currentInputIterationDS, $lastValidatedIterationDS, 1); // Limite à 1 itération pour C3

// Préparation des données d'itération pour l'affichage
for ($iter = 1; $iter <= ($isC3 ? 1 : 3); $iter++) {
    if (!isset($iterationProgress[$iter])) {
        $iterationProgress[$iter] = ['OK' => 0, 'total' => 0, 'allOK' => false];
    }
    if (!isset($iterationInfo[$iter])) {
        $iterationInfo[$iter] = null;
    }
}
?>

<div class="small text-muted text-end mb-2">
    [DEBUG: Itération <?= $currentInputIterationDS ?> | 
    Modifiable: <?= $canEditCurrentIteration ? 'Oui' : 'Non' ?> | 
    Attente QG: <?= $isWaitingQG ? 'Oui' : 'Non' ?>]
</div>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h2 class="h3 mb-0 text-gray-800">
        <i class="fas fa-clipboard-check me-2"></i> Controle de la Checklist - Données de Sortie
    </h2>
    <span class="badge bg-primary ms-2">
        Itération : <?= $currentInputIterationDS ?>/<?= $isC3 ? 1 : 3 ?>
    </span>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations de la Checklist
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Référence :</strong> <?= htmlspecialchars($checklist['Reference_Unique']) ?></p>
                <p><strong>Activité :</strong> <?= htmlspecialchars($checklist['Name_Activity']) ?></p>
                <p><strong>Criticité :</strong> <span class="badge <?= $checklist['Criticality_Level'] === 'C1' ? 'bg-danger' : ($checklist['Criticality_Level'] === 'C2' ? 'bg-warning text-dark' : 'bg-info') ?>">
                    <?= htmlspecialchars($checklist['Criticality_Level']) ?>
                </span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Business Unit :</strong> <?= htmlspecialchars($checklist['Name_BU']) ?></p>
                <p><strong>Date Livraison :</strong> <?= htmlspecialchars($checklist['Expected_Delivery_Date']) ?></p>
                <?php if ($isC3): ?>
                    <p class="text-info"><i class="fas fa-info-circle"></i> Checklist C3 - Validation simplifiée (1 itération)</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Progress Cards - Limité à 1 itération pour C3 -->
<div class="row mb-4 justify-content-center">
    <?php for ($iterationNumber = 1; $iterationNumber <= ($isC3 ? 1 : 3); $iterationNumber++): ?>
        <?php 
        $progress = $dsIterationProgress[$iterationNumber];
        $consultantPercentage = $progress['total_items'] > 0 
            ? round(($progress['consultant_OK'] / $progress['total_items']) * 100)
            : 0;
        $qg1Percentage = $progress['total_items'] > 0 
            ? round(($progress['qg1_OK'] / $progress['total_items']) * 100)
            : 0;
        $qg2Percentage = ($criticality === 'C1' && $progress['total_items'] > 0) 
            ? round(($progress['qg2_OK'] / $progress['total_items']) * 100)
            : 0;
        
        $validationInfo = $iterationInfo[$iterationNumber] ?? null;

        // Statut d'affichage
        if ($iterationNumber < $currentInputIterationDS) {
            $status = $progress['all_validated'] ? 'validée' : 'rejetée';
        } elseif ($iterationNumber == $currentInputIterationDS) {
            if ($isChecklistFullyCompleted) {
                $status = 'finalisée';
            } elseif ($isWaitingQG) {
                $status = 'attente_qg';
            } elseif ($canEditCurrentIteration) {
                $status = 'active';
            } else {
                $status = 'active'; // fallback
            }
        } else {
            $status = 'future';
        }

        // Vérifier les approbations QG pour cette itération
        $isQGIterationApproved1 = ($iterationInfo[$iterationNumber]['qg1_status'] ?? false);
        $isQGIterationApproved2 = ($criticality === 'C1') ? ($iterationInfo[$iterationNumber]['qg2_status'] ?? false) : true;
        ?>
        <div class="col-md-<?= $isC3 ? '12' : '4' ?> mb-3">
            <div class="card h-100 <?= $status === 'active' ? 'border-left-primary' : '' ?>" style="min-width: 200px;">
                 <div class="card-body text-center d-flex flex-column p-3">
                    <h6 class="card-title mb-3">
                        Itération <?= $iterationNumber ?>
                        <?php if ($status === 'active'): ?>
                            <span class="badge bg-primary ms-2">
                                <i class="fas fa-sync-alt"></i> Active
                            </span>
                        <?php elseif ($status === 'validée'): ?>
                            <?php if ($progress['all_validated']): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="fas fa-check-circle"></i> <?= $isChecklistFullyCompleted ? 'Finalisée' : 'Validée' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="fas fa-check-circle"></i> Achevée
                                </span>
                            <?php endif; ?>
                        <?php elseif ($status === 'rejetée'): ?>
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-exclamation-triangle"></i> Rejetée
                            </span>
                        <?php elseif ($status === 'attente_qg'): ?>
                            <span class="badge bg-info ms-2">
                                <i class="fas fa-hourglass-half"></i> En attente QG
                            </span>
                        <?php elseif ($status === 'future'): ?>
                            <span class="badge bg-secondary ms-2">
                                <i class="fas fa-hourglass-half"></i> Future
                            </span>
                        <?php endif; ?>
                    </h6>
                    
                    <!-- Progress Bar Consultant -->
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-primary" 
                            role="progressbar" 
                            style="width: <?= $consultantPercentage ?>%" 
                            aria-valuenow="<?= $consultantPercentage ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                    <span class="badge bg-primary mb-2">
                        <?= $consultantPercentage ?>% Consultant OK
                    </span>
                    
                    <?php if (!$isC3): ?>
                        <!-- Progress Bar QG1 -->
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-info" 
                                role="progressbar" 
                                style="width: <?= $qg1Percentage ?>%" 
                                aria-valuenow="<?= $qg1Percentage ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                        <span class="badge bg-info mb-2">
                            <?= $qg1Percentage ?>% QG1 OK
                            <?php if ($isQGIterationApproved1): ?>
                                <span class="badge bg-white text-info ms-1">
                                    <i class="fas fa-check"></i> Approuvé
                                </span>
                            <?php endif; ?>
                        </span>
                        
                        <?php if ($criticality === 'C1'): ?>
                            <!-- Progress Bar QG2 -->
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                    role="progressbar" 
                                    style="width: <?= $qg2Percentage ?>%" 
                                    aria-valuenow="<?= $qg2Percentage ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <span class="badge bg-success mb-2">
                                <?= $qg2Percentage ?>% QG2 OK
                                <?php if ($isQGIterationApproved2): ?>
                                    <span class="badge bg-white text-success ms-1">
                                        <i class="fas fa-check"></i> Approuvé
                                    </span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Validation Info -->
                    <?php if ($validationInfo): ?>
                        <div class="mt-auto small text-muted">
                            <i class="fas fa-calendar-check"></i> Validée le <?= htmlspecialchars($validationInfo['date']) ?>
                            <br><i class="fas fa-user"></i> Par <?= htmlspecialchars($validationInfo['by']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>
<!-- Message d'attente QG si besoin -->
<?php if ($isWaitingQG && !$isChecklistFullyCompleted): ?>
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        En attente de validation QG pour l'itération <?= $currentInputIterationDS ?>.
    </div>
<?php endif; ?>

<!-- Items Table et Formulaire -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-tasks me-2"></i> Items de la Checklist
        </h6>
    </div>
    <div class="card-body">
        <form action="index.php?controller=checklist&action=updateItems&id=<?= $checklist['ID_CLC'] ?>" method="POST" id="checklistForm">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Itération</th>
                            <th class="text-center">Commentaire Consultant</th>
                                <?php if (!$isC3): ?>
                                    <th class="text-center">Statut QG1</th>
                                    <th class="text-center"><?= htmlspecialchars($qgname['QG1_Name'] ?? 'Non assigné') ?></th>
                                    <?php if ($checklist['Criticality_Level'] === 'C1'): ?>
                                        <th class="text-center">Statut QG2</th>
                                        <th class="text-center"><?= htmlspecialchars($qgname['QG2_Name'] ?? 'Non assigné') ?></th>
                                    <?php endif; ?>
                                <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items['DS'])): ?>
                            
                            <?php foreach ($items['DS'] as $index => $item): ?>
                                <?php for ($i = 1; $i <= $iterationDisplayLimit; $i++): ?>
                                    <?php
                                    $is_editable_iteration = (
                                         $i === $currentInputIterationDS
                                         && $canEditCurrentIteration
                                         && !$isChecklistFullyCompleted
                                     );
                                        $is_past_iteration = ($i < $currentInputIterationDS || ($isChecklistFullyCompleted && $i <= $currentInputIterationDS));
                                        $is_future_iteration = ($i > $currentInputIterationDS && !$isChecklistFullyCompleted);

                                        $row_class = '';
                                        if ($i === $currentInputIterationDS && $canEditCurrentIteration) {
                                            $row_class = 'bg-primary-soft';
                                        }

                                        $current_iter_status = 'NA';
                                        $current_iter_comment = '';

                                        if (isset($item['iterations'][$i]) && $item['iterations'][$i]['status'] !== null) {
                                            $current_iter_status = $item['iterations'][$i]['status'];
                                            $comment_data = $item['iterations'][$i]['comment'] ?? null;
                                            if (is_array($comment_data) && isset($comment_data['consultant'])) {
                                                $current_iter_comment = $comment_data['consultant'];
                                            } elseif (is_string($comment_data)) {
                                                $current_iter_comment = $comment_data;
                                            }
                                        } elseif ($is_editable_iteration) {
                                            $current_iter_status = $item['current_status'] ?? 'NA';
                                            $current_iter_comment = $item['current_comment'] ?? '';
                                        }
                                        
                                        // Logs pour débogage des variables clés
                                        error_log("Iteration: $i, Item ID: {$item['id']}");
                                        error_log("current_iter_status: " . $current_iter_status);
                                        error_log("current_iter_comment: " . $current_iter_comment);
                                    ?>
                                    <tr class="<?= $row_class ?>">
                                        <!-- Numéro et nom de l'item -->
                                        <?php if ($i === 1): ?>
                                            <td rowspan="<?= $iterationDisplayLimit ?>"><?= htmlspecialchars($item['name']) ?></td>
                                        <?php endif; ?>
                                        
                                        <!-- Statut -->
                                        <?php if ($i === 1): ?>
                                            <td class="text-center" rowspan="<?= $iterationDisplayLimit ?>">
                                                <?php if ($isChecklistFullyCompleted): ?>
                                                    <span class="badge <?= 'bg-' . ($item['current_status'] === 'OK' ? 'success' : ($item['current_status'] === 'NOK' ? 'danger' : 'secondary')) ?>">
                                                        <?= htmlspecialchars($item['current_status'] ?? 'NA') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <select class="form-control form-control-sm item-status" 
                                                        name="items[<?= $item['id'] ?>][status]"
                                                        data-debug="status-<?= $item['id'] ?>"
                                                        <?= (($canEditCurrentIteration || $isC3) && !$isChecklistFullyCompleted ? '' : 'disabled') ?>>
                                                        <option value="OK" <?= ($current_iter_status ?? 'NA') === 'OK' ? 'selected' : '' ?>>OK</option>
                                                        <option value="NOK" <?= ($current_iter_status ?? 'NA') === 'NOK' ? 'selected' : '' ?>>NOK</option>
                                                        <option value="NA" <?= ($current_iter_status ?? 'NA') === 'NA' ? 'selected' : '' ?>>NA</option>
                                                    </select>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <!-- Itération -->
                                        <td class="text-center">It<?= $i ?></td>
                                        <!-- Commentaire Consultant -->
                                        <td>
                                            <textarea class="form-control form-control-sm comments-field" 
                                                name="items[<?= $item['id'] ?>][comment]" 
                                                data-debug="comment-<?= $item['id'] ?>"
                                                rows="1"
                                                data-item-id="<?= $item['id'] ?>" data-iteration="<?= $i ?>"
                                                 <?= (($i === $currentInputIterationDS && ($canEditCurrentIteration || $isC3)) ? '' : 'disabled') ?>
                                                ><?= htmlspecialchars(trim($current_iter_comment ?? '')) ?></textarea>
                                        </td>
                                        <!-- Commentaires QG -->
                                        <?php if (!$isC3): ?>
                                            <td class="text-center">
                                                <span class="badge <?= 'bg-' . ($item['iterations'][$i]['qg1_status'] === 'OK' ? 'success' : ($item['iterations'][$i]['qg1_status'] === 'NOK' ? 'danger' : 'secondary')) ?>">
                                                    <?= htmlspecialchars($item['iterations'][$i]['qg1_status'] ?? 'NA') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small qg-comment qg1-comment">
                                                    <?= htmlspecialchars($item['iterations'][$i]['comment']['qg1'] ?? '') ?>
                                                </div>
                                            </td>
                                            <?php if ($checklist['Criticality_Level'] === 'C1'): ?>
                                                <td class="text-center">
                                                    <span class="badge <?= 'bg-' . ($item['iterations'][$i]['qg2_status'] === 'OK' ? 'success' : ($item['iterations'][$i]['qg2_status'] === 'NOK' ? 'danger' : 'secondary')) ?>">
                                                        <?= htmlspecialchars($item['iterations'][$i]['qg2_status'] ?? 'NA') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small qg-comment qg2-comment">
                                                        <?= htmlspecialchars($item['iterations'][$i]['comment']['qg2'] ?? '') ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 5 + (!$isC3 ? 2 : 0) + ($checklist['Criticality_Level'] === 'C1' ? 2 : 0) ?>" class="text-center py-4">
                                    <div class="text-muted">Aucun item de sortie trouvé pour cette activité</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Actions -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                <?php if ($qgValidationStatus['rejected']): ?>
                    <?php 
                    $qg1Rejected = $this->checklistModel->wasIterationRejected($checklist['ID_CLC'], $currentInputIterationDS-1, 'qg1');
                    $qg2Rejected = ($checklist['Criticality_Level'] === 'C1') 
                        ? $this->checklistModel->wasIterationRejected($checklist['ID_CLC'], $currentInputIterationDS-1, 'qg2')
                        : false;
                    ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Itération <?= $currentInputIterationDS-1 ?> rejetée par 
                        <?= $qg1Rejected ? htmlspecialchars($qgname['QG1_Name'] ?? 'QG1') : '' ?>
                        <?= ($qg1Rejected && $qg2Rejected) ? ' et ' : '' ?>
                        <?= $qg2Rejected ? htmlspecialchars($qgname['QG2_Name'] ?? 'QG2') : '' ?>
                        - Passage à l'itération <?= $currentInputIterationDS ?>
                    </div>
                <?php elseif ($qgValidationStatus['approved'] || $isApprovedByAllQG): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        Itération <?= $currentInputIterationDS ?> validée
                        <?php if ($isApprovedByQG1): ?>
                            <span class="badge bg-info ms-2">
                                <i class="fas fa-check"></i> <?= htmlspecialchars($qgname['QG1_Name'] ?? 'QG1') ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($criticality === 'C1' && $isApprovedByQG2): ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check"></i> <?= htmlspecialchars($qgname['QG2_Name'] ?? 'QG2') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <a href="index.php?controller=checklist&action=view&id=<?= $checklist['ID_CLC'] ?>&view=input" 
                class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i> Retour au DE
                </a>
                
                <?php if (!$isChecklistCompleted && ($canEditCurrentIteration || $isC3)): ?>
                    <button type="submit" class="btn btn-success" id="submitButton">
                        <i class="fas fa-save me-2"></i>
                        Valider l'itération <?= $currentInputIterationDS ?>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $isChecklistCompleted ? 'Checklist Complétée' : (!$canEditCurrentIteration ? 'En attente QG' : 'Itération Terminée') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        </form>
    </div>
</div>

<style>
.bg-primary-soft {
    background-color: rgba(13, 110, 253, 0.05);
}
.qg-comment {
    padding: 0.375rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    min-height: 38px;
}
.qg1-comment {
    background-color: #e8f4fd;
    border-left: 3px solid #0d6efd;
}
.qg2-comment {
    background-color: #e2f0ea;
    border-left: 3px solid #198754;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour gérer le changement de statut
    function handleStatusChange(select) {
        const row = select.closest('tr');
        const commentField = row.querySelector('.comments-field');
        
        // Activer/désactiver le champ commentaire selon le statut
        if (select.value === 'OK') {
            commentField.disabled = false;
            commentField.required = false;
        } else { // NOK ou NA
            commentField.disabled = false;
            commentField.required = true;
        }
    }

    // Validation avant soumission
    document.getElementById('checklistForm').addEventListener('submit', function(e) {
        let isValid = true;
        const errorMessages = [];
        
        // Vérifier tous les items de toutes les itérations
        document.querySelectorAll('.item-status').forEach(select => {
            const commentField = select.closest('tr').querySelector('.comments-field');
            
            if ((select.value === 'NOK' || select.value === 'NA') && !commentField.value.trim()) {
                isValid = false;
                commentField.classList.add('is-invalid');
                
                // Récupérer le numéro de l'item pour le message d'erreur
                const itemNumber = select.closest('tr').querySelector('td:first-child').textContent;
                errorMessages.push(`Item ${itemNumber} (Itération ${select.dataset.iteration}) : commentaire requis pour statut ${select.value}`);
            } else {
                commentField.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            // Afficher une alerte avec tous les messages d'erreur
            alert("Commentaires manquants :\n\n" + errorMessages.join('\n'));
            
            // Faire défiler vers le premier champ invalide
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Initialisation et écouteurs d'événements
    document.querySelectorAll('.item-status').forEach(select => {
        handleStatusChange(select);
        select.addEventListener('change', function() {
            handleStatusChange(this);
        });
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>