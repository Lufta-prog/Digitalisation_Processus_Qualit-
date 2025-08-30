<?php

require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../models/ChecklistModel.php';
require_once __DIR__ . '/../models/UserModel.php'; // Assurez-vous que le modèle UserModel est inclus ici
require_once __DIR__ . '/../models/ActivityModel.php'; // Assurez-vous que le modèle ActivityModel est inclus ici
require_once __DIR__ . '/../models/BusinessUnitModel.php'; // Assurez-vous que le modèle BusinessUnitModel est inclus ici
require_once __DIR__ . '/../models/ProjectModel.php'; // Assurez-vous que le modèle ProjectModel est inclus ici
require_once __DIR__ . '/../models/QGValidationModel.php'; // Assurez-vous que le modèle QGValidationModel est inclus ici
require_once __DIR__ . '/../models/TemplateModel.php'; // Assurez-vous que le modèle TemplateModel est inclus ici
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class ChecklistController {
    private $checklistModel;
    private $db;
    private $userModel;
    private $activityModel;
    private $businessUnitModel;
    private $projectModel;
    private $qgValidationModel; // Nouveau modèle QGValidationModel
    private $templateModel; // Nouveau modèle TemplateModel

    public function __construct($db) {
        $this->db = $db;
        $this->checklistModel = new ChecklistModel($db);
        $this->userModel = new UserModel($db);
        $this->activityModel = new ActivityModel($db);
        $this->businessUnitModel = new BusinessUnitModel($db);
        $this->projectModel = new ProjectModel($db);
        $this->qgValidationModel = new QGValidationModel($db); // Initialisation du modèle QGValidationModel
        $this->templateModel = new TemplateModel($db); // Initialisation du modèle TemplateModel
    }

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
    // Afficher la liste des checklists
    public function index() {
    try {
        // Vérification de l'authentification
        $this->checkAuth();

        // Paramètres de pagination
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        // Récupération des filtres
        $filters = [
            'bu' => $_GET['bu'] ?? null,
            'project' => $_GET['project'] ?? null,
            'activity' => $_GET['activity'] ?? null,
            'qg1' => $_GET['qg1'] ?? null,
            'qg2' => $_GET['qg2'] ?? null,
            'status' => $_GET['status'] ?? null
        ];

        // Récupération des données
        if ($_SESSION['user_level'] === 'Consultant') {
            $result = $this->checklistModel->getPaginatedChecklistsForConsultant(
                $_SESSION['user_id'],
                $currentPage,
                $perPage,
                $filters
            );
        }

        // Préparation des données pour la vue
        $data = [
            'checklists' => $result['data'] ?? [], // Toujours définir un tableau vide par défaut
            'businessUnits' => $this->businessUnitModel->getBusinessUnits(),
            'activities' => $this->activityModel->getAllActivities(),
            'consultants' => $this->userModel->getConsultants(),
            'currentPage' => $currentPage,
            'totalPages' => ceil(($result['total'] ?? 0) / $perPage),
            'totalChecklists' => $result['total'] ?? 0,
            'filters' => $filters
        ];

        // Extraction des variables pour la vue
        extract($data);

        include_once 'views/checklist/index.php';

    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header('Location: index.php?controller=dashboard');
        exit;
    }
}
    public function close() {
        try {
            $this->checkAuth(); // Vérification de l'authentification

            // Paramètres de pagination
            $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 10;

            // Récupération des filtres
            $filters = [
                'bu' => $_GET['bu'] ?? null,
                'activity' => $_GET['activity'] ?? null,
                'qg1' => $_GET['qg1'] ?? null,
                'qg2' => $_GET['qg2'] ?? null,
                'status' => $_GET['status'] ?? null
            ];

            // Récupération des données
            if ($_SESSION['user_level'] === 'Consultant') {
                $result = $this->checklistModel->getPaginatedChecklistsForConsultant(
                    $_SESSION['user_id'],
                    $currentPage,
                    $perPage,
                    $filters
                );
            }

            // Préparation des données pour la vue
            $data = [
                'checklists' => $result['data'] ?? [], // Toujours définir un tableau vide par défaut
                'businessUnits' => $this->businessUnitModel->getBusinessUnits(),
                'activities' => $this->activityModel->getAllActivities(),
                'consultants' => $this->userModel->getConsultants(),
                'currentPage' => $currentPage,
                'totalPages' => ceil(($result['total'] ?? 0) / $perPage),
                'totalChecklists' => $result['total'] ?? 0,
                'filters' => $filters
            ];

            // Extraction des variables pour la vue
            extract($data);

            include_once 'views/checklist/indexDS.php';

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }
    // Afficher le formulaire de création
    public function create() {
        try {
            // Vérification de l'authentification
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Utilisateur non connecté.");
            }

            // Récupérer les détails du consultant connecté
            $consultantDetails = $this->userModel->getConsultantDetails($_SESSION['user_id']);
            if (!$consultantDetails) {
                throw new Exception("Impossible de récupérer les détails de l'utilisateur connecté.");
            }

            // Récupérer les templates et projets
            $templates = $this->checklistModel->getTemplates();
            $projects = $this->projectModel->getAllProjects();
            $consultants = $this->userModel->getConsultants();

            // Préparer les données pour la vue
            $data = [
                'consultantDetails' => $consultantDetails,
                'templates' => $templates,
                'projects' => $projects,
                'consultants' => $consultants
            ];

            // Extraction des données pour la vue
            extract($data);

            // Inclure la vue
            include 'views/checklist/create.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la préparation du formulaire : " . $e->getMessage();
            header("Location: index.php?controller=checklist&action=index");
            exit;
        }
    }

    // Afficher le formulaire de modification
    public function edit($id) {
        try {
            // Vérification de l'authentification
            $this->checkAuth();

            // Validation de l'ID
            if (!is_numeric($id)) {
                throw new Exception("ID invalide.");
            }

            // Récupération des détails de la checklist
            $checklist = $this->checklistModel->getChecklistById($id);
            if (!$checklist) {
                throw new Exception("Checklist introuvable.");
            }
             // Récupérer les détails du consultant connecté
            $consultantDetails = $this->userModel->getConsultantDetails($_SESSION['user_id']);
            if (!$consultantDetails) {
                throw new Exception("Impossible de récupérer les détails de l'utilisateur connecté.");
            }


            // Récupération des templates, projets et consultants
            $templates = $this->checklistModel->getTemplates();
            $projects = $this->projectModel->getAllProjects();
            $consultants = $this->userModel->getConsultants();

            // Préparer les données pour la vue
            $data = [
                'checklist' => $checklist,
                'templates' => $templates,
                'projects' => $projects,
                'consultants' => $consultants,
                'consultantDetails' => $consultantDetails
            ];

            extract($data);

            // Inclure la vue
            include 'views/checklist/edit.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la récupération des détails : " . $e->getMessage();
            header("Location: index.php?controller=checklist&action=index");
            exit;
        }
    }

    
    public function processEdit($id) {
        try {
            $this->checkAuth();

            // Validation de l'ID
            if (!is_numeric($id)) {
                throw new Exception("ID invalide.");
            }

            // Validation de la date de livraison
            if (empty($_POST['expected_date'])) {
                throw new Exception("La date de livraison prévue est requise.");
            }

            // Mise à jour de la checklist
            $data = [
                'expected_date' => $_POST['expected_date']
            ];
            $this->checklistModel->updateChecklist($id, $data);

            $_SESSION['success'] = "Checklist modifiée avec succès.";
            header("Location: index.php?controller=checklist&action=view&id=$id");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
            header("Location: index.php?controller=checklist&action=edit&id=$id");
            exit;
        }
    }
   
    public function processCreation() {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Utilisateur non connecté.");
            }

            // Liste des champs obligatoires
            $requiredFields = [
                'template_id', 
                'criticality', 
                'activity_id', 
                'bu_id', 
                'expected_date', 
                'project_id', 
                'reference_unique'
            ];

            // Validation des champs requis
            $this->validateRequiredFields($_POST, $requiredFields);
            
            // Validation du format de la référence
            if (!preg_match('/^CLC_BU\d+_[a-zA-Z0-9_]+$/', $_POST['reference_unique'])) {
                throw new Exception("Format de référence invalide. Utiliser: CLC_BUXX_NomActivite");
            }

            // Validation de la date de livraison (utilisation de la fonction du helper)
            validateDeliveryDate($_POST['expected_date']);

            // Vérification cohérence BU/Activité/Template
            $templateInfo = $this->templateModel->getTemplateWithActivityAndBU((int)$_POST['template_id']);
            if (!$templateInfo || $templateInfo['ID_Activity'] != (int)$_POST['activity_id']) {
                throw new Exception("Incohérence entre le template sélectionné et l'activité.");
            }

            $data = [
                'template_id' => (int)$_POST['template_id'],
                'criticality' => $_POST['criticality'],
                'consultant_id' => (int)$_SESSION['user_id'],
                'qg1_id' => !empty($_POST['qg1_id']) ? (int)$_POST['qg1_id'] : null,
                'qg2_id' => !empty($_POST['qg2_id']) ? (int)$_POST['qg2_id'] : null,
                'activity_id' => (int)$_POST['activity_id'],
                'bu_id' => (int)$_POST['bu_id'],
                'expected_date' => $_POST['expected_date'],
                'project_id' => (int)$_POST['project_id'],
                'reference_unique' => $_POST['reference_unique']
            ];

            $result = $this->checklistModel->createChecklist($data);

            $_SESSION['success'] = "Checklist créée avec succès !";
            header("Location: index.php?controller=checklist&action=index");
            exit;
        } catch (Exception $e) {
            $_SESSION['old_post'] = $_POST;
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=checklist&action=create");
            exit;
        }
    }


    private function validateRequiredFields($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Le champ $field est requis.");
            }
        }
    }
    private function validateIds(array $ids): void {
        foreach ($ids as $type => $id) {
            if ($id !== null && (!is_numeric($id) || $id <= 0)) {
                throw new Exception("ID $type invalide.");
            }
        }
    }
    
    // Afficher le formulaire de modification
    public function delete() {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Vous devez être connecté pour accéder à cette page.");
            }

            
            // Récupérez les checklists du consultant connecté
            $consultantId = $_SESSION['user_id'];
            $checklists = $this->checklistModel->getChecklistsByConsultant($consultantId);

            // Passez les données à la vue
            $data = [
                'checklists' => $checklists
            ];

            // Inclure la vue de suppression
            include_once 'views/checklist/delete.php';
        } catch (Exception $e) {
            // Gestion des erreurs
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=checklist&action=index");
            exit;
        }
    }
    // Traiter la suppression d'une checklist
    public function processDelete() {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Vous devez être connecté pour effectuer cette action.");
            }

            if (empty($_POST['checklist_id'])) {
                throw new Exception("Aucune checklist sélectionnée pour suppression.");
            }

            $checklistId = (int) $_POST['checklist_id'];
            $consultantId = $_SESSION['user_id'];

            // Récupère la checklist
            $checklist = $this->checklistModel->getChecklistById($checklistId);
            if (!$checklist) {
                throw new Exception("Checklist introuvable.");
            }

            if ($checklist['Consultant_ID'] != $consultantId) {
                throw new Exception("Vous n'êtes pas autorisé à supprimer cette checklist.");
            }

            $criticalityId = $checklist['Criticality_Matrix_ID'];

            // Suppression logique de la checklist
            $this->checklistModel->deleteChecklist($checklistId);

            // Suppression des items associés à la checklist
            $this->checklistModel->deleteAllItemsByChecklistId($checklistId);

            // Vérifie si la criticité est encore utilisée
            if (!$this->checklistModel->isCriticalityUsed($criticalityId)) {
                $this->checklistModel->softDeleteCriticality($criticalityId);
            }

            $_SESSION['success'] = "Checklist supprimée avec succès.";
            header("Location: index.php?controller=checklist&action=index");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=checklist&action=delete");
            exit;
        }
    }

