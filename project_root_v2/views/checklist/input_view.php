<?php
$title = 'Détails de la Checklist';
include_once 'views/includes/header.php';
require_once __DIR__ . '/../../utils/helpers.php';

// Récupération des données de session/post
$post = $_SESSION['old_post'] ?? $_POST ?? [];
unset($_SESSION['old_post']);

// Configuration de l'affichage des itérations
$maxDisplayedIterations = 3;
$isChecklistFullyCompleted = ($activeIterationDE > 3 && $currentView === 'input');

// Déterminer quelles itérations afficher
if ($blockCurrentIterationInput && $activeIterationDE > 1) {
    $iterationDisplayLimit = min($activeIterationDE - 1, 3);
} else {
    $iterationDisplayLimit = min($activeIterationDE, 3);
}


?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h2 class="h3 mb-0 text-gray-800">
        <i class="fas fa-clipboard-check me-2"></i> Controle de la Checklist - Données d'Entrée
    </h2>
    <span class="badge bg-primary ms-2">
        Itération : <?= min($activeIterationDE, 3) ?>/3
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
<div class="row mb-4 justify-content-center">
    <?php foreach (range(1, $iterationDisplayLimit) as $iterationNumber): ?>
        <?php 
        $progress = $deIterationProgress[$iterationNumber];
        $percentage = $progress['total'] > 0 
            ? round(($progress['OK'] / $progress['total']) * 100, 2)
            : 0;
        $badgeClass = getBadgeClassFromPercentage($percentage);
        $validationInfo = $iterationInfo[$iterationNumber] ?? null;
        $isActiveIteration = $iterationNumber === $activeIterationDE && !$isChecklistFullyCompleted;
        $isPastIteration = $iterationNumber < $activeIterationDE || ($isChecklistFullyCompleted && $iterationNumber <= $activeIterationDE);
        ?>
        
        <div class="col-md-4 mb-2"> <!-- Changé de col-auto à col-md-4 -->
            <div class="card h-100 <?= $isActiveIteration ? 'border-left-primary' : '' ?>">
                <div class="card-body text-center d-flex flex-column"> <!-- Ajout de flexbox -->
                    <h6 class="card-title mb-2">
                        Itération <?= $iterationNumber ?>
                        <?php if ($isActiveIteration): ?>
                            <span class="badge bg-primary ms-2">
                                <i class="fas fa-sync-alt"></i> Active
                            </span>
                        <?php elseif ($isPastIteration): ?>
                            <?php if ($progress['allOK']): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="fas fa-check-circle"></i> <?= $isChecklistFullyCompleted ? 'Finalisée' : 'Validée' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="fas fa-check-circle"></i> Achevée
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary ms-2">
                               <i class="fas fa-hourglass-half"></i> Future
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
                    
                    <span class="badge <?= $badgeClass ?> mb-3">
                        <?= $percentage ?>% DE OK
                    </span>
                    
                    <!-- Validation Info -->
                    <?php if ($validationInfo): ?>
                        <div class="mt-auto small text-muted"> <!-- mt-auto pour pousser vers le bas -->
                            <i class="fas fa-calendar-check"></i> Validée le <?= htmlspecialchars($validationInfo['date']) ?>
                            <br><i class="fas fa-user"></i> Par <?= htmlspecialchars($validationInfo['by']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Items Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-tasks me-2"></i> Items de la Checklist
        </h6>
    </div>
    <div class="card-body">
        <form action="index.php?controller=checklist&action=updateItems&id=<?= $checklist['ID_CLC'] ?>" method="POST" id="checklistForm" class="checklist-form">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px"></th>
                            <th class="text-center align-middle">
                                <div class="font-weight-bold">
                                    <?= htmlspecialchars($templateInfo['Name_Template']) ?>
                                </div>
                            </th>
                            <?php for ($i = 1; $i <= $iterationDisplayLimit; $i++): ?>
                                <th colspan="2" class="text-center <?= ($i === $activeIterationDE && !$isChecklistFullyCompleted && !$blockCurrentIterationInput) ? 'bg-primary text-white' : '' ?>">
                                    Itération <?= $i ?>
                                </th>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <th style="width: 50px"></th>
                            <th>Item</th>
                            <?php for ($i = 1; $i <= $iterationDisplayLimit; $i++): ?>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Commentaire</th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items['DE'])): ?>
                            <?php foreach ($items['DE'] as $index => $item): ?>
                                <tr>
                                    <!-- <?= $index + 1 ?>-->
                                    <td class="text-center fw-bold"></td> 
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    
                                    <?php for ($i = 1; $i <= $iterationDisplayLimit; $i++): ?>
                                        <?php
                                            $is_editable_iteration = ($i === $activeIterationDE && !$isChecklistFullyCompleted && !$blockCurrentIterationInput);
                                            $is_past_iteration = ($i < $activeIterationDE || ($isChecklistFullyCompleted && $i <= $activeIteration));
                                            $is_future_iteration = ($i > $activeIterationDE && !$isChecklistFullyCompleted) || ($i === $activeIterationDE && $blockCurrentIterationInput);
                                            
                                            $cell_class = '';
                                            if ($is_future_iteration) $cell_class = 'bg-light';
                                            
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
                                        ?>
                                        <!-- Status Column -->
                                        <td class="<?= $cell_class ?>">
                                            <?php if ($is_editable_iteration): ?>
                                                <select class="form-control form-control-sm item-status"
                                                        name="items[<?= $item['id'] ?>][status]"
                                                        data-item-id="<?= $item['id'] ?>" data-iteration="<?= $i ?>">
                                                    <option value="OK" <?= ($current_iter_status ?? 'NA') === 'OK' ? 'selected' : '' ?>>OK</option>
                                                    <option value="NOK" <?= ($current_iter_status ?? 'NA') === 'NOK' ? 'selected' : '' ?>>NOK</option>
                                                    <option value="NA" <?= ($current_iter_status ?? 'NA') === 'NA' ? 'selected' : '' ?>>NA</option>
                                                </select>
                                            <?php else: ?>
                                                <span class="badge <?= 'bg-' . ($current_iter_status === 'OK' ? 'success' : ($current_iter_status === 'NOK' ? 'danger' : 'secondary')) ?>">
                                                    <?= htmlspecialchars($current_iter_status ?? 'NA') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Comment Column -->
                                        <td class="<?= $cell_class ?>">
                                            <?php if ($is_editable_iteration): ?>
                                                <textarea class="form-control form-control-sm comments-field"
                                                          name="items[<?= $item['id'] ?>][comment]"
                                                          rows="2"
                                                          data-item-id="<?= $item['id'] ?>" data-iteration="<?= $i ?>"
                                                          <?= ($activeIterationDE === 3 && ($current_iter_status ?? 'NA') === 'OK') ? 'disabled' : '' ?>
                                                          ><?= htmlspecialchars(trim($current_iter_comment ?? '')) ?></textarea>
                                            <?php else: ?>
                                                <?php
                                                if (!empty(trim($current_iter_comment ?? ''))) {
                                                    echo '<div class="small">' . htmlspecialchars($current_iter_comment) . '</div>';
                                                } elseif (($current_iter_status ?? '') === 'OK') {
                                                    echo '<div class="text-success"><i class="fas fa-check"></i></div>';
                                                } else {
                                                    echo '<div class="text-muted small">(aucun commentaire)</div>';
                                                }
                                                ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 2 + ($iterationDisplayLimit * 2) ?>" class="text-center py-4">
                                    <div class="text-muted">Aucun item d'entrée trouvé pour cette activité</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <?php if ($isChecklistFullyCompleted): ?>
                        <div class="alert alert-success mb-0 py-2">
                            <i class="fas fa-check-circle"></i> Checklist terminée. Toutes les itérations sont complétées.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <?php if ($activeIterationDE > 1): ?>
                        <a href="index.php?controller=checklist&action=view&id=<?= $checklist['ID_CLC'] ?>&view=output" 
                        class="btn btn-primary me-2">
                            <i class="fas fa-arrow-right me-2"></i> Passer au DS
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!$isChecklistFullyCompleted && $activeIterationDE <= 3 && !$blockCurrentIterationInput): ?>
                        <button type="submit" class="btn btn-success" id="submitButton">
                            <i class="fas fa-save me-2"></i>
                            Valider l'itération <?= $activeIterationDE ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $isChecklistFullyCompleted ? 'Checklist Complétée' : 'Itération Terminée' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initChecklistPage === 'function') {
        initChecklistPage({
            formId: 'checklistForm',
            statusClass: 'item-status',
            commentClass: 'comments-field'
        });
    }
    
    // Validation spécifique pour l'itération 3
    const form = document.getElementById('checklistForm');
    const submitButton = document.getElementById('submitButton');

    if (form && submitButton) {
        form.addEventListener('submit', function(e) {
            const currentActiveIteration = <?= $activeIterationDE?>;
            const isFinalActiveIteration = currentActiveIteration === 3;
            const isChecklistDone = <?= $isChecklistFullyCompleted ? 'true' : 'false' ?>;
            const blockCurrentInputJS = <?= $blockCurrentIterationInput ? 'true' : 'false' ?>;
 
            if (isChecklistDone || blockCurrentInputJS) {
                e.preventDefault();
                alert(isChecklistDone ? 'La checklist est déjà terminée. Aucune modification n\'est possible.' : 'L\'itération précédente étant complétée avec tous les items OK, cette itération est bloquée.');
                return false;
            }
            
            if (isFinalActiveIteration) {
                let allValidForFinal = true;
                const invalidElements = [];

                document.querySelectorAll(`.item-status[data-iteration='${currentActiveIteration}']`).forEach(select => {
                    const status = select.value;
                    const textarea = select.closest('tr').querySelector(`textarea.comments-field[data-item-id='${select.dataset.itemId}'][data-iteration='${currentActiveIteration}']`);
                    const comment = textarea ? textarea.value.trim() : '';

                    select.closest('td').classList.remove('border-danger');

                    if (status === 'NOK') {
                        allValidForFinal = false;
                        invalidElements.push(select);
                        select.closest('td').classList.add('border-danger');
                    }
                });
                
                if (!allValidForFinal) {
                    e.preventDefault();
                    alert('Pour l\'itération finale (3), tous les items en NOK doivent avoir le statut OK.');
                    if (invalidElements.length > 0) {
                        invalidElements[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        invalidElements[0].focus();
                    }
                    return false;
                }
            }
        });
    }
});
</script>

<?php include_once 'views/includes/footer.php'; ?>