<?php 
// views/delivrables/edit.php

// Définit le titre
$pageTitle = "Modifier le Livrable";

// Vérif. auth
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: index.php?controller=auth&action=login'); exit;
}

// Vérif. existence
if (empty($delivrable)) {
    $_SESSION['error'] = "Le livrable demandé n'existe pas.";
    header('Location: index.php?controller=delivrables'); exit;
}

// Header
include_once 'views/includes/header.php'; 
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 text-gray-800">
    <i class="fas fa-edit me-2"></i>Modifier #<?= $delivrable['ID_Row'] ?>
  </h1>
  <a href="index.php?controller=delivrables" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Retour
  </a>
</div>

<?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?= $_SESSION['error'] ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form id="editForm" action="index.php?controller=delivrables&action=update&id=<?= $delivrable['ID_Row'] ?>" method="post">
  <div class="row">
    <div class="col-lg-6">
      <!-- --- Général --- -->
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h6 class="text-primary mb-0"><i class="fas fa-info-circle me-2"></i>Général</h6>
        </div>
        <div class="card-body">
          <!-- ID Topic -->
          <div class="mb-3">
            <label class="form-label required-field">ID Topic</label>
            <input type="number" name="ID_Topic" class="form-control" required
                   value="<?= $delivrable['ID_Topic'] ?>">
          </div>
          <!-- Description -->
          <div class="mb-3">
            <label class="form-label required-field">Description</label>
            <textarea name="Description_Topic" class="form-control" rows="3" required><?= htmlspecialchars($delivrable['Description_Topic']) ?></textarea>
          </div>
          <!-- Leader / Demandeur -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label required-field">Leader</label>
              <select name="Leader_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($users as $u): ?>
                  <option value="<?= $u['ID_User'] ?>"
                    <?= $u['ID_User']==$delivrable['Leader_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($u['Full_Name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <label class="form-label required-field">Demandeur</label>
              <select name="Requester_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($users as $u): ?>
                  <option value="<?= $u['ID_User'] ?>"
                    <?= $u['ID_User']==$delivrable['Requester_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($u['Full_Name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <!-- Client / Projet -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label required-field">Client</label>
              <select name="Customer_ID" id="Customer_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($customers as $c): ?>
                  <option value="<?= $c['ID_Customer'] ?>"
                    <?= $c['ID_Customer']==$delivrable['Customer_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($c['Name_Customer']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <label class="form-label required-field">Projet</label>
              <select name="Project_ID" id="Project_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($projects as $p): ?>
                  <option value="<?= $p['ID_Project'] ?>"
                    <?= $p['ID_Project']==$delivrable['Project_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($p['Name_Project']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <!-- Activité / Périmètre -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label required-field">Activité</label>
              <select name="Activity_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($activities as $a): ?>
                  <option value="<?= $a['ID_Activity'] ?>"
                    <?= $a['ID_Activity']==$delivrable['Activity_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($a['Name_Activity']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <label class="form-label required-field">Périmètre</label>
              <select name="Perimeter_ID" class="form-select select2" required>
                <option value="">Sélectionnez…</option>
                <?php foreach($perimeters as $per): ?>
                  <option value="<?= $per['ID_Perimeter'] ?>"
                    <?= $per['ID_Perimeter']==$delivrable['Perimeter_ID']?'selected':'' ?>>
                    <?= htmlspecialchars($per['Name_Perimeter']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <!-- Typologie -->
          <div class="mb-3">
            <label class="form-label">Typologie</label>
            <select name="Typologie_ID" class="form-select select2">
              <option value="">Aucune</option>
              <?php foreach($typologies as $t): ?>
                <option value="<?= $t['ID_Typologie'] ?>"
                  <?= $t['ID_Typologie']==$delivrable['Typologie_ID']?'selected':'' ?>>
                  <?= htmlspecialchars($t['Nom_Typologie']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Checkbox Livrable -->
          <div class="form-check form-switch mb-3">
            <input type="checkbox" name="Livrable" class="form-check-input"
                   id="Livrable" <?= $delivrable['Livrable']?'checked':'' ?>>
            <label for="Livrable" class="form-check-label">Livrable officiel</label>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <!-- --- Validation --- -->
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h6 class="text-success mb-0"><i class="fas fa-clipboard-check me-2"></i>Validation</h6>
        </div>
        <div class="card-body">
          <!-- Type de validation -->
          <div class="mb-3">
            <label class="form-label">Type validation</label>
            <select name="type_validation" id="type_validation" class="form-select">
              <option value="">Aucun</option>
              <option value="checklist" <?= $delivrable['type_validation']=='checklist'?'selected':'' ?>>Checklist</option>
              <option value="derogation" <?= $delivrable['type_validation']=='derogation'?'selected':'' ?>>Dérogation</option>
              <option value="NC" <?= $delivrable['type_validation']=='NC'?'selected':'' ?>>NC</option>
            </select>
          </div>
          <!-- CLC -->
          <div class="mb-3 validation-field checklist-field <?= $delivrable['type_validation']==='checklist'?'':'d-none' ?>">
            <label class="form-label">CLC</label>
            <select name="CLC_ID" id="CLC_ID" class="form-select select2">
              <option value="">Sélectionnez…</option>
              <?php foreach($clcs as $c): ?>
                <option value="<?= $c['ID_CLC'] ?>"
                  <?= $c['ID_CLC']==$delivrable['CLC_ID']?'selected':'' ?>>
                  <?= $c['ID_CLC'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Dérogation -->
          <div class="mb-3 validation-field derogation-field <?= $delivrable['type_validation']==='derogation'?'':'d-none' ?>">
            <label class="form-label">Dérogation</label>
            <select name="ID_Derogation" id="ID_Derogation" class="form-select select2">
              <option value="">Sélectionnez…</option>
              <?php foreach($derogations as $d): ?>
                <option value="<?= $d['ID_Derogation'] ?>"
                  <?= $d['ID_Derogation']==$delivrable['ID_Derogation']?'selected':'' ?>>
                  <?= htmlspecialchars($d['Nom_Derogation']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Dates & statut -->
          <div class="row mb-3">
            <?php foreach(['Original_Expected_Date'=>'Prévue','Postponed_Date'=>'Reportée','Real_Date'=>'Réelle'] as $field=>$lbl): ?>
            <div class="col">
              <label class="form-label"><?= $lbl ?> (JJ/MM/AAAA)</label>
              <input type="date" name="<?= $field ?>" class="form-control"
                     value="<?= $delivrable[$field] ?>">
            </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label required-field">Statut</label>
            <select name="Status_Delivrables" class="form-select" required>
              <option value="In Progress" <?= $delivrable['Status_Delivrables']=='In Progress'?'selected':'' ?>>En cours</option>
              <option value="Closed"      <?= $delivrable['Status_Delivrables']=='Closed'?'selected':'' ?>>Terminé</option>
              <option value="Cancelled"   <?= $delivrable['Status_Delivrables']=='Cancelled'?'selected':'' ?>>Annulé</option>
            </select>
          </div>

          <!-- Commentaire -->
          <div class="mb-3">
            <label class="form-label">Commentaire</label>
            <textarea name="Comment" class="form-control" rows="2"><?= htmlspecialchars($delivrable['Comment']) ?></textarea>
          </div>

          <!-- Indicateurs -->
          <div class="row mb-3">
            <?php foreach([
              'FTR_Segula'=>'FTR Segula','OTD_Segula'=>'OTD Segula',
              'FTR_Customer'=>'FTR Client','OTD_Customer'=>'OTD Client'
            ] as $field=>$lbl): ?>
            <div class="col">
              <label class="form-label"><?= $lbl ?></label>
              <select name="<?= $field ?>" class="form-select">
                <?php foreach(['NA','OK','NOK'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $delivrable[$field]==$opt?'selected':'' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="d-flex justify-content-between mb-4">
    <a href="index.php?controller=delivrables" class="btn btn-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
  </div>
</form>

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
