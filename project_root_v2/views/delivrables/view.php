<?php 
// Définition du titre de la page
$pageTitle = "Détails du Livrable";

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: index.php?controller=auth&action=login');
    exit;
}

// Vérification que le livrable existe
if (!isset($delivrable) || empty($delivrable)) {
    $_SESSION['error'] = "Le livrable demandé n'existe pas.";
    header('Location: index.php?controller=delivrables');
    exit;
}
$status = [
    'Status_Delivrables' => $status['Status_Delivrables'] ?? '',
    'Original_Expected_Date' => $status['Original_Expected_Date'] ?? '',
    'Postponed_Date' => $status['Postponed_Date'] ?? '',
    'Real_Date' => $status['Real_Date'] ?? '',
    'Open_Date' => $status['Open_Date'] ?? '',
];

$validation = [
    'FTR_Customer' => $validation['FTR_Customer'] ?? 'NA',
    'OTD_Customer' => $validation['OTD_Customer'] ?? 'NA',
    'FTR_Segula' => $validation['FTR_Segula'] ?? 'NA',
    'OTD_Segula' => $validation['OTD_Segula'] ?? 'NA',
];

// Inclusion de l'en-tête
include_once 'views/includes/header.php'; 
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-clipboard-list me-2"></i> Détails du Livrable #<?= $delivrable['ID_Row'] ?>
    </h1>
    <div>
        <a href="index.php?controller=delivrables&action=edit&id=<?= $delivrable['ID_Row'] ?>" class="btn btn-warning me-2">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <a href="index.php?controller=delivrables" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Détails du livrable -->
