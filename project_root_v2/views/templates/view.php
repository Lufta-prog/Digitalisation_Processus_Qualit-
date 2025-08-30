<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-eye me-2"></i> Détails du modèle
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
            <i class="fas fa-info-circle me-2"></i> Informations générales
        </h6>
    </div>
    <div class="card-body">
        <?php include 'views/includes/alerts.php'; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><strong>Nom du modèle</strong></label>
                    <p class="form-control-plaintext"><?= htmlspecialchars($template['Name_Template']) ?></p>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold"><strong>Activité associée</strong></label>
                    <p class="form-control-plaintext"><?= htmlspecialchars($template['Activity_Name'] ?? 'Non assignée') ?></p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><strong>Description</strong></label>
                    <div class="p-3 bg-light rounded">
                        <?= !empty($template['Description_Template']) ? nl2br(htmlspecialchars($template['Description_Template'])) : '<span class="text-muted">Aucune description fournie</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Elements Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list-ul me-2"></i> Éléments du modèle
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($items)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-gray-200">
                        <tr>
                            <th width="30%">Nom</th>
                            <th width="15%">Type</th>
                            <th width="55%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['Name_Item']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= $item['Item_Type'] === 'DE' ? 'bg-info' : 'bg-warning' ?>">
                                        <?= htmlspecialchars($item['Item_Type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($item['Description_Item']) ? nl2br(htmlspecialchars($item['Description_Item'])) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Aucun élément trouvé pour ce modèle.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Action Buttons -->
<div class="form-group mt-4">
    <a href="index.php?controller=templates&action=edit&id=<?= $template['ID_Template'] ?>" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i> Modifier le modèle
    </a>
    <a href="index.php?controller=templates" class="btn btn-secondary">
        <i class="fas fa-times me-2"></i> Annuler
    </a>
</div>

<?php include_once 'views/includes/footer.php'; ?>