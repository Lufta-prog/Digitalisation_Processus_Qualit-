<?php
/**
 * Helper functions for the application
 */

/**
 * HTML escape function to prevent XSS
 * 
 * @param string $string The string to be escaped
 * @return string The escaped string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date for display
 * 
 * @param string $date Date in Y-m-d format
 * @param string $format Output format (default: d/m/Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Calculate the number of days between two dates
 * 
 * @param string $startDate Start date in Y-m-d format
 * @param string $endDate End date in Y-m-d format
 * @return int Number of days
 */
function daysBetween($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days;
}

/**
 * Convert a percentage value to a color code (green to red)
 * 
 * @param float $percentage Percentage value (0-100)
 * @return string CSS color code
 */
function percentageToColor($percentage) {
    // Ensure percentage is between 0 and 100
    $percentage = max(0, min(100, $percentage));
    
    if ($percentage >= 90) {
        return '#28a745'; // Success green
    } elseif ($percentage >= 75) {
        return '#17a2b8'; // Info blue
    } elseif ($percentage >= 60) {
        return '#ffc107'; // Warning yellow
    } else {
        return '#dc3545'; // Danger red
    }
}

/**
 * Get month name from month number
 * 
 * @param int $monthNumber Month number (1-12)
 * @return string Month name
 */
function getMonthName($monthNumber) {
    $months = [
        1 => 'Janvier', 
        2 => 'Février', 
        3 => 'Mars', 
        4 => 'Avril', 
        5 => 'Mai', 
        6 => 'Juin',
        7 => 'Juillet', 
        8 => 'Août', 
        9 => 'Septembre', 
        10 => 'Octobre', 
        11 => 'Novembre', 
        12 => 'Décembre'
    ];
    
    return isset($months[$monthNumber]) ? $months[$monthNumber] : '';
}

/**
 * Get week dates from week number and year
 * 
 * @param int $weekNumber Week number
 * @param int $year Year
 * @return array Start and end dates of the week
 */
function getWeekDates($weekNumber, $year) {
    $dto = new DateTime();
    $dto->setISODate($year, $weekNumber);
    $start = $dto->format('Y-m-d');
    $dto->modify('+6 days');
    $end = $dto->format('Y-m-d');
    
    return [
        'start' => $start,
        'end' => $end
    ];
}

/**
 * Truncate text to a specified length
 * 
 * @param string $text The text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 50, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Get status class for Bootstrap
 * 
 * @param string $status Status value
 * @return string CSS class
 */
function getStatusClass($status) {
    $statusMap = [
        'In Progress' => 'primary',
        'En cours' => 'primary',
        'Completed' => 'success',
        'Terminé' => 'success',
        'Closed' => 'success',
        'Cancelled' => 'danger',
        'Annulé' => 'danger',
        'OK' => 'success',
        'NOK' => 'danger',
        'NA' => 'secondary'
    ];
    
    return isset($statusMap[$status]) ? $statusMap[$status] : 'secondary';
}