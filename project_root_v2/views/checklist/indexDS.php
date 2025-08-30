<?php
$title = 'Liste des Checklists';
$checklists = $data['checklists'] ?? [];
$businessUnits = $data['businessUnits'] ?? [];
$activities = $data['activities'] ?? [];
$consultants = $data['consultants'] ?? [];
include_once 'views/includes/header.php';
?>

<!-- ---------- CSS ---------- -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-clipboard-check me-2"></i>Gestion des Checklists
        </h1>
        <a href="index.php?controller=checklist&action=create" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Créer une Checklist
        </a>
    </div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $type):
        if (isset($_SESSION[$key])): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show">
                <?= $_SESSION[$key] ?>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif;
    endforeach; ?>

    <!-- ---------- Filtres ---------- -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtres</h6>
            <button id="resetFilters" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i>Réinitialiser
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                $filters = [
                    ['filterBU', 'Business Unit', $businessUnits, 'Name_BU'],
                    ['filterActivity', 'Activité', $activities, 'Name_Activity'],
                    ['filterQG1', 'Quality Gate 1', $consultants, 'Fname_User'],
                    ['filterQG2', 'Quality Gate 2', $consultants, 'Fname_User'],
                ];
                foreach ($filters as [$id, $lbl, $data, $field]): ?>
                    <div class="col-md-3 mb-3">
                        <label for="<?= $id ?>"><?= $lbl ?></label>
                        <select id="<?= $id ?>" class="form-select select2-filter">
                            <option value="">Tous</option>
                            <?php foreach ($data as $o):
                                $val = $o[$field] ?? '';
                                ?>
                                <option value="<?= htmlspecialchars($val) ?>">
                                    <?= htmlspecialchars($val) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="filterStatus">Statut</label>
                    <select id="filterStatus" class="form-select select2-filter">
                        <option value="">Tous</option>
                        <option value="En cours">En cours</option>
                        <option value="Validé">Validé</option>
                        <option value="Rejeté">Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="searchTerm">Recherche générale</label>
                    <div class="input-group">
                        <input id="searchTerm" class="form-control" placeholder="Rechercher…">
                        <button id="searchBtn" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ---------- Tableau ---------- -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Liste des Checklists
                <span id="totalCount" class="badge bg-secondary"><?= count($checklists) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <table id="checklistsTable" class="table table-striped table-bordered w-100">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Activité</th>
                    <th>Business Unit</th>
                    <th>Criticité</th>
                    <th>Quality Gate 1</th>
                    <th>Quality Gate 2</th>
                    <th>Date d'Initialisation</th>
                    <th>Date Livraison</th>
                
                    <th>Checker les Données de Sorties</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($checklists as $checklist): ?>
                    <tr>
                        <td><?= htmlspecialchars($checklist['ID_CLC']) ?></td>
                        <td><?= htmlspecialchars($checklist['Name_Activity']) ?></td>
                        <td><?= htmlspecialchars($checklist['Name_BU']) ?></td>
                        <td><?= htmlspecialchars($checklist['Criticality_Level']) ?></td>
                        <td><?= htmlspecialchars($checklist['QG1_Name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($checklist['QG2_Name'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($checklist['Date_Initiation'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($checklist['Expected_Delivery_Date'])) ?></td>
                        
                        <td>
                            <a href="index.php?controller=checklist&action=view&id=<?= htmlspecialchars($checklist['ID_CLC']) ?>&view=output" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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
        $('.select2-filter').select2({
            theme: 'bootstrap-5', width: '100%', allowClear: true,
            placeholder: 'Sélectionner…'
        });

        const tbl = $('#checklistsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [],
            order: [[0, 'desc']],
            responsive: true,
            pageLength: 10,
            language: {url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'}
        });

        $('.select2-filter').on('change', function () {
            const map = {
                filterBU: 2, filterActivity: 1, filterQG1: 4, filterQG2: 5, filterStatus: 8
            };
            let col = map[this.id], v = $(this).val() || '';
            tbl.column(col).search(v).draw();
        });

        $('#searchBtn,#searchTerm').on('click keypress', e => {
            if (e.type === 'click' || e.which === 13) tbl.search($('#searchTerm').val()).draw();
        });

        $('#resetFilters').click(() => {
            $('.select2-filter').val(null).trigger('change');
            $('#searchTerm').val('');
            tbl.search('').columns().search('').draw();
        });

        tbl.on('draw', () => {
            $('#totalCount').text(tbl.rows({search: 'applied'}).count());
        });
    });
</script>

<?php include_once 'views/includes/footer.php'; ?>