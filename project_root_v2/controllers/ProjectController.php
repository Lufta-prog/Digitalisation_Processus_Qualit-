<?php
require_once __DIR__ . '/../models/ProjectModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class ProjectController {
    private $model;
    private $db;
    private $customers;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new ProjectModel($db);
        $this->customers = new CustomerModel($db);
    }

    /**
     * List all projects with pagination
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
            
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }
            
            $result = $this->model->getPaginatedProjects($page, $perPage);
            
            $data = [
                'projects' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => $result['totalPages']
                ]
            ];
            
            extract($data);
            include_once 'views/projects/list.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }

    /**
     * Show create project form
     */
    public function create() {
        try {
            $customers = $this->customers->getAllCustomers();
            $data = [
                'customers' => $customers,
                'projectLevels' => ['FO' => 'Front Office', 'BO' => 'Back Office'],
                'statuses' => ['Active' => 'Actif', 'Inactive' => 'Inactif'],
                'engagementTypes' => ['AT' => 'Assistance Technique', 'WP' => 'Work Package']
            ];
            extract($data);
            include_once 'views/projects/create.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=projects');
            exit;
        }
    }

    /**
     * Store new project
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=projects&action=create");
            exit;
        }

        $data = [
            'customer_id' => (int)$_POST['customer_id'],
            'name' => trim($_POST['name']),
            'contract_code' => trim($_POST['contract_code']),
            'project_level' => $_POST['project_level'],
            'starting_date' => $_POST['starting_date'],
            'expected_ending_date' => $_POST['expected_ending_date'],
            'status' => $_POST['status'],
            'type_engagement' => $_POST['type_engagement']
        ];

        // Validation
        if (empty($data['name'])) {
            $_SESSION['error'] = "Le nom du projet est obligatoire";
            header("Location: index.php?controller=projects&action=create");
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Check for duplicate name
            if ($this->model->projectNameExists($data['name'])) {
                $_SESSION['error'] = "Un projet avec ce nom existe déjà";
                header("Location: index.php?controller=projects&action=create");
                exit;
            }

            // Create project
            $this->model->createProject($data);
            
            $this->db->commit();
            $_SESSION['success'] = "Projet créé avec succès";
            header("Location: index.php?controller=projects");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
            header("Location: index.php?controller=projects&action=create");
            exit;
        }
    }

    /**
     * Show edit project form
     */
    public function edit(int $id) {
        try {
            $project = $this->model->getProjectById($id);
            if (!$project) {
                $_SESSION['error'] = "Projet non trouvé";
                header("Location: index.php?controller=projects");
                exit;
            }
            
            $customers = $this->customers->getAllCustomers();
            $data = [
                'project' => $project,
                'customers' => $customers,
                'projectLevels' => ['FO' => 'Front Office', 'BO' => 'Back Office'],
                'statuses' => ['Active' => 'Actif', 'Inactive' => 'Inactif'],
                'engagementTypes' => ['AT' => 'Assistance Technique', 'WP' => 'Work Package']
            ];
            extract($data);
            include_once 'views/projects/edit.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=projects");
            exit;
        }
    }

    /**
     * Update project
     */
    public function update(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=projects&action=edit&id=$id");
            exit;
        }

        $project = $this->model->getProjectById($id);
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header("Location: index.php?controller=projects");
            exit;
        }
        
        $data = [
            'customer_id' => (int)$_POST['customer_id'],
            'name' => trim($_POST['name']),
            'contract_code' => trim($_POST['contract_code']),
            'project_level' => $_POST['project_level'],
            'starting_date' => $_POST['starting_date'],
            'expected_ending_date' => $_POST['expected_ending_date'],
            'status' => $_POST['status'],
            'type_engagement' => $_POST['type_engagement']
        ];

        // Validation
        if (empty($data['name'])) {
            $_SESSION['error'] = "Le nom du projet est obligatoire";
            header("Location: index.php?controller=projects&action=edit&id=$id");
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Check for duplicate name (excluding current project)
            if ($this->model->projectNameExists($data['name'], $id)) {
                $_SESSION['error'] = "Un projet avec ce nom existe déjà";
                header("Location: index.php?controller=projects&action=edit&id=$id");
                exit;
            }

            // Update project
            $this->model->updateProject($id, $data);
            
            $this->db->commit();
            $_SESSION['success'] = "Projet mis à jour avec succès";
            header("Location: index.php?controller=projects");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
            header("Location: index.php?controller=projects&action=edit&id=$id");
            exit;
        }
    }

    /**
     * Delete project
     */
    public function delete(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            header("Location: index.php?controller=projects");
            exit;
        }

        $project = $this->model->getProjectById($id);
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header("Location: index.php?controller=projects");
            exit;
        }

        try {
            $this->db->beginTransaction();
            $result = $this->model->deleteProject($id);
            
            if ($result) {
                $this->db->commit();
                $_SESSION['success'] = "Projet supprimé avec succès";
            } else {
                $this->db->rollBack();
                $_SESSION['error'] = "Échec de la suppression du projet";
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }

        header("Location: index.php?controller=projects");
        exit;
    }

    /**
     * View project details
     */
    public function view(int $id) {
        try {
            $project = $this->model->getProjectById($id);
            if (!$project) {
                $_SESSION['error'] = "Projet non trouvé";
                header("Location: index.php?controller=projects");
                exit;
            }
            
            $data = ['project' => $project];
            extract($data);
            include_once 'views/projects/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=projects");
            exit;
        }
    }
}
?>