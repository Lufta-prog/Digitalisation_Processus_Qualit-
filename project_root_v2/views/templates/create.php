<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-file-alt me-2"></i> Créer un nouveau modèle
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
            <i class="fas fa-plus-circle me-2"></i> Informations du modèle
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>

        <form method="POST" action="index.php?controller=templates&action=store">
            <!-- Basic Information Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nom du modèle <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="activity_id" class="font-weight-bold">Activité associée <span class="text-danger">*</span></label>
                        <select class="form-control" id="activity_id" name="activity_id" required>
                            <option value="">Sélectionner une activité</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?= $activity['ID_Activity'] ?>"
                                    <?= (isset($_POST['activity_id']) && $_POST['activity_id'] == $activity['ID_Activity']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($activity['Name_Activity']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="description" class="font-weight-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Elements Section -->
            <div class="card mb-4 border-left-primary">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul me-2"></i> Éléments du modèle
                    </h6>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        <div class="item-form mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Nom de l'élément <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="items[0][name]" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Type</label>
                                        <select class="form-control" name="items[0][type]">
                                            <option value="DE">DE</option>
                                            <option value="DS">DS</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Description</label>
                                <textarea class="form-control" name="items[0][description]" rows="2"></textarea>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                <i class="fas fa-trash me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" id="add-item">
                        <i class="fas fa-plus me-2"></i> Ajouter un élément
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Créer le modèle
                </button>
                <a href="index.php?controller=templates" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const index = container.children.length;
    const newItem = document.createElement('div');
    newItem.className = 'item-form mb-3 p-3 border rounded';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Nom de l'élément <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="items[${index}][name]" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Type</label>
                    <select class="form-control" name="items[${index}][type]">
                        <option value="DE">DE</option>
                        <option value="DS">DS</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Description</label>
            <textarea class="form-control" name="items[${index}][description]" rows="2"></textarea>
        </div>
        <button type="button" class="btn btn-sm btn-danger remove-item">
            <i class="fas fa-trash me-1"></i> Supprimer
        </button>
    `;
    container.appendChild(newItem);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.closest('.item-form').remove();
        // Réindexer les éléments si nécessaire
        const items = document.querySelectorAll('.item-form');
        items.forEach((item, index) => {
            item.querySelectorAll('[name^="items["]').forEach(input => {
                input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    }
});
</script>

<?php include_once 'views/includes/footer.php'; ?>