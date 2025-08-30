<?php
$title = 'Liste des Checklists';
$checklists = $data['checklists'] ?? [];
$businessUnits = $data['businessUnits'] ?? [];
$activities = $data['activities'] ?? [];
$consultants = $data['consultants'] ?? [];
$statuses = ['Completed', 'In-Progress', 'Rejected'];
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
            <i class="fas fa-clipboard-check me-2"></i> Gestion des Checklists
        </h1>
        <a href="index.php?controller=checklist&action=create" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i> Créer une Checklist
        </a>
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
                <?php
                $filters = [
                    ['filterBU', 'Business Unit', $businessUnits, 'Name_BU'],
                    ['filterActivity', 'Activité', $activities, 'Name_Activity'],
                    ['filterQG1', 'Quality Gate 1', $consultants, 'Fname_User'],
                    ['filterQG2', 'Quality Gate 2', $consultants, 'Fname_User'],
                    
                ];
                foreach ($filters as [$id, $lbl, $data, $field]): ?>
                    <div class="col-md-6 col-lg-3">
                        <label for="<?= $id ?>" class="form-label fw-bold"><?= $lbl ?></label>
                        <select id="<?= $id ?>" class="form-select select2-filter">
                            <option value="">Tous</option>
                            <?php foreach ($data as $o): ?>
                                <option value="<?= htmlspecialchars($o[$field] ?? '') ?>">
                                    <?= htmlspecialchars($o[$field] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                

                <div class="col-md-6 col-lg-3">
                    <label for="searchTerm" class="form-label fw-bold">Recherche</label>
                    <div class="input-group">
                        <input id="searchTerm" class="form-control" placeholder="Rechercher...">
                        <button id="searchBtn" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i> Liste des Checklists
                <span id="totalCount" class="badge bg-light text-dark ms-2"><?= count($checklists) ?></span>
            </h6>
            
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="checklistsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Reference Unique</th>
                            <th>Projet</th>
                            <th>Activité</th>
                            <th>Business Unit</th>
                            <th>Criticité</th>
                            <th>Quality Gate 1</th>
                            <th>Quality Gate 2</th>
                            <th>Status</th>
                            <th>Date Initialisation</th>
                            <th>Date de Clôture</th>
                            <th>Date Livraison</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklists as $checklist): ?>
                            <tr>
                                <td><?= htmlspecialchars($checklist['Reference_Unique']) ?></td>
                                <td><?= htmlspecialchars($checklist['Name_Project']) ?></td>
                                <td><?= htmlspecialchars($checklist['Name_Activity']) ?></td>
                                <td><?= htmlspecialchars($checklist['Name_BU']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $checklist['Criticality_Level'] === 'Haute' ? 'danger' : 
                                        ($checklist['Criticality_Level'] === 'Moyenne' ? 'warning' : 'success') 
                                    ?>">
                                        <?= htmlspecialchars($checklist['Criticality_Level']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($checklist['QG1_Name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($checklist['QG2_Name'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $checklist['Status'] === 'Completed' ? 'success' : 
                                        ($checklist['Status'] === 'Rejected' ? 'danger' : 
                                        ($checklist['Status'] === 'In-Progress' ? 'warning' : 'secondary')) 
                                    ?>">
                                        <?= htmlspecialchars($checklist['Status']) ?>
                                    </span>
                                <td><?= date('d/m/Y', strtotime($checklist['Date_Initiation'])) ?></td>
                                <td><?= !empty($checklist['Real_Delivery_Date']) ? date('d/m/Y', strtotime($checklist['Real_Delivery_Date'])) : '-' ?></td>
                                <td><?= date('d/m/Y', strtotime($checklist['Expected_Delivery_Date'])) ?></td>
                                <td class="text-nowrap">
                                    <div class="btn-group" role="group">
                                        <a href="index.php?controller=checklist&action=view&id=<?= $checklist['ID_CLC'] ?>" 
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?controller=checklist&action=edit&id=<?= $checklist['ID_CLC'] ?>" 
                                           class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
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

        // Initialize DataTable with export buttons
        const tbl = $('#checklistsTable').DataTable({
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-1"></i> CSV',
                    className: 'btn btn-sm btn-outline-secondary'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-sm btn-outline-secondary'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                    className: 'btn btn-sm btn-outline-secondary'
                }
            ],
            order: [[0, 'desc']],
            responsive: false,
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
            },
            initComplete: function() {
                $('.dt-buttons').addClass('btn-group');
            }
        });

        // Filter handlers
        $('.select2-filter').on('change', function () {
            const map = {
                filterBU: 1,         // Business Unit column
                filterActivity: 0,   // Activity column
                filterQG1: 4,        // QG1 column
                filterQG2: 5,        // QG2 column
                filterStatus: 6      // Status column
            };
            let col = map[this.id], v = $(this).val() || '';
            tbl.column(col).search(v).draw();
        });

        // Search handler
        $('#searchBtn,#searchTerm').on('click keypress', e => {
            if (e.type === 'click' || e.which === 13) {
                tbl.search($('#searchTerm').val()).draw();
            }
        });

        // Reset filters
        $('#resetFilters').click(() => {
            $('.select2-filter').val(null).trigger('change');
            $('#searchTerm').val('');
            tbl.search('').columns().search('').draw();
        });

        // Update count on draw
        tbl.on('draw', () => {
            $('#totalCount').text(tbl.rows({search: 'applied'}).count());
        });

        
    });
</script>

<?php include_once 'views/includes/footer.php'; ?>