public function view($id) {
    try {
        // Initialisation de toutes les variables
        $isApprovedByQG = false;
        $isRejectedByQG = false;
        $isPendingQG = false;
        $qgValidationStatus = ['approved' => false, 'pending' => false, 'rejected' => false];
        $dbIterationDS = 0;
        $dbIterationDE = 0;

        // Validation de base
        if (!is_numeric($id)) {
            throw new Exception("ID de checklist invalide.");
        }

        // Récupération des données principales
        $checklist = $this->checklistModel->getChecklistById($id);
        if (!$checklist) {
            throw new Exception("Checklist introuvable.");
        }
        $qgname = $this->checklistModel->getChecklistWithQualityGates($id);
        // Initialisation des variables d'itération
        $dbIterationDE = (int)($checklist['Iteration_DE'] ?? 0);
        $dbIterationDS = (int)($checklist['Iteration_DS'] ?? 0);
        
        $currentIterationDE = $dbIterationDE + 1;
        $activeIterationDE = $currentIterationDE;
        $lastValidatedIterationDS = $dbIterationDS;
        $criticality = $checklist['Criticality_Level'];
        $isC3 = ($criticality === 'C3');

        // Vérification des statuts QG
        $hasPendingQGApproval = $this->checklistModel->hasPendingQGApproval(
            $checklist['ID_CLC'], 
            max(1, $lastValidatedIterationDS)
        );
        
        $wasIterationRejectedByQG1 = $this->checklistModel->wasIterationRejected(
            $checklist['ID_CLC'],  
            max(1, $lastValidatedIterationDS),
            'qg1'
        );
        $isIterationApprovedByQG1 = $this->checklistModel->isIterationApprovedByQG(
            $checklist['ID_CLC'],  
            max(1, $lastValidatedIterationDS),
            'qg1'
        );

        $wasIterationRejectedByQG2 = false;
        if ($criticality === 'C1') {
            $wasIterationRejectedByQG2 = $this->checklistModel->wasIterationRejected(
                $checklist['ID_CLC'],  
                max(1, $lastValidatedIterationDS),
                'qg2'
            );
        }
        $isIterationApprovedByQG2 = $this->checklistModel->isIterationApprovedByQG(
            $checklist['ID_CLC'],  
            max(1, $lastValidatedIterationDS),
            'qg2'
        );

        $wasIterationRejected = $wasIterationRejectedByQG1 || $wasIterationRejectedByQG2;
        $isApprovedByQG = $isIterationApprovedByQG1 && ($criticality !== 'C1' || $isIterationApprovedByQG2);
        // Détermination de l'itération courante DS
       if ($isC3) {
            // Pour C3 : une seule itération nécessaire
            $currentInputIterationDS = 1;
            $iterationDisplayLimit = 1;
        } else {
            // Logique existante pour C1/C2
            if ($wasIterationRejected) {
                $currentInputIterationDS = $lastValidatedIterationDS + 1;
            } else {
                $currentInputIterationDS = $lastValidatedIterationDS;
            }
            $currentInputIterationDS = min($currentInputIterationDS, 3);
        }

        // Autres données
        $templateInfo = $this->checklistModel->getTemplateInfoByChecklistId($id);
        $items = $this->checklistModel->getChecklistItems($id);
        $iterationInfo = $this->checklistModel->getIterationValidationInfo($id);
        $statusCounts = calculateStatusCounts($items);

        // Détermination du type de vue
        $viewType = $_GET['view'] ?? 'input';
        if (!in_array($viewType, ['input', 'output'])) {
            throw new Exception("Type de vue invalide.");
        }

        $itemType = ($viewType === 'input') ? 'DE' : 'DS';
        $deIterationProgress = calculateDEIterationProgress($items);
        $dsIterationProgress = calculateDSIterationProgress($items, $criticality);

        // Gestion spécifique à la vue Output
        $blockCurrentIterationOutput = false;
        
        

        if ($viewType === 'output' && !$isC3 && $currentInputIterationDS > 1) {
            $qgValidationStatus = $this->getQGValidationStatus(
                $checklist['ID_CLC'],
                $currentInputIterationDS - 1,
                $criticality
            );
            if (!$qgValidationStatus['approved']) {
                $blockCurrentIterationOutput = true;
            }
        }

        // Vérification blocage itération input
        $blockCurrentIterationInput = $this->shouldBlockCurrentIteration(
            $viewType, 
            $activeIterationDE, 
            $dbIterationDE,
            $id
        );

        $allDEItemsOK = $this->checklistModel->checkAllDEItemsOKForIteration(
            $checklist['ID_CLC'],
            $activeIterationDE - 1
        );

        // Préparation des données pour la vue
        $viewData = [
            'checklist' => $checklist,
            'criticality' => $criticality,
            'qgname' => $qgname,
            'items' => $items,
            'statusCounts' => $statusCounts,
            'allDEItemsOK' => $allDEItemsOK,
            'currentView' => $viewType,
            'dbIterationDE' => $dbIterationDE,
            'dbIterationDS' => $dbIterationDS,
            'currentIterationDE' => $currentIterationDE,
            'activeIterationDE' => $activeIterationDE,
            'lastValidatedIterationDS' => $lastValidatedIterationDS,
            'currentInputIterationDS' => $currentInputIterationDS,
            'blockCurrentIterationOutput' => $blockCurrentIterationOutput,
            'maxIteration' => max($checklist['Iteration_DS'] ?? 0, $checklist['Iteration_DE'] ?? 0),
            'templateInfo' => $templateInfo,
            'iterationInfo' => $iterationInfo,
            'hasPendingQGApproval' => $hasPendingQGApproval,
            'wasIterationRejected' => $wasIterationRejected,
            'wasIterationRejectedByQG1' => $wasIterationRejectedByQG1,
            'wasIterationRejectedByQG2' => $wasIterationRejectedByQG2,
            'isIterationApprovedByQG1' => $isIterationApprovedByQG1,
            'isIterationApprovedByQG2' => $isIterationApprovedByQG2,
            'deIterationProgress' => $deIterationProgress,
            'dsIterationProgress' => $dsIterationProgress,
            'isApprovedByQG' => $isApprovedByQG,
            'isPendingQG' => $isPendingQG,
            'isRejectedByQG' => $isRejectedByQG,
            'isC3' => $isC3,
            'qgValidationStatus' => $qgValidationStatus,
            'blockCurrentIterationInput' => $blockCurrentIterationInput
        ];

        extract($viewData);
        $this->loadChecklistView($viewType, $viewData);

    } catch (Exception $e) {
        error_log("Erreur dans ChecklistController::view - " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de l'affichage de la checklist : " . $e->getMessage();
        header("Location: index.php?controller=checklist&action=index");
        exit;
    }
}



private function getQGValidationStatus($clcId, $iteration, $criticality) {
    $status = [
        'approved' => false,
        'rejected' => false,
        'pending' => false
    ];
    
    

    // Vérification QG1
    $qg1Status = $this->qgValidationModel->getQGStatus($clcId, $iteration, 'qg1');
    
    // Vérification QG2 seulement pour C1
    $qg2Status = ($criticality === 'C1') 
        ? $this->qgValidationModel->getQGStatus($clcId, $iteration, 'qg2')
        : 'Approved'; // Pour C2/C3, on considère QG2 comme approuvé

    // Si l'un des deux est rejeté, c'est un rejet global
    if ($qg1Status === 'Rejected' || $qg2Status === 'Rejected') {
        $status['rejected'] = true;
    } 
    // Sinon, si les deux sont approuvés, c'est approuvé
    elseif ($qg1Status === 'Approved' && $qg2Status === 'Approved') {
        $status['approved'] = true;
    } 
    // Sinon, c'est en attente
    else {
        $status['pending'] = true;
    }

    return $status;
}

/**
 * Détermine si l'itération actuelle doit être bloquée
 */
private function shouldBlockCurrentIteration(
    string $viewType, 
    int $activeIterationDE, 
    int $dbIterationDE,
    int $checklistId
): bool {
    $block = false;
    if ($viewType !== 'input' || $activeIterationDE <= 1) {
        $block = false;
    } else {
        if ($activeIterationDE <= 3) {
            $previousIteration = $activeIterationDE - 2; // Conversion en index 0-based
            $block = $this->checklistModel->checkAllItemsOKForIteration(
                $checklistId,
                'DE',
                $previousIteration
            );
        } else {
            $block = false;
        }
    }
    error_log("shouldBlockCurrentIteration - viewType: $viewType, activeIteration: $activeIterationDE, dbIterationDE: $dbIterationDE, checklistId: $checklistId, block: " . ($block ? 'true' : 'false'));
    return $block;
}

/**
 * Charge la vue appropriée
 */
private function loadChecklistView(string $viewType, array $data): void {
    extract($data);
    
    switch ($viewType) {
        case 'input':
            include 'views/checklist/input_view.php';
            break;
        case 'output':
            include 'views/checklist/output_view.php';
            break;
        default:
            throw new Exception("Type de vue non supporté");
    }
}  


public function updateItems() {
    try {
        $clcId = $_GET['id'] ?? null;
        if (!$clcId || !is_numeric($clcId)) {
            throw new Exception("Identifiant de checklist invalide.");
        }

        if (empty($_POST['items'])) {
            throw new Exception("Aucune donnée reçue pour la mise à jour.");
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            throw new Exception("Utilisateur non authentifié.");
        }

        $checklist = $this->checklistModel->getChecklistById($clcId);
        if (!$checklist) {
            throw new Exception("Checklist introuvable.");
        }

        $currentIterationDE = (int)($checklist['Iteration_DE'] ?? 0);
        $currentIterationDS = (int)($checklist['Iteration_DS'] ?? 0);
        $criticality = $this->checklistModel->getCriticalityForChecklist($clcId);
        $isC3 = ($criticality === 'C3');

        // Préparer les items à mettre à jour
        $itemsToUpdate = [];
        foreach ($_POST['items'] as $itemId => $itemData) {
            $validation = validateComment(
                $itemData['comment'] ?? '',
                $itemData['status'] ?? 'NA'
            );
            
            if (!$validation['valid']) {
                throw new Exception(
                    "Item $itemId : " . implode(', ', $validation['errors'])
                );
            }

            // Si le commentaire est valide, l'ajouter à la liste des items à mettre à jour
            $itemsToUpdate[$itemId] = [
                'status' => $itemData['status'] ?? 'NA',
                'comment' => $itemData['comment'] ?? ''
            ];
        }

        // Séparer les items DE et DS
        $deItems = [];
        $dsItems = [];
        foreach ($itemsToUpdate as $itemId => $item) {
            $itemType = $this->checklistModel->getItemType($itemId);
            if ($itemType === 'DE') {
                $deItems[$itemId] = $item;
            } elseif ($itemType === 'DS') {
                $dsItems[$itemId] = $item;
            }
        }

        // Vérification métier pour DE
        if (!empty($deItems)) {
            if ($currentIterationDE >= 3) {
                throw new Exception("L'itération 3 des données d'entrée (DE) a déjà été soumise. Aucune autre modification DE n'est possible.");
            }
            
            if ($currentIterationDE === 2) {
                foreach ($deItems as $itemId => $item) {
                    if (($item['status'] === 'NOK') && empty(trim($item['comment']))) {
                        $itemName = $this->checklistModel->getItemNameById($itemId);
                        throw new Exception("Pour l'itération finale (3), l'item '" . ($itemName ?: "ID: $itemId") . "' avec statut {$item['status']} doit avoir un commentaire justificatif.");
                    }
                }
            }
        }

        // Vérification métier pour DS
        if (!empty($dsItems)) {
            // 1. Toutes les DE doivent être OK (valable pour toutes les criticités)
            if (!$this->checklistModel->areAllDEItemsOK($clcId, $currentIterationDE)) {
                throw new Exception("Il reste des items DE avec statut NOK qui doivent être OK avant de modifier les DS.");
            }
            
            // 2. Gestion spécifique pour C1/C2 (vérifications QG)
            if (!$isC3 && $currentIterationDS > 0) {
                $qg1Done = $this->qgValidationModel->isValidationDoneForQGType($clcId, $currentIterationDS, 'qg1');
                $qg2Done = ($criticality === 'C1') 
                    ? $this->qgValidationModel->isValidationDoneForQGType($clcId, $currentIterationDS, 'qg2')
                    : true;
                    
                if (!($qg1Done && $qg2Done)) {
                    throw new Exception("Impossible de modifier les DS tant que la validation QG de l'itération précédente n'est pas terminée.");
                }
            }
            
            // 3.Pour C3, on valide directement après la première itération
            if ($isC3 && $currentIterationDS >= 1) {
                $this->checklistModel->updateChecklistStatus($clcId);
                $_SESSION['success'] = "Checklist C3 validée avec succès après une itération.";
            }
            // 4. Limite d'itérations DS (valable pour toutes les criticités)
            if ($currentIterationDS >= 3) {
                throw new Exception("L'itération 3 des données de sortie (DS) a déjà été soumise. Aucune autre modification DS n'est possible.");
            }
            
        }

        // Mise à jour des items (DE et DS)
        $this->checklistModel->updateChecklistItems($clcId, $itemsToUpdate, $userId);

        // Mise à jour du rating
        $ratingResult = $this->checklistModel->updateChecklistRating($clcId);
        if (!$ratingResult['success']) {
            throw new Exception($ratingResult['error']);
        }

        // Après soumission du consultant, créer la validation QG pour la nouvelle itération si besoin
        // On ne crée la validation QG que si on vient de soumettre une nouvelle itération DS
        if (!empty($dsItems)) {
            $nextIteration = $currentIterationDS + 1;
            if ($nextIteration <= 3) {
                $this->checklistModel->createQGValidations($clcId, $nextIteration);
            }
             // 3. Mettre à jour le statut de la checklist
            $this->checklistModel->updateChecklistStatus($clcId);
        }

        $view = $_GET['view'] ?? 'input';
        $_SESSION['success'] = "Les modifications ont été mises à jour avec succès.";
        header("Location: index.php?controller=checklist&action=view&id=$clcId&view=$view");
        exit;

    } catch (Exception $e) {
        $view = $_GET['view'] ?? 'input';
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        header("Location: index.php?controller=checklist&action=view&id=$clcId&view=$view");
        exit;
    }
}
private function processComment($commentData, $iteration) {
    // Si c'est un tableau (structure avec itérations)
    if (is_array($commentData)) {
        // Cas 1: Structure avec itération (nouveau format)
        if (isset($commentData['consultant'][$iteration])) {
            return is_string($commentData['consultant'][$iteration]) ? 
                   trim($commentData['consultant'][$iteration]) : 
                   '';
        }
        // Cas 2: Structure plate (ancien format)
        elseif (isset($commentData['consultant'])) {
            return is_string($commentData['consultant']) ? 
                   trim($commentData['consultant']) : 
                   '';
        }
        // Cas 3: Tableau simple
        else {
            return trim(implode("\n", $commentData));
        }
    }
    // Si c'est une chaîne directement
    elseif (is_string($commentData)) {
        return trim($commentData);
    }
    
    return '';
}
// Mettre à jour la cotation globale de la checklist
public function updateRating($clcId) {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Utilisateur non connecté.");
        }

        // Appel au modèle pour mettre à jour la cotation
        $result = $this->checklistModel->updateChecklistRating($clcId);

        if ($result['success']) {
            $_SESSION['success'] = "Cotation mise à jour avec succès : " 
                                . $result['global_rating']
                                . " (OK: {$result['status_counts']['OK']}, "
                                . "NOK: {$result['status_counts']['NOK']}, "
                                . "NA: {$result['status_counts']['NA']})";
        } else {
            throw new Exception($result['error']);
        }

        header("Location: index.php?controller=checklist&action=view&id=$clcId");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour de la cotation : " . $e->getMessage();
        header("Location: index.php?controller=checklist&action=view&id=$clcId");
        exit;
    }
}


    
    public function startNextIteration($clcId, $currentIteration) {
        try {
            $checklist = $this->checklistModel->getChecklistById($clcId);
            $criticality = $checklist['Criticality'];

            // Vérifier si une action QG est requise pour la prochaine itération
            $nextIteration = $currentIteration + 1;
            if ($nextIteration <= 3) {
                $this->qgValidationModel->initializeQGValidation($clcId, $criticality);
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors du démarrage de l'itération suivante : " . $e->getMessage();
            header("Location: index.php?controller=checklist&action=view&id=$clcId");
            exit;
        }
    }

    public function getTemplateInfo() {
        try {
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                throw new Exception("ID de template invalide");
            }

            $templateId = (int)$_GET['id'];
            
            // Récupérer les infos du template avec BU et Activité associées
            $query = "
                SELECT t.ID_Template, t.Name_Template, 
                    a.ID_Activity, a.Name_Activity,
                    bu.ID_BU, bu.Name_BU
                FROM template t
                JOIN activity a ON t.Activity_ID = a.ID_Activity
                JOIN business_unit bu ON a.BU_ID = bu.ID_BU
                WHERE t.ID_Template = ?
                AND t.Deleted_At IS NULL
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$templateId]);
            $templateInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$templateInfo) {
                throw new Exception("Template introuvable");
            }

            // Retourner les données en JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'template_name' => $templateInfo['Name_Template'],
                'activity_id' => $templateInfo['ID_Activity'],
                'activity_name' => $templateInfo['Name_Activity'],
                'bu_id' => $templateInfo['ID_BU'],
                'bu_name' => $templateInfo['Name_BU']
            ]);
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }

}
