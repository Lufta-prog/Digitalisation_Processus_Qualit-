<?php 
$pageTitle = "Nouveau Livrable";

// Calcul automatique du prochain ID_Topic

include_once 'views/includes/header.php'; 
?>

<!-- Page heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-plus-circle me-2"></i> Ajouter un Nouveau Livrable
    </h1>
    <a href="index.php?controller=delivrables" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
</div>

<!-- Alerts -->
<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Form Card -->
<div class="card">
    <div class="card-header primary-gradient">
        <h6 class="m-0 font-weight-bold text-white">Informations du Livrable</h6>
    </div>
    <div class="card-body">
        <form action="index.php?controller=delivrables&action=store" method="post" id="createDelivrableForm">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-6">
                    <div class="card mb-4 border-left-primary h-100">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle me-2"></i>Informations Générales
                            </h6>
                        </div>
                        <div class="card-body">
                        <div class="mb-3">
    <label for="ID_Topic" class="form-label required-field">ID Topic</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
        <input type="number" class="form-control bg-light" id="ID_Topic_Display" value="<?= $nextIDTopic ?>" disabled>
        <input type="hidden" name="ID_Topic" value="<?= $nextIDTopic ?>">
    </div>
    <div class="form-text text-info">ID généré automatiquement basé sur le dernier livrable</div>
