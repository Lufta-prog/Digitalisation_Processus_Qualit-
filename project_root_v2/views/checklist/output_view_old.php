<?php
$title = 'Détails de la Checklist - Données de Sortie';
include_once 'views/includes/header.php';

$post = $_SESSION['old_post'] ?? $_POST ?? [];
unset($_SESSION['old_post']);

// Initialisation des variables d'itération
$maxIteration = 3;
$currentIteration = $checklist['Iteration_DS'] ?? 0;
$nextIteration = $currentIteration + 1;
$isCompleted = ($currentIteration >= $maxIteration);

// Déterminer l'état de validation QG pour l'itération courante
$qgValidationState = 'pending'; // Par défaut
if ($currentIteration > 0) {
    $isApprovedByQG = !$this->checklistModel->hasPendingQGApproval($checklist['ID_CLC'], $currentIteration);
    $wasRejected = $this->checklistModel->wasIterationRejected($checklist['ID_CLC'], $currentIteration);
    
    if ($isApprovedByQG) {
        $qgValidationState = 'approved';
    } elseif ($wasRejected) {
        $qgValidationState = 'rejected';
    }
}

// Remplissage des données de progression
$iterationProgress = [];
foreach ($items['DS'] ?? [] as $item) {
    for ($i = 1; $i <= $maxIteration; $i++) {
        if (!isset($iterationProgress[$i])) {
            $iterationProgress[$i] = ['OK' => 0, 'total' => 0, 'allOK' => true];
        }
        $status = $item['iterations'][$i]['status'] ?? 'NA';
        if ($status !== 'NA') {
            $iterationProgress[$i]['total']++;
            if ($status === 'OK') {
                $iterationProgress[$i]['OK']++;
            } else {
                $iterationProgress[$i]['allOK'] = false;
            }
        }
    }
}

