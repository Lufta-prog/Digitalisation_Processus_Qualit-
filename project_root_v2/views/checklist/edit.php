<?php
$title = 'Modifier une Checklist';
include_once 'views/includes/header.php';

$data = [
    'checklist' => $checklist,
    'templates' => $templates,
    'projects' => $projects,
    'consultants' => $consultants,
    'consultantDetails' => $consultantDetails
];
extract($data);

// Calculer la date minimale (demain)
$minDate = date('Y-m-d', strtotime('+1 day'));
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-edit me-2"></i> Modifier une Checklist
    </h1>
    <div>
        <a href="index.php?controller=checklist&action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations de la Checklist
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form action="index.php?controller=checklist&action=processEdit&id=<?= htmlspecialchars($checklist['ID_CLC'] ?? '') ?>" method="POST" novalidate>
            <div class="row mb-4">
                <!-- Référence Unique -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Référence Unique</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?= htmlspecialchars($checklist['Reference_Unique'] ?? 'Non défini') ?>" disabled>
                    </div>
                </div>
                
                <!-- Date de Livraison Prévue -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_date" class="font-weight-bold">Date de Livraison Prévue <span class="text-danger">*</span></label>
                        <input type="date" name="expected_date" id="expected_date" class="form-control" required
                            value="<?= htmlspecialchars($checklist['Expected_Delivery_Date'] ?? '') ?>"
                            min="<?= $minDate ?>">
                        <small class="form-text text-muted">
                            La date doit être postérieure à aujourd'hui (<?= date('d/m/Y') ?>)
                        </small>
                        <div class="invalid-feedback">Veuillez saisir une date valide postérieure à aujourd'hui</div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Business Unit -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Business Unit</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?= htmlspecialchars($consultantDetails['business_unit_name'] ?? 'Non défini') ?>" disabled>
                    </div>
                </div>
                
                <!-- Activité -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Activité</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?= htmlspecialchars($consultantDetails['activity_name'] ?? 'Non défini') ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Template -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Template</label>
                        <select class="form-control bg-light" disabled>
                            <?php foreach ($templates as $template): ?>
                                <option <?= ($checklist['Template_ID'] ?? null) == $template['ID_Template'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($template['Name_Template'] ?? 'Non défini') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Projet -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Projet</label>
                        <select class="form-control bg-light" disabled>
                            <?php foreach ($projects as $project): ?>
                                <option <?= ($checklist['Project_ID'] ?? null) == $project['ID_Project'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['Name_Project'] ?? 'Non défini') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Niveau de Criticité -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Niveau de Criticité</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php
                            $critLevels = ['C1' => 'C1 - Criticité élevée', 'C2' => 'C2 - Criticité moyenne', 'C3' => 'C3 - Criticité faible'];
                            foreach ($critLevels as $value => $label):
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" disabled
                                        <?= ($checklist['Criticality'] ?? null) === $value ? 'checked' : '' ?>>
                                    <label class="form-check-label"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consultant -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Consultant</label>
                        <input type="text" class="form-control bg-light" 
                               value="<?= htmlspecialchars($consultantDetails['first_name'] . ' ' . $consultantDetails['last_name']) ?>" disabled>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                </button>
                <a href="index.php?controller=checklist&action=index" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation de la date
    const dateField = document.getElementById('expected_date');
    
    if (dateField) {
        dateField.addEventListener('change', function() {
            validateDateField(this);
        });
        
        dateField.addEventListener('blur', function() {
            validateDateField(this);
        });
    }
    
    // Fonction pour valider la date
    function validateDateField(dateField) {
        clearFieldError(dateField);
        
        if (!dateField.value.trim()) {
            if (dateField.required) {
                showFieldError(dateField, 'Ce champ est obligatoire');
                return false;
            }
            return true;
        }
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const selectedDate = new Date(dateField.value);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate <= today) {
            showFieldError(dateField, 'La date doit être postérieure à aujourd\'hui');
            return false;
        }
        
        return true;
    }
    
    // Fonction utilitaire pour afficher les erreurs (déjà définie dans scripts.js)
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        let feedback = field.nextElementSibling;
        
        while (feedback && !feedback.classList.contains('invalid-feedback')) {
            feedback = feedback.nextElementSibling;
        }
        
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        let feedback = field.nextElementSibling;
        
        while (feedback && !feedback.classList.contains('invalid-feedback')) {
            feedback = feedback.nextElementSibling;
        }
        
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
    }
    
    // Validation du formulaire
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation de la date
            if (!validateDateField(dateField)) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Faire défiler jusqu'au champ invalide
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        });
    }
});
</script>

<?php include_once 'views/includes/footer.php'; ?>