<?php
class NotificationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère les notifications de l'utilisateur.
     *
     * @param int  $userId
     * @param bool $unreadOnly
     * @param int  $limit
     * @return array
     */
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 50) {
        $sql = "SELECT * FROM user_notifications WHERE user_id = ?";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle notification.
     * Les données sont nettoyées et validées.
     *
     * @param int    $userId
     * @param string $message
     * @param string $actionUrl
     * @param string $type
     * @return bool
     */
    public function createNotification(int $userId, string $message, string $actionUrl, string $type): bool {
        // Nettoyage minimal des données
        $message = trim(filter_var($message, FILTER_SANITIZE_STRING));
        $actionUrl = trim(filter_var($actionUrl, FILTER_SANITIZE_URL));
        $type = trim(filter_var($type, FILTER_SANITIZE_STRING));

        if (empty($message) || empty($actionUrl) || empty($type)) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO user_notifications 
            (user_id, message, action_url, type, is_read, created_at)
            VALUES (:user_id, :message, :action_url, :type, 0, NOW())
        ");

        return $stmt->execute([
            'user_id'    => $userId,
            'message'    => $message,
            'action_url' => $actionUrl,
            'type'       => $type
        ]);
    }

    /**
     * Marque une notification comme lue.
     *
     * @param int      $notificationId
     * @param int|null $userId Optionnel, pour vérifier l'appartenance
     * @return bool
     */
    public function markNotificationAsRead($notificationId, $userId = null): bool {
        // Vous pouvez ici ajouter une vérification si $userId est fourni
        $stmt = $this->db->prepare("UPDATE user_notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Retourne le nombre de notifications non lues pour un utilisateur.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM user_notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }


    public function doesNotificationBelongToUser($notificationId, $userId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM user_notifications 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notificationId, $userId]);
        return $stmt->fetchColumn() > 0;
    }


     public function createChecklistNotification($clcId, $userId, $message, $type = 'info') {
        try {
            $actionUrl = "index.php?controller=checklist&action=view&id=$clcId";
            
            $notificationModel = new NotificationModel($this->db);
            return $notificationModel->createNotification(
                $userId,
                $message,
                $actionUrl,
                $type
            );
        } catch (Exception $e) {
            error_log("Erreur création notification: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Crée une notification dans l'application.
     *
     * @param int    $userId
     * @param string $message
     * @param string $actionUrl
     * @param string $type
     */
    private function createInAppNotification(int $userId, string $message, string $actionUrl, string $type): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_notifications 
            (user_id, message, action_url, type, is_read, created_at)
            VALUES (:user_id, :message, :action_url, :type, 0, NOW())
        ");

        $stmt->execute([
            'user_id'    => $userId,
            'message'    => $message,
            'action_url' => $actionUrl,
            'type'       => $type
        ]);
    }

    public function countUserNotifications(int $userId, bool $unreadOnly = false): int {
        try {
            // Construire la requête SQL
            $query = "SELECT COUNT(*) FROM user_notifications WHERE user_id = ?";
            $params = [$userId];

            // Ajouter une condition pour les notifications non lues
            if ($unreadOnly) {
                $query .= " AND is_read = 0";
            }

            // Préparer et exécuter la requête
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            // Retourner le nombre total de notifications
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des notifications : " . $e->getMessage());
        }
    }
    /**
     * Marque toutes les notifications d'un utilisateur comme lues.
     *
     * @param int $userId
     * @return bool
     */
    public function markAllNotificationsAsRead(int $userId): bool {
        try {
            $query = "UPDATE user_notifications SET is_read = 1 WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour des notifications : " . $e->getMessage());
        }
    }

}
?>