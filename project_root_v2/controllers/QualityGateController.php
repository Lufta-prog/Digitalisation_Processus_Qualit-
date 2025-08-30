<?php
require_once __DIR__ . '/../models/QGValidationModel.php';
require_once __DIR__ . '/../models/ChecklistModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class QualityGateController {
    private $db;
    private $qgModel;
    private $checklistModel;
    private $notificationModel;

    public function __construct($db) {
        $this->db = $db;
        $this->qgModel = new QGValidationModel($db);
        $this->checklistModel = new ChecklistModel($db);
        $this->notificationModel = new NotificationModel($db);
    }


public function dashboard() {
    $this->checkAuth();
    $userId = $_SESSION['user_id'];
    
    
    $pendingApprovals = $this->qgModel->getPendingApprovals($userId);
    
    include 'views/checklist/qg_dashboard.php';
}
   public function index() {
        $this->dashboard();

    }

    public function review() {
    $this->checkAuth();
    
    // Try to get by individual parameters first
    $clcId = $_GET['clc_id'] ?? null;
    $iteration = $_GET['iteration'] ?? null;
    $qgType = $_GET['qg_type'] ?? null;
    
    // Fallback: try to get by validation ID
    if (!$clcId && isset($_GET['id'])) {
        $validation = $this->qgModel->getValidationById($_GET['id']);
        if ($validation) {
            $clcId = $validation['clc_id'];
            $iteration = $validation['iteration'];
            $qgType = $validation['qg_type'];
        }
    }

    $userId = $_SESSION['user_id'];
    $validation = $this->qgModel->getPendingValidation($clcId, $iteration, $qgType, $userId);

    if (!$validation) {
        $_SESSION['error'] = "Validation non trouvée ou non autorisée";
        header("Location: index.php?controller=dashboard");
        exit;
    }

    $checklist = $this->checklistModel->getChecklistById($clcId);
    $items = $this->qgModel->getItemsForValidation($clcId, $iteration);
    // Ajoutez ce log pour déboguer
    error_log("Items retrieved: " . print_r($items, true));

    include 'views/checklist/qg_validation_form.php';
}

  public function validate() {
    $this->checkAuth();
    $transactionStarted = false;

    try {
        // 1. Validation des données de base
        if (empty($_POST['validation_id'])) {
            throw new Exception("ID de validation manquant.");
        }

        $validationId = (int)$_POST['validation_id'];
        $action = $_POST['action'] ?? '';
        $comment = $_POST['global_comment'] ?? '';
        $items = $_POST['items'] ?? [];

        if (!in_array($action, ['approve', 'reject'])) {
            throw new Exception("Action de validation invalide.");
        }

        // 2. Récupérer les détails de la validation
        $validation = $this->qgModel->getValidationDetails($validationId);
        if (!$validation) {
            throw new Exception("Validation QG introuvable.");
        }

        // 3. Vérifier les permissions
        $userId = $_SESSION['user_id'];
        if ($validation['validator_id'] != $userId) {
            throw new Exception("Vous n'êtes pas autorisé à effectuer cette validation.");
        }

        if ($validation['status'] != 'pending') {
            throw new Exception("Cette validation a déjà été traitée.");
        }

        // 4. Préparer les données pour les mises à jour
        $dsItems = [];
        foreach ($items as $itemId => $itemData) {
            if (!is_numeric($itemId)) continue;

            $itemId = (int)$itemId;
            $status = strtoupper(trim($itemData['status'] ?? 'NA'));

            $allowedStatuses = ['OK', 'NOK', 'NA'];
            if (!in_array($status, $allowedStatuses)) {
                error_log("Invalid status received for item $itemId: " . print_r($itemData, true));
                throw new Exception("Statut invalide pour l'item $itemId. Valeurs acceptées: " . implode(', ', $allowedStatuses));
            }

            $itemComment = $itemData['comment'] ?? '';

            if ($action === 'reject' && in_array($status, ['NOK', 'NA']) && empty(trim($itemComment))) {
                throw new Exception("Un commentaire est requis pour les items marqués NOK ou NA");
            }

            $dsItems[$itemId] = [
                'status' => $status,
                'comment' => $itemComment
            ];
        }

        // 5. Démarrer la transaction
        $this->db->beginTransaction();
        $transactionStarted = true;

        try {
            // 6. Mettre à jour la validation QG
            $this->qgModel->completeQGAction($validationId, $action === 'approve' ? 'approved' : 'rejected', $comment);

            // 7. Mettre à jour les items DS
            foreach ($dsItems as $itemId => $itemData) {
                $this->qgModel->updateItemStatus(
                    $validation['clc_id'],
                    $itemId,
                    $itemData['status'],
                    $itemData['comment'],
                    $validation['qg_type']
                );

                $this->qgModel->logItemHistory(
                    $validation['clc_id'],
                    $itemId,
                    $itemData['status'],
                    $itemData['comment'],
                    $validation['iteration'],
                    $userId,
                    $validation['qg_type']
                );
            }

           // 8. Mettre à jour l'itération dans clc_master si approbation
             if ($action === 'approve') {
                $this->qgModel->completeQGAction($validationId, 'approved', $comment);
                
                // Après approbation QG1 pour C1, créer QG2
                if ($validation['qg_type'] === 'qg1') {
                    $checklist = $this->checklistModel->getChecklistById($validation['clc_id']);
                    $criticality = $this->checklistModel->getCriticalityForChecklist($validation['clc_id']);
                    
                    if ($criticality === 'C1' && !empty($checklist['Consultant_QG2_ID'])) {
                        // Vérifier si QG2 n'existe pas déjà
                        $existingQG2 = $this->qgModel->getPendingValidation(
                            $validation['clc_id'],
                            $validation['iteration'],
                            'qg2',
                            $checklist['Consultant_QG2_ID']
                        );
                        
                        if (!$existingQG2) {
                            $this->qgModel->createQGAction(
                                $validation['clc_id'],
                                $validation['iteration'],
                                'qg2',
                                $checklist['Consultant_QG2_ID']
                            );
                            
                            // Notifier le QG2
                            $this->notificationModel->createNotification(
                                $checklist['Consultant_QG2_ID'],
                                "Nouvelle validation QG2 disponible pour la checklist {$checklist['Reference_Unique']} (Itération {$validation['iteration']})",
                                "index.php?controller=qualityGate&action=review&clc_id={$validation['clc_id']}&iteration={$validation['iteration']}&qg_type=qg2",
                                'qg_validation'
                            );
                        }
                    }
                }
            
                // Mettre à jour le statut de la checklist
                 $this->checklistModel->updateChecklistStatus($validation['clc_id']);
            } elseif ($action === 'reject') {
                // Préparer la prochaine itération si rejet
                /*$nextIteration = $validation['iteration'] + 1;
                if ($nextIteration <= 3) {
                    $this->qgModel->insertQGValidationForDSItems($validation['clc_id'], $_SESSION['user_id']);
                }*/

                $this->qgModel->updateChecklistIterationAfterRejection(
                    $validation['clc_id'],
                    $validation['qg_type']
                );
                $this->qgModel->handleRejection($validation);
            }
            // 9. Valider la transaction
            $this->db->commit();
            $transactionStarted = false;

            // 10. Envoyer les notifications
            $this->sendNotifications($validation, $action);

            $_SESSION['success'] = "Validation QG enregistrée avec succès";
            header("Location: index.php?controller=qualityGate&action=dashboard");
            exit;

        } catch (Exception $e) {
            if ($transactionStarted) {
                try {
                    $this->db->rollBack();
                } catch (PDOException $rollbackEx) {
                    error_log("Rollback failed: " . $rollbackEx->getMessage());
                }
            }
            throw $e;
        }

    } catch (Exception $e) {
        error_log("Erreur validation QG: " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de la validation QG: " . $e->getMessage();
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=qualityGate&action=dashboard"));
        exit;
    }
}

/**
 * Prépare les données de commentaire pour le stockage
 */
private function prepareCommentData($qgType, $newComment, $clcId, $itemId) {
    return $newComment;
}
    

   
    /**
     * Envoie les notifications aux parties prenantes
     */
    private function sendNotifications($validation, $action) {
        $message = "Votre validation QG pour la checklist {$validation['Reference_Unique']} (Itération {$validation['iteration']}) a été ";
        $message .= ($action === 'approve') ? "approuvée" : "rejetée";
        
        // Notification au consultant
        $this->notificationModel->createNotification(
            $validation['consultant_id'],
            $message,
            "index.php?controller=checklist&action=view&id={$validation['clc_id']}",
            'info' // <-- Ajoute ce paramètre
        );

        // Si rejet, notification aux autres QG
        if ($action === 'reject') {
            $criticality = $this->checklistModel->getCriticalityMatrixById($validation['Criticality_Matrix_ID']);

            if ($validation['qg_type'] === 'qg1' && $criticality['Consultant_QG2_ID']) {
                $this->notificationModel->create(
                    $criticality['Consultant_QG2_ID'],
                    "Une validation QG1 a été rejetée pour {$validation['Reference_Unique']} (Itération {$validation['iteration']})",
                    "index.php?controller=qualityGate&action=dashboard"
                );
            }
        }
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=login");
            exit;
        }
    }


