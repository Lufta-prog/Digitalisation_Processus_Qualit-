<?php
require_once __DIR__ . '/../models/TemplateModel.php';
require_once __DIR__ . '/../models/ActivityModel.php';
class TemplateController {
    private $model;
    private $activityModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new TemplateModel($db);
        $this->activityModel = new ActivityModel($db);
    }

    /**
     * List all templates with pagination
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
            
            // Validate perPage value
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }
            
            $result = $this->model->getPaginatedTemplates($page, $perPage);
            
            $data = [
                'templates' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => $result['totalPages']
                ]
            ];
            
            extract($data);
            include_once 'views/templates/list.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }

    /**
     * Show create template form
     */
    public function create() {
        $activities = $this->activityModel->getAllActivities();
        include_once 'views/templates/create.php';
    }

    /**
     * Store new template
     * @throws Exception On database error
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /templates/create");
            exit;
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? null;
        $activityId = $_POST['activity_id'] ?? null;
        
        // Validate input
        if (empty($name)) {
            $_SESSION['error'] = "Nom de la template est requis";
            header("Location: index.php?controller=templates&action=create");
            exit;
        }
        
        if ($this->model->templateNameExists($name)) {
            $_SESSION['error'] = "Nom de la template existe déjà";
            header("Location: index.php?controller=templates&action=create");
            exit;
        }
        
       try {
            $this->db->beginTransaction();
            
            $templateId = $this->model->createTemplate($name, $description, $activityId);
            
            // Vérifier si c'est une restauration
            $template = $this->model->getTemplateById($templateId);
            if ($template['Deleted_At'] === null && $template['Created_At'] !== $template['Updated_At']) {
                $_SESSION['success'] = "Modèle créé avec succès";
            } else {
                $_SESSION['success'] = "Modèle créé avec succès";
            }
        
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['name'])) {
                        $this->model->addItemToTemplate(
                            $templateId,
                            $item['name'],
                            $item['description'] ?? null,
                            $item['type']
                        );
                    }
                }
            }
            
            $this->db->commit();
            header("Location: index.php?controller=templates");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la creation: " . $e->getMessage();
            header("Location: index.php?controller=templates&action=create");
            exit;
        }
    }

    /**
     * Show edit template form
     * @param int $id Template ID
     */
    public function edit(int $id) {
        $template = $this->model->getTemplateById($id);
        if (!$template) {
            $_SESSION['error'] = "Template introuvable";
            header("Location: index.php?controller=templates");
            exit;
        }

        $items = $this->model->getTemplateItems($id);
        $activities = $this->activityModel->getAllActivities();
        $data = [
            'template' => $template,
            'items' => $items,
            'activities' => $activities
        ];
        extract($data);
        include_once 'views/templates/edit.php';
    }
    /**
     * Update template
     * @param int $id Template ID
     * @throws Exception On database error
     */
    public function update(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=templates&action=edit&id=$id");
            exit;
        }

        $template = $this->model->getTemplateById($id);
        if (!$template) {
            $_SESSION['error'] = "Template introuvable";
            header("Location: index.php?controller=templates");
            exit;
        }
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? null;
        $activityId = $_POST['activity_id'] ?? null;
        
        // Validate input
        if (empty($name)) {
            $_SESSION['error'] = "Le nom du template est requis";
            header("Location: index.php?controller=templates&action=edit&id=$id");
            exit;
        }
        
        if ($this->model->templateNameExists($name, $id)) {
            $_SESSION['error'] = "Le nom du template existe déjà";
            header("Location: index.php?controller=templates&action=edit&id=$id");
            exit;
        }
        
        try {
            $this->db->beginTransaction();
            
            $this->model->updateTemplate($id, $name, $description, $activityId);
            
            // Handle items
            $this->syncTemplateItems($id, $_POST['items'] ?? []);
            
            $this->db->commit();
            $_SESSION['success'] = "Template mise à jour avec succès";
            header("Location: index.php?controller=templates");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
            header("Location: index.php?controller=templates&action=edit&id=$id");
            exit;
        }
    }

    /**
     * Sync template items (add/update/delete)
     * @param int $templateId Template ID
     * @param array $items Submitted items
     */
    private function syncTemplateItems(int $templateId, array $items) {
        $existingItems = $this->model->getTemplateItems($templateId);
        $existingItemIds = array_column($existingItems, 'ID_Item');
        $submittedItemIds = [];
        
        foreach ($items as $item) {
            if (!empty($item['name'])) {
                if (isset($item['id']) && in_array($item['id'], $existingItemIds)) {
                    // Update existing item
                    $this->model->updateTemplateItem(
                        $item['id'],
                        $item['name'],
                        $item['description'] ?? null,
                        $item['type']
                    );
                    $submittedItemIds[] = $item['id'];
                } else {
                    // Add new item
                    $this->model->addItemToTemplate(
                        $templateId,
                        $item['name'],
                        $item['description'] ?? null,
                        $item['type']
                    );
                }
            }
        }
        
        // Delete items not in the submitted list
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem['ID_Item'], $submittedItemIds)) {
                $this->model->deleteTemplateItem($existingItem['ID_Item']);
            }
        }
    }

    /**
     * Delete template
     * @param int $id Template ID
     */
    public function delete(int $id) {
        try {
            $this->db->beginTransaction();
            
            // Cette appel va maintenant aussi soft delete les items associés
            $result = $this->model->deleteTemplate($id);
            
            if ($result) {
                $this->db->commit();
                $_SESSION['success'] = "Template et ses items supprimés avec succès";
            } else {
                throw new Exception("Échec de la suppression");
            }
            
            header("Location: index.php?controller=templates");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
            header("Location: index.php?controller=templates");
            exit;
        }
    }
    /**
     * Show template details
     * @param int $id Template ID
     */
    public function view(int $id) {
        $template = $this->model->getTemplateById($id);
        if (!$template) {
            $_SESSION['error'] = "Template non trouvé";
            header("Location: index.php?controller=templates");
            exit;
        }
        
        $items = $this->model->getTemplateItems($id);
        
        // Passer les données à la vue
        $data = [
            'template' => $template,
            'items' => $items
        ];
        extract($data);
        include_once 'views/templates/view.php';
    }
}