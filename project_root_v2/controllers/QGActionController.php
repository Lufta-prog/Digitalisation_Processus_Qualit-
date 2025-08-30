<?php
require_once __DIR__ . '/../models/ChecklistModel.php';
require_once __DIR__ . '/../models/QGValidationModel.php';

class QGActionController {
    private $qgValidationModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->qgValidationModel = new QGValidationModel($db);
    }


    
    public function dashboard() {
        try {
            $this->checkUserAuthentication();
            $userId = $_SESSION['user_id'];
            $pendingApprovals = $this->qgValidationModel->getPendingApprovals($userId);

            $data = [
                'pendingApprovals' => $pendingApprovals,
            ];

            $this->render('checklist/dashbordQG', $data);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    /**
     * Valide ou rejette une action QG.
     */
    public function validateAction() {
        try {
            // Vérifier si l'utilisateur est connecté
            $this->checkUserAuthentication();

            // Récupérer et valider les données POST
            $validationId = $this->getPostData('validation_id');
            $status = $this->getPostData('status');
            $comment = $_POST['comment'] ?? null;

            // Vérifier que le statut est valide
            if (!in_array($status, ['approved', 'rejected'])) {
                throw new Exception("Statut invalide.");
            }

            // Vérifier que l'utilisateur a le droit de valider
            $validation = $this->qgValidationModel->getQGValidationById($validationId);
            $this->checkUserAuthorization($validation);

            // Effectuer la validation
            $this->qgValidationModel->completeQGAction($validationId, $status, $comment);

            // Redirection avec un message de succès
            $_SESSION['success'] = "Validation enregistrée avec succès.";
            header("Location: index.php?controller=checklist&action=view&id=" . $validation['clc_id']);
            exit;
        } catch (Exception $e) {
            // Gestion des erreurs et redirection
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    /**
     * Vérifie si l'utilisateur est authentifié.
     */
    private function checkUserAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Utilisateur non connecté.");
        }
    }

    /**
     * Vérifie si l'utilisateur est autorisé à valider l'action QG.
     */
    private function checkUserAuthorization($validation) {
        if (!$validation) {
            throw new Exception("Validation introuvable.");
        }

        if ($validation['validator_id'] != $_SESSION['user_id']) {
            throw new Exception("Vous n'êtes pas autorisé à effectuer cette validation.");
        }
    }

    /**
     * Récupère une donnée POST et vérifie qu'elle est présente.
     */
    private function getPostData($key) {
        if (!isset($_POST[$key]) || empty($_POST[$key])) {
            throw new Exception("Le paramètre '$key' est manquant.");
        }
        return htmlspecialchars($_POST[$key]); // Échapper les données pour éviter les injections XSS
    }
}