<?php
require_once __DIR__ . '/../models/ActivityModel.php';
require_once __DIR__ . '/../models/BusinessUnitModel.php';

class ActivityController {
    private $model;
    private $businessUnitModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new ActivityModel($db);
        $this->businessUnitModel = new BusinessUnitModel($db);
    }

    /**
     * List all activities
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 10; 
            $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }
            
            $result = $this->model->getPaginatedActivities($page, $perPage);
            
            $data = [
                'activities' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => $result['totalPages']
                ]
            ];
            
            extract($data);
            include_once 'views/activity/list.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }

    /**
     * Show create activity form
     */
    public function create() {
        try {
            $businessUnits = $this->businessUnitModel->getAllBusinessUnits();
            $data = [
                'businessUnits' => $businessUnits
            ];
            extract($data);
            include_once 'views/activity/create.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=activity');
            exit;
        }
    }

    /**
     * Store new activity
     * @throws Exception On database error
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=activity&action=create");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $buId = (int)($_POST['bu_id'] ?? 0);
        
        // Validation
        if (empty($name) || $buId <= 0) {
            $_SESSION['error'] = "Veuillez remplir tous les champs correctement.";
            header("Location: index.php?controller=activity&action=create");
            exit;
        }
        
        try {
            $this->db->beginTransaction();

            // Vérification doublon
            if ($this->model->activityNameExists($name, $buId)) {
                $_SESSION['error'] = "Une activité avec ce nom existe déjà pour cette Business Unit.";
                header("Location: index.php?controller=activity&action=create");
                exit;
            }
            
            // Insertion
            $query = "INSERT INTO activity (Name_Activity, BU_ID) VALUES (:name, :buId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':buId', $buId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->db->commit();
            $_SESSION['success'] = "Activité créée avec succès.";
            header("Location: index.php?controller=activity");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la création: " . $e->getMessage();
            header("Location: index.php?controller=activity&action=create");
            exit;
        }
    }
    /**
     * Show edit activity form
     * @param int $id Activity ID
     */
    public function edit(int $id) {
        try {
            $activity = $this->model->getActivityById($id);
            if (!$activity) {
                $_SESSION['error'] = "Activité non trouvée.";
                header("Location: index.php?controller=activity");
                exit;
            }
            
            $businessUnits = $this->businessUnitModel->getAllBusinessUnits();
            
            $data = [
                'activity' => $activity,
                'businessUnits' => $businessUnits
            ];
            extract($data);
            include_once 'views/activity/edit.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=activity");
            exit;
        }
    }

    /**
     * Update activity
     * @param int $id Activity ID
     * @throws Exception On database error
     */
   public function update(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=activity&action=edit&id=$id");
            exit;
        }

        $activity = $this->model->getActivityById($id);
        if (!$activity) {
            $_SESSION['error'] = "Activité non trouvée.";
            header("Location: index.php?controller=activity");
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $buId = (int)($_POST['bu_id'] ?? 0);
        
        // Validation
        if (empty($name) || $buId <= 0) {
            $_SESSION['error'] = "Veuillez remplir tous les champs correctement.";
            header("Location: index.php?controller=activity&action=edit&id=$id");
            exit;
        }
        
        try {
            $this->db->beginTransaction();

            // Vérification doublon (en excluant l'activité actuelle)
            if ($this->model->activityNameExists($name, $buId, $id)) {
                $_SESSION['error'] = "Une activité avec ce nom existe déjà pour cette Business Unit.";
                header("Location: index.php?controller=activity&action=edit&id=$id");
                exit;
            }
            
            // Mise à jour
            $query = "UPDATE activity SET Name_Activity = :name, BU_ID = :buId WHERE ID_Activity = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':buId', $buId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->db->commit();
            $_SESSION['success'] = "Activité mise à jour avec succès.";
            header("Location: index.php?controller=activity");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
            header("Location: index.php?controller=activity&action=edit&id=$id");
            exit;
        }
    }
    /**
     * Delete activity (soft delete)
     * @param int $id Activity ID
     */
    public function delete(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée.";
            header("Location: index.php?controller=activity");
            exit;
        }
       $activity = $this->model->getActivityById($id);
    if (!$activity) {
        $_SESSION['error'] = "Activité non trouvée.";
        header("Location: index.php?controller=activity");
        exit;
    }
    
    try {
        $this->db->beginTransaction();
        
        $query = "UPDATE activity SET Deleted_At = CURRENT_TIMESTAMP WHERE ID_Activity = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        if ($result && $stmt->rowCount() > 0) {
            $this->db->commit();
            $_SESSION['success'] = "Activité supprimée avec succès.";
        } else {
            $this->db->rollBack();
            $_SESSION['error'] = "Échec de la suppression de l'activité.";
        }
    } catch (Exception $e) {
        $this->db->rollBack();
        $_SESSION['error'] = "Erreur lors de la suppression de l'activité: " . $e->getMessage();
    }

    header("Location: index.php?controller=activity");
    exit;
}

    /**
     * View activity details
     * @param int $id Activity ID
     */
    public function view(int $id) {
        try {
            $activity = $this->model->getActivityById($id);
            if (!$activity) {
                $_SESSION['error'] = "Activité non trouvée.";
                header("Location: index.php?controller=activity");
                exit;
            }
            
            $businessUnit = $this->businessUnitModel->getBusinessUnitById($activity['BU_ID']);
            
            $data = [
                'activity' => $activity,
                'businessUnit' => $businessUnit
            ];
            extract($data);
            include_once 'views/activity/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=activity");
            exit;
        }
    }
}