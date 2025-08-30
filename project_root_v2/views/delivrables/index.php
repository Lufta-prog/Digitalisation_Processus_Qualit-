<?php
// views/delivrables/index.php
$pageTitle = 'Liste des Livrables';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
    header('Location: index.php?controller=auth&action=login');
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
include_once 'views/includes/header.php';
?>

<!-- ---------- CSS ---------- -->
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet"
      href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3">
    <i class="fas fa-clipboard-list me-2"></i>Gestion des Livrables
  </h1>
  <a class="btn btn-primary"
     href="index.php?controller=delivrables&action=create">
     <i class="fas fa-plus-circle me-2"></i>Nouveau Livrable
  </a>
</div>

<?php foreach (['success'=>'success','error'=>'danger'] as $key=>$type):
      if(isset($_SESSION[$key])): ?>
  <div class="alert alert-<?= $type ?> alert-dismissible fade show">
    <?= $_SESSION[$key] ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php unset($_SESSION[$key]); endif; endforeach; ?>

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
        ['filterBU',       'Business Unit', $business_units, 'Name_BU'],
        ['filterActivity', 'Activité',       $activities,     'Name_Activity'],
        ['filterCustomer', 'Client',         $customers,      'Name_Customer'],
        ['filterProject',  'Projet',         $projects,       'Name_Project'],
      ];
      foreach ($filters as [$id, $lbl, $data, $field]): ?>
        <div class="col-md-3 mb-3">
          <label for="<?= $id ?>"><?= $lbl ?></label>
          <select id="<?= $id ?>" class="form-select select2-filter">
            <option value="">Tous</option>
            <?php foreach ($data as $o):
              // si la clé n'existe pas, on affiche chaîne vide
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
          <option value="In Progress">En cours</option>
          <option value="Closed">Terminé</option>
          <option value="Cancelled">Annulé</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <label for="filterFTR">FTR Segula</label>
        <select id="filterFTR" class="form-select select2-filter">
          <option value="">Tous</option>
          <option value="OK">OK</option>
          <option value="NOK">NOK</option>
          <option value="NA">NA</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <label for="filterOTD">OTD Segula</label>
        <select id="filterOTD" class="form-select select2-filter">
          <option value="">Tous</option>
          <option value="OK">OK</option>
          <option value="NOK">NOK</option>
          <option value="NA">NA</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <label for="filterLivrable">Type</label>
        <select id="filterLivrable" class="form-select select2-filter">
          <option value="">Tous</option>
          <option value="1">Livrable</option>
          <option value="0">Standard</option>
        </select>
      </div>
    </div>

    <div class="row">
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
  <div class="card-header">
    <h6 class="m-0 font-weight-bold text-primary">
      <i class="fas fa-list me-2"></i>Liste des Livrables
      <span id="totalCount" class="badge bg-secondary"><?= count($delivrables) ?></span>
    </h6>
  </div>
  <div class="card-body">
    <table id="tabl" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th>ID</th><th>ID Topic</th><th>Description</th><th>Client</th><th>Projet</th>
          <th>BU</th><th>Activité</th><th>Date Prévue</th><th>Date Réelle</th>
          <th>FTR</th><th>OTD</th><th>Statut</th><th>Type</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($delivrables as $d): ?>
          <tr>
            <td class="text-center"><?= $d['ID_Row'] ?></td>
            <td><?= $d['ID_Topic'] ?></td>
            <td title="<?= htmlspecialchars($d['Description_Topic']) ?>">
              <?= mb_strimwidth(htmlspecialchars($d['Description_Topic']), 0, 50, '…') ?>
            </td>
            <td><?= htmlspecialchars($d['Customer_Name']) ?></td>
            <td><?= htmlspecialchars($d['Project_Name']) ?></td>
            <td><?= htmlspecialchars($d['BU_Name']) ?></td>
            <td><?= htmlspecialchars($d['Activity_Name']) ?></td>
            <td>
              <?php
                if (!empty($d['Postponed_Date'])) {
                  echo '<span class="text-warning">'
                       . date('d/m/Y', strtotime($d['Postponed_Date']))
                       . '</span>';
                } elseif (!empty($d['Original_Expected_Date'])) {
                  echo date('d/m/Y', strtotime($d['Original_Expected_Date']));
                } else {
                  echo 'N/A';
                }
              ?>
            </td>
            <td><?= $status['Real_Date'] ? date('d/m/Y', strtotime($status['Real_Date'])) : 'N/A' ?></td>
            <td><?= htmlspecialchars($validation['FTR_Segula']) ?></td>
            <td><?= htmlspecialchars($validation['OTD_Segula']) ?></td>
            <td>
              <?= $status['Status_Delivrables'] === 'In Progress' ? 'En cours'
                 : ($status['Status_Delivrables'] === 'Closed' ? 'Terminé' : 'Annulé') ?>
            </td>
            <td><?= $d['Livrable'] ? 'Livrable' : 'Standard' ?></td>
            <td class="text-center">
              <div class="btn-group btn-group-sm">
                <a class="btn btn-info"
                   href="index.php?controller=delivrables&action=view&id=<?= $d['ID_Row'] ?>">
                   <i class="fas fa-eye"></i>
                </a>
                <a class="btn btn-warning"
                   href="index.php?controller=delivrables&action=edit&id=<?= $d['ID_Row'] ?>">
                   <i class="fas fa-edit"></i>
                </a>
                <button class="btn btn-danger del" data-id="<?= $d['ID_Row'] ?>">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Supprimer le livrable ?</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Action irréversible !</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <a id="confirmDelete" class="btn btn-danger">Supprimer</a>
      </div>
    </div>
  </div>
