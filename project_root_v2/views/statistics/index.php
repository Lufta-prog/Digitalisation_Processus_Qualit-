<?php
/*==============================================================*/
/* views/statistics/index.php                                   */
/*==============================================================*/
$pageTitle = "Statistiques des Livrables";
require_once 'views/includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <?php require_once 'views/includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Statistiques des Livrables</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?controller=statistics&action=exportCSV<?= isset($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '' ?>" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-download"></i> Exporter CSV
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-filters">
                        <i class="fas fa-filter"></i> Filtres
                    </button>
                </div>
            </div>
            
            <?php require_once 'views/includes/alerts.php'; ?>
            
            <!-- Filters Section -->
            <div class="card mb-4" id="filters-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filtres avancés</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php" id="statistics-filter-form">
                        <input type="hidden" name="controller" value="statistics">
                        <div class="row">
                            <!-- BU Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="bu_id" class="form-label">Business Unit</label>
                                <select class="form-select select2" id="bu_id" name="bu_id">
                                    <option value="">Toutes les BU</option>
                                    <?php foreach ($businessUnits as $bu): ?>
                                        <option value="<?= $bu['ID_BU'] ?>" <?= isset($_GET['bu_id']) && $_GET['bu_id'] == $bu['ID_BU'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($bu['Name_BU']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Activity Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="activity_id" class="form-label">Activité</label>
                                <select class="form-select select2" id="activity_id" name="activity_id">
                                    <option value="">Toutes les activités</option>
                                    <?php foreach ($activities as $activity): ?>
                                        <option value="<?= $activity['ID_Activity'] ?>" <?= isset($_GET['activity_id']) && $_GET['activity_id'] == $activity['ID_Activity'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($activity['Name_Activity']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Project Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="project_id" class="form-label">Projet</label>
                                <select class="form-select select2" id="project_id" name="project_id">
                                    <option value="">Tous les projets</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= $project['ID_Project'] ?>" <?= isset($_GET['project_id']) && $_GET['project_id'] == $project['ID_Project'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($project['Name_Project']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Contract Code Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="contract_code" class="form-label">Code Contrat</label>
                                <input type="text" class="form-control" id="contract_code" name="contract_code" value="<?= isset($_GET['contract_code']) ? htmlspecialchars($_GET['contract_code']) : '' ?>">
                            </div>
                            
                            <!-- Customer Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="customer_id" class="form-label">Client</label>
                                <select class="form-select select2" id="customer_id" name="customer_id">
                                    <option value="">Tous les clients</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['ID_Customer'] ?>" <?= isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['ID_Customer'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($customer['Name_Customer']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Perimeter Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="perimeter_id" class="form-label">Périmètre</label>
                                <select class="form-select select2" id="perimeter_id" name="perimeter_id">
                                    <option value="">Tous les périmètres</option>
                                    <?php foreach ($perimeters as $perimeter): ?>
                                        <option value="<?= $perimeter['ID_Perimeter'] ?>" <?= isset($_GET['perimeter_id']) && $_GET['perimeter_id'] == $perimeter['ID_Perimeter'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($perimeter['Name_Perimeter']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Industry Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="industry_id" class="form-label">Industrie</label>
                                <select class="form-select select2" id="industry_id" name="industry_id">
                                    <option value="">Toutes les industries</option>
                                    <?php foreach ($industries as $industry): ?>
                                        <option value="<?= $industry['ID_Industry'] ?>" <?= isset($_GET['industry_id']) && $_GET['industry_id'] == $industry['ID_Industry'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($industry['Industry_Name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Project Status Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Statut Projet</label>
                                <select class="form-select select2" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="Active" <?= isset($_GET['status']) && $_GET['status'] == 'Active' ? 'selected' : '' ?>>Actif</option>
                                    <option value="Inactive" <?= isset($_GET['status']) && $_GET['status'] == 'Inactive' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                            
                            <!-- Date Range Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="start_date" class="form-label">Date début</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="end_date" class="form-label">Date fin</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
                            </div>
                            
                            <!-- Month Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="month" class="form-label">Mois</label>
                                <select class="form-select select2" id="month" name="month">
                                    <option value="">Tous les mois</option>
                                    <?php
                                    $months = [
                                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                                    ];
                                    foreach ($months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= isset($_GET['month']) && $_GET['month'] == $num ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Year Filter -->
                            <div class="col-md-3 mb-3">
                                <label for="year" class="form-label">Année</label>
                                <select class="form-select select2" id="year" name="year">
                                    <option value="">Toutes les années</option>
                                    <?php
                                    $currentYear = date('Y');
                                    for ($y = $currentYear; $y >= ($currentYear - 5); $y--): ?>
                                        <option value="<?= $y ?>" <?= isset($_GET['year']) && $_GET['year'] == $y ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Appliquer les filtres
                                </button>
                                <a href="index.php?controller=statistics" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total des livrables</h5>
                            <h2 class="card-text"><?= number_format($statisticsSummary['Total_Deliverables']) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">FTR Segula</h5>
                            <h2 class="card-text"><?= number_format($statisticsSummary['Total_FTR_Segula_Percent'], 1) ?>%</h2>
                            <small><?= $statisticsSummary['Total_FTR_Segula_OK'] ?> / <?= $statisticsSummary['Total_Deliverables'] ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">OTD Segula</h5>
                            <h2 class="card-text"><?= number_format($statisticsSummary['Total_OTD_Segula_Percent'], 1) ?>%</h2>
                            <small><?= $statisticsSummary['Total_OTD_Segula_OK'] ?> / <?= $statisticsSummary['Total_Deliverables'] ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">FTR Client</h5>
                            <h2 class="card-text"><?= number_format($statisticsSummary['Total_FTR_Customer_Percent'], 1) ?>%</h2>
                            <small><?= $statisticsSummary['Total_FTR_Customer_OK'] ?> / <?= $statisticsSummary['Total_Deliverables'] ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mt-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">OTD Client</h5>
                            <h2 class="card-text"><?= number_format($statisticsSummary['Total_OTD_Customer_Percent'], 1) ?>%</h2>
                            <small><?= $statisticsSummary['Total_OTD_Customer_OK'] ?> / <?= $statisticsSummary['Total_Deliverables'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Table -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Résultats des statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="statistics-table" class="table table-striped table-bordered table-hover datatable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Année</th>
                                    <th>Mois</th>
                                    <th>Code Contrat</th>
                                    <th>Niveau Projet</th>
                                    <th>BU</th>
                                    <th>Activité</th>
                                    <th>Périmètre</th>
                                    <th>Nombre livrables</th>
                                    <th>Nb. OK FTR Segula</th>
                                    <th>% FTR Segula</th>
                                    <th>Nb. OK OTD Segula</th>
                                    <th>% OTD Segula</th>
                                    <th>Nb. OK FTR Client</th>
                                    <th>% FTR Client</th>
                                    <th>Nb. OK OTD Client</th>
                                    <th>% OTD Client</th>
                                    <th>Date Début</th>
                                    <th>Date Fin Prévue</th>
                                    <th>Statut</th>
                                    <th>Industrie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statisticsData)): ?>
                                    <tr>
                                        <td colspan="20" class="text-center">Aucune donnée trouvée</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($statisticsData as $row): ?>
                                        <tr>
                                            <td><?= $row['Year'] ?></td>
                                            <td><?= date('F', mktime(0, 0, 0, $row['Month'], 1)) ?></td>
                                            <td><?= htmlspecialchars($row['contract_code'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['Project_Level']) ?></td>
                                            <td><?= htmlspecialchars($row['BU']) ?></td>
                                            <td><?= htmlspecialchars($row['Activity']) ?></td>
                                            <td><?= htmlspecialchars($row['Perimeter']) ?></td>
                                            <td><?= $row['Nombre des livrables'] ?></td>
                                            <td><?= $row['Nombre des livrables OK FTR Segula'] ?></td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $row['% FTR Segula'] ?>%"></div>
                                                </div>
                                                <?= number_format($row['% FTR Segula'], 1) ?>%
                                            </td>
                                            <td><?= $row['Nombre des livrables OK OTD Segula'] ?></td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $row['% OTD Segula'] ?>%"></div>
                                                </div>
                                                <?= number_format($row['% OTD Segula'], 1) ?>%
                                            </td>
                                            <td><?= $row['Nombre des livrables OK FTR Customer'] ?></td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $row['% FTR Customer'] ?>%"></div>
                                                </div>
                                                <?= number_format($row['% FTR Customer'], 1) ?>%
                                            </td>
                                            <td><?= $row['Nombre des livrables OK OTD Customer'] ?></td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $row['% OTD Customer'] ?>%"></div>
                                                </div>
                                                <?= number_format($row['% OTD Customer'], 1) ?>%
                                            </td>
                                            <td><?= $row['Starting_Date'] ?></td>
                                            <td><?= $row['Expected_Ending_Date'] ?></td>
                                            <td>
                                                <span class="badge <?= $row['Status_Project'] == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $row['Status_Project'] == 'Active' ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['Industry_Name'] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Ajout de styles spécifiques pour corriger l'espacement -->
<style>
    /* Correction de l'espacement entre la sidebar et le contenu principal */
    @media (min-width: 768px) {
        .sidebar {
            width: 250px;
        }
        
        .main-content, 
        main.col-md-9.ms-sm-auto.col-lg-10 {
            margin-left: 250px !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            width: calc(100% - 250px) !important;
        }
        
        .row.g-0 {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    }
    
    /* Amélioration des cartes de statistiques */
    .card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    /* Styles pour la table */
    #statistics-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .progress {
        border-radius: 10px;
    }
</style>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Sélectionner...',
        allowClear: true
    });
    
    // Toggle filters section
    $("#toggle-filters").click(function() {
        $("#filters-card").toggle();
    });
    
    // Load activities based on selected BU
    $("#bu_id").change(function() {
        var buId = $(this).val();
        if (buId) {
            $.ajax({
                url: 'index.php?controller=statistics&action=getActivitiesByBu',
                type: 'GET',
                data: { bu_id: buId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var activities = response.activities;
                        var options = '<option value="">Toutes les activités</option>';
                        
                        $.each(activities, function(index, activity) {
                            options += '<option value="' + activity.ID_Activity + '">' + activity.Name_Activity + '</option>';
                        });
                        
                        $("#activity_id").html(options);
                        $("#activity_id").trigger('change');
                    }
                },
                error: function() {
                    alert('Erreur lors du chargement des activités');
                }
            });
        } else {
            // Reset activities dropdown if no BU selected
            var options = '<option value="">Toutes les activités</option>';
            $("#activity_id").html(options);
            $("#activity_id").trigger('change');
        }
    });
    
    // Date range validation
    $("#statistics-filter-form").submit(function(e) {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        
        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert("La date de début doit être antérieure à la date de fin");
            }
        }
    });
    
    // Initialize DataTable with advanced features
    $('#statistics-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
        order: [[0, 'desc'], [1, 'desc']], // Order by Year desc, then Month desc
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copier',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-outline-primary',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-success',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-danger',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-sm btn-outline-dark',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns"></i> Colonnes',
                className: 'btn btn-sm btn-outline-info'
            }
        ]
    });
});
</script>

<?php require_once 'views/includes/footer.php'; ?>