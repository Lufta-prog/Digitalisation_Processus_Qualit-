<?php
/**
 * Fonctions d'exportation de données
 */

/**
 * Exporte des données au format CSV
 * @param array $data Les données à exporter
 * @param string $filename Le nom du fichier
 * @return void
 */
function export_to_csv(array $data, string $filename = 'export.csv'): void {
    if (empty($data)) {
        return;
    }
    
    // Définir les en-têtes pour le téléchargement
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Ouvrir le flux de sortie
    $output = fopen('php://output', 'w');
    
    // Ajouter BOM UTF-8 pour une meilleure compatibilité avec Excel
    fputs($output, "\xEF\xBB\xBF");
    
    // Écrire les en-têtes (noms des colonnes)
    fputcsv($output, array_keys($data[0]));
    
    // Écrire les données
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    // Fermer le flux
    fclose($output);
    exit;
}

/**
 * Exporte des données vers un fichier Excel (nécessite la bibliothèque PhpSpreadsheet)
 * Cette fonction est une simulation - dans un environnement réel, 
 * il faudrait installer PhpSpreadsheet via Composer.
 * 
 * @param array $data Les données à exporter
 * @param string $filename Le nom du fichier
 * @return void
 */
function export_to_excel(array $data, string $filename = 'export.xlsx'): void {
    if (empty($data)) {
        return;
    }
    
    // Dans un projet réel, vous incluriez ici le code pour utiliser PhpSpreadsheet
    // Ici, nous utilisons simplement la méthode CSV comme solution de repli
    export_to_csv($data, str_replace('.xlsx', '.csv', $filename));
}

/**
 * Formate un tableau de données pour l'exportation
 * @param array $data Les données brutes
 * @param array $columns Les colonnes à inclure (clé => titre)
 * @return array Les données formatées
 */
function format_export_data(array $data, array $columns): array {
    $formatted = [];
    
    foreach ($data as $row) {
        $formattedRow = [];
        
        foreach ($columns as $key => $title) {
            $formattedRow[$title] = $row[$key] ?? '';
        }
        
        $formatted[] = $formattedRow;
    }
    
    return $formatted;
}