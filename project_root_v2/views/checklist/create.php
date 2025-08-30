
<?php
$title = 'Créer une Checklist';
include_once 'views/includes/header.php';

$data = [
    'consultantDetails' => $consultantDetails,
    'templates' => $templates,
    'projects' => $projects,
    'consultants' => $consultants
];
extract($data);

// Pré-remplir les champs après une erreur ou soumission
$post = $_SESSION['old_post'] ?? $_POST ?? [];
unset($_SESSION['old_post']);
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-clipboard-check me-2"></i> Créer une Checklist
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

        <form action="index.php?controller=checklist&action=processCreation" method="POST" novalidate>
            <!-- Référence Unique -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="reference_unique" class="font-weight-bold">Référence Unique <span class="text-danger">*</span></label>
                        <input type="text" name="reference_unique" id="reference_unique" class="form-control" 
                               value="<?= htmlspecialchars($post['reference_unique'] ?? '') ?>" required
                               pattern="CLC_BU\d+_[a-zA-Z0-9_]+" 
                               title="Format: CLC_BUXX_NomActivite (ex: CLC_BU1_Interiors)">
                        <small class="form-text text-muted">
                            Format: <strong>CLC_BU{numéro BU}_{NomActivité}</strong> (sans espaces, utilisez des underscores).<br>
                            Exemples: CLC_BU1_Interiors, CLC_BU5_VBA, CLC_BU3_Optimisation
                        </small>
                        <div class="invalid-feedback">Veuillez saisir une référence valide</div>
                    </div>
                </div>
            </div>

            
            <!-- Template et Projet -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="template_id" class="font-weight-bold">Template <span class="text-danger">*</span></label>
                        <select name="template_id" id="template_id" class="form-control select2" required>
                            <option value="">Sélectionnez un template</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?= $template['ID_Template'] ?>" 
                                        <?= (isset($post['template_id']) && $post['template_id'] == $template['ID_Template']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($template['Name_Template']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un template</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="project_id" class="font-weight-bold">Projet <span class="text-danger">*</span></label>
                        <select name="project_id" id="project_id" class="form-control select2" required>
                            <option value="">Sélectionnez un projet</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['ID_Project'] ?>" <?= (isset($post['project_id']) && $post['project_id'] == $project['ID_Project']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['Name_Project']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un projet</div>
                    </div>
                </div>
            </div>
            
            <!-- Business Unit et Activité -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Business Unit <span class="text-danger">*</span></label>
                        <input type="text" id="bu_field" class="form-control bg-light" readonly>
                        <input type="hidden" name="bu_id" id="bu_id">
                        <div class="invalid-feedback">Veuillez d'abord sélectionner un template</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Activité <span class="text-danger">*</span></label>
                        <input type="text" id="activity_field" class="form-control bg-light" readonly>
                        <input type="hidden" name="activity_id" id="activity_id">
                        <div class="invalid-feedback">Veuillez d'abord sélectionner un template</div>
                    </div>
                </div>
            </div>


            <!-- Niveau de Criticité -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Niveau de Criticité <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php
                            $critLevels = ['C1' => 'C1 - Criticité élevée', 'C2' => 'C2 - Criticité moyenne', 'C3' => 'C3 - Criticité faible'];
                            foreach ($critLevels as $value => $label):
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="criticality" id="crit_<?= $value ?>" value="<?= $value ?>" required
                                        <?= (isset($post['criticality']) && $post['criticality'] === $value) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="crit_<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="criticality-feedback" class="invalid-feedback" style="display: none;">Veuillez sélectionner un niveau de criticité</div>
                    </div>
                </div>
            </div>

            <!-- Consultant -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Consultant</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($consultantDetails['first_name'] . ' ' . $consultantDetails['last_name']) ?>" readonly>
                        <input type="hidden" name="consultant_id" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">
                    </div>
                </div>
                <!-- Dans la section du formulaire -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_date" class="font-weight-bold">Date de Livraison Prévue <span class="text-danger">*</span></label>
                        <input type="date" name="expected_date" id="expected_date" class="form-control" required
                            value="<?= htmlspecialchars($post['expected_date'] ?? '') ?>"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        <small class="form-text text-muted">
                            La date doit être postérieure à aujourd'hui (<?= date('d/m/Y') ?>)
                        </small>
                        <div class="invalid-feedback">Veuillez saisir une date valide postérieure à aujourd'hui</div>
                    </div>
                </div>
            </div>

            <!-- Validateurs (dynamiques selon criticité) -->
            <div id="qg1_container" class="row mb-4" style="display:none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="qg1_id" class="font-weight-bold">Validateur QG1</label>
                        <select name="qg1_id" id="qg1_id" class="form-control select2">
                            <option value="">Sélectionnez un validateur QG1</option>
                            <?php foreach ($consultants as $consultant): ?>
                                <option value="<?= $consultant['ID_User'] ?>" <?= (isset($post['qg1_id']) && $post['qg1_id'] == $consultant['ID_User']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($consultant['Fname_User'] . ' ' . $consultant['Lname_User']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un validateur</div>
                    </div>
                </div>
            </div>

            <div id="qg2_container" class="row mb-4" style="display:none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="qg2_id" class="font-weight-bold">Validateur QG2</label>
                        <select name="qg2_id" id="qg2_id" class="form-control select2">
                            <option value="">Sélectionnez un validateur QG2</option>
                            <?php foreach ($consultants as $consultant): ?>
                                <option value="<?= $consultant['ID_User'] ?>" <?= (isset($post['qg2_id']) && $post['qg2_id'] == $consultant['ID_User']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($consultant['Fname_User'] . ' ' . $consultant['Lname_User']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un validateur</div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Créer la Checklist
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
    // Initialiser Select2
    $('.select2').select2();

    // Fonction pour charger les infos du template
    function loadTemplateInfo(templateId) {
        if (!templateId) {
            $('#bu_field').val('');
            $('#bu_id').val('');
            $('#activity_field').val('');
            $('#activity_id').val('');
            return;
        }

        // Afficher un indicateur de chargement
        $('#bu_field').val('Chargement...');
        $('#activity_field').val('Chargement...');

        $.ajax({
            url: `index.php?controller=checklist&action=getTemplateInfo&id=${templateId}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#bu_field').val(data.bu_name);
                    $('#bu_id').val(data.bu_id);
                    $('#activity_field').val(data.activity_name);
                    $('#activity_id').val(data.activity_id);
                    
                    // Retirer les classes d'erreur si elles existent
                    $('#bu_field, #activity_field').removeClass('is-invalid');
                } else {
                    $('#bu_field').val('Erreur de chargement');
                    $('#activity_field').val('Erreur de chargement');
                    console.error(data.error);
                }
            },
            error: function(xhr, status, error) {
                $('#bu_field').val('Erreur de chargement');
                $('#activity_field').val('Erreur de chargement');
                console.error("Erreur AJAX:", error);
            }
        });
    }

    // Gestion des champs QG1 et QG2 selon la criticité
    function toggleQGFields() {
        const crit = $('input[name="criticality"]:checked').val();
        const qg1Container = $('#qg1_container');
        const qg2Container = $('#qg2_container');
        const qg1Select = $('#qg1_id');
        const qg2Select = $('#qg2_id');

        if (crit === 'C1' || crit === 'C2') {
            qg1Container.show();
            qg1Select.prop('required', true);
        } else {
            qg1Container.hide();
            qg1Select.prop('required', false).val('');
            qg1Select.trigger('change');
        }

        if (crit === 'C1') {
            qg2Container.show();
            qg2Select.prop('required', true);
        } else {
            qg2Container.hide();
            qg2Select.prop('required', false).val('');
            qg2Select.trigger('change');
        }
    }

    // Écouter les changements de template
    $('#template_id').on('change', function() {
        loadTemplateInfo($(this).val());
    });

    // Écouter les changements de criticité
    $('input[name="criticality"]').on('change', toggleQGFields);

    // Validation du formulaire
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Réinitialiser les états d'erreur
        $('.is-invalid').removeClass('is-invalid');
        
        // Validation des champs requis
        const requiredFields = [
            { id: 'reference_unique', message: 'La référence unique est requise' },
            { id: 'template_id', message: 'Veuillez sélectionner un template' },
            { id: 'project_id', message: 'Veuillez sélectionner un projet' },
            { id: 'expected_date', message: 'Veuillez saisir une date de livraison' }
        ];
        
        requiredFields.forEach(field => {
            const element = $(`#${field.id}`);
            if (!element.val().trim()) {
                element.addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Validation spécifique de la référence
        const referenceInput = $('#reference_unique');
        if (referenceInput.val().trim() && !/^CLC_BU\d+_[a-zA-Z0-9_]+$/.test(referenceInput.val())) {
            referenceInput.addClass('is-invalid');
            isValid = false;
        }
        
        // Validation de la criticité
        if (!$('input[name="criticality"]:checked').length) {
            $('#criticality-feedback').show();
            isValid = false;
        } else {
            $('#criticality-feedback').hide();
        }
        
        // Vérifier que les champs BU et Activité sont remplis
        if (!$('#bu_id').val() || !$('#activity_id').val()) {
            $('#bu_field, #activity_field').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Faire défiler vers le premier champ invalide
            $('.is-invalid').first().get(0).scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            $('.is-invalid').first().focus();
        }
    });

    // Initialiser les champs QG
    toggleQGFields();
    
    // Si un template est déjà sélectionné (après erreur de validation), charger ses infos
    if ($('#template_id').val()) {
        loadTemplateInfo($('#template_id').val());
    }
});
</script>

<?php include_once 'views/includes/footer.php'; ?>