<?php
require_once __DIR__ . '/../models/ChecklistModel.php';
class QGValidationModel {
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
 /**
     * Crée une nouvelle action QG pour une checklist, une itération et un type QG donnés.
     */
     public function createQGAction($clcId, $iteration, $qgType, $userId) {
    try {
        // Vérifier d'abord si l'action existe déjà
        $checkQuery = "SELECT id FROM clc_qg_validation 
                      WHERE clc_id = :clc_id AND iteration = :iteration AND qg_type = :qg_type";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([
            'clc_id' => $clcId, 
            'iteration' => $iteration, 
            'qg_type' => $qgType
        ]);
        
        if ($checkStmt->fetchColumn()) {
            return true; // Action existe déjà
        }

        // Créer la nouvelle action QG
        $query = "INSERT INTO clc_qg_validation 
                (clc_id, iteration, qg_type, validator_id, status, created_at)
                VALUES (:clc_id, :iteration, :qg_type, :validator_id, 'pending', NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'clc_id' => $clcId, 
            'iteration' => $iteration, 
            'qg_type' => $qgType, 
            'validator_id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log("Erreur dans createQGAction: " . $e->getMessage());
        throw new Exception("Erreur lors de la création de l'action QG : " . $e->getMessage());
    }
}

    public function insertQGValidationForDSItems($clcId, $userId) {
        try {
            // 1. Récupérer la matrice de criticité associée à la checklist
            $matrixQuery = "SELECT cm.Consultant_QG1_ID, cm.Consultant_QG2_ID, cm.Criticality_Level 
                            FROM criticality_matrix cm
                            JOIN clc_master clc ON cm.ID_Row = clc.Criticality_Matrix_ID
                            WHERE clc.ID_CLC = ?";
            $matrixStmt = $this->db->prepare($matrixQuery);
            $matrixStmt->execute([$clcId]);
            $matrixData = $matrixStmt->fetch(PDO::FETCH_ASSOC);

            if (!$matrixData) {
                throw new Exception("Aucune matrice de criticité trouvée pour cette checklist.");
            }

            $qg1Id = $matrixData['Consultant_QG1_ID'];
            $qg2Id = $matrixData['Consultant_QG2_ID'];
            $criticality = $matrixData['Criticality_Level'];

            // 2. Récupérer l'itération DS actuelle depuis clc_master
            $iterationQuery = "SELECT Iteration_DS FROM clc_master WHERE ID_CLC = ?";
            $iterationStmt = $this->db->prepare($iterationQuery);
            $iterationStmt->execute([$clcId]);
            $currentIteration = $iterationStmt->fetchColumn();

            // 3. Calculer la prochaine itération (currentIteration est 0-based)
            $nextIteration = $currentIteration + 1;

            // 4. Créer les validations QG pour la prochaine itération
            if ($qg1Id) {
                $this->createQGAction($clcId, $nextIteration, 'qg1', $qg1Id);
            }

            // Pour C1, créer la validation QG2 seulement si QG1 est approuvé
            if ($criticality === 'C1' && $qg2Id) {
                // Vérifier si QG1 est approuvé pour cette itération
                $isQG1Approved = $this->isApprovedByQGType($clcId, $nextIteration, 'qg1');
                if ($isQG1Approved) {
                    $this->createQGAction($clcId, $nextIteration, 'qg2', $qg2Id);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Erreur dans insertQGValidationForDSItems: " . $e->getMessage());
            throw new Exception("Erreur lors de la création des validations QG: " . $e->getMessage());
        }
    }

    /**
     * Vérifie si une action QG est en attente pour une checklist, une itération et un type QG donnés.
     */
    public function isQGActionRequired($clcId, $iteration, $qgType) {
        try {
            $query = "SELECT COUNT(*) FROM clc_qg_validation 
                      WHERE clc_id = ? AND iteration = ? AND qg_type = ? AND status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clcId, $iteration, $qgType]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification de la validation QG : " . $e->getMessage());
        }
    }

    /**
     * Supprime les validations QG existantes pour les itérations futures et réinitialise les validations.
     */
    public function updateQGValidation($clcId, $criticality) {
        try {
            // Supprimer les validations existantes pour les itérations futures
            $query = "DELETE FROM clc_qg_validation WHERE clc_id = ? AND iteration > ? AND status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clcId, $criticality['current_iteration']]);

            // Réinitialiser les validations QG pour les itérations futures
            $this->initializeQGValidation($clcId, $criticality);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour des validations QG : " . $e->getMessage());
        }
    }

    /**
     * Initialise les actions QG pour une checklist en fonction de la criticité.
     */
    public function initializeQGValidation($clcId, $criticality) {
    try {
        if (!is_array($criticality)) {
            throw new Exception("Criticality data must be an array");
        }

        $qgTypes = [];
        if (($criticality['Criticality_Level'] ?? '') === 'C1') {
            $qgTypes = ['qg1', 'qg2'];
        } elseif (($criticality['Criticality_Level'] ?? '') === 'C2') {
            $qgTypes = ['qg1'];
        }

        foreach ($qgTypes as $qgType) {
            for ($iteration = 1; $iteration <= 3; $iteration++) {
                $validatorId = $criticality["Consultant_" . strtoupper($qgType) . "_ID"];
                if ($validatorId) {
                    try {
                        $this->createQGAction($clcId, $iteration, $qgType, $validatorId);
                    } catch (Exception $e) {
                        // Log the error but continue with other iterations/types
                        error_log("Warning: " . $e->getMessage());
                        continue;
                    }
                }
            }
        }
        return true;
    } catch (Exception $e) {
        throw new Exception("Erreur lors de l'initialisation des validations QG : " . $e->getMessage());
    }
}

    /**
     * Récupère les détails d'une validation QG spécifique.
     */
    public function getQGValidationById($validationId) {
        try {
            $query = "SELECT * FROM clc_qg_validation WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$validationId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de la validation QG : " . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les actions QG associées à une checklist, avec une option pour filtrer par itération.
     */
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
            throw new Exception("Erreur lors de la récupération des actions QG : " . $e->getMessage());
        }
    }

    public function getPendingValidation($clcId, $iteration, $qgType, $userId) {
        try {
            $query = "SELECT * FROM clc_qg_validation 
                    WHERE clc_id = ? AND iteration = ? 
                    AND qg_type = ? AND validator_id = ? 
                    ";
                    
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clcId, $iteration, $qgType, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de la validation QG en attente : " . $e->getMessage());
        }
    }

  public function getItemsForValidation($clcId, $iteration) {
    try {
        // Requête révisée pour obtenir les items uniques
        $query = "SELECT 
                    ci.ID_CLC_Item,
                    i.ID_Item,
                    i.Name_Item,
                    ci.Status,
                    ci.QG1_Status,
                    ci.QG2_Status,
                    ci.QG1_Comment,
                    ci.QG2_Comment,
                    ci.Comment
                  FROM clc_items ci
                  JOIN items i ON ci.Item_ID = i.ID_Item
                  WHERE ci.CLC_ID = :clc_id
                  AND i.Item_Type = 'DS'
                  GROUP BY i.ID_Item  -- Garantit l'unicité par ID_Item
                  ORDER BY ci.ID_CLC_Item"; 
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':clc_id', $clcId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getItemsForValidation: " . $e->getMessage());
        return [];
    }
}

public function getPendingApprovals($userId) {
    $query = "SELECT 
                v.id AS validation_id, 
                v.clc_id,
                v.iteration, 
                v.qg_type, 
                v.created_at,
                c.Reference_Unique,
                a.Name_Activity,
                bu.Name_BU,
                v.validator_id,
                v.status
              FROM clc_qg_validation v
              JOIN clc_master c ON v.clc_id = c.ID_CLC
              LEFT JOIN activity a ON c.Activity_ID = a.ID_Activity
              LEFT JOIN business_unit bu ON c.BU_ID = bu.ID_BU
              WHERE  v.validator_id = ?";
    
    $stmt = $this->db->prepare($query);
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function getValidationDetails($validationId) {
    $query = "SELECT v.*, c.Reference_Unique, a.Name_Activity, bu.Name_BU, 
                     cmx.Consultant_ID as consultant_id
              FROM clc_qg_validation v
              JOIN clc_master c ON v.clc_id = c.ID_CLC
              JOIN activity a ON c.Activity_ID = a.ID_Activity
              JOIN business_unit bu ON c.BU_ID = bu.ID_BU
              JOIN criticality_matrix cmx ON c.Criticality_Matrix_ID = cmx.ID_Row
              WHERE v.id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$validationId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function processValidation($validationId, $action, $comment) {
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $query = "UPDATE clc_qg_validation 
              SET status = ?, comment = ?, validated_at = NOW() 
              WHERE id = ?";
    $stmt = $this->db->prepare($query);
    if (!$stmt->execute([$status, $comment, $validationId])) {
        $error = $stmt->errorInfo();
        error_log("Erreur SQL : " . $error[2]);
        throw new Exception("Erreur lors de la mise à jour de la validation QG.");
    }
    return true;
}

public function completeQGAction($validationId, $status, $comment) {
    $query = "UPDATE clc_qg_validation 
              SET status = ?, comment = ?, validated_at = NOW() 
              WHERE id = ?";
    $stmt = $this->db->prepare($query);
    return $stmt->execute([$status, $comment, $validationId]);
}

public function updateItemStatus($clcId, $itemId, $status, $comment, $qgType) {
    try {
        // Déterminer les champs à mettre à jour en fonction du type de QG
        $statusField = ($qgType === 'qg1') ? 'QG1_Status' : 'QG2_Status';
        $commentField = ($qgType === 'qg1') ? 'QG1_Comment' : 'QG2_Comment';
        
        $query = "UPDATE clc_items 
                 SET $statusField = :status, 
                     $commentField = :comment,
                     Modified_At = NOW() 
                 WHERE CLC_ID = :clc_id AND Item_ID = :item_id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':comment' => $comment,
            ':clc_id' => $clcId,
            ':item_id' => $itemId
        ]);
    } catch (PDOException $e) {
        error_log("Erreur dans updateItemStatus - CLC_ID: $clcId, Item_ID: $itemId, QGType: $qgType");
        error_log("Message d'erreur: " . $e->getMessage());
        throw new Exception("Erreur lors de la mise à jour du statut de l'item: " . $e->getMessage());
    }
}

public function logItemHistory($clcId, $itemId, $status, $comment, $iteration, $userId, $qgType = null) {
    try {
        // Récupérer les données actuelles
        $currentDataQuery = "SELECT Status, QG1_Status, QG2_Status, QG1_Comment, QG2_Comment 
                            FROM clc_items 
                            WHERE CLC_ID = ? AND Item_ID = ?";
        $currentStmt = $this->db->prepare($currentDataQuery);
        $currentStmt->execute([$clcId, $itemId]);
        $currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);

        // Initialiser les valeurs pour l'historique
        $oldStatus = $currentData['Status'] ?? 'NA';
        $newStatus = $oldStatus; // Par défaut, on garde l'ancien statut
        $qg1Status = $currentData['QG1_Status'] ?? 'NA';
        $qg2Status = $currentData['QG2_Status'] ?? 'NA';
        $qg1Comment = $currentData['QG1_Comment'] ?? null;
        $qg2Comment = $currentData['QG2_Comment'] ?? null;
        $consultantComment = null;

        // Déterminer quels champs mettre à jour selon le type d'action
        if ($qgType === 'qg1') {
            // Action QG1: on met à jour seulement les champs QG1
            $qg1Status = $status;
            $qg1Comment = $comment;
        } elseif ($qgType === 'qg2') {
            // Action QG2: on met à jour seulement les champs QG2
            $qg2Status = $status;
            $qg2Comment = $comment;
        } else {
            // Action consultant: on met à jour le statut principal et le commentaire consultant
            $newStatus = $status;
            $consultantComment = $comment;
        }

        $query = "INSERT INTO clc_history 
                  (clc_id, item_id, old_status, new_status, 
                   qg1_status, qg2_status, qg1_comment, qg2_comment, 
                   comment, iteration, changed_by, change_date)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $clcId,
            $itemId,
            $oldStatus, // ancien statut consultant
            $newStatus, // nouveau statut consultant (changé seulement si action consultant)
            $qg1Status, // statut QG1 (changé seulement si action QG1)
            $qg2Status, // statut QG2 (changé seulement si action QG2)
            $qg1Comment, // commentaire QG1 (changé seulement si action QG1)
            $qg2Comment, // commentaire QG2 (changé seulement si action QG2)
            $consultantComment, // commentaire consultant (changé seulement si action consultant)
            $iteration,
            $userId
        ]);
    } catch (PDOException $e) {
        error_log("Erreur dans logItemHistory - CLC_ID: $clcId, Item_ID: $itemId");
        error_log("Message d'erreur: " . $e->getMessage());
        throw new Exception("Erreur lors de l'enregistrement de l'historique: " . $e->getMessage());
    }
}

