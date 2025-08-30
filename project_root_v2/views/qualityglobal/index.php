<?php
$title = 'Qualité Globale';
$checklists = $data['checklists'] ?? [];
$businessUnits = $data['businessUnits'] ?? [];
$activities = $data['activities'] ?? [];
$consultants = $data['consultants'] ?? [];
$totals = $data['totals'] ?? [];
include_once 'views/includes/header.php';
?>

<!-- ---------- CSS ---------- -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line me-2"></i> Qualité Globale
        </h1>
    </div>

    <!-- Alerts -->
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $type): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-4">
                <?= $_SESSION[$key] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter me-2"></i> Filtres
            </h6>
            <button id="resetFilters" class="btn btn-sm btn-light">
                <i class="fas fa-sync-alt me-1"></i> Réinitialiser
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Année -->
                <div class="col-md-3">
                    <label for="filterYear" class="form-label fw-bold">Année</label>
                    <select id="filterYear" class="form-select select2-filter">
                        <option value="">Toutes</option>
                        <option value="2025" selected>2025</option>
                    </select>
                </div>
                
                <!-- Business Unit -->
                <div class="col-md-3">
                    <label for="filterBU" class="form-label fw-bold">Business Unit</label>
                    <select id="filterBU" class="form-select select2-filter">
                        <option value="">Toutes</option>
                        <?php foreach ($businessUnits as $bu): ?>
                            <option value="<?= htmlspecialchars($bu['Name_BU']) ?>">
                                <?= htmlspecialchars($bu['Name_BU']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Activité -->
                <div class="col-md-3">
                    <label for="filterActivity" class="form-label fw-bold">Activité</label>
                    <select id="filterActivity" class="form-select select2-filter">
                        <option value="">Toutes</option>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?= htmlspecialchars($activity) ?>">
                                <?= htmlspecialchars($activity) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Consultant -->
                <div class="col-md-3">
                    <label for="filterConsultant" class="form-label fw-bold">Consultant</label>
                    <select id="filterConsultant" class="form-select select2-filter">
                        <option value="">Tous</option>
                        <?php foreach ($consultants as $consultant): ?>
                            <option value="<?= htmlspecialchars($consultant['consultant_name']) ?>">
                                <?= htmlspecialchars($consultant['consultant_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i> Statistiques par consultant
                <span id="totalCount" class="badge bg-light text-dark ms-2"><?= count($consultants) ?></span>
            </h6>
            
            <!-- Boutons d'export -->
            <div class="btn-group" role="group">
                <button id="exportCsv" class="btn btn-sm btn-light">
                    <i class="fas fa-file-csv me-1"></i> CSV
                </button>
                <button id="exportExcel" class="btn btn-sm btn-light">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
                <button id="exportPdf" class="btn btn-sm btn-light">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="qualityTable" class="table table-bordered table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">Nom du consultant</th>
                            <?php 
                            // Déterminer le nombre max d'itérations à afficher
                           $maxIterations = 0;
                            foreach ($consultants as $consultant) {
                                if (!empty($consultant['iterations'])) {
                                    $maxIterations = max($maxIterations, max(array_keys($consultant['iterations'])));
                                }
                            }
                            if (!empty($totals['iterations'])) {
                                $maxIterations = max($maxIterations, max(array_keys($totals['iterations'])));
                            }
                            // Générer les en-têtes dynamiquement
                            for ($i = 1; $i <= $maxIterations; $i++): ?>
                            <th colspan="3" class="text-center">Itération <?= $i ?></th>
                            <?php endfor; ?>
                            <th colspan="3" class="text-center">Total</th>
                        </tr>
                        <tr>
                            <?php for ($i = 1; $i <= $maxIterations; $i++): ?>
                                <th class="text-center">Nbr livrable</th>
                                <th class="text-center">Nbr livrable OK</th>
                                <th class="text-center">% conformité</th>
                            <?php endfor; ?>
                            <!-- En-têtes pour le total -->
                            <th class="text-center">Nbr livrable</th>
                            <th class="text-center">Nbr livrable OK</th>
                            <th class="text-center">% conformité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Données dynamiques -->
                        <?php foreach ($consultants as $consultant): ?>
                        <tr>
                            <td><?= htmlspecialchars($consultant['consultant_name']) ?></td>
                            
                            <?php for ($i = 1; $i <= $maxIterations; $i++): ?>
                                <td class="text-center"><?= $consultant['iterations'][$i]['total'] ?? 0 ?></td>
                                <td class="text-center"><?= $consultant['iterations'][$i]['ok'] ?? 0 ?></td>
                                <td class="text-center percentage-cell" data-percent="<?= $consultant['iterations'][$i]['percent'] ?? 0 ?>">
                                    <?= isset($consultant['iterations'][$i]) ? number_format($consultant['iterations'][$i]['percent'], 2) . '%' : '0%' ?>
                                </td>
                            <?php endfor; ?>
                            
                            <!-- Données pour le total -->
                            <td class="text-center"><?= $consultant['total']['total'] ?? 0 ?></td>
                            <td class="text-center"><?= $consultant['total']['ok'] ?? 0 ?></td>
                            <td class="text-center percentage-cell" data-percent="<?= $consultant['total']['percent'] ?? 0 ?>">
                                <?= number_format($consultant['total']['percent'] ?? 0, 2) ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Ligne de total -->
                        <tr class="table-info fw-bold">
                            <td>Total</td>
                            
                            <?php for ($i = 1; $i <= $maxIterations; $i++): ?>
                            <!-- Totaux pour chaque itération -->
                            <td class="text-center"><?= $totals['iterations'][$i]['total'] ?? 0 ?></td>
                            <td class="text-center"><?= $totals['iterations'][$i]['ok'] ?? 0 ?></td>
                            <td class="text-center percentage-cell" data-percent="<?= $totals['iterations'][$i]['percent'] ?? 0 ?>">
                                <?= isset($totals['iterations'][$i]) ? number_format($totals['iterations'][$i]['percent'], 2) . '%' : '0%' ?>
                            </td>
                            <?php endfor; ?>
                            
                            <!-- Totaux globaux -->
                            <td class="text-center"><?= $totals['global']['total'] ?? 0 ?></td>
                            <td class="text-center"><?= $totals['global']['ok'] ?? 0 ?></td>
                            <td class="text-center percentage-cell" data-percent="<?= $totals['global']['percent'] ?? 0 ?>">
                                <?= number_format($totals['global']['percent'] ?? 0, 2) ?>%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ---------- JS ---------- -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function () {
    // Initialize Select2
    $('.select2-filter').select2({
        theme: 'bootstrap-5', 
        width: '100%', 
        allowClear: true,
        placeholder: 'Sélectionner...'
    });

    // Initialize DataTable
    const tbl = $('#qualityTable').DataTable({
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    order: [[0, 'asc']],
    responsive: false, // Désactive complètement le responsive
    pageLength: 25,
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
    }
});

    // Filter handlers
    $('.select2-filter').on('change', function () {
        tbl.draw();
    });

    // Custom filtering function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            const year = $('#filterYear').val();
            const bu = $('#filterBU').val();
            const activity = $('#filterActivity').val();
            const consultant = $('#filterConsultant').val();
            
            // Implémentez votre logique de filtrage ici
            // Retournez true pour inclure la ligne, false pour l'exclure
            return true;
        }
    );

    // Reset filters
    $('#resetFilters').click(() => {
        $('.select2-filter').val(null).trigger('change');
        tbl.draw();
    });

    // Export buttons
    $('#exportCsv').click(function() {
        // Implémentez l'export CSV
    });
    
    $('#exportExcel').click(function() {
        // Implémentez l'export Excel
    });
    
    $('#exportPdf').click(function() {
        // Implémentez l'export PDF
    });

    // Update count on draw
    tbl.on('draw', () => {
        $('#totalCount').text(tbl.rows({search: 'applied'}).count());
    });

    // Coloration des cellules de pourcentage
    $('.percentage-cell').each(function() {
        const percent = parseFloat($(this).data('percent'));
        
        if (percent >= 90) {
            $(this).addClass('bg-success text-white');
        } else if (percent >= 70) {
            $(this).addClass('bg-warning');
        } else if (percent > 0) {
            $(this).addClass('bg-danger text-white');
        }
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>