</div>

<!-- ---------- JS ---------- -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function () {
  $('.select2-filter').select2({
    theme: 'bootstrap-5', width: '100%', allowClear: true,
    placeholder: 'Sélectionner…'
  });

  const tbl = $('#tabl').DataTable({
    dom: 'Bfrtip',
    buttons: [
      { extend:'excelHtml5', text:'<i class="fas fa-file-excel me-1"></i>Excel', className:'btn btn-success btn-sm' },
      { extend:'csvHtml5',   text:'<i class="fas fa-file-csv me-1"></i>CSV',   className:'btn btn-info btn-sm'    }
    ],
    order: [[0,'desc']], responsive: true, pageLength:10,
    language: { url:'//cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' }
  });

  $('.select2-filter').on('change', function () {
    const map = {
      filterBU:5, filterActivity:6, filterCustomer:3, filterProject:4,
      filterStatus:11, filterFTR:9, filterOTD:10, filterLivrable:12
    };
    let col = map[this.id], v = $(this).val() || '';
    if (this.id==='filterStatus') {
      if(v==='In Progress') v='En cours';
      if(v==='Closed')      v='Terminé';
      if(v==='Cancelled')   v='Annulé';
    }
    if (this.id==='filterLivrable') {
      v = v==='1' ? 'Livrable' : v==='0' ? 'Standard' : '';
    }
    tbl.column(col).search(v).draw();
  });

  $('#searchBtn,#searchTerm').on('click keypress', e => {
    if (e.type==='click' || e.which===13) tbl.search($('#searchTerm').val()).draw();
  });

  $('#resetFilters').click(() => {
    $('.select2-filter').val(null).trigger('change');
    $('#searchTerm').val('');
    tbl.search('').columns().search('').draw();
  });

  $('.del').click(function(){
    $('#confirmDelete').attr('href',
      'index.php?controller=delivrables&action=delete&id='+$(this).data('id'));
    $('#deleteModal').modal('show');
  });

  tbl.on('draw', () => {
    $('#totalCount').text(tbl.rows({search:'applied'}).count());
  });
});
</script>

<?php include_once 'views/includes/footer.php'; ?>
