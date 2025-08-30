<?php
// Afficher toutes les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<h1>Test de connexion à la base de données</h1>';

try {
    // Utiliser le singleton pour la connexion
    require_once 'views/includes/db_connection.php';
    $db = Database::getInstance()->getConnection();
    
    echo '<p style="color: green; font-weight: bold;">Connexion à la base de données réussie!</p>';
    
    // Tester une requête simple
    $stmt = $db->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    
    echo '<p>Nombre d\'utilisateurs dans la base de données: ' . $result['count'] . '</p>';
    
    // Afficher quelques informations sur les utilisateurs
    $stmt = $db->query('SELECT ID_User, Fname_User, Lname_User, Email_User, Password_User FROM users LIMIT 5');
    $users = $stmt->fetchAll();
    
    echo '<h2>Premiers utilisateurs dans la base de données:</h2>';
    echo '<table border="1" cellpadding="10">';
    echo '<tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Mot de passe</th></tr>';
    
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . $user['ID_User'] . '</td>';
        echo '<td>' . $user['Fname_User'] . '</td>';
        echo '<td>' . $user['Lname_User'] . '</td>';
        echo '<td>' . $user['Email_User'] . '</td>';
        echo '<td>' . $user['Password_User'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    echo '<p>Vous pouvez utiliser ces informations pour vous connecter à l\'application.</p>';
    echo '<p><a href="index.php">Retour à la page d\'accueil</a></p>';
    
} catch (PDOException $e) {
    echo '<p style="color: red; font-weight: bold;">Erreur de connexion à la base de données:</p>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<h2>Suggestions:</h2>';
    echo '<ul>';
    echo '<li>Vérifiez que le serveur MySQL est bien démarré</li>';
    echo '<li>Vérifiez que la base de données "quality_control" existe</li>';
    echo '<li>Vérifiez les informations de connexion (nom d\'utilisateur et mot de passe)</li>';
    echo '<li>Vérifiez que l\'utilisateur a les droits d\'accès à la base de données</li>';
    echo '</ul>';
}