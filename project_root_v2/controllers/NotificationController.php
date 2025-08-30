<?php
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController {
    private $notificationModel;

    public function __construct($db) {
        $this->notificationModel = new NotificationModel($db);
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     */
    private function checkAuthentication(): void {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Utilisateur non authentifié.");
        }
    }

   protected function loadHeaderData() {
        $notificationCount = 0;
        if (isset($_SESSION['user_id'])) {
            $notificationCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
        }
        return [
            'notificationCount' => $notificationCount
        ];
    }

    public function index() {
    try {
        $this->checkAuthentication();

        $userId = $_SESSION['user_id'];
        $filter = $_GET['filter'] ?? 'all';
        $page = $_GET['page'] ?? 1;
        $perPage = 10;

        // Récupérer les notifications avec filtres et pagination
        $notifications = $this->notificationModel->getUserNotifications($userId, $filter === 'unread', $perPage, $page);

        // Calculer la pagination
        $totalNotifications = $this->notificationModel->countUserNotifications($userId, $filter === 'unread');
        $totalPages = ceil($totalNotifications / $perPage);

        $data = [
            'notifications' => $notifications,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
            ],
        ];

        include_once __DIR__ . '/../views/notification/index.php';
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

    /**
     * Marque une notification comme lue.
     */
    public function markAsRead() {
        $this->checkAuthentication();

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = "ID de notification invalide.";
            header("Location: index.php?controller=notification");
            exit;
        }

        $notificationId = (int) $_GET['id'];
        $this->notificationModel->markNotificationAsRead($notificationId);

        $_SESSION['success'] = "Notification marquée comme lue.";
        header("Location: index.php?controller=notification");
        exit;
    }
    
    /**
     * Marque toutes les notifications comme lues.
     */
    public function markAllAsRead() {
        try {
            $this->checkAuthentication();
            $userId = $_SESSION['user_id'];

            // Utiliser le modèle pour marquer toutes les notifications comme lues
            $this->notificationModel->markAllNotificationsAsRead($userId);

            $_SESSION['success'] = "Toutes les notifications ont été marquées comme lues.";
            header("Location: index.php?controller=notification&action=index");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=notification&action=index");
            exit;
        }
    }

    public function getUnreadCount() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            exit;
        }
        
        $count = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
        echo json_encode(['count' => $count]);
        exit;
    }
}
?>