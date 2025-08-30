<?php
/**
 * Script pour réinitialiser toutes les connexions à la base de données
 * À utiliser avec précaution et seulement en cas de problème "too many connections"
 */

// Afficher toutes les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<h1>Reset des connexions à la base de données</h1>';

try {
    // Connexion en tant qu'administrateur pour pouvoir réinitialiser les connexions
    $db = new PDO('mysql:host=localhost', 'root', '');
    
    // Cette requête montre les processus/connexions actives
    $stmt = $db->query("SHOW PROCESSLIST");
    $processes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<h2>Processus actifs avant reset:</h2>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>ID</th><th>User</th><th>Host</th><th>DB</th><th>Command</th><th>Time</th><th>State</th></tr>';
    
    foreach ($processes as $process) {
        echo '<tr>';
        echo '<td>' . $process['Id'] . '</td>';
        echo '<td>' . $process['User'] . '</td>';
        echo '<td>' . $process['Host'] . '</td>';
        echo '<td>' . ($process['db'] ?? 'NULL') . '</td>';
        echo '<td>' . $process['Command'] . '</td>';
        echo '<td>' . $process['Time'] . '</td>';
        echo '<td>' . ($process['State'] ?? 'NULL') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // Comptez uniquement les connexions à la base de données quality_control
    $qualityControlConnections = 0;
    foreach ($processes as $process) {
        if (isset($process['db']) && $process['db'] === 'quality_control') {
            $qualityControlConnections++;
        }
    }
    
    echo '<p>Nombre de connexions à la base de données quality_control: ' . $qualityControlConnections . '</p>';
    
    // Tue toutes les connexions à la base de données quality_control sauf la connexion actuelle
    echo '<h2>Réinitialisation des connexions:</h2>';
    
    if ($qualityControlConnections > 0) {
        foreach ($processes as $process) {
            if (isset($process['db']) && $process['db'] === 'quality_control') {
                try {
                    $db->exec("KILL " . $process['Id']);
                    echo '<p>Connexion ID ' . $process['Id'] . ' terminée.</p>';
                } catch (PDOException $e) {
                    echo '<p>Impossible de terminer la connexion ID ' . $process['Id'] . ': ' . $e->getMessage() . '</p>';
                }
            }
        }
        echo '<p style="color: green;">Toutes les connexions à la base de données quality_control ont été réinitialisées.</p>';
    } else {
        echo '<p>Aucune connexion à la base de données quality_control n\'a été trouvée.</p>';
    }
    
    echo '<p><a href="db_test.php">Tester maintenant la connexion à la base de données</a></p>';
    echo '<p><a href="index.php">Retour à la page d\'accueil</a></p>';
    
} catch (PDOException $e) {
    echo '<p style="color: red; font-weight: bold;">Erreur:</p>';
    echo '<p>' . $e->getMessage() . '</p>';
}