public function processQGValidation($clcId, $iteration, $action) {
    try {
        $this->checkAuth();

        // Récupérer la checklist et vérifier la criticité
        $checklist = $this->checklistModel->getChecklistById($clcId);
        $criticality = $checklist['Criticality_Level'] ?? 'C2';
        
        // Traitement de la validation QG
        if ($action === 'approve') {
            $this->qgModel->approveValidation($clcId, $iteration, $_SESSION['user_id'], $globalComment);

            // Si dernière itération ou validation finale selon criticité
            if ($iteration >= 3 || $this->isFinalApproval($clcId, $iteration, $criticality)) {
                $this->completeChecklist($clcId);
            }

            $_SESSION['success'] = "Validation QG approuvée pour l'itération $iteration";
        } elseif ($action === 'reject') {
            // Vérifier qu'on peut créer une nouvelle itération
            if ($iteration >= 3) {
                throw new Exception("Nombre maximum d'itérations atteint");
            }

            $this->qgModel->rejectValidation($clcId, $iteration, $_SESSION['user_id'], $globalComment);

            // Préparer la prochaine itération
            $this->prepareNextIteration($clcId, $iteration, $criticality);

            $_SESSION['success'] = "Validation QG rejetée - Nouvelle itération préparée";
        }

        header("Location: index.php?controller=checklist&action=view&id=$clcId");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php?controller=qualityGate&action=review&clc_id=$clcId");
        exit;
    }
}

private function prepareNextIteration($clcId, $currentIteration, $criticality) {
        // On ne crée plus la validation QG ici. Elle sera créée après la soumission du consultant.
        // On peut notifier le consultant qu'il doit retravailler l'itération.
        $this->checklistModel->notifyStakeholders($clcId, $currentIteration + 1);
}

// À adapter selon ta logique métier
private function isFinalApproval($clcId, $iteration, $criticality) {
    // Pour C1, il faut QG1 et QG2 approuvés ; pour C2, seulement QG1
    $qg1Approved = $this->checklistModel->isIterationApprovedByQG($clcId, $iteration, 'qg1');
    $qg2Approved = ($criticality === 'C1') ? $this->checklistModel->isIterationApprovedByQG($clcId, $iteration, 'qg2') : true;
    return $qg1Approved && $qg2Approved;
}

// À adapter selon ta logique métier
private function completeChecklist($clcId) {
    $this->checklistModel->closeIfAllQGApproved($clcId);
}



}