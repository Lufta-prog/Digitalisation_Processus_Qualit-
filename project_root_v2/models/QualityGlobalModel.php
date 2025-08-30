<?php
class QualityGlobalModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

public function getFormattedStats()
    {
        $query = "SELECT 
                    u.ID_User,
                    CONCAT(u.Fname_User, ' ', u.Lname_User) AS consultant_name,
                    ch.iteration,
                    COUNT(CASE WHEN ch.new_status = 'NOK' THEN 1 END) AS nok_count,
                    COUNT(CASE WHEN ch.new_status = 'OK' THEN 1 END) AS ok_count,
                    COUNT(*) AS total_items
                  FROM users u
                  JOIN clc_history ch ON ch.changed_by = u.ID_User
                  JOIN items i ON i.ID_Item = ch.item_id AND i.Item_Type = 'DE'
                  JOIN clc_master cm ON cm.ID_CLC = ch.clc_id
                  WHERE u.Deleted_At IS NULL 
                    AND cm.Deleted_At IS NULL
                    AND ch.iteration != 0
                    AND ch.new_status IN ('NOK', 'OK')
                  GROUP BY u.ID_User, consultant_name, ch.iteration
                  ORDER BY u.ID_User, ch.iteration";
        
        $result = $this->db->query($query);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->formatStatsData($rows);
    }

    public function getFilteredStats($filters)
    {
        // Construire la requête avec les filtres
        $whereClauses = ["u.Deleted_At IS NULL", "ch.iteration != 0", "ch.new_status IN ('NOK', 'OK')", "cm.Deleted_At IS NULL"];
        $params = [];
        
        // Filtre par année
        if (!empty($filters['years'])) {
            $years = array_map('intval', $filters['years']);
            $whereClauses[] = "YEAR(ch.change_date) IN (" . implode(',', $years) . ")";
        }
        
        // Filtre par BU
        if (!empty($filters['business_units'])) {
            $whereClauses[] = "cm.BU_ID IN (" . implode(',', array_map('intval', $filters['business_units'])) . ")";
        }
        
        // Filtre par activité
        if (!empty($filters['activities'])) {
            $activityNames = array_map([$this->db, 'quote'], $filters['activities']);
            $whereClauses[] = "a.Name_Activity IN (" . implode(',', $activityNames) . ")";
        }
        
        $query = "SELECT 
                    u.ID_User,
                    CONCAT(u.Fname_User, ' ', u.Lname_User) AS consultant_name,
                    ch.iteration,
                    COUNT(CASE WHEN ch.new_status = 'NOK' THEN 1 END) AS nok_count,
                    COUNT(CASE WHEN ch.new_status = 'OK' THEN 1 END) AS ok_count,
                    COUNT(*) AS total_items
                  FROM users u
                  JOIN clc_history ch ON ch.changed_by = u.ID_User
                  JOIN items i ON i.ID_Item = ch.item_id AND i.Item_Type = 'DE'
                  JOIN clc_master cm ON cm.ID_CLC = ch.clc_id
                  JOIN activity a ON a.ID_Activity = cm.Activity_ID
                  WHERE " . implode(' AND ', $whereClauses) . "
                  GROUP BY u.ID_User, consultant_name, ch.iteration
                  ORDER BY u.ID_User, ch.iteration";
        
        $result = $this->db->query($query);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->formatStatsData($rows);
    }

    private function formatStatsData($rows)
{
    $consultants = [];
    $totals = [
        'iterations' => [],
        'global' => ['nok' => 0, 'ok' => 0, 'total' => 0]
    ];
    
    foreach ($rows as $row) {
        $userId = $row['ID_User'];
        $iteration = (int)$row['iteration'];
        
        if (!isset($consultants[$userId])) {
            $consultants[$userId] = [
                'ID_User' => $userId,
                'consultant_name' => $row['consultant_name'],
                'iterations' => [],
                'total' => ['nok' => 0, 'ok' => 0, 'total' => 0]
            ];
        }
        
        $consultants[$userId]['iterations'][$iteration] = [
            'nok' => (int)$row['nok_count'],
            'ok' => (int)$row['ok_count'],
            'total' => (int)$row['total_items'],
            'percent' => $row['total_items'] > 0 ? round(($row['ok_count'] / $row['total_items']) * 100, 2) : 0
        ];
        
        // Mise à jour des totaux globaux par itération
        if (!isset($totals['iterations'][$iteration])) {
            $totals['iterations'][$iteration] = ['nok' => 0, 'ok' => 0, 'total' => 0];
        }
        
        $totals['iterations'][$iteration]['nok'] += (int)$row['nok_count'];
        $totals['iterations'][$iteration]['ok'] += (int)$row['ok_count'];
        $totals['iterations'][$iteration]['total'] += (int)$row['total_items'];
    }
    
    // Calcul des totaux globaux (uniquement itération 1)
    foreach ($consultants as &$consultant) {
        if (isset($consultant['iterations'][1])) {
            $consultant['total'] = [
                'nok' => $consultant['iterations'][1]['nok'],
                'ok' => $consultant['iterations'][1]['ok'],
                'total' => $consultant['iterations'][1]['total'],
                'percent' => $consultant['iterations'][1]['percent']
            ];
        }
    }
    
    // Totaux globaux (uniquement itération 1)
    if (isset($totals['iterations'][1])) {
        $totals['global'] = [
            'nok' => $totals['iterations'][1]['nok'],
            'ok' => $totals['iterations'][1]['ok'],
            'total' => $totals['iterations'][1]['total'],
            'percent' => $totals['iterations'][1]['total'] > 0 
                ? round(($totals['iterations'][1]['ok'] / $totals['iterations'][1]['total']) * 100, 2) 
                : 0
        ];
    }
    
    // Calcul des pourcentages pour les autres itérations
    foreach ($totals['iterations'] as &$iter) {
        $iter['percent'] = $iter['total'] > 0 ? round(($iter['ok'] / $iter['total']) * 100, 2) : 0;
    }
    
    return [
        'consultants' => array_values($consultants),
        'totals' => $totals
    ];
}
}
?>