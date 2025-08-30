<?php
/*==============================================================*/
/* controllers/DelivrableController.php                         */
/*==============================================================*/
declare(strict_types=1);

require_once 'models/DelivrableModel.php';

class DelivrableController
{
    private $db;
    private $model;

    public function __construct($db)
    {
        $this->db = $db;
        // Activer les exceptions PDO pour mieux capturer les erreurs SQL
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new DelivrableModel($db);
        // La session est démarrée dans index.php
    }

    /** Vérifie l'authentification, redirige vers la page de login si besoin */
    private function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    /** Affiche la liste des livrables */
    public function index(): void
    {
        $this->checkAuth();
        $data = [
            'delivrables'    => $this->model->getAllDelivrables(),
            'business_units' => $this->model->getAllBusinessUnits(),
            'activities'     => $this->model->getAllActivities(),
            'customers'      => $this->model->getAllCustomers(),
            'projects'       => $this->model->getAllProjects(),
        ];
        extract($data);
        require BASE_PATH . '/views/delivrables/index.php';
    }

    /** Formulaire de création */
    public function create(): void
    {
        $this->checkAuth();
        $nextIDTopic     = $this->model->getNextIDTopic();
        $business_units  = $this->model->getAllBusinessUnits();
        $activities      = $this->model->getAllActivities();
        $users           = $this->model->getAllUsers();
        $typologies      = $this->model->getAllTypologies();
        $customers       = $this->model->getAllCustomers();
        $perimeters      = $this->model->getAllPerimeters();
        $projects        = $this->model->getAllProjects();
        $clcs            = $this->model->getAllCLCs();
        $derogations     = $this->model->getAllDerogations();
        require BASE_PATH . '/views/delivrables/create.php';
    }

    /** Traitement du POST de création */
    public function store(): void{
    $this->checkAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?controller=delivrables');
        exit;
    }