<div class="row">
    <!-- Informations générales -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Informations Générales</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%">ID Livrable:</th>
                                <td><?= $delivrable['ID_Row'] ?></td>
                            </tr>
                            <tr>
                                <th>ID Topic:</th>
                                <td><?= $delivrable['ID_Topic'] ?></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td><?= htmlspecialchars($delivrable['Description_Topic']) ?></td>
                            </tr>
                            <tr>
                                <th>Leader:</th>
                                <td><?= htmlspecialchars($delivrable['Leader_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Demandeur:</th>
                                <td><?= htmlspecialchars($delivrable['Requester_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Typologie:</th>
                                <td><?= htmlspecialchars($delivrable['Typologie_Name'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%">Client:</th>
                                <td><?= htmlspecialchars($delivrable['Customer_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Projet:</th>
                                <td><?= htmlspecialchars($delivrable['Project_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Activité:</th>
                                <td><?= htmlspecialchars($delivrable['Activity_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Périmètre:</th>
                                <td><?= htmlspecialchars($delivrable['Perimeter_Name']) ?></td>
                            </tr>
                            <tr>
                                <th>Type Validation:</th>
                                <td>
                                    <?php if ($delivrable['type_validation'] == 'checklist'): ?>
                                        <span class="badge bg-info">Checklist</span>
                                    <?php elseif ($delivrable['type_validation'] == 'derogation'): ?>
                                        <span class="badge bg-warning">Dérogation</span>
                                    <?php elseif ($delivrable['type_validation'] == 'NC'): ?>
                                        <span class="badge bg-danger">NC</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non défini</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Livrable:</th>
                                <td>
                                    <?php if ($delivrable['Livrable']): ?>
                                        <span class="badge bg-success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Commentaire -->
                <div class="mt-3">
                    <h6 class="font-weight-bold">Commentaire:</h6>
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <?= !empty($delivrable['Comment']) ? htmlspecialchars($delivrable['Comment']) : '<em>Aucun commentaire</em>' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dates et Statut -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">Dates et Statut</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="small font-weight-bold">Statut: 
                        <?php if ($status['Status_Delivrables'] == 'In Progress'): ?>
                            <span class="badge bg-primary">En cours</span>
                        <?php elseif ($status['Status_Delivrables'] == 'Closed'): ?>
                            <span class="badge bg-success">Terminé</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Annulé</span>
                        <?php endif; ?>
                    </h6>
                </div>
                
                <!-- Dates -->
                <table class="table">
                    <tr>
                        <th style="width: 50%">Date d'ouverture:</th>
                        <td><?= isset($status['Open_Date']) ? date('d/m/Y', strtotime($status['Open_Date'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Date prévue initiale:</th>
                        <td>
                            <?= $status['Original_Expected_Date'] ? date('d/m/Y', strtotime($status['Original_Expected_Date'])) : 'N/A' ?>
                        </td>
                    </tr>
                    <?php if ($status['Postponed_Date']): ?>
                    <tr>
                        <th>Date reportée:</th>
                        <td class="text-warning"><?= date('d/m/Y', strtotime($status['Postponed_Date'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Date réelle:</th>
                        <td>
                            <?php if ($status['Real_Date']): ?>
                                <?php 
                                $compareDate = $status['Postponed_Date'] ? $status['Postponed_Date'] : $status['Original_Expected_Date'];
                                $isLate = strtotime($status['Real_Date']) > strtotime($compareDate);
                                ?>
                                <span class="<?= $isLate ? 'text-danger' : 'text-success' ?>">
                                    <?= date('d/m/Y', strtotime($status['Real_Date'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <!-- FTR/OTD -->
                <div class="card mt-4">
                    <div class="card-header py-2 bg-light">
                        <h6 class="mb-0 small">Indicateurs de Performance</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center">FTR</th>
                                    <th class="text-center">OTD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Segula</th>
                                    <td class="text-center">
                                        <span class="badge <?= $validation['FTR_Segula'] == 'OK' ? 'bg-success' : ($validation['FTR_Segula'] == 'NOK' ? 'bg-danger' : 'bg-secondary') ?>">
                                            <?= $validation['FTR_Segula'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $validation['OTD_Segula'] == 'OK' ? 'bg-success' : ($validation['OTD_Segula'] == 'NOK' ? 'bg-danger' : 'bg-secondary') ?>">
                                            <?= $validation['OTD_Segula'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Client</th>
                                    <td class="text-center">
                                        <span class="badge <?= $validation['FTR_Customer'] == 'OK' ? 'bg-success' : ($validation['FTR_Customer'] == 'NOK' ? 'bg-danger' : 'bg-secondary') ?>">
                                            <?= $validation['FTR_Customer'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $validation['OTD_Customer'] == 'OK' ? 'bg-success' : ($validation['OTD_Customer'] == 'NOK' ? 'bg-danger' : 'bg-secondary') ?>">
                                            <?= $validation['OTD_Customer'] ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Légende -->
                <div class="mt-3">
                    <h6 class="small font-weight-bold">Légende:</h6>
                    <div class="small">
                        <div><strong>FTR:</strong> First Time Right (Qualité du livrable)</div>
                        <div><strong>OTD:</strong> On Time Delivery (Respect des délais)</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations de validation -->
        <?php if ($delivrable['type_validation'] === 'checklist' && $delivrable['CLC_ID']): ?>
        <div class="card mb-4">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Checklist</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>CLC ID:</th>
                        <td><?= $delivrable['CLC_ID'] ?></td>
                    </tr>
                    <!-- Ajouter d'autres détails de CLC si disponibles -->
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($delivrable['type_validation'] === 'derogation' && $delivrable['ID_Derogation']): ?>
        <div class="card mb-4">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Dérogation</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>ID Dérogation:</th>
                        <td><?= $delivrable['ID_Derogation'] ?></td>
                    </tr>
                    <tr>
                        <th>Nom Dérogation:</th>
                        <td><?= htmlspecialchars($delivrable['Derogation_Name'] ?? 'N/A') ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Historique des modifications (simplifié) -->
<div class="card mb-4">
    <div class="card-header py-3 bg-secondary text-white">
        <h6 class="m-0 font-weight-bold">Historique des modifications</h6>
    </div>
    <div class="card-body">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= isset($delivrable['Open_Date']) ? date('d/m/Y H:i', strtotime($delivrable['Open_Date'])) : 'N/A' ?></td>
                    <td>Système</td>
                    <td>Création du livrable</td>
                </tr>
                <?php if (isset($delivrable['Real_Date']) && $delivrable['Real_Date']): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($delivrable['Real_Date'])) ?></td>
                    <td>Système</td>
                    <td>Finalisation du livrable</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?>