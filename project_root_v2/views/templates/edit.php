<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-edit me-2"></i> Modifier le modèle
    </h1>
    <div>
        <a href="index.php?controller=templates" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Card Container -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-info-circle me-2"></i> Informations du modèle
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form method="post" action="index.php?controller=templates&action=update&id=<?= $template['ID_Template'] ?>">
            <!-- Basic Information Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nom du modèle <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" 
                               value="<?= htmlspecialchars($template['Name_Template'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="activity_id" class="font-weight-bold">Activité associée <span class="text-danger">*</span></label>
                        <select name="activity_id" id="activity_id" class="form-control" required>
                            <option value="">Sélectionner une activité</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?= $activity['ID_Activity'] ?>" 
                                    <?= ($template['Activity_ID'] ?? null) == $activity['ID_Activity'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($activity['Name_Activity'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="description" class="font-weight-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($template['Description_Template'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Elements Section -->
            <div class="card mb-4 border-left-success">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-list-ol me-2"></i> Éléments du modèle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30%">Nom</th>
                                    <th width="45%">Description</th>
                                    <th width="15%">Type</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="items[<?= $item['ID_Item'] ?? '' ?>][id]" value="<?= $item['ID_Item'] ?? '' ?>">
                                            <input type="text" class="form-control" 
                                                   name="items[<?= $item['ID_Item'] ?? '' ?>][name]" 
                                                   value="<?= htmlspecialchars($item['Name_Item'] ?? '') ?>" required>
                                        </td>
                                        <td>
                                            <textarea class="form-control" 
                                                   name="items[<?= $item['ID_Item'] ?? '' ?>][description]" 
                                                   rows="2"><?= htmlspecialchars($item['Description_Item'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <select class="form-control" name="items[<?= $item['ID_Item'] ?? '' ?>][type]">
                                                <option value="DE" <?= (isset($item['Item_Type']) && $item['Item_Type'] == 'DE') ? 'selected' : '' ?>>DE</option>
                                                <option value="DS" <?= (isset($item['Item_Type']) && $item['Item_Type'] == 'DS') ? 'selected' : '' ?>>DS</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-item" data-id="<?= $item['ID_Item'] ?? '' ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary" id="add-item">
                            <i class="fas fa-plus me-2"></i> Ajouter un élément
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                </button>
                <a href="index.php?controller=templates" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Script pour ajouter un nouvel élément
document.getElementById('add-item').addEventListener('click', function() {
    const tbody = document.querySelector('table tbody');
    const newId = Date.now(); // ID temporaire unique
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <input type="hidden" name="items[new_${newId}][id]" value="new">
            <input type="text" class="form-control" name="items[new_${newId}][name]" required>
        </td>
        <td>
            <textarea class="form-control" name="items[new_${newId}][description]" rows="2"></textarea>
        </td>
        <td>
            <select class="form-control" name="items[new_${newId}][type]">
                <option value="DE">DE</option>
                <option value="DS">DS</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
});

// Script pour supprimer un élément
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
            const btn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
            const row = btn.closest('tr');
            const itemId = btn.dataset.id;
            
            if (itemId) {
                // Si l'élément existe déjà en base, on ajoute un champ caché pour suppression
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = `items[${itemId}][delete]`;
                deleteInput.value = '1';
                row.parentNode.appendChild(deleteInput);
            }
            
            row.remove();
        }
    }
});
</script>

<?php include_once 'views/includes/footer.php'; ?>