<?php

require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/QGValidationModel.php';
if (!function_exists('json_validate')) {
    function json_validate($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

class ChecklistModel {
    private $db;
    private $notificationModel;
    private $qgValidationModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->notificationModel = new NotificationModel($dbConnection);
        $this->qgValidationModel = new QGValidationModel($dbConnection);
    }
    //  Verifier le niveau de l'utilisateur
    // Cette méthode est utilisée pour vérifier le niveau d'un utilisateur spécifique dans la base de données.
    public function getUserLevel($userId) {
        try {
            $query = "SELECT user_level FROM users WHERE ID_User = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération du rôle de l\'utilisateur : ' . $e->getMessage());
        }
    }
  
    // 6. Créer une nouvelle checklist master
    public function createChecklistMaster($matrixId, $projectId, $activityId, $buId, $expectedDate, $referenceUnique) {
        try {
            // Insérer la checklist dans la table `clc_master`
            $query = "INSERT INTO clc_master 
                    (Criticality_Matrix_ID, Project_ID, Activity_ID, BU_ID, Expected_Delivery_Date, Reference_Unique, Status, Created_At) 
                    VALUES (?, ?, ?, ?, ?, ?, 'In-Progress', NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$matrixId, $projectId, $activityId, $buId, $expectedDate, $referenceUnique]);

            // Récupérer l'ID de la checklist nouvellement créée
            $clcId = $this->db->lastInsertId();
            return $clcId;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de la checklist : " . $e->getMessage());
        }
    }
    
    // 7.  Insérer les items dans la checklist
    public function initializeChecklistItems($clcId, $templateId) {
        try {
            // Étape 1 : Récupérer les items associés au Template_ID
            $query = "SELECT ID_Item,Item_Type FROM items WHERE Template_ID = :template_id AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':template_id', $templateId, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                throw new Exception("Aucun item trouvé pour le template spécifié.");
            }

            // Étape 2 : Insérer les items dans clc_items
            $query = "INSERT INTO clc_items (CLC_ID, Item_ID, Status, QG1_Status, QG2_Status, Comment, QG1_Comment, QG2_Comment) 
                    VALUES (:clc_id, :item_id, 'NA', 'NA', 'NA', NULL, NULL, NULL)";
            $stmt = $this->db->prepare($query);

            $queryHistory = "INSERT INTO clc_history 
            (clc_id, item_id, old_status, new_status, qg1_status, qg2_status, comment, iteration, changed_by, change_date)
            VALUES (:clc_id, :item_id, 'NA', 'NA', 'NA', 'NA', NULL, 0, NULL, NOW())";
            $stmtHistory = $this->db->prepare($queryHistory);

            foreach ($items as $item) {
                $stmt->execute([
                    'clc_id' => $clcId,
                    'item_id' => $item['ID_Item']
                ]);
                // Historisation initiale
                $stmtHistory->execute([
                    'clc_id' => $clcId,
                    'item_id' => $item['ID_Item']
                ]);
            }
        
            return count($items); // Retourne le nombre d'items initialisés
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de l\'initialisation des items : ' . $e->getMessage());
        }
    }

    // 8. Créer une matrice de criticité
    // Cette méthode est utilisée pour créer une matrice de criticité dans la base de données.
    public function createCriticalityMatrix($templateId, $consultantId, $qg1Id, $qg2Id, $criticality) {
        try {
            // 1. Vérifier s'il existe une ligne soft deleted
            $checkQuery = "SELECT ID_Row FROM criticality_matrix 
                        WHERE Template_ID = :template_id 
                            AND Consultant_ID = :consultant_id 
                            AND Criticality_Level = :criticality 
                            AND Deleted_At IS NOT NULL
                        LIMIT 1";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([
                'template_id' => $templateId,
                'consultant_id' => $consultantId,
                'criticality' => $criticality
            ]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                // 2. Réactiver la ligne soft deleted
                $updateQuery = "UPDATE criticality_matrix 
                                SET Consultant_QG1_ID = :qg1_id,
                                    Consultant_QG2_ID = :qg2_id,
                                    Deleted_At = NULL,
                                    Updated_At = CURRENT_TIMESTAMP()
                                WHERE ID_Row = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([
                    'qg1_id' => $qg1Id,
                    'qg2_id' => $qg2Id,
                    'id' => $existingId
                ]);
                return $existingId;
            } else {
                // 3. Insérer une nouvelle ligne
                $insertQuery = "INSERT INTO criticality_matrix 
                        (Template_ID, Consultant_ID, Consultant_QG1_ID, Consultant_QG2_ID, Criticality_Level) 
                        VALUES (:template_id, :consultant_id, :qg1_id, :qg2_id, :criticality)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->execute([
                    'template_id' => $templateId,
                    'consultant_id' => $consultantId,
                    'qg1_id' => $qg1Id,
                    'qg2_id' => $qg2Id,
                    'criticality' => $criticality
                ]);
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la création de la matrice de criticité : ' . $e->getMessage());
        }
    }

    // 8. Créer une checklist
    public function createChecklist($data) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            // Vérifier l'unicité de la référence
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM clc_master WHERE Reference_Unique = :ref AND Deleted_At IS NULL");
            $stmt->execute(['ref' => $data['reference_unique']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Une checklist active avec cette référence existe déjà.");
            }

            // Étape 1 : Insérer dans criticality_matrix
            $matrixId = $this->createCriticalityMatrix(
                $data['template_id'],
                $data['consultant_id'],
                $data['qg1_id'],
                $data['qg2_id'],
                $data['criticality']
            );

            // Étape 2 : Insérer dans clc_master
            $clcId = $this->createChecklistMaster(
                $matrixId,
                $data['project_id'],
                $data['activity_id'],
                $data['bu_id'],
                $data['expected_date'],
                $data['reference_unique'] 
            );

            // Étape 3 : Initialiser les items dans clc_items
            $itemsCount = $this->initializeChecklistItems($clcId, $data['template_id']);

            // Valider la transaction
            $this->db->commit();
            if ($clcId && is_numeric($clcId)) {
                $this->notifyStakeholders($clcId, 1);
            }
            return [
                'clc_id' => $clcId,
                'items_count' => $itemsCount
            ];
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception('Erreur lors de la création de la checklist : ' . $e->getMessage());
        }
    }
    
    
    // 7. Mettre à jour le statut et le commentaire d'un item
   public function updateItemStatus($clcId, $itemId, $status, $comment) {
        try {
            $sql = "UPDATE clc_items 
                    SET Status = :status, Comment = :comment 
                    WHERE CLC_ID = :clcId AND Item_ID = :itemId";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':comment' => $comment,
                ':clcId' => $clcId,
                ':itemId' => $itemId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans updateItemStatus: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAllItemsByChecklistId($checklistId) {
        try {
            $query = "DELETE FROM clc_items WHERE CLC_ID = :checklist_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['checklist_id' => $checklistId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression des items de la checklist : " . $e->getMessage());
        }
    }

    // 9. Récupérer les templates
    // Cette méthode est utilisée pour récupérer tous les templates disponibles dans la base de données.
    public function getTemplates() {
        try {
            $query = "SELECT ID_Template, Name_Template FROM template WHERE Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération des templates : ' . $e->getMessage());
        }
    }

    // 10. Récupérer les items d'une checklist
    // Cette méthode est utilisée pour récupérer tous les items associés à une checklist spécifique.
public function getChecklistItems($id) {
    try {
        // 1. Récupérer les informations de base de la checklist
        $checklistInfo = $this->getChecklistById($id);
        if (!$checklistInfo) {
            throw new Exception("Checklist introuvable");
        }

        // 2. Requête pour les items actuels avec leur type
        $currentItemsQuery = "
            SELECT 
                ci.Item_ID, 
                i.Name_Item, 
                ci.Status as current_status,
                ci.Comment as current_comment,
                ci.QG1_Status, 
                ci.QG2_Status,
                ci.QG1_Comment,
                ci.QG2_Comment,
                i.Item_Type
            FROM clc_items ci
            JOIN items i ON ci.Item_ID = i.ID_Item
            WHERE ci.CLC_ID = :id
            ORDER BY i.Item_Type, i.Name_Item
        ";
        $stmt = $this->db->prepare($currentItemsQuery);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // 3. Requête optimisée pour l'historique groupé par item et itération
        $historyQuery = "
            SELECT 
                item_id,
                iteration,
                new_status as status,
                comment,
                qg1_comment,
                qg2_comment,
                qg1_status,
                qg2_status,
                change_date
            FROM clc_history
            WHERE clc_id = :id
            ORDER BY item_id, iteration
        ";
        $historyStmt = $this->db->prepare($historyQuery);
        $historyStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $historyStmt->execute();
        
        // Préparer un tableau d'historique groupé par item_id et iteration
        $historyData = [];
        while ($row = $historyStmt->fetch(PDO::FETCH_ASSOC)) {
            $historyData[$row['item_id']][$row['iteration']] = $row;
        }

        // 4. Structurer les données finales
        $items = ['DE' => [], 'DS' => []];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $type = $row['Item_Type'];
            $itemId = $row['Item_ID'];
            
            $itemData = [
                'id' => $itemId,
                'name' => $row['Name_Item'],
                'current_status' => $row['current_status'],
                'current_comment' => $row['current_comment'],
                'type' => $type,
                'iterations' => []
            ];

            // Remplir les données pour chaque itération (1 à 3)
            for ($i = 1; $i <= 3; $i++) {
                if (isset($historyData[$itemId][$i])) {
                    // Données historiques existantes
                    $hist = $historyData[$itemId][$i];
                    $itemData['iterations'][$i] = [
                        'status' => $hist['status'],
                        'comment' => [
                            'consultant' => $hist['comment'] ?? '',
                            'qg1' => $hist['qg1_comment'] ?? '',
                            'qg2' => $hist['qg2_comment'] ?? ''
                        ],
                        'qg1_status' => $hist['qg1_status'] ?? null,
                        'qg2_status' => $hist['qg2_status'] ?? null,
                        'change_date' => $hist['change_date']
                    ];
                } else {
                    // Pas d'historique pour cette itération
                    $itemData['iterations'][$i] = [
                        'status' => null,
                        'comment' => [
                            'consultant' => '',
                            'qg1' => ($i === 1) ? $row['QG1_Comment'] ?? '' : '',
                            'qg2' => ($i === 1) ? $row['QG2_Comment'] ?? '' : ''
                        ],
                        'qg1_status' => ($i === 1) ? $row['QG1_Status'] ?? null : null,
                        'qg2_status' => ($i === 1) ? $row['QG2_Status'] ?? null : null,
                        'change_date' => null
                    ];
                }
            }
            
            $items[$type][$itemId] = $itemData;
        }

        return $items;
    } catch (PDOException $e) {
        error_log("Erreur dans getChecklistItems: " . $e->getMessage());
        throw new Exception('Erreur lors de la récupération des items de la checklist');
    }
}
    public function getChecklistsByConsultant($consultantId) {
        try {
            $query = "
                SELECT cm.ID_CLC, cm.Reference_Unique, cmx.Criticality_Level
                FROM clc_master cm
                INNER JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
                WHERE cm.Deleted_At IS NULL 
                AND cmx.Consultant_ID = :consultant_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['consultant_id' => $consultantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des checklists : " . $e->getMessage());
        }
    }
    // 11. Récupérer une checklist par ID
    // Cette méthode est utilisée pour récupérer une checklist spécifique à partir de son ID.
    public function getChecklistById($checklistId) {
        try {
            $query = "
                SELECT cm.ID_CLC, cm.Reference_Unique, a.Name_Activity, bu.Name_BU,cmx.Criticality_Level, cmx.Consultant_ID, cmx.Consultant_QG1_ID, cmx.Consultant_QG2_ID,
                cm.Date_Initiation, cm.Expected_Delivery_Date, cm.Real_Delivery_Date, cm.Status,
                cm.Iteration_DS, cm.Iteration_DE, cm.Iteration_QG1, cm.Iteration_QG2, cm.Criticality_Matrix_ID, cm.Updated_At
                FROM clc_master cm
                JOIN activity a ON cm.Activity_ID = a.ID_Activity
                JOIN business_unit bu ON cm.BU_ID = bu.ID_BU
                JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
                WHERE cm.ID_CLC = :id AND cm.Deleted_At IS NULL
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $checklistId, PDO::PARAM_INT);
            $stmt->execute();
            $checklist = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$checklist) {
                throw new Exception("Checklist introuvable.");
            }
    
            return $checklist;
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération de la checklist : ' . $e->getMessage());
        }
    }

    public function updateChecklist($id, $data) {
        try {
            $query = "UPDATE clc_master SET Expected_Delivery_Date = :expected_date WHERE ID_CLC = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':expected_date', $data['expected_date'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la checklist : " . $e->getMessage());
        }
    }


    public function deleteChecklist($checklistId) {
        try {
            $query = "UPDATE clc_master SET Deleted_At = NOW() WHERE ID_CLC = :checklist_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['checklist_id' => $checklistId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la checklist : " . $e->getMessage());
        }
    }

    // 13. Vérifier si une criticité est utilisée
    public function isCriticalityUsed($criticalityId) {
        try {
            $query = "SELECT COUNT(*) FROM clc_master WHERE Criticality_Matrix_ID = :id AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $criticalityId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification de l'utilisation de la criticité : " . $e->getMessage());
        }
    }
    
    
    public function softDeleteCriticality($criticalityId) {
        try {
            $query = "UPDATE criticality_matrix SET Deleted_At = NOW() WHERE ID_Row = :id";

            // Préparation de la requête
            $stmt = $this->db->prepare($query);

            // Exécution de la requête
            $result = $stmt->execute(['id' => $criticalityId]);

            
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la criticité : " . $e->getMessage());
        }
    }

    public function restoreChecklist($checklistId) {
        try {
            $query = "UPDATE clc_master SET Deleted_At = NULL WHERE ID_CLC = :checklist_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['checklist_id' => $checklistId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la restauration de la checklist : " . $e->getMessage());
        }
    }

 public function updateDEItems($clcId, $deItems, $userId, $currentIterationDE) {
        foreach ($deItems as $itemId => $data) {
            $status = strtoupper($data['status'] ?? 'NA');
            $comment = $data['comment'] ?? '';

            // Validation des données
            if (!is_numeric($itemId) || empty($itemId)) {
                throw new Exception("ID d'item invalide : $itemId");
            }
            if (!in_array($status, ['OK', 'NOK', 'NA'])) {
                throw new Exception("Statut invalide pour DE: $status");
            }
            if (in_array($status, ['NOK', 'NA']) && empty(trim($comment))) {
                throw new Exception("Commentaire requis pour l'item DE ayant le statut $status");
            }

            // Récupérer l'ancien statut
            $stmtOld = $this->db->prepare("SELECT Status FROM clc_items WHERE CLC_ID = ? AND Item_ID = ?");
            $stmtOld->execute([$clcId, $itemId]);
            $oldStatus = $stmtOld->fetchColumn();

            // Mise à jour de l'item dans clc_items
            $updateItem = $this->db->prepare("
                UPDATE clc_items 
                SET Status = ?, Comment = ?, Modified_At = NOW()
                WHERE CLC_ID = ? AND Item_ID = ?
            ");
            $updateItem->execute([$status, $comment, $clcId, $itemId]);

            // Historisation dans clc_history
            $logHistory = $this->db->prepare("
                INSERT INTO clc_history 
                (clc_id, item_id, old_status, new_status, comment, iteration, changed_by, change_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $logHistory->execute([
                $clcId,
                $itemId,
                $oldStatus,
                $status,
                $comment,
                $currentIterationDE + 1, // Log for the iteration being submitted (1-indexed)
                $userId
            ]);
        }
    }


  
    public function updateDSItems($clcId, $dsItems, $userId, $currentIterationDS) {
        foreach ($dsItems as $itemId => $itemData) {
            $status = strtoupper($itemData['status'] ?? 'NA');
            $comment = $itemData['comment'] ?? '';

            if (!in_array($status, ['OK', 'NOK', 'NA'])) {
                throw new Exception("Statut invalide pour DS: $status");
            }
            if (in_array($status, ['NOK', 'NA']) && empty(trim($comment))) {
                throw new Exception("Commentaire requis pour l'item DS ayant le statut $status");
            }

            // Récupérer l'ancien statut
            $stmtOld = $this->db->prepare("SELECT Status FROM clc_items WHERE CLC_ID = ? AND Item_ID = ?");
            $stmtOld->execute([$clcId, $itemId]);
            $oldStatus = $stmtOld->fetchColumn();

            // Mise à jour de l'item dans clc_items
            $updateItem = $this->db->prepare("
                UPDATE clc_items 
                SET Status = ?, Comment = ?, Modified_At = NOW()
                WHERE CLC_ID = ? AND Item_ID = ?
            ");
            $updateItem->execute([$status, $comment, $clcId, $itemId]);

            // Historisation dans clc_history
            $logHistory = $this->db->prepare("
                INSERT INTO clc_history 
                (clc_id, item_id, old_status, new_status, comment, iteration, changed_by, change_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $logHistory->execute([
                $clcId,
                $itemId,
                $oldStatus,
                $status,
                $comment,
                $currentIterationDS + 1, // Log for the iteration being submitted (1-indexed)
                $userId
            ]);
        }
    }
    

    /*public function validateC1Requirements($clcId, $iteration) {
        // Pour C1, vérifier que QG1 ET QG2 ont validé l'itération précédente
        if ($iteration > 1) {
            $qg1Approved = $this->qgValidationModel->isApprovedByQGType($clcId, $iteration - 1, 'qg1');
            $qg2Approved = $this->qgValidationModel->isApprovedByQGType($clcId, $iteration - 1, 'qg2');

            if (!$qg1Approved || !$qg2Approved) {
                throw new Exception("Pour C1, les validations QG1 et QG2 sont requises avant de passer à la nouvelle itération.");
            }
        }
    }
    public function validateC2Requirements($clcId, $iteration) {
        // Pour C2, seule la validation QG1 est requise
        if ($iteration > 1) {
            $qg1Approved = $this->qgValidationModel->isApprovedByQGType($clcId, $iteration - 1, 'qg1');

            if (!$qg1Approved) {
                throw new Exception("Pour C2, la validation QG1 est requise avant de passer à la nouvelle itération.");
            }
        }
    }*/

    public function updateChecklistItems($clcId, $items, $userId) {
        try {
            // Validation de l'utilisateur
            if (empty($userId)) {
                throw new Exception("L'ID utilisateur est requis pour cette opération");
            }

            $userCheck = $this->db->prepare("SELECT COUNT(*) FROM users WHERE ID_User = ?");
            $userCheck->execute([$userId]);
            if ($userCheck->fetchColumn() === 0) {
                throw new Exception("L'utilisateur avec l'ID $userId n'existe pas");
            }

            $this->db->beginTransaction();

            // Récupération de l'état actuel
            $stmt = $this->db->prepare("
                SELECT Iteration_DS, Iteration_DE, Status, Criticality_Matrix_ID 
                FROM clc_master 
                WHERE ID_CLC = ? 
                FOR UPDATE
            ");
            $stmt->execute([$clcId]);
            $checklistData = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentIterationDE = (int)$checklistData['Iteration_DE'];
            $currentIterationDS = (int)$checklistData['Iteration_DS'];
            
            // Récupérer la criticité
            $matrix = $this->getCriticalityMatrixById($checklistData['Criticality_Matrix_ID']);
            $criticality = $matrix['Criticality_Level'];

            // Séparation des items par type
            $deItems = [];
            $dsItems = [];
            foreach ($items as $itemId => $data) {
                $stmtType = $this->db->prepare("SELECT Item_Type FROM items WHERE ID_Item = ?");
                $stmtType->execute([$itemId]);
                $itemType = $stmtType->fetchColumn();

                if ($itemType === 'DE') {
                    $deItems[$itemId] = $data;
                } elseif ($itemType === 'DS') {
                    $dsItems[$itemId] = $data;
                } else {
                    throw new Exception("Type d'item inconnu pour l'ID : $itemId");
                }
            }

            // Mise à jour des DE
            if (!empty($deItems)) {
                $this->updateDEItems($clcId, $deItems, $userId, $currentIterationDE);
            }

            // Mise à jour des DS - règles différentes selon la criticité
            // Ne pas incrémenter Iteration_DS ici, seulement après validation QG
            if (!empty($dsItems)) {
                $this->updateDSItems($clcId, $dsItems, $userId, $currentIterationDS);
                
                // Ne pas incrémenter Iteration_DS ici pour C1/C2
                if ($criticality !== 'C3') {
                    $this->qgValidationModel->insertQGValidationForDSItems($clcId, $userId);
                } else {
                    // Pour C3, pas de validation QG, on incrémente directement
                    $this->db->prepare("UPDATE clc_master SET Iteration_DS = Iteration_DS + 1 WHERE ID_CLC = ?")
                        ->execute([$clcId]);
                }
            }

            // Mise à jour des itérations
            $updateQuery = "UPDATE clc_master SET ";
            $params = [];

            if (!empty($deItems)) {
                $updateQuery .= "Iteration_DE = Iteration_DE + 1, ";
            }



            $updateQuery .= "Updated_At = NOW() WHERE ID_CLC = ?";
            $params[] = $clcId;

            $this->db->prepare($updateQuery)->execute($params);

            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur de mise à jour : " . $e->getMessage());
        }
    }

public function updateChecklistRating($clcId) {
    try {
        $this->db->beginTransaction();

        // 1. Récupérer uniquement les items de type DS
        $stmt = $this->db->prepare("
            SELECT ci.Status
            FROM clc_items ci
            JOIN items i ON ci.Item_ID = i.ID_Item
            WHERE ci.CLC_ID = :clc_id AND i.Item_Type = 'DS'
        ");
        $stmt->execute(['clc_id' => $clcId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DEBUG : journaliser le nombre d'items renvoyés et leur contenu
        error_log("Debug updateChecklistRating: clcId = $clcId");
        error_log("Nombre d'items DS trouvés: " . count($items));
        error_log("Contenu des items DS: " . print_r($items, true));

        // 2. Compter les statuts
        $statusCounts = [
            'OK' => 0,
            'NOK' => 0,
            'NA' => 0
        ];

        foreach ($items as $item) {
            $status = strtoupper($item['Status']);
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        // 3. Déterminer la cotation globale
        $globalRating = ($statusCounts['NOK'] === 0 && $statusCounts['NA'] === 0) 
            ? 'OK' 
            : 'NOK';

        // 4. Mettre à jour la cotation dans clc_master
        $updateStmt = $this->db->prepare("
            UPDATE clc_master
            SET Cotation = :rating
            WHERE ID_CLC = :clc_id
        ");
        $updateStmt->execute([
            'rating' => $globalRating,
            'clc_id' => $clcId
        ]);

        $this->db->commit();

        // DEBUG : journaliser la cotation globale mise à jour et les compteurs de statuts
        error_log("Cotation globale mise à jour : " . $globalRating);
        error_log("Compte des statuts: " . print_r($statusCounts, true));

        return [
            'success' => true,
            'global_rating' => $globalRating,
            'status_counts' => $statusCounts
        ];

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Erreur updateChecklistRating: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

    

   
   public function getPaginatedChecklistsForConsultant($consultantId, $page, $perPage, $filters = []) {
    try {
        $offset = ($page - 1) * $perPage;
        
        // Construction de la requête de base
        $query = "SELECT SQL_CALC_FOUND_ROWS 
                    cm.ID_CLC,p.Name_Project, a.Name_Activity, bu.Name_BU, cm.Reference_Unique, cmx.Criticality_Level,
                    CONCAT(qg1.Fname_User, ' ', qg1.Lname_User) AS QG1_Name,
                    CONCAT(qg2.Fname_User, ' ', qg2.Lname_User) AS QG2_Name,
                    cm.Date_Initiation, cm.Expected_Delivery_Date,cm.Real_Delivery_Date, cm.Status
                  FROM clc_master cm
                  JOIN project p ON cm.Project_ID = p.ID_Project
                  JOIN activity a ON cm.Activity_ID = a.ID_Activity
                  JOIN business_unit bu ON cm.BU_ID = bu.ID_BU
                  JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
                  JOIN users consultant ON cmx.Consultant_ID = consultant.ID_User
                  LEFT JOIN users qg1 ON cmx.Consultant_QG1_ID = qg1.ID_User
                  LEFT JOIN users qg2 ON cmx.Consultant_QG2_ID = qg2.ID_User
                  WHERE cm.Deleted_At IS NULL AND cmx.Consultant_ID = :consultant_id";
        
        // Ajout des filtres
        $params = ['consultant_id' => $consultantId];
        
        if (!empty($filters['bu'])) {
            $query .= " AND bu.Name_BU = :bu_name";
            $params['bu_name'] = $filters['bu'];
        }
        if (!empty($filters['project'])) {
            $query .= " AND p.Name_Project = :project_name";
            $params['project_name'] = $filters['project'];
        }
        
        if (!empty($filters['activity'])) {
            $query .= " AND a.Name_Activity = :activity_name";
            $params['activity_name'] = $filters['activity'];
        }
        
        if (!empty($filters['qg1'])) {
            $query .= " AND CONCAT(qg1.Fname_User, ' ', qg1.Lname_User) = :qg1_name";
            $params['qg1_name'] = $filters['qg1'];
        }
        
        if (!empty($filters['qg2'])) {
            $query .= " AND CONCAT(qg2.Fname_User, ' ', qg2.Lname_User) = :qg2_name";
            $params['qg2_name'] = $filters['qg2'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND cm.Status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Ajout de la pagination
        $query .= " ORDER BY cm.ID_CLC DESC LIMIT :offset, :per_page";
        $params['offset'] = $offset;
        $params['per_page'] = $perPage;
        
        // Exécution
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $value, $paramType);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupération du total
        $stmt = $this->db->query("SELECT FOUND_ROWS()");
        $total = $stmt->fetchColumn();
        
        return [
            'data' => $data,
            'total' => $total
        ];
        
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération des checklists: " . $e->getMessage());
    }
}


public function getChecklistWithQualityGates($checklistId) {
    $query = "
        SELECT 
            cm.*, 
            a.Name_Activity,
            bu.Name_BU,
            cmx.Criticality_Level,
            -- QG1
            qg1.ID_User AS QG1_ID,
            CONCAT(qg1.Fname_User, ' ', qg1.Lname_User) AS QG1_Name,
            -- QG2 (seulement pour C1)
            qg2.ID_User AS QG2_ID,
            CONCAT(qg2.Fname_User, ' ', qg2.Lname_User) AS QG2_Name
        FROM clc_master cm
        JOIN activity a ON cm.Activity_ID = a.ID_Activity
        JOIN business_unit bu ON cm.BU_ID = bu.ID_BU
        JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
        LEFT JOIN users qg1 ON cmx.Consultant_QG1_ID = qg1.ID_User
        LEFT JOIN users qg2 ON cmx.Consultant_QG2_ID = qg2.ID_User
        WHERE cm.ID_CLC = :checklistId
    ";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':checklistId', $checklistId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


public function getChecklistHistory($clcId){
    try {
        $query = "
            SELECT cm.ID_CLC, a.Name_Activity, bu.Name_BU, cm.Date_Initiation, cm.Expected_Delivery_Date, cm.Real_Delivery_Date, cm.Status,
            cm.Iteration_DS, cm.Iteration_DE, cm.Iteration_QG1, cm.Iteration_QG2, cm.Updated_At
            FROM clc_master cm
            JOIN activity a ON cm.Activity_ID = a.ID_Activity
            JOIN business_unit bu ON cm.BU_ID = bu.ID_BU
            WHERE cm.ID_CLC = :id AND cm.Deleted_At IS NULL
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $clcId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception('Erreur lors de la récupération de l\'historique de la checklist : ' . $e->getMessage());
    }
}

    public function getIterationValidationInfo($clcId) {
        try {
            $query = "SELECT 
                        iteration, 
                        DATE_FORMAT(MAX(change_date), '%d/%m/%Y %H:%i') as formatted_date,
                        CONCAT(u.Fname_User, ' ', u.Lname_User) as validator_name
                    FROM clc_history h
                    JOIN users u ON h.changed_by = u.ID_User
                    WHERE h.clc_id = :clc_id
                    GROUP BY iteration, u.ID_User
                    ORDER BY iteration";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['clc_id' => $clcId]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organiser les résultats par itération
            $validationInfo = [];
            foreach ($results as $row) {
                $validationInfo[$row['iteration']] = [
                    'date' => $row['formatted_date'],
                    'by' => $row['validator_name']
                ];
            }
            
            return $validationInfo;
        } catch (PDOException $e) {
            error_log("Erreur dans getIterationValidationInfo: " . $e->getMessage());
            return [];
        }
    }


public function getTemplateInfoByChecklistId($checklistId) {
    try {
        $query = "
            SELECT t.ID_Template, t.Name_Template, t.Description_Template
            FROM clc_master cm
            JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
            JOIN template t ON cmx.Template_ID = t.ID_Template
            WHERE cm.ID_CLC = :checklist_id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':checklist_id', $checklistId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des infos de la template : " . $e->getMessage());
        return null;
    }
}

    public function isReferenceUnique($reference, $excludeId = null) {
    try {
        $query = "SELECT COUNT(*) FROM clc_master 
                 WHERE Reference_Unique = :reference";
        
        if ($excludeId) {
            $query .= " AND ID_CLC != :exclude_id";
        }
        
        $stmt = $this->db->prepare($query);
        $params = ['reference' => $reference];
        
        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchColumn() === 0;
    } catch (PDOException $e) {
        throw new Exception('Erreur lors de la vérification de la référence : ' . $e->getMessage());
    }
    }

   private function notifyStakeholders(int $clcId, int $newIteration): void {
    try {
        $checklist = $this->getChecklistById($clcId);
        if (!$checklist) return;

        $notificationModel = new NotificationModel($this->db);
        $baseMessage = sprintf(
            "Nouvelle itération DS (%d) à valider pour la checklist %s (%s)",
            $newIteration,
            $checklist['Reference_Unique'],
            $checklist['Criticality_Level']
        );

        // Notification pour le consultant
        $notificationModel->createNotification(
            $checklist['Consultant_ID'],
            $baseMessage,
            "index.php?controller=checklist&action=view&id=$clcId",
            'checklist_update'
        );

        // Notifications pour les QG
        $validations = $this->qgValidationModel->getQGValidations($clcId, $newIteration);
        foreach ($validations as $validation) {
            $qgMessage = "[Action Requise] " . $baseMessage;
            $notificationModel->createNotification(
                $validation['validator_id'],
                $qgMessage,
                "index.php?controller=qualityGate&action=review&clc_id=$clcId&iteration=$newIteration&qg_type=".$validation['qg_type'],
                'qg_validation'
            );
        }
    } catch (Exception $e) {
        error_log("Erreur notification stakeholders: " . $e->getMessage());
    }
}
   
    public function getCriticalityMatrixById($matrixId) {
        try {
            $query = "SELECT * FROM criticality_matrix WHERE ID_Row = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$matrixId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de la matrice de criticité: " . $e->getMessage());
        }
    }

public function getCriticalityForChecklist($clcId) {
    // Récupère l'ID de la matrice de criticité associée à la checklist
    $stmt = $this->db->prepare("SELECT Criticality_Matrix_ID FROM clc_master WHERE ID_CLC = ?");
    $stmt->execute([$clcId]);
    $matrixId = $stmt->fetchColumn();

    if (!$matrixId) {
        throw new Exception("Aucune matrice de criticité associée à la checklist $clcId.");
    }

    // Utilise ta méthode existante pour récupérer la criticité
    $matrix = $this->getCriticalityMatrixById($matrixId);
    if (!$matrix || empty($matrix['Criticality_Level'])) {
        throw new Exception("Impossible de récupérer le niveau de criticité pour la checklist $clcId.");
    }

    return $matrix['Criticality_Level'];
}
public function getCriticalityMatrixForChecklist($clcId) {
    try {
        // Get the matrix ID first
        $stmt = $this->db->prepare("SELECT Criticality_Matrix_ID FROM clc_master WHERE ID_CLC = ?");
        $stmt->execute([$clcId]);
        $matrixId = $stmt->fetchColumn();

        if (!$matrixId) {
            throw new Exception("Aucune matrice de criticité associée à la checklist $clcId.");
        }

        // Return the full matrix using existing method
        return $this->getCriticalityMatrixById($matrixId);
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération de la criticité: " . $e->getMessage());
    }
}

    

    public function getQGActionsForChecklist($clcId, $iteration = null) {
        try {
            $query = "SELECT v.*, CONCAT(u.Fname_User, ' ', u.Lname_User) as validator_name 
                    FROM clc_qg_validation v
                    JOIN users u ON v.validator_id = u.ID_User
                    WHERE v.clc_id = ?";
            
            $params = [$clcId];
            
            if ($iteration !== null) {
                $query .= " AND v.iteration = ?";
                $params[] = $iteration;
            }
            
            $query .= " ORDER BY v.iteration, v.qg_type";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des actions QG: " . $e->getMessage());
        }
    }

    public function isIterationApprovedByQG($clcId, $iteration, $qgType) {
        $stmt = $this->db->prepare("
            SELECT status FROM clc_qg_validation 
            WHERE clc_id = :clc_id AND iteration = :iteration AND qg_type = :qg_type
        ");
        $stmt->execute(['clc_id' => $clcId, 'iteration' => $iteration, 'qg_type' => $qgType]);
        return $stmt->fetchColumn() === 'approved';
    }

    public function wasIterationRejected($clcId, $iteration, $qgType) {
        $stmt = $this->db->prepare("
            SELECT status FROM clc_qg_validation 
            WHERE clc_id = :clc_id AND iteration = :iteration AND qg_type = :qg_type
        ");
        $stmt->execute(['clc_id' => $clcId, 'iteration' => $iteration, 'qg_type' => $qgType]);
        return $stmt->fetchColumn() === 'rejected';
    }

public function closeIfAllQGApproved($clcId) {
    // Vérifie s'il reste des validations QG non approuvées
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM clc_qg_validation
        WHERE clc_id = :clcId AND status != 'Approved'
    ");
    $stmt->execute(['clcId' => $clcId]);
    $notApproved = $stmt->fetchColumn();

    if ($notApproved == 0) {
        // Toutes les validations sont approuvées, on clôture la checklist
        $stmt2 = $this->db->prepare("
            UPDATE clc_master SET Status = 'Completed', Updated_At = NOW()
            WHERE ID_CLC = :clcId
        ");
        $stmt2->execute(['clcId' => $clcId]);
    }
}
public function hasPendingQGApproval($clcId, $iteration) {
    try {
        $query = "SELECT COUNT(*) FROM clc_qg_validation 
                 WHERE clc_id = ? AND iteration = ? AND status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clcId, $iteration]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking QG approval: " . $e->getMessage());
        return false;
    }
}


public function createQGValidations($clcId, $iteration) {
    try {
        $checklist = $this->getChecklistById($clcId);
        $matrixId = $checklist['Criticality_Matrix_ID'];
        $criticality = $this->getCriticalityForChecklist($clcId);
        
        // Pour C1, on ne crée que QG1 initialement
        $qgTypes = ($criticality === 'C1') ? ['qg1'] : ['qg1', 'qg2'];
        
        $matrix = $this->getCriticalityMatrixById($matrixId);
        
        foreach ($qgTypes as $qgType) {
            $validatorId = $matrix["Consultant_" . strtoupper($qgType) . "_ID"];
            if ($validatorId) {
                $this->qgValidationModel->createQGAction($clcId, $iteration, $qgType, $validatorId);
            }
        }
        
        return true;
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la création des validations QG : " . $e->getMessage());
    }
}

public function areAllDEItemsOK($clcId, $iteration) {
    try {
        $query = "SELECT
                    ch.item_id,
                    i.Name_Item,
                    ch.new_status,
                    ch.iteration
                  FROM clc_history ch
                  JOIN items i ON ch.item_id = i.ID_Item
                  WHERE ch.clc_id = :clc_id
                  AND i.Item_Type = 'DE'
                  AND ch.iteration = :iteration
                  AND ch.new_status NOT IN ('OK', 'NA')";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['clc_id' => $clcId, 'iteration' => $iteration]);
        $nonOKItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return empty($nonOKItems);
    } catch (PDOException $e) {
        error_log("Erreur dans areAllDEItemsOK: " . $e->getMessage());
        return false;
    }
}

public function getItemType($itemId) {
    $stmt = $this->db->prepare("SELECT Item_Type FROM items WHERE ID_Item = ?");
    $stmt->execute([$itemId]);
    return $stmt->fetchColumn() ?: null;
}


public function getItemNameById($itemId) {
    try {
        $stmt = $this->db->prepare("SELECT Name_Item FROM items WHERE ID_Item = :item_id");
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur dans getItemNameById: " . $e->getMessage());
        return null; // Ou gérer l'erreur comme vous préférez
    }
}

    public function checkAllItemsOKForIteration($clcId, $itemType, $iterationNumber) {
        // Note: $iterationNumber here is 0-indexed as passed from updateChecklistItems
        // but clc_history.iteration is 1-indexed. So, we add 1.
        $historyIteration = $iterationNumber + 1;
        try {
            $query = "SELECT COUNT(*)
                      FROM clc_history ch
                      JOIN items i ON ch.item_id = i.ID_Item
                      WHERE ch.clc_id = :clc_id
                      AND i.Item_Type = :item_type
                      AND ch.iteration = :iteration
                      AND ch.new_status <> 'OK'"; // Check for any non-OK status
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'clc_id' => $clcId,
                'item_type' => $itemType,
                'iteration' => $historyIteration
            ]);
            $countNonOK = $stmt->fetchColumn();
            
            // If there are no items of this type for this iteration in history yet, it's not "all OK"
            $queryTotal = "SELECT COUNT(*) FROM clc_history ch
                           JOIN items i ON ch.item_id = i.ID_Item
                           WHERE ch.clc_id = :clc_id AND i.Item_Type = :item_type AND ch.iteration = :iteration";
            $stmtTotal = $this->db->prepare($queryTotal);
            $stmtTotal->execute([
                'clc_id' => $clcId,
                'item_type' => $itemType,
                'iteration' => $historyIteration
            ]);
            $totalItemsInHistory = $stmtTotal->fetchColumn();

            if ($totalItemsInHistory == 0) {
                return false; // No items recorded for this iteration and type yet.
            }
            
            return $countNonOK == 0;
        } catch (PDOException $e) {
            error_log("Erreur dans checkAllItemsOKForIteration: " . $e->getMessage());
            return false;
        }
    }
    public function checkAllDEItemsOKForIteration($clcId, $iterationNumber) {
        $historyIteration = $iterationNumber + 1; // Conversion index
        
        try {
            // 1. Vérifier d'abord les items DE courants
            $currentQuery = "SELECT COUNT(*) 
                            FROM clc_items ci
                            JOIN items i ON ci.Item_ID = i.ID_Item
                            WHERE ci.CLC_ID = :clc_id
                            AND i.Item_Type = 'DE'
                            AND ci.Status <> 'OK'";
            
            $stmtCurrent = $this->db->prepare($currentQuery);
            $stmtCurrent->execute(['clc_id' => $clcId]);
            $currentNonOK = $stmtCurrent->fetchColumn();

            // Si tous les items DE courants sont OK, retourner true immédiatement
            if ($currentNonOK == 0) {
                return true;
            }

            // 2. Vérifier l'historique des items DE pour l'itération spécifique
            $historyQuery = "SELECT COUNT(*) 
                            FROM clc_history ch
                            JOIN items i ON ch.item_id = i.ID_Item
                            WHERE ch.clc_id = :clc_id
                            AND i.Item_Type = 'DE'
                            AND ch.iteration = :iteration
                            AND ch.new_status <> 'OK'";
            
            $stmtHistory = $this->db->prepare($historyQuery);
            $stmtHistory->execute([
                'clc_id' => $clcId,
                'iteration' => $historyIteration
            ]);
            $historyNonOK = $stmtHistory->fetchColumn();

            return $historyNonOK == 0;

        } catch (PDOException $e) {
            error_log("Erreur dans checkAllDEItemsOKForIteration: " . $e->getMessage());
            return false;
        }
    }
    public function updateChecklistStatus($clcId) {
    try {
        $query = "UPDATE clc_master cm
            JOIN criticality_matrix cmx ON cm.Criticality_Matrix_ID = cmx.ID_Row
            SET 
            cm.Status = CASE 
                WHEN cmx.Criticality_Level = 'C3' AND cm.Iteration_DS >= 1 THEN 'Completed'
                
                WHEN cmx.Criticality_Level = 'C2' AND (
                     cm.Iteration_QG1 >= 3
                     OR EXISTS (
                         SELECT 1 FROM clc_qg_validation
                         WHERE clc_id = cm.ID_CLC AND qg_type = 'qg1' AND (status = 'approved' OR status = 'rejected')
                     )
                ) THEN 'Completed'
                
                WHEN cmx.Criticality_Level = 'C1' AND (
                     (cm.Iteration_QG1 >= 3 AND cm.Iteration_QG2 >= 3)
                     OR EXISTS (
                         SELECT 1 FROM clc_qg_validation
                         WHERE clc_id = cm.ID_CLC AND qg_type = 'qg2' AND (status = 'approved' OR status = 'rejected')
                     )
                ) THEN 'Completed'

                ELSE cm.Status
            END,

            cm.Real_Delivery_Date = CASE 
                WHEN
                    (cmx.Criticality_Level = 'C3' AND cm.Iteration_DS >= 3)
                    OR (cmx.Criticality_Level = 'C2' AND (
                        cm.Iteration_QG1 >= 3
                        OR EXISTS (
                            SELECT 1 FROM clc_qg_validation
                            WHERE clc_id = cm.ID_CLC AND qg_type = 'qg1' AND status = 'approved'
                        )
                    ))
                    OR (cmx.Criticality_Level = 'C1' AND (
                        (cm.Iteration_QG1 >= 3 AND cm.Iteration_QG2 >= 3)
                        OR EXISTS (
                            SELECT 1 FROM clc_qg_validation
                            WHERE clc_id = cm.ID_CLC AND qg_type = 'qg2' AND status = 'approved'
                        )
                    ))
                THEN CURDATE()
                ELSE cm.Real_Delivery_Date
            END
            WHERE cm.ID_CLC = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clcId]);

        return true;

    } catch (PDOException $e) {
        error_log("Erreur dans updateChecklistStatus: " . $e->getMessage());
        return false;
    }

}
}
