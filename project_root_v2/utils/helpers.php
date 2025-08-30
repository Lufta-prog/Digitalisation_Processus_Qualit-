<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Échappe les données de sortie HTML
 * @param string $str La chaîne à échapper
 * @return string La chaîne échappée
 */
function h($str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Génère un jeton CSRF et le stocke en session
 * @return string Le jeton CSRF
 */
function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si un jeton CSRF est valide
 * @param string $token Le jeton à vérifier
 * @return bool True si le jeton est valide, sinon False
 */
function csrf_verify($token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirige vers une autre URL
 * @param string $url L'URL de redirection
 * @return void
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool True si l'utilisateur est connecté, sinon False
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Formate une date au format français
 * @param string|null $date La date à formater
 * @param bool $with_time Inclure l'heure ou non
 * @return string La date formatée
 */
function format_date(?string $date, bool $with_time = false): string {
    if (!$date) return 'N/A';
    
    $datetime = new DateTime($date);
    $format = $with_time ? 'd/m/Y H:i' : 'd/m/Y';
    return $datetime->format($format);
}

/**
 * Vérifie si une date est dépassée
 * @param string|null $date La date à vérifier
 * @return bool True si la date est dépassée, sinon False
 */
function is_date_overdue(?string $date): bool {
    if (!$date) return false;
    return strtotime($date) < strtotime(date('Y-m-d'));
}

/**
 * Génère un badge de statut basé sur une condition
 * @param string $text Texte du badge
 * @param string $type Type de badge (success, warning, danger, info, primary, secondary)
 * @return string Le HTML du badge
 */
function status_badge(string $text, string $type): string {
    return "<span class=\"badge bg-$type\">$text</span>";
}

/**
 * Détermine le statut d'un livrable
 * @param array $livrable Données du livrable
 * @return array [texte, type de badge]
 */
function get_livrable_status(array $livrable): array {
    if ($livrable['Status_Delivrables'] === 'Cancelled') {
        return ['Annulé', 'danger'];
    }
    
    if ($livrable['Status_Delivrables'] === 'Closed') {
        return ['Terminé', 'success'];
    }
    
    // Si en cours
    if ($livrable['Real_Date']) {
        return ['En traitement', 'info'];
    }
    
    $compareDate = $livrable['Postponed_Date'] ?? $livrable['Original_Expected_Date'];
    
    if (!$compareDate) {
        return ['En cours', 'primary'];
    }
    
    if (is_date_overdue($compareDate)) {
        return ['En retard', 'danger'];
    }
    
    return ['En cours', 'primary'];
}

/**
 * Tronque un texte à une longueur donnée
 * @param string $text Le texte à tronquer
 * @param int $length La longueur maximale
 * @return string Le texte tronqué
 */
function truncate(string $text, int $length = 50): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

// Méthodes helper privées
function determineGlobalRating(int $okCount, int $nokCount, int $naCount): string
{
    if ($nokCount === 0 && $naCount === 0) {
        return 'OK';
    }
    return 'NOK';
}

function getRatingBadge(string $rating): string
{
    $badgeClasses = [
        'OK' => 'bg-success',
        'NOK' => 'bg-danger',
        'NA' => 'bg-warning text-dark'
    ];

    $class = $badgeClasses[$rating] ?? 'bg-secondary';

    return sprintf('<span class="badge %s">%s</span>', $class, $rating);
}

function calculateCompletionPercentage(int $okCount, int $totalItems): float
{
    if ($totalItems === 0) return 0;
    return round(($okCount / $totalItems) * 100, 2);
}

function generateSummaryText(int $ok, int $nok, int $na, int $total): string
{
    return sprintf(
        "%d OK • %d NOK • %d NA | Total: %d items (%d%%)",
        $ok, $nok, $na, $total,
        calculateCompletionPercentage($ok, $total)
    );
}

// Dans utils/helpers.php
function getStatusColor($status) {
    $status = strtoupper($status);
    switch ($status) {
        case 'OK': return 'success';
        case 'NOK': return 'danger';
        case 'NA': return 'warning';
        default: return 'secondary';
    }
}

function formatDate($dateString) {
    if (empty($dateString) || $dateString == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date('d/m/Y H:i', strtotime($dateString));
}

function calculateStatusCounts($items) {
        $statusCounts = [
            'OK' => 0,
            'NOK' => 0,
            'NA' => 0
        ];

        foreach ($items as $item) {
            $status = isset($item['Status']) ? strtoupper($item['Status']) : 'NA';
            if (array_key_exists($status, $statusCounts)) {
                $statusCounts[$status]++;
            }
        }

        return $statusCounts;
    }


// À ajouter dans la classe, ou dans un helper séparé
function calculerPourcentageOk(array $itemsDE): float {
    $okCount = 0;
    $naCount = 0;
    $total = count($itemsDE);

    foreach ($itemsDE as $item) {
        $status = $item['status'] ?? 'NA';
        if ($status === 'OK') {
            $okCount++;
        } elseif ($status === 'NA') {
            $naCount++;
        }
    }

    $validTotal = $total - $naCount;
    if ($validTotal === 0) {
        return 0.0; // éviter la division par zéro
    }

    return round(($okCount / $validTotal) * 100, 2);
}

function getBadgeClassFromPercentage($percentage) {
    if ($percentage == 100) return 'bg-success';
    if ($percentage >= 75) return 'bg-warning';
    if ($percentage < 50) return 'bg-danger';
    return 'bg-danger';
}

function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
/**
 * Valide un commentaire selon les règles métier
 * @param string $comment Le commentaire à valider
 * @param string $status Le statut associé (OK, NOK, NA)
 * @return array ['valid' => bool, 'errors' => string[]]
 */
function validateComment(string $comment, string $status): array
{
    $errors = [];
    $comment = trim($comment);

    // 1. Validation de base
    if (strlen($comment) === 0 && in_array($status, ['NOK', 'NA'])) {
        $errors[] = "Un commentaire est requis pour les statuts NOK/NA";
    }

    // 2. Longueur minimale
    if (strlen($comment) > 0 && strlen($comment) < 10) {
        $errors[] = "Le commentaire doit contenir au moins 10 caractères";
    }

    // 3. Longueur maximale
    if (strlen($comment) > 200) {
        $errors[] = "Le commentaire ne doit pas dépasser 200 caractères";
    }

    // 4. Caractères interdits
    if (preg_match('/[<>{}[\];\'"`]/', $comment)) {
        $errors[] = "Le commentaire contient des caractères spéciaux non autorisés";
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Calcule la progression des items DE pour chaque itération (en excluant les NA)
 * 
 * @param array $items Les items de la checklist
 * @return array Tableau avec la progression par itération
 */
function calculateDEIterationProgress(array $items): array {
    $iterationProgress = [];
    
    // Initialiser les itérations 1 à 3
    for ($i = 1; $i <= 3; $i++) {
        $iterationProgress[$i] = ['OK' => 0, 'total' => 0, 'allOK' => true];
    }
    
    // Compter les statuts pour chaque item DE et itération
    foreach ($items['DE'] ?? [] as $item) {
        foreach ($item['iterations'] as $iteration => $data) {
            if (!isset($iterationProgress[$iteration])) {
                continue;
            }
            
            $status = $data['status'] ?? 'NA';
            
            // Ne pas compter les NA dans le total
            if ($status === 'NA') {
                continue;
            }
            
            $iterationProgress[$iteration]['total']++;
            
            if ($status === 'OK') {
                $iterationProgress[$iteration]['OK']++;
            } else {
                $iterationProgress[$iteration]['allOK'] = false;
            }
        }
    }
    
    return $iterationProgress;
}

/**
 * Calcule la progression des items DS pour chaque itération avec validation QG
 * @param array $items Les items de la checklist
 * @param string $criticality Niveau de criticité (C1, C2, C3)
 * @return array Tableau avec la progression par itération
 */
function calculateDSIterationProgress(array $items, string $criticality): array {
    $iterationProgress = [];
    
    // Initialiser les itérations 1 à 3
    for ($i = 1; $i <= 3; $i++) {
        $iterationProgress[$i] = [
            'consultant_OK' => 0,    // Items OK par le consultant
            'qg1_OK' => 0,          // Items validés QG1
            'qg2_OK' => 0,          // Items validés QG2 (seulement pour C1)
            'total_items' => 0,      // Total items non-NA
            'all_validated' => true  // Tous validés (consultant + QG)
        ];
    }
    
    foreach ($items['DS'] ?? [] as $item) {
        foreach ($item['iterations'] as $iteration => $data) {
            if (!isset($iterationProgress[$iteration])) {
                continue;
            }
            
            $status = $data['status'] ?? 'NA';
            
            // Ignorer les items NA
            if ($status === 'NA') {
                $iterationProgress[$iteration]['all_validated'] = false;
                continue;
            }
            
            $iterationProgress[$iteration]['total_items']++;
            
            // Validation consultant
            if ($status === 'OK') {
                $iterationProgress[$iteration]['consultant_OK']++;
            } else {
                $iterationProgress[$iteration]['all_validated'] = false;
            }
            
            // Validation QG1
            if (isset($data['qg1_status']) && $data['qg1_status'] === 'OK') {
                $iterationProgress[$iteration]['qg1_OK']++;
            } else {
                if ($criticality !== 'C3') { // QG1 obligatoire sauf pour C3
                    $iterationProgress[$iteration]['all_validated'] = false;
                }
            }
            
            // Validation QG2 (seulement pour C1)
            if ($criticality === 'C1') {
                if (isset($data['qg2_status']) && $data['qg2_status'] === 'OK') {
                    $iterationProgress[$iteration]['qg2_OK']++;
                } else {
                    $iterationProgress[$iteration]['all_validated'] = false;
                }
            }
        }
    }
    
    return $iterationProgress;
}

function validateDeliveryDate($expectedDate): void
    {
        if (empty($expectedDate)) {
            throw new Exception("La date de livraison prévue est requise.");
        }
        
        $today = date('Y-m-d');
        $initiationDate = date('Y-m-d'); // Date d'initiation = aujourd'hui
        
        // Vérifier que la date n'est pas dans le passé
        if ($expectedDate < $today) {
            throw new Exception("La date de livraison prévue ne peut pas être dans le passé.");
        }
        
        // Vérifier que la date est supérieure à la date d'initiation
        if ($expectedDate <= $initiationDate) {
            throw new Exception("La date de livraison prévue doit être postérieure à la date d'initiation (" . date('d/m/Y', strtotime($initiationDate)) . ").");
        }
    }