</div>
                            
                            <div class="mb-3">
                                <label for="Description_Topic" class="form-label required-field">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <textarea class="form-control" id="Description_Topic" name="Description_Topic" rows="3" required></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Leader_ID" class="form-label required-field">Leader</label>
                                        <select class="form-select select2" id="Leader_ID" name="Leader_ID" required>
                                            <option value="">Sélectionner un leader</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['ID_User'] ?>"><?= htmlspecialchars($user['Full_Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Requester_ID" class="form-label required-field">Demandeur</label>
                                        <select class="form-select select2" id="Requester_ID" name="Requester_ID" required>
                                            <option value="">Sélectionner un demandeur</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['ID_User'] ?>"><?= htmlspecialchars($user['Full_Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Customer_ID" class="form-label required-field">Client</label>
                                        <select class="form-select select2" id="Customer_ID" name="Customer_ID" required>
                                            <option value="">Sélectionner un client</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['ID_Customer'] ?>"><?= htmlspecialchars($customer['Name_Customer']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Project_ID" class="form-label required-field">Projet</label>
                                        <select class="form-select select2" id="Project_ID" name="Project_ID" required>
                                            <option value="">Sélectionner un projet</option>
                                            <?php foreach ($projects as $project): ?>
                                                <option value="<?= $project['ID_Project'] ?>"><?= htmlspecialchars($project['Name_Project']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Activity_ID" class="form-label required-field">Activité</label>
                                        <select class="form-select select2" id="Activity_ID" name="Activity_ID" required>
                                            <option value="">Sélectionner une activité</option>
                                            <?php foreach ($activities as $activity): ?>
                                                <option value="<?= $activity['ID_Activity'] ?>"><?= htmlspecialchars($activity['Name_Activity']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="Perimeter_ID" class="form-label required-field">Périmètre</label>
                                        <select class="form-select select2" id="Perimeter_ID" name="Perimeter_ID" required>
                                            <option value="">Sélectionner un périmètre</option>
                                            <?php foreach ($perimeters as $perimeter): ?>
                                                <option value="<?= $perimeter['ID_Perimeter'] ?>"><?= htmlspecialchars($perimeter['Name_Perimeter']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="Typologie_ID" class="form-label">Typologie</label>
                                <select class="form-select select2" id="Typologie_ID" name="Typologie_ID">
                                    <option value="">Sélectionner une typologie</option>
                                    <?php foreach ($typologies as $typologie): ?>
                                        <option value="<?= $typologie['ID_Typologie'] ?>"><?= htmlspecialchars($typologie['Nom_Typologie']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="Livrable" name="Livrable">
                                <label class="form-check-label" for="Livrable">
                                    <span class="badge bg-primary rounded-pill">Livrable</span>
                                </label>
                                <div class="form-text">Cocher si c'est un livrable officiel</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-lg-6">
                    <div class="card mb-4 border-left-success h-100">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-clipboard-check me-2"></i>Informations de Validation
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="type_validation" class="form-label">Type de Validation</label>
                                <select class="form-select" id="type_validation" name="type_validation">
                                    <option value="">Sélectionner un type</option>
                                    <option value="checklist">Checklist</option>
                                    <option value="derogation">Dérogation</option>
                                    <option value="NC">NC</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 validation-field checklist-field d-none">
                                        <label for="CLC_ID" class="form-label">CLC</label>
                                        <select class="form-select select2" id="CLC_ID" name="CLC_ID">
                                            <option value="">Sélectionner un CLC</option>
                                            <?php foreach ($clcs as $clc): ?>
                                                <option value="<?= $clc['ID_CLC'] ?>"><?= htmlspecialchars($clc['ID_CLC']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 validation-field derogation-field d-none">
                                        <label for="ID_Derogation" class="form-label">Dérogation</label>
                                        <select class="form-select select2" id="ID_Derogation" name="ID_Derogation">
                                            <option value="">Sélectionner une dérogation</option>
                                            <?php foreach ($derogations as $derogation): ?>
                                                <option value="<?= $derogation['ID_Derogation'] ?>"><?= htmlspecialchars($derogation['Nom_Derogation']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="Original_Expected_Date" class="form-label">Date Prévue Initiale</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="Original_Expected_Date" name="Original_Expected_Date">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="Postponed_Date" class="form-label">Date Reportée</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                            <input type="date" class="form-control" id="Postponed_Date" name="Postponed_Date">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="Real_Date" class="form-label">Date Réelle</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                            <input type="date" class="form-control" id="Real_Date" name="Real_Date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="Status_Delivrables" class="form-label required-field">Statut</label>
                                <select class="form-select" id="Status_Delivrables" name="Status_Delivrables" required>
                                    <option value="In Progress" selected>En cours</option>
                                    <option value="Closed">Terminé</option>
                                    <option value="Cancelled">Annulé</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="Comment" class="form-label">Commentaire</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                    <textarea class="form-control" id="Comment" name="Comment" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-info mb-3">
                                        <div class="card-header bg-info text-white"><i class="fas fa-building me-2"></i>Segula</div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="FTR_Segula" class="form-label">FTR</label>
                                                        <select class="form-select" id="FTR_Segula" name="FTR_Segula">
                                                            <option value="NA" selected>N/A</option>
                                                            <option value="OK">OK</option>
                                                            <option value="NOK">NOK</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="OTD_Segula" class="form-label">OTD</label>
                                                        <select class="form-select" id="OTD_Segula" name="OTD_Segula">
                                                            <option value="NA" selected>N/A</option>
                                                            <option value="OK">OK</option>
                                                            <option value="NOK">NOK</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-warning mb-3">
                                        <div class="card-header bg-warning text-dark"><i class="fas fa-user-tie me-2"></i>Client</div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="FTR_Customer" class="form-label">FTR</label>
                                                        <select class="form-select" id="FTR_Customer" name="FTR_Customer">
                                                            <option value="NA" selected>N/A</option>
                                                            <option value="OK">OK</option>
                                                            <option value="NOK">NOK</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="OTD_Customer" class="form-label">OTD</label>
                                                        <select class="form-select" id="OTD_Customer" name="OTD_Customer">
                                                            <option value="NA" selected>N/A</option>
                                                            <option value="OK">OK</option>
                                                            <option value="NOK">NOK</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4 border-left-info">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-info-circle me-2"></i>Légende
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-2"><strong>FTR</strong>: First Time Right</div>
                                    <div>Qualité du livrable à la première remise</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2"><strong>OTD</strong>: On Time Delivery</div>
                                    <div>Respect des délais de livraison</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2"><strong>OK</strong>: Conforme aux attentes</div>
                                    <div><span class="badge bg-success">Réussite</span></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2"><strong>NOK</strong>: Non conforme aux attentes</div>
                                    <div><span class="badge bg-danger">Échec</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php?controller=delivrables" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Page specific script -->

<script>
document.addEventListener('DOMContentLoaded', function(){
  // Init Select2 on all selects
  $('.select2').select2({
    theme: 'bootstrap-5',
    width: '100%',
    allowClear: true,
    placeholder: 'Sélectionner…'
  });

  const $typeVal = $('#type_validation');
  const $clc     = $('#CLC_ID');
  const $der     = $('#ID_Derogation');

  function toggleValidationFields() {
    const v = $typeVal.val();

    // Hide both blocks, disable & clear both selects
    $('.validation-field').addClass('d-none');
    $clc.prop('disabled', true).val('').trigger('change.select2');
    $der.prop('disabled', true).val('').trigger('change.select2');

    // Show & enable the one matching the selected type
    if (v === 'checklist') {
      $('.checklist-field').removeClass('d-none');
      $clc.prop('disabled', false);
    }
    else if (v === 'derogation') {
      $('.derogation-field').removeClass('d-none');
      $der.prop('disabled', false);
    }
    // if v is '' or 'NC', both stay hidden/disabled
  }

  // Bind change and run once on load
  $typeVal.on('change', toggleValidationFields);
  toggleValidationFields();
});
</script>





<?php include_once 'views/includes/footer.php'; ?>