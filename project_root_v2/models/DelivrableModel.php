<?php
class DelivrableModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all delivrables with related information
     */
    public function getAllDelivrables() {
        $query = "
            SELECT pd.*, 
                CONCAT(u1.Fname_User, ' ', u1.Lname_User) as Leader_Name,
                CONCAT(u2.Fname_User, ' ', u2.Lname_User) as Requester_Name,
                tl.Nom_Typologie as Typologie_Name,
                c.Name_Customer as Customer_Name,
                p.Name_Perimeter as Perimeter_Name,
                pr.Name_Project as Project_Name,
                a.Name_Activity as Activity_Name,
                bu.Name_BU as BU_Name,
                d.Nom_Derogation as Derogation_Name,
                ds.Real_Date, ds.Status_Delivrables, dv.FTR_Segula, dv.OTD_Segula
            FROM planning_delivrables pd
            LEFT JOIN users u1 ON pd.Leader_ID = u1.ID_User
            LEFT JOIN users u2 ON pd.Requester_ID = u2.ID_User
            LEFT JOIN typologie_livrables tl ON pd.Typologie_ID = tl.ID_Typologie
            LEFT JOIN customers c ON pd.Customer_ID = c.ID_Customer
            LEFT JOIN perimeters p ON pd.Perimeter_ID = p.ID_Perimeter
            LEFT JOIN project pr ON pd.Project_ID = pr.ID_Project
            LEFT JOIN activity a ON pd.Activity_ID = a.ID_Activity
            LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            LEFT JOIN derogations d ON pd.ID_Derogation = d.ID_Derogation
            LEFT JOIN delivrable_status ds ON pd.ID_Row = ds.Delivrable_ID
            LEFT JOIN delivrable_validation dv ON pd.ID_Row = dv.Delivrable_ID
            ORDER BY pd.ID_Row DESC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get a specific delivrable by ID
     */
    public function getDelivrableById($id) {
        $query = "
            SELECT pd.*, 
                CONCAT(u1.Fname_User, ' ', u1.Lname_User) as Leader_Name,
                CONCAT(u2.Fname_User, ' ', u2.Lname_User) as Requester_Name,
                tl.Nom_Typologie as Typologie_Name,
                c.Name_Customer as Customer_Name,
                p.Name_Perimeter as Perimeter_Name,
                pr.Name_Project as Project_Name,
                a.Name_Activity as Activity_Name,
                bu.Name_BU as BU_Name,
                d.Nom_Derogation as Derogation_Name
            FROM planning_delivrables pd
            LEFT JOIN users u1 ON pd.Leader_ID = u1.ID_User
            LEFT JOIN users u2 ON pd.Requester_ID = u2.ID_User
            LEFT JOIN typologie_livrables tl ON pd.Typologie_ID = tl.ID_Typologie
            LEFT JOIN customers c ON pd.Customer_ID = c.ID_Customer
            LEFT JOIN perimeters p ON pd.Perimeter_ID = p.ID_Perimeter
            LEFT JOIN project pr ON pd.Project_ID = pr.ID_Project
            LEFT JOIN activity a ON pd.Activity_ID = a.ID_Activity
            LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            LEFT JOIN derogations d ON pd.ID_Derogation = d.ID_Derogation
            WHERE pd.ID_Row = :id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    public function getDelivrableStatus($id) {
        $stmt = $this->db->prepare("SELECT * FROM delivrable_status WHERE Delivrable_ID = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDelivrableValidation($id) {
        $stmt = $this->db->prepare("SELECT * FROM delivrable_validation WHERE Delivrable_ID = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all business units
     */
    public function getAllBusinessUnits(): array {
        $query = "SELECT ID_BU, Name_BU FROM business_unit ORDER BY Name_BU";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);    // ← ici
    }

    /**
     * Get all activities
     */
    public function getAllActivities(): array {
        $query = "SELECT ID_Activity, Name_Activity FROM activity ORDER BY Name_Activity";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);    // ← et ici
    }
    

    /**
     * Get all users
     */
    public function getAllUsers() {
        $query = "SELECT ID_User, CONCAT(Fname_User, ' ', Lname_User) as Full_Name FROM users ORDER BY Fname_User";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all typologies
     */
    public function getAllTypologies() {
        $query = "SELECT ID_Typologie, Nom_Typologie FROM typologie_livrables ORDER BY Nom_Typologie";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all customers
     */
    public function getAllCustomers(): array {
        $query = "SELECT ID_Customer, Name_Customer FROM customers ORDER BY Name_Customer";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all perimeters
     */
    public function getAllPerimeters() {
        $query = "SELECT ID_Perimeter, Name_Perimeter FROM perimeters ORDER BY Name_Perimeter";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all projects
     */
    public function getAllProjects(): array {
        $query = "SELECT ID_Project, Name_Project FROM project WHERE Status_Project = 'Active' ORDER BY Name_Project";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all CLCs
     */
    public function getAllCLCs() {
        $query = "SELECT ID_CLC FROM clc_master ORDER BY ID_CLC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all derogations
     */
    public function getAllDerogations() {
        $query = "SELECT ID_Derogation, Nom_Derogation FROM derogations ORDER BY Nom_Derogation";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Create a new delivrable
     */
   public function createDelivrable(array $data): bool
{
    // 1. Insertion dans planning_delivrables
    $sql = "
        INSERT INTO planning_delivrables (
            ID_Topic, Description_Topic, Leader_ID, Requester_ID, Typologie_ID,
            Customer_ID, Activity_ID, Livrable, type_validation, CLC_ID,
            ID_Derogation, Perimeter_ID, Project_ID, Comment
        ) VALUES (
            :id_topic, :description, :leader_id, :requester_id, :typologie_id,
            :customer_id, :activity_id, :livrable, :type_validation, :clc_id,
            :derogation_id, :perimeter_id, :project_id, :comment
        )
    ";

    echo "Requête SQL préparée : <pre>" . htmlspecialchars($sql) . "</pre>";

    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(':id_topic',   $data['ID_Topic'],            PDO::PARAM_INT);
    $stmt->bindValue(':description',$data['Description_Topic'],   PDO::PARAM_STR);
    $stmt->bindValue(':leader_id',  $data['Leader_ID'],           PDO::PARAM_INT);
    $stmt->bindValue(':requester_id',$data['Requester_ID'],       PDO::PARAM_INT);
    $stmt->bindValue(':typologie_id',
        $data['Typologie_ID'] !== null ? $data['Typologie_ID'] : null,
        $data['Typologie_ID'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL
    );
    $stmt->bindValue(':customer_id',$data['Customer_ID'],         PDO::PARAM_INT);
    $stmt->bindValue(':activity_id',$data['Activity_ID'],         PDO::PARAM_INT);
    $stmt->bindValue(':livrable',   $data['Livrable'],            PDO::PARAM_INT);
    $stmt->bindValue(':type_validation',
        $data['type_validation'] !== null ? $data['type_validation'] : null,
        $data['type_validation'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
    );
    $stmt->bindValue(':clc_id',
        $data['CLC_ID'] !== null ? $data['CLC_ID'] : null,
        $data['CLC_ID'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL
    );
    $stmt->bindValue(':derogation_id',
        $data['ID_Derogation'] !== null ? $data['ID_Derogation'] : null,
        $data['ID_Derogation'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL
    );
    $stmt->bindValue(':comment',   $data['Comment'] ?? '',       PDO::PARAM_STR);
    $stmt->bindValue(':perimeter_id',$data['Perimeter_ID'],       PDO::PARAM_INT);
    $stmt->bindValue(':project_id', $data['Project_ID'],          PDO::PARAM_INT);

    echo "<pre>Paramètres planning_delivrables : ";
    print_r($data);
    echo "</pre>";

    $result = $stmt->execute();
    if (!$result) return false;

    $delivrableId = $this->db->lastInsertId();

    // 2. Insertion dans delivrable_status
    $sqlStatus = "
        INSERT INTO delivrable_status (
            Delivrable_ID, Status_Delivrables, Original_Expected_Date, Postponed_Date, Real_Date
        ) VALUES (
            :delivrable_id, :status, :original_date, :postponed_date, :real_date
        )
    ";
    $stmtStatus = $this->db->prepare($sqlStatus);
    $stmtStatus->bindValue(':delivrable_id', $delivrableId, PDO::PARAM_INT);
    $stmtStatus->bindValue(':status', $data['Status_Delivrables'], PDO::PARAM_STR);
    $stmtStatus->bindValue(':original_date',
        $data['Original_Expected_Date'] !== null ? $data['Original_Expected_Date'] : null,
        $data['Original_Expected_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
    );
    $stmtStatus->bindValue(':postponed_date',
        $data['Postponed_Date'] !== null ? $data['Postponed_Date'] : null,
        $data['Postponed_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
    );
    $stmtStatus->bindValue(':real_date',
        $data['Real_Date'] !== null ? $data['Real_Date'] : null,
        $data['Real_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
    );
   

    echo "<pre>Paramètres delivrable_status : ";
    print_r([
        'Delivrable_ID' => $delivrableId,
        'Status_Delivrables' => $data['Status_Delivrables'],
        'Original_Expected_Date' => $data['Original_Expected_Date'],
        'Postponed_Date' => $data['Postponed_Date'],
        'Real_Date' => $data['Real_Date'],
        'Comment' => $data['Comment'],
    ]);
    echo "</pre>";

    $resultStatus = $stmtStatus->execute();
    if (!$resultStatus) return false;

    // 3. Insertion dans delivrable_validation
    $sqlValidation = "
        INSERT INTO delivrable_validation (
            Delivrable_ID, FTR_Segula, OTD_Segula, FTR_Customer, OTD_Customer
        ) VALUES (
            :delivrable_id, :ftr_segula, :otd_segula, :ftr_customer, :otd_customer
        )
    ";
    $stmtValidation = $this->db->prepare($sqlValidation);
    $stmtValidation->bindValue(':delivrable_id', $delivrableId, PDO::PARAM_INT);
    $stmtValidation->bindValue(':ftr_segula', $data['FTR_Segula'], PDO::PARAM_STR);
    $stmtValidation->bindValue(':otd_segula', $data['OTD_Segula'], PDO::PARAM_STR);
    $stmtValidation->bindValue(':ftr_customer', $data['FTR_Customer'], PDO::PARAM_STR);
    $stmtValidation->bindValue(':otd_customer', $data['OTD_Customer'], PDO::PARAM_STR);

    echo "<pre>Paramètres delivrable_validation : ";
    print_r([
        'Delivrable_ID' => $delivrableId,
        'FTR_Segula' => $data['FTR_Segula'],
        'OTD_Segula' => $data['OTD_Segula'],
        'FTR_Customer' => $data['FTR_Customer'],
        'OTD_Customer' => $data['OTD_Customer'],
    ]);
    echo "</pre>";

    $resultValidation = $stmtValidation->execute();
    if (!$resultValidation) return false;

    return true;
}


    
   public function updateDelivrable($id, $data)
{
    // 1. MAJ planning_delivrables (avec Comment)
    $sql = "
        UPDATE planning_delivrables SET
            ID_Topic = :id_topic,
            Description_Topic = :description,
            Leader_ID = :leader_id,
            Requester_ID = :requester_id,
            Typologie_ID = :typologie_id,
            Customer_ID = :customer_id,
            Activity_ID = :activity_id,
            Livrable = :livrable,
            type_validation = :type_validation,
            CLC_ID = :clc_id,
            ID_Derogation = :derogation_id,
            Perimeter_ID = :perimeter_id,
            Project_ID = :project_id,
            Comment = :comment
        WHERE ID_Row = :id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':id_topic', $data['ID_Topic'], PDO::PARAM_INT);
    $stmt->bindValue(':description', $data['Description_Topic'], PDO::PARAM_STR);
    $stmt->bindValue(':leader_id', $data['Leader_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':requester_id', $data['Requester_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':typologie_id', $data['Typologie_ID'] !== null ? $data['Typologie_ID'] : null, $data['Typologie_ID'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':customer_id', $data['Customer_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':activity_id', $data['Activity_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':livrable', isset($data['Livrable']) ? ($data['Livrable'] ? 1 : 0) : 0, PDO::PARAM_INT);
    $stmt->bindValue(':type_validation', $data['type_validation'] !== null ? $data['type_validation'] : null, $data['type_validation'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':clc_id', $data['CLC_ID'] !== null ? $data['CLC_ID'] : null, $data['CLC_ID'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':derogation_id', $data['ID_Derogation'] !== null ? $data['ID_Derogation'] : null, $data['ID_Derogation'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':perimeter_id', $data['Perimeter_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':project_id', $data['Project_ID'], PDO::PARAM_INT);
    $stmt->bindValue(':comment', $data['Comment'] !== null ? $data['Comment'] : null, $data['Comment'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

    if (!$stmt->execute()) return false;

    // 2. MAJ delivrable_status (statut, dates)
    $sqlStatus = "
        UPDATE delivrable_status SET
            Status_Delivrables = :status,
            Original_Expected_Date = :original_date,
            Postponed_Date = :postponed_date,
            Real_Date = :real_date
        WHERE Delivrable_ID = :delivrable_id
    ";
    $stmtStatus = $this->db->prepare($sqlStatus);
    $stmtStatus->bindValue(':status', $data['Status_Delivrables'], PDO::PARAM_STR);
    $stmtStatus->bindValue(':original_date', $data['Original_Expected_Date'] !== null ? $data['Original_Expected_Date'] : null, $data['Original_Expected_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmtStatus->bindValue(':postponed_date', $data['Postponed_Date'] !== null ? $data['Postponed_Date'] : null, $data['Postponed_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmtStatus->bindValue(':real_date', $data['Real_Date'] !== null ? $data['Real_Date'] : null, $data['Real_Date'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmtStatus->bindValue(':delivrable_id', $id, PDO::PARAM_INT);

    if (!$stmtStatus->execute()) return false;

    // 3. MAJ delivrable_validation (indicateurs)
    $valid_values = ['NA', 'OK', 'NOK'];
    $ftr_segula = isset($data['FTR_Segula']) && in_array($data['FTR_Segula'], $valid_values) ? $data['FTR_Segula'] : 'NA';
    $otd_segula = isset($data['OTD_Segula']) && in_array($data['OTD_Segula'], $valid_values) ? $data['OTD_Segula'] : 'NA';
    $ftr_customer = isset($data['FTR_Customer']) && in_array($data['FTR_Customer'], $valid_values) ? $data['FTR_Customer'] : 'NA';
    $otd_customer = isset($data['OTD_Customer']) && in_array($data['OTD_Customer'], $valid_values) ? $data['OTD_Customer'] : 'NA';

    $sqlValidation = "
        UPDATE delivrable_validation SET
            FTR_Segula = :ftr_segula,
            OTD_Segula = :otd_segula,
            FTR_Customer = :ftr_customer,
            OTD_Customer = :otd_customer
        WHERE Delivrable_ID = :delivrable_id
    ";
    $stmtValidation = $this->db->prepare($sqlValidation);
    $stmtValidation->bindValue(':ftr_segula', $ftr_segula, PDO::PARAM_STR);
    $stmtValidation->bindValue(':otd_segula', $otd_segula, PDO::PARAM_STR);
    $stmtValidation->bindValue(':ftr_customer', $ftr_customer, PDO::PARAM_STR);
    $stmtValidation->bindValue(':otd_customer', $otd_customer, PDO::PARAM_STR);
    $stmtValidation->bindValue(':delivrable_id', $id, PDO::PARAM_INT);

    if (!$stmtValidation->execute()) return false;

    return true;
}
        



    /**
     * Delete a delivrable
     */
    public function deleteDelivrable($id) {
        $query = "DELETE FROM planning_delivrables WHERE ID_Row = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Get the next ID_Topic value
     */
    public function getNextIDTopic() {
        $query = "SELECT MAX(ID_Topic) + 1 AS next_id FROM planning_delivrables";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        // Si aucun résultat ou résultat NULL, retourner la valeur par défaut 1000
        return ($result && isset($result['next_id']) && !is_null($result['next_id'])) 
               ? $result['next_id'] 
               : 1000;
    }

    /**
     * Get the last delivrable in the database
     */
    public function getLastDelivrable() {
        $query = "SELECT * FROM planning_delivrables ORDER BY ID_Row DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

/**
 * Récupère les livrables filtrés par ID si spécifié
 * @param string|null $filteredIds Liste d'IDs séparés par des virgules
 * @return array Tableau des livrables filtrés
 */
public function getFilteredDelivrables($filteredIds = null) {
    if (!$filteredIds) {
        // Si aucun filtre n'est spécifié, retourner tous les livrables
        return $this->getAllDelivrables();
    }
    
    // Convertir la chaîne d'IDs en tableau
    $idArray = explode(',', $filteredIds);
    
    // S'assurer que tous les éléments sont des nombres
    $idArray = array_map('intval', $idArray);
    
    if (empty($idArray)) {
        return $this->getAllDelivrables();
    }
    
    // Construire les placeholders pour la requête IN ()
    $placeholders = implode(',', array_fill(0, count($idArray), '?'));
    
    // Requête avec filtre sur les IDs
    $query = "
        SELECT pd.*, 
            CONCAT(u1.Fname_User, ' ', u1.Lname_User) as Leader_Name,
            CONCAT(u2.Fname_User, ' ', u2.Lname_User) as Requester_Name,
            tl.Nom_Typologie as Typologie_Name,
            c.Name_Customer as Customer_Name,
            p.Name_Perimeter as Perimeter_Name,
            pr.Name_Project as Project_Name,
            a.Name_Activity as Activity_Name,
            bu.Name_BU as BU_Name,
            d.Nom_Derogation as Derogation_Name
        FROM planning_delivrables pd
        LEFT JOIN users u1 ON pd.Leader_ID = u1.ID_User
        LEFT JOIN users u2 ON pd.Requester_ID = u2.ID_User
        LEFT JOIN typologie_livrables tl ON pd.Typologie_ID = tl.ID_Typologie
        LEFT JOIN customers c ON pd.Customer_ID = c.ID_Customer
        LEFT JOIN perimeters p ON pd.Perimeter_ID = p.ID_Perimeter
        LEFT JOIN project pr ON pd.Project_ID = pr.ID_Project
        LEFT JOIN activity a ON pd.Activity_ID = a.ID_Activity
        LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
        LEFT JOIN derogations d ON pd.ID_Derogation = d.ID_Derogation
        WHERE pd.ID_Row IN ($placeholders)
        ORDER BY pd.ID_Row DESC
    ";
    
    $stmt = $this->db->prepare($query);
    
    // Binder les IDs
    foreach ($idArray as $index => $id) {
        // Les index de PDO commencent à 1
        $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}
    /**
 * Export des livrables au format Excel sans dépendance à PHPSpreadsheet
 */
public function exportToExcel() {
    try {
        // Vérifier si un filtre est appliqué
        $filteredIds = isset($_GET['filtered_ids']) ? $_GET['filtered_ids'] : null;
        
        // Récupérer les données filtrées si nécessaire
        $livrables = $this->getFilteredDelivrables($filteredIds);
        
        // Créer un fichier CSV que Excel peut ouvrir
        $filename = 'livrables_export_' . date('Y-m-d') . '.xls';
        
        // Définir les en-têtes HTTP pour forcer le téléchargement comme Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Début du document HTML pour Excel
        echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
        echo "<head>";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
        echo "<style>
            table {border-collapse: collapse;}
            td, th {border: 1px solid black; padding: 5px;}
            th {background-color: #4472C4; color: white; font-weight: bold;}
          </style>";
        echo "</head>";
        echo "<body>";
        echo "<table>";
        
        // En-têtes
        echo "<tr>";
        $headers = [
            'ID', 'ID Topic', 'Description', 'Leader', 'Demandeur',
            'Client', 'Projet', 'BU', 'Activité', 'Périmètre',
            'Date Prévue', 'Date Reportée', 'Date Réelle',
            'Statut', 'FTR Segula', 'OTD Segula', 'FTR Client', 'OTD Client',
            'Livrable', 'Type Validation', 'Commentaire'
        ];
        
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        // Données
        foreach ($livrables as $livrable) {
            echo "<tr>";
            
            // ID et informations de base
            echo "<td>" . $livrable['ID_Row'] . "</td>";
            echo "<td>" . $livrable['ID_Topic'] . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Description_Topic']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Leader_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Requester_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Customer_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Project_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['BU_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Activity_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Perimeter_Name']) . "</td>";
            
            // Dates
            echo "<td>" . ($livrable['Original_Expected_Date'] ? date('d/m/Y', strtotime($livrable['Original_Expected_Date'])) : 'N/A') . "</td>";
            echo "<td>" . ($livrable['Postponed_Date'] ? date('d/m/Y', strtotime($livrable['Postponed_Date'])) : 'N/A') . "</td>";
            echo "<td>" . ($livrable['Real_Date'] ? date('d/m/Y', strtotime($livrable['Real_Date'])) : 'N/A') . "</td>";
            
            // Statut et indicateurs
            $statut = $livrable['Status_Delivrables'] == 'In Progress' ? 'En cours' : 
                     ($livrable['Status_Delivrables'] == 'Closed' ? 'Terminé' : 'Annulé');
            echo "<td>" . $statut . "</td>";
            echo "<td>" . $livrable['FTR_Segula'] . "</td>";
            echo "<td>" . $livrable['OTD_Segula'] . "</td>";
            echo "<td>" . $livrable['FTR_Customer'] . "</td>";
            echo "<td>" . $livrable['OTD_Customer'] . "</td>";
            
            // Autres informations
            echo "<td>" . ($livrable['Livrable'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . ($livrable['type_validation'] ?: 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($livrable['Comment'] ?: '') . "</td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
        
        exit;
        
    } catch (\Exception $e) {
        // En cas d'erreur, revenir à l'exportation CSV
        error_log('Erreur lors de l\'export Excel: ' . $e->getMessage());
        $this->exportToCSV();
    }
}

/**
 * Export des livrables au format CSV
 */
public function exportToCSV() {
    try {
        // Vérifier si un filtre est appliqué
        $filteredIds = isset($_GET['filtered_ids']) ? $_GET['filtered_ids'] : null;
        
        // Récupérer les données filtrées si nécessaire
        $livrables = $this->getFilteredDelivrables($filteredIds);
        
        // Préparer le fichier CSV
        $filename = 'livrables_export_' . date('Y-m-d') . '.csv';
        
        // Définir les en-têtes HTTP pour forcer le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Créer un fichier de sortie
        $output = fopen('php://output', 'w');
        
        // Ajouter le BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Écrire les en-têtes
        fputcsv($output, [
            'ID', 'ID Topic', 'Description', 'Leader', 'Demandeur',
            'Client', 'Projet', 'BU', 'Activité', 'Périmètre',
            'Date Prévue', 'Date Reportée', 'Date Réelle',
            'Statut', 'FTR Segula', 'OTD Segula', 'FTR Client', 'OTD Client',
            'Livrable', 'Type Validation', 'Commentaire'
        ], ';');
        
        // Écrire les données
        foreach ($livrables as $livrable) {
            $statut = $livrable['Status_Delivrables'] == 'In Progress' ? 'En cours' : 
                     ($livrable['Status_Delivrables'] == 'Closed' ? 'Terminé' : 'Annulé');
                     
            fputcsv($output, [
                $livrable['ID_Row'],
                $livrable['ID_Topic'],
                $livrable['Description_Topic'],
                $livrable['Leader_Name'],
                $livrable['Requester_Name'],
                $livrable['Customer_Name'],
                $livrable['Project_Name'],
                $livrable['BU_Name'],
                $livrable['Activity_Name'],
                $livrable['Perimeter_Name'],
                $livrable['Original_Expected_Date'] ? date('d/m/Y', strtotime($livrable['Original_Expected_Date'])) : 'N/A',
                $livrable['Postponed_Date'] ? date('d/m/Y', strtotime($livrable['Postponed_Date'])) : 'N/A',
                $livrable['Real_Date'] ? date('d/m/Y', strtotime($livrable['Real_Date'])) : 'N/A',
                $statut,
                $livrable['FTR_Segula'],
                $livrable['OTD_Segula'],
                $livrable['FTR_Customer'],
                $livrable['OTD_Customer'],
                $livrable['Livrable'] ? 'Oui' : 'Non',
                $livrable['type_validation'] ?: 'N/A',
                $livrable['Comment'] ?: ''
            ], ';');
        }
        
        fclose($output);
        exit;
        
    } catch (\Exception $e) {
        // Log l'erreur mais retourne à l'utilisateur
        error_log('Erreur lors de l\'export CSV: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur lors de l\'exportation des données. Veuillez réessayer.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

       /**
         * Recherche des livrables selon des critères
         * @param array $criteria Critères de recherche
         * @return array Résultats de la recherche
         */
    
        public function searchDelivrables($criteria = []) {
            $conditions = [];
            $params = [];
            
            $query = "
                SELECT pd.*, 
                    CONCAT(u1.Fname_User, ' ', u1.Lname_User) as Leader_Name,
                    CONCAT(u2.Fname_User, ' ', u2.Lname_User) as Requester_Name,
                    tl.Nom_Typologie as Typologie_Name,
                    c.Name_Customer as Customer_Name,
                    p.Name_Perimeter as Perimeter_Name,
                    pr.Name_Project as Project_Name,
                    a.Name_Activity as Activity_Name,
                    bu.Name_BU as BU_Name,
                    d.Nom_Derogation as Derogation_Name
                FROM planning_delivrables pd
                LEFT JOIN users u1 ON pd.Leader_ID = u1.ID_User
                LEFT JOIN users u2 ON pd.Requester_ID = u2.ID_User
                LEFT JOIN typologie_livrables tl ON pd.Typologie_ID = tl.ID_Typologie
                LEFT JOIN customers c ON pd.Customer_ID = c.ID_Customer
                LEFT JOIN perimeters p ON pd.Perimeter_ID = p.ID_Perimeter
                LEFT JOIN project pr ON pd.Project_ID = pr.ID_Project
                LEFT JOIN activity a ON pd.Activity_ID = a.ID_Activity
                LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
                LEFT JOIN derogations d ON pd.ID_Derogation = d.ID_Derogation
            ";
            
            // Construction des conditions selon les critères fournis
            if (!empty($criteria['customer_id'])) {
                $conditions[] = "pd.Customer_ID = :customer_id";
                $params[':customer_id'] = $criteria['customer_id'];
            }
            
            if (!empty($criteria['project_id'])) {
                $conditions[] = "pd.Project_ID = :project_id";
                $params[':project_id'] = $criteria['project_id'];
            }
            
            if (!empty($criteria['bu_id'])) {
                $conditions[] = "bu.ID_BU = :bu_id";
                $params[':bu_id'] = $criteria['bu_id'];
            }
            
            if (!empty($criteria['activity_id'])) {
                $conditions[] = "pd.Activity_ID = :activity_id";
                $params[':activity_id'] = $criteria['activity_id'];
            }
            
            if (!empty($criteria['status'])) {
                $conditions[] = "ds.Status_Delivrables = :status";
                $params[':status'] = $criteria['status'];
            }
            
            if (!empty($criteria['ftr'])) {
                $conditions[] = "dv.FTR_Segula = :ftr";
                $params[':ftr'] = $criteria['ftr'];
            }
            
            if (!empty($criteria['otd'])) {
                $conditions[] = "dv.OTD_Segula = :otd";
                $params[':otd'] = $criteria['otd'];
            }
            
            if (isset($criteria['livrable'])) {
                $conditions[] = "pd.Livrable = :livrable";
                $params[':livrable'] = $criteria['livrable'];
            }
            
            if (!empty($criteria['search_term'])) {
                $searchTerm = '%' . $criteria['search_term'] . '%';
                $conditions[] = "(
                    pd.ID_Topic LIKE :search_term OR 
                    pd.Description_Topic LIKE :search_term OR
                    CONCAT(u1.Fname_User, ' ', u1.Lname_User) LIKE :search_term OR
                    CONCAT(u2.Fname_User, ' ', u2.Lname_User) LIKE :search_term OR
                    c.Name_Customer LIKE :search_term OR
                    pr.Name_Project LIKE :search_term OR
                    a.Name_Activity LIKE :search_term OR
                    bu.Name_BU LIKE :search_term
                )";
                $params[':search_term'] = $searchTerm;
            }
            
            // Ajout des conditions WHERE si nécessaire
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            // Ordre de tri
            $query .= " ORDER BY pd.ID_Row DESC";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
    
        /**
         * Obtient des statistiques sur les livrables
         * @return array Statistiques des livrables
         */
       public function getDelivrablesStats() {
        $stats = [];

        // Total des livrables
        $query = "SELECT COUNT(*) as total FROM planning_delivrables";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();

        // Livrables par statut (jointure avec delivrable_status)
        $query = "SELECT ds.Status_Delivrables, COUNT(*) as count 
                FROM planning_delivrables pd
                LEFT JOIN delivrable_status ds ON pd.ID_Row = ds.Delivrable_ID
                GROUP BY ds.Status_Delivrables";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Livrables par FTR (jointure avec delivrable_validation)
        $query = "SELECT dv.FTR_Segula, COUNT(*) as count 
                FROM planning_delivrables pd
                LEFT JOIN delivrable_validation dv ON pd.ID_Row = dv.Delivrable_ID
                GROUP BY dv.FTR_Segula";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['by_ftr'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Livrables par OTD (jointure avec delivrable_validation)
        $query = "SELECT dv.OTD_Segula, COUNT(*) as count 
                FROM planning_delivrables pd
                LEFT JOIN delivrable_validation dv ON pd.ID_Row = dv.Delivrable_ID
                GROUP BY dv.OTD_Segula";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['by_otd'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Livrables par client
        $query = "SELECT c.Name_Customer, COUNT(*) as count 
                FROM planning_delivrables pd
                JOIN customers c ON pd.Customer_ID = c.ID_Customer
                GROUP BY pd.Customer_ID
                ORDER BY count DESC
                LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['by_customer'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Livrables par BU
        $query = "SELECT bu.Name_BU, COUNT(*) as count 
                FROM planning_delivrables pd
                JOIN activity a ON pd.Activity_ID = a.ID_Activity
                JOIN business_unit bu ON a.BU_ID = bu.ID_BU
                GROUP BY bu.ID_BU
                ORDER BY count DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['by_bu'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
    
        /**
         * Get projects filtered by customer
         * @param int $customerId ID du client
         * @return array Projets du client
         */
        public function getProjectsByCustomer($customerId) {
            $query = "SELECT ID_Project, Name_Project 
                     FROM project 
                     WHERE Customer_ID = :customer_id 
                     AND Status_Project = 'Active'
                     ORDER BY Name_Project";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
    }