// Configuration QG
$criticalityLevel = $checklist['Criticality_Level'];
$showQG1 = in_array($criticalityLevel, ['C1', 'C2']);
$showQG2 = ($criticalityLevel === 'C1');
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h2 class="h3 mb-0 text-gray-800">
        <i class="fas fa-clipboard-check me-2"></i> Controle de la Checklist - Données de Sortie
    </h2>
    <span class="badge bg-primary ms-2">
        Itération : <?= min($currentIteration + 1, $maxIteration) ?>/<?= $maxIteration ?>
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
            </div>
            <div class="col-md-6">
                <p><strong>Business Unit :</strong> <?= htmlspecialchars($checklist['Name_BU']) ?></p>
                <p><strong>Date Livraison :</strong> <?= htmlspecialchars($checklist['Expected_Delivery_Date']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Cards -->
<div class="row mb-4 g-3">
    <?php for ($i = 1; $i <= $maxIteration; $i++): 
        $percentage = $iterationProgress[$i]['total'] > 0 
            ? round(($iterationProgress[$i]['OK'] / $iterationProgress[$i]['total']) * 100, 2) 
            : 0;
        $badgeClass = getBadgeClassFromPercentage($percentage);
        $iterationValidation = $iterationInfo[$i] ?? null;
        
        $iterationState = 'future';
        if ($i <= $currentIteration) {
            $iterationState = ($i < $currentIteration || $qgValidationState === 'approved') 
                ? 'completed' 
                : ($qgValidationState === 'rejected' ? 'rejected' : 'pending');
        }
    ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm <?= $iterationState === 'pending' ? 'border-left-warning' : '' ?>
                <?= $iterationState === 'completed' ? 'border-left-success' : '' ?>
                <?= $iterationState === 'rejected' ? 'border-left-danger' : '' ?>">
                <div class="card-body text-center">
                    <h6 class="card-title mb-3">
                        Itération <?= $i ?>
                        <?php if ($iterationState === 'pending'): ?>
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-hourglass-half"></i> En attente QG
                            </span>
                        <?php elseif ($iterationState === 'completed'): ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle"></i> Validée
                            </span>
                        <?php elseif ($iterationState === 'rejected'): ?>
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-times-circle"></i> Rejetée
                            </span>
                        <?php endif; ?>
                    </h6>
                    
                    <!-- Progress Bar -->
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar <?= $badgeClass ?>" 
                            role="progressbar" 
                            style="width: <?= $percentage ?>%" 
                            aria-valuenow="<?= $percentage ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                    
                    <span class="badge <?= $badgeClass ?>">
                        <?= $percentage ?>% DS OK
                    </span>
                    
                    <?php if ($iterationValidation): ?>
                        <div class="mt-2 small text-muted">
                            <i class="fas fa-calendar-check"></i> Validée le <?= htmlspecialchars($iterationValidation['date']) ?>
                            <br><i class="fas fa-user"></i> Par <?= htmlspecialchars($iterationValidation['by']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

<!-- Items Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-tasks me-2"></i> Items de la Checklist
        </h6>
    </div>
    <div class="card-body">
        <form action="index.php?controller=checklist&action=updateItems&id=<?= $checklist['ID_CLC'] ?>&view=output" method="POST" id="checklistForm">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">N°</th>
                            <th>Item</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Itération</th>
                            <th class="text-center">Commentaire Consultant</th>
                            <?php if ($showQG1): ?>
                                <th class="text-center">Statut QG1</th>
                                <th class="text-center">Commentaire QG1</th>
                            <?php endif; ?>
                            <?php if ($showQG2): ?>
                                <th class="text-center">Statut QG2</th>
                                <th class="text-center">Commentaire QG2</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items['DS'])): ?>
                            <?php foreach ($items['DS'] as $index => $item): ?>
                                <?php for ($i = 1; $i <= $maxIteration; $i++): ?>
                                    <?php
                                        // Déterminer si l'itération est éditable
                                       $isEditable = (
                                            ($i === $nextIteration && !$isCompleted && ($currentIteration === 0 || $qgValidationState === 'approved')) || 
                                            ($i === $currentIteration && $qgValidationState === 'rejected')
                                        );
                                    ?>
                                    <tr class="<?= $i === $nextIteration ? 'bg-primary-soft' : '' ?>">
                                        <!-- Numéro et nom de l'item -->
                                        <?php if ($i === 1): ?>
                                            <td class="text-center fw-bold" rowspan="<?= $maxIteration ?>"><?= $index + 1 ?></td>
                                            <td rowspan="<?= $maxIteration ?>"><?= htmlspecialchars($item['name']) ?></td>
                                        <?php endif; ?>
                                        
                                        <!-- Statut -->
                                        <?php if ($i === 1): ?>
                                            <td class="text-center" rowspan="<?= $maxIteration ?>">
                                                 <?php if ($isCompleted): ?>
                                                <span class="badge <?= 'bg-' . ($item['current_status'] === 'OK' ? 'success' : ($item['current_status'] === 'NOK' ? 'danger' : 'secondary')) ?>">
                                                    <?= htmlspecialchars($item['current_status'] ?? 'NA') ?>
                                                </span>
                                            <?php else: ?>
                                                <select class="form-control form-control-sm item-status" 
                                                    name="items[<?= $item['id'] ?>][status]"
                                                    <?= !$isEditable ? 'disabled' : '' ?>>
                                                    <option value="OK" <?= ($item['current_status'] ?? 'NA') === 'OK' ? 'selected' : '' ?>>OK</option>
                                                    <option value="NOK" <?= ($item['current_status'] ?? 'NA') === 'NOK' ? 'selected' : '' ?>>NOK</option>
                                                    <option value="NA" <?= ($item['current_status'] ?? 'NA') === 'NA' ? 'selected' : '' ?>>NA</option>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        
                                        <!-- Itération -->
                                        <td class="text-center">It<?= $i ?></td>

                                        <!-- Commentaire Consultant -->
                                        <td>
                                            <?php if ($i === $nextIteration && $isEditable): ?>
                                                <textarea class="form-control form-control-sm comments-field" 
                                                    name="items[<?= $item['id'] ?>][comment]" 
                                                    rows="1"><?= is_string($item['iterations'][$i]['comment']['consultant'] ?? '') ? htmlspecialchars($item['iterations'][$i]['comment']['consultant']) : '' ?></textarea>
                                            <?php else: ?>
                                                <div class="small">
                                                    <?php if (!empty(trim($item['iterations'][$i]['comment']['consultant'] ?? ''))): ?>
                                                        <?= htmlspecialchars($item['iterations'][$i]['comment']['consultant']) ?>
                                                    <?php elseif (($item['iterations'][$i]['status'] ?? '') === 'OK'): ?>
                                                        <div class="text-success"><i class="fas fa-check"></i></div>
                                                    <?php else: ?>
                                                        <div class="text-muted small">(aucun commentaire)</div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Commentaires QG -->
                                        <?php if ($showQG1): ?>
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
                                        <?php endif; ?>
                                        
                                        <?php if ($showQG2): ?>
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
                                    </tr>
                                <?php endfor; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 5 + ($showQG1 ? 2 : 0) + ($showQG2 ? 2 : 0) ?>" class="text-center py-4">
                                    <div class="text-muted">Aucun item DS trouvé pour cette activité</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <?php if ($qgValidationState === 'pending'): ?>
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            L'itération <?= $currentIteration ?> est en attente de validation QG. 
                            Vous ne pouvez pas modifier les items tant que la validation n'est pas reçue.
                        </div>
                    <?php elseif ($qgValidationState === 'rejected'): ?>
                        <div class="alert alert-danger mb-0 py-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            L'itération <?= $currentIteration ?> a été rejetée. Veuillez corriger les problèmes et resoumettre.
                        </div>
                    <?php elseif ($isCompleted): ?>
                        <div class="alert alert-success mb-0 py-2">
                            <i class="fas fa-check-circle me-2"></i>
                            Cette checklist est terminée (3 itérations validées).
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <a href="index.php?controller=checklist&action=index" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="index.php?controller=checklist&action=view&id=<?= $checklist['ID_CLC'] ?>&view=input" 
                                    class="btn btn-primary me-2">
                            <i class="fas fa-arrow-left me-2"></i> Retourner au DE
                        </a>
                    <?php if (!$isCompleted && $nextIteration <= $maxIteration && ($nextIteration === 1 || $qgValidationState === 'approved')): ?>
                        <button type="submit" class="btn btn-success" id="submitButton">
                            <i class="fas fa-save me-2"></i>
                            Valider l'itération <?= $nextIteration ?>
                        </button>
                    <?php elseif ($isCompleted): ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fas fa-check-circle me-2"></i>
                            Checklist Complétée
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
            if (select.disabled) return;
            
            const commentField = select.closest('tr').querySelector('.comments-field');
            
            if ((select.value === 'NOK' || select.value === 'NA') && !commentField.value.trim()) {
                isValid = false;
                commentField.classList.add('is-invalid');
                
                // Récupérer le numéro de l'item pour le message d'erreur
                const itemNumber = select.closest('tr').querySelector('td:first-child').textContent;
                errorMessages.push(`Item ${itemNumber} : commentaire requis pour statut ${select.value}`);
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
        if (select.disabled) return;
        handleStatusChange(select);
        select.addEventListener('change', function() {
            handleStatusChange(this);
        });
    });
});
</script>

<?php
include_once 'views/includes/footer.php';