    // Vérification des champs obligatoires (hors statut/indicateurs)
    $required = [
        'ID_Topic','Description_Topic','Leader_ID','Requester_ID',
        'Customer_ID','Project_ID','Activity_ID','Perimeter_ID'
    ];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header('Location: index.php?controller=delivrables&action=create');
            exit;
        }
    }

    // Préparation des données pour chaque table
    $dataMain = [
        'ID_Topic'          => $_POST['ID_Topic'],
        'Description_Topic' => trim($_POST['Description_Topic']),
        'Leader_ID'         => (int) $_POST['Leader_ID'],
        'Requester_ID'      => (int) $_POST['Requester_ID'],
        'Customer_ID'       => (int) $_POST['Customer_ID'],
        'Project_ID'        => (int) $_POST['Project_ID'],
        'Activity_ID'       => (int) $_POST['Activity_ID'],
        'Perimeter_ID'      => (int) $_POST['Perimeter_ID'],
        'Typologie_ID'      => !empty($_POST['Typologie_ID']) ? (int)$_POST['Typologie_ID'] : null,
        'Livrable'          => isset($_POST['Livrable']) ? 1 : 0,
        'type_validation'   => $_POST['type_validation'] ?: null,
        'CLC_ID'            => !empty($_POST['CLC_ID']) ? (int)$_POST['CLC_ID'] : null,
        'ID_Derogation'     => !empty($_POST['ID_Derogation']) ? (int)$_POST['ID_Derogation'] : null,
        'Comment'           => trim($_POST['Comment']) ?: null,
    ];
    $dataStatus = [
        'Status_Delivrables'     => $_POST['Status_Delivrables'] ?? 'In Progress',
        'Original_Expected_Date' => $_POST['Original_Expected_Date'] ?: null,
        'Postponed_Date'         => $_POST['Postponed_Date'] ?: null,
        'Real_Date'              => $_POST['Real_Date'] ?: null,
    ];
    $dataValidation = [
        'FTR_Segula'    => $_POST['FTR_Segula'] ?? 'NA',
        'OTD_Segula'    => $_POST['OTD_Segula'] ?? 'NA',
        'FTR_Customer'  => $_POST['FTR_Customer'] ?? 'NA',
        'OTD_Customer'  => $_POST['OTD_Customer'] ?? 'NA',
    ];

    try {
        // 1. Création du livrable principal
        $this->model->createDelivrable(array_merge($dataMain, $dataStatus, $dataValidation));
        $_SESSION['success'] = 'Livrable créé avec succès.';
        header('Location: index.php?controller=delivrables');
        exit;
    } catch (\PDOException $e) {
        if ($e->getCode() === '23000') {
            $_SESSION['error'] = 'Erreur de référence : clé étrangère invalide.';
        } else {
            $_SESSION['error'] = 'Erreur SQL : ' . $e->getMessage();
        }
        header('Location: index.php?controller=delivrables&action=create');
        exit;
    }
}

    /** Formulaire d'édition */
    public function edit(int $id): void
    {
        $this->checkAuth();
        $delivrable = $this->model->getDelivrableById($id);
        if (!$delivrable) {
            $_SESSION['error'] = 'Livrable non trouvé.';
            header('Location: index.php?controller=delivrables');
            exit;
        }
        // Ajout : charger les statuts et validations liés
        $status = $this->model->getDelivrableStatus($id);         // SELECT * FROM delivrable_status WHERE Delivrable_ID = $id
        $validation = $this->model->getDelivrableValidation($id); // SELECT * FROM delivrable_validation WHERE Delivrable_ID = $id

        $business_units = $this->model->getAllBusinessUnits();
        $activities     = $this->model->getAllActivities();
        $users          = $this->model->getAllUsers();
        $typologies     = $this->model->getAllTypologies();
        $customers      = $this->model->getAllCustomers();
        $perimeters     = $this->model->getAllPerimeters();
        $projects       = $this->model->getAllProjects();
        $clcs           = $this->model->getAllCLCs();
        $derogations    = $this->model->getAllDerogations();
        require BASE_PATH . '/views/delivrables/edit.php';
    }

    /** Traitement du POST de mise à jour */
    public function update(int $id): void
{
    $this->checkAuth();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?controller=delivrables');
        exit;
    }

    $required = [
        'ID_Topic','Description_Topic','Leader_ID','Requester_ID',
        'Customer_ID','Project_ID','Activity_ID','Perimeter_ID'
    ];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header("Location: index.php?controller=delivrables&action=edit&id=$id");
            exit;
        }
    }

    // Préparation des données pour chaque table
    $dataMain = [
        'ID_Topic'          => $_POST['ID_Topic'],
        'Description_Topic' => trim($_POST['Description_Topic']),
        'Leader_ID'         => (int) $_POST['Leader_ID'],
        'Requester_ID'      => (int) $_POST['Requester_ID'],
        'Customer_ID'       => (int) $_POST['Customer_ID'],
        'Project_ID'        => (int) $_POST['Project_ID'],
        'Activity_ID'       => (int) $_POST['Activity_ID'],
        'Perimeter_ID'      => (int) $_POST['Perimeter_ID'],
        'Typologie_ID'      => !empty($_POST['Typologie_ID']) ? (int)$_POST['Typologie_ID'] : null,
        'Livrable'          => isset($_POST['Livrable']) ? 1 : 0,
        'type_validation'   => $_POST['type_validation'] ?: null,
        'CLC_ID'            => !empty($_POST['CLC_ID']) ? (int)$_POST['CLC_ID'] : null,
        'ID_Derogation'     => !empty($_POST['ID_Derogation']) ? (int)$_POST['ID_Derogation'] : null,
        'Comment'           => trim($_POST['Comment']) ?: null,
    ];
    $dataStatus = [
        'Status_Delivrables'     => $_POST['Status_Delivrables'] ?? 'In Progress',
        'Original_Expected_Date' => $_POST['Original_Expected_Date'] ?: null,
        'Postponed_Date'         => $_POST['Postponed_Date'] ?: null,
        'Real_Date'              => $_POST['Real_Date'] ?: null,
    ];
    $dataValidation = [
        'FTR_Segula'    => $_POST['FTR_Segula'] ?? 'NA',
        'OTD_Segula'    => $_POST['OTD_Segula'] ?? 'NA',
        'FTR_Customer'  => $_POST['FTR_Customer'] ?? 'NA',
        'OTD_Customer'  => $_POST['OTD_Customer'] ?? 'NA',
    ];

    $ok = $this->model->updateDelivrable($id, array_merge($dataMain, $dataStatus, $dataValidation));
    $_SESSION[$ok ? 'success' : 'error'] = $ok
        ? 'Livrable mis à jour avec succès.'
        : 'Erreur lors de la mise à jour du livrable.';
    header('Location: index.php?controller=delivrables');
    exit;
}

    /** Affiche un livrable */
    public function view(int $id): void
    {
        $this->checkAuth();
        $delivrable = $this->model->getDelivrableById($id);
        if (!$delivrable) {
            $_SESSION['error'] = 'Livrable non trouvé.';
            header('Location: index.php?controller=delivrables');
            exit;
        }
        // Récupère les vraies valeurs dans les tables liées
        $status = $this->model->getDelivrableStatus($id);         // SELECT * FROM delivrable_status WHERE Delivrable_ID = $id
        $validation = $this->model->getDelivrableValidation($id); // SELECT * FROM delivrable_validation WHERE Delivrable_ID = $id

        require BASE_PATH . '/views/delivrables/view.php';
    }

    /** Supprime un livrable */
    public function delete(int $id): void
    {
        $this->checkAuth();
        if (!$this->model->getDelivrableById($id)) {
            $_SESSION['error'] = 'Livrable non trouvé.';
        } else {
            $ok = $this->model->deleteDelivrable($id);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Livrable supprimé avec succès.'
                : 'Erreur lors de la suppression du livrable.';
        }
        header('Location: index.php?controller=delivrables');
        exit;
    }

    /** Export Excel */
    public function exportExcel(): void
    {
        $this->checkAuth();
        $this->model->exportToExcel();
    }

    /** Export CSV */
    public function exportCSV(): void
    {
        $this->checkAuth();
        $this->model->exportToCSV();
    }

    /** AJAX : projets selon client */
    public function getProjectsByCustomer(): void
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['customer_id'])) {
            $this->json(['success'=>false,'message'=>'Paramètres invalides']);
        }

        try {
            $cid  = (int) $_POST['customer_id'];
            $stmt = $this->db->prepare(
                'SELECT ID_Project, Name_Project 
                   FROM project 
                  WHERE Customer_ID = :cid AND Status_Project = "Active" 
               ORDER BY Name_Project'
            );
            $stmt->execute([':cid' => $cid]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            $this->json(['success'=>true,'projects'=>$projects]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $this->json(['success'=>false,'message'=>'Erreur serveur']);
        }
    }

    /** Prépare les données pour create/update */
    private function prepareData(array $s): array
{
    $dataMain = [
        'ID_Topic'          => $s['ID_Topic'],
        'Description_Topic' => trim($s['Description_Topic']),
        'Leader_ID'         => (int)$s['Leader_ID'],
        'Requester_ID'      => (int)$s['Requester_ID'],
        'Typologie_ID'      => $s['Typologie_ID'] ?? null,
        'Customer_ID'       => (int)$s['Customer_ID'],
        'Activity_ID'       => (int)$s['Activity_ID'],
        'Perimeter_ID'      => (int)$s['Perimeter_ID'],
        'Project_ID'        => (int)$s['Project_ID'],
        'Livrable'          => isset($s['Livrable']) ? 1 : 0,
        'type_validation'   => $s['type_validation'] ?? null,
        'CLC_ID'            => $s['CLC_ID'] ?? null,
        'ID_Derogation'     => $s['ID_Derogation'] ?? null,
        'Comment'           => $s['Comment'] ?? '',
    ];
    $dataStatus = [
        'Status_Delivrables'     => $s['Status_Delivrables'] ?? 'In Progress',
        'Original_Expected_Date' => $s['Original_Expected_Date'] ?: null,
        'Postponed_Date'         => $s['Postponed_Date'] ?: null,
        'Real_Date'              => $s['Real_Date'] ?: null,
    ];
    $dataValidation = [
        'FTR_Segula'    => $s['FTR_Segula'] ?? 'NA',
        'OTD_Segula'    => $s['OTD_Segula'] ?? 'NA',
        'FTR_Customer'  => $s['FTR_Customer'] ?? 'NA',
        'OTD_Customer'  => $s['OTD_Customer'] ?? 'NA',
    ];
    return array_merge($dataMain, $dataStatus, $dataValidation);
}

    /** Envoie un JSON et termine */
    private function json(array $p): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($p);
        exit;
    }
}