public function getItemComment($clcId, $itemId) {
    try {
        $query = "SELECT Comment 
                  FROM clc_items 
                  WHERE CLC_ID = ? AND Item_ID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clcId, $itemId]);
        return $stmt->fetchColumn(); // Retourne uniquement la colonne "Comment"
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération du commentaire de l'item : " . $e->getMessage());
    }
}

public function isApprovedByQGType($clcId, $iteration, $qgType) {
    $stmt = $this->db->prepare("
        SELECT status 
        FROM clc_qg_validation 
        WHERE clc_id = :clcId AND iteration = :iteration AND qg_type = :qgType
        LIMIT 1
    ");
    $stmt->execute([
        'clcId' => $clcId,
        'iteration' => $iteration,
        'qgType' => $qgType
    ]);
    $status = $stmt->fetchColumn();
    return $status === 'approved';
}
    public function isValidationDoneForQGType($clcId, $iteration, $qgType) {
        // Cas spécial pour QG2 : si QG1 n'a pas approuvé, considérer QG2 comme terminé
        if ($qgType === 'qg2') {
            $qg1Status = $this->getQGStatus($clcId, $iteration, 'qg1');
            if ($qg1Status !== 'approved') {
                return true; // QG2 non requis si QG1 n'a pas approuvé
            }
        }
        
        // Vérification standard
        $stmt = $this->db->prepare("
            SELECT status FROM clc_qg_validation 
            WHERE clc_id = ? AND iteration = ? AND qg_type = ?
            LIMIT 1
        ");
        $stmt->execute([$clcId, $iteration, $qgType]);
        $status = $stmt->fetchColumn();
        
        return ($status === 'approved' || $status === 'rejected');
    }
public function getQGStatus($clcId, $iteration, $qgType) {
    $stmt = $this->db->prepare("
        SELECT status FROM clc_qg_validation 
        WHERE clc_id = ? AND iteration = ? AND qg_type = ?
        LIMIT 1
    ");
    $stmt->execute([$clcId, $iteration, $qgType]);
    return $stmt->fetchColumn() ?? 'Pending';
}


public function approveValidation($clcId, $iteration, $userId,$globalComment) {
    $stmt = $this->db->prepare("
        UPDATE clc_qg_validation
        SET status = 'Approved', validated_by = :userId, validated_at = NOW(), comment = :comment
        WHERE clc_id = :clcId AND iteration = :iteration
    ");
    $stmt->execute([
        'userId' => $userId,
        'clcId' => $clcId,
        'iteration' => $iteration,
        'comment' => $globalComment
    ]);
}


public function rejectValidation($clcId, $iteration, $userId, $globalComment) {
    $stmt = $this->db->prepare("
        UPDATE clc_qg_validation
        SET status = 'Rejected', 
            validated_by = :userId, 
            validated_at = NOW(), 
            comment = :comment
        WHERE clc_id = :clcId AND iteration = :iteration
    ");
    return $stmt->execute([
        'userId' => $userId,
        'clcId' => $clcId,
        'iteration' => $iteration,
        'comment' => $globalComment
    ]);
}

public function hasPendingValidation($clcId, $iteration) {
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM clc_qg_validation 
        WHERE clc_id = ? AND iteration = ? AND status = 'pending'
    ");
    $stmt->execute([$clcId, $iteration]);
    return $stmt->fetchColumn() > 0;
}


public function getQGValidations($clcId, $iteration = null) {
    try {
        $query = "SELECT * FROM clc_qg_validation WHERE clc_id = :clc_id";
        $params = ['clc_id' => $clcId];
        
        if ($iteration !== null) {
            $query .= " AND iteration = :iteration";
            $params['iteration'] = $iteration;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération des validations QG: " . $e->getMessage());
    }
}

public function updateQGValidationStatus($validationId, $status, $comment = null) {
    try {
        $query = "UPDATE clc_qg_validation 
                 SET status = :status, 
                     comment = :comment,
                     updated_at = NOW()
                 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $validationId,
            'status' => $status,
            'comment' => $comment
        ]);
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la mise à jour de la validation QG: " . $e->getMessage());
    }
}

public function isIterationFullyApproved($clcId, $iteration) {
    try {
        $query = "SELECT COUNT(*) FROM clc_qg_validation 
                 WHERE clc_id = :clc_id 
                 AND iteration = :iteration
                 AND status != 'approved'";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['clc_id' => $clcId, 'iteration' => $iteration]);
        return $stmt->fetchColumn() === 0;
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la vérification de l'approbation: " . $e->getMessage());
    }
}

/**
 * Met à jour l'itération QG dans clc_master après un rejet
 * selon le type de QG (qg1 ou qg2)
 */
public function updateChecklistIterationAfterRejection($clcId, $qgType) {
    $column = ($qgType === 'qg1') ? 'Iteration_QG1' : 'Iteration_QG2';
    $query = "UPDATE clc_master 
              SET $column = $column + 1, 
                  Updated_At = NOW() 
              WHERE ID_CLC = ?";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$clcId]);
}
 /**
     * Met à jour le compteur d'itération pour le QG dans clc_master apres une approbation
     * selon le type de QG (qg1 ou qg2)
     */
    public function updateChecklistIterationAfterApproval($clcId, $qgType, $iteration) {
        $column = '';
        switch ($qgType) {
            case 'qg1':
                $column = 'Iteration_QG1';
                break;
            case 'qg2':
                $column = 'Iteration_QG2';
                break;
            default:
                throw new Exception("Type de QG invalide");
        }

        $query = "UPDATE clc_master 
                SET $column = GREATEST($column, ?), 
                    Updated_At = NOW() 
                WHERE ID_CLC = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$iteration, $clcId]);
    }

public function handleRejection($validation) {
    // 1. Marquer la validation comme rejetée
    $this->db->prepare("
        UPDATE clc_qg_validation 
        SET status = 'rejected', 
            validated_at = NOW()
        WHERE clc_id = ? AND iteration = ?
    ")->execute([$validation['clc_id'], $validation['iteration']]);

    // 2. Incrémenter Iteration_DS seulement après rejet
    $this->db->prepare("
        UPDATE clc_master 
        SET Iteration_DS = Iteration_DS + 1,
            Updated_At = NOW()
        WHERE ID_CLC = ?
    ")->execute([$validation['clc_id']]);
}

public function handleApproval($validation) {
    // 1. Marquer la validation comme approuvée
    $this->db->prepare("
        UPDATE clc_qg_validation 
        SET status = 'approved', 
            validated_at = NOW()
        WHERE clc_id = ? AND iteration = ?
    ")->execute([$validation['clc_id'], $validation['iteration']]);

    // 2. Incrémenter Iteration_DS
    $this->db->prepare("
        UPDATE clc_master 
        SET Iteration_DS = Iteration_DS + 1,
            Updated_At = NOW()
        WHERE ID_CLC = ?
    ")->execute([$validation['clc_id']]);
}

    public function isQG1ApprovedForIteration($clcId, $iteration) {
        $stmt = $this->db->prepare("
            SELECT status 
            FROM clc_qg_validation 
            WHERE clc_id = :clcId 
            AND iteration = :iteration 
            AND qg_type = 'qg1'
            LIMIT 1
        ");
        $stmt->execute([
            'clcId' => $clcId,
            'iteration' => $iteration
        ]);
        $status = $stmt->fetchColumn();
        return $status === 'approved';
    }
}