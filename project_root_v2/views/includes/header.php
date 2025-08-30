<?php
require_once 'config/constant.php';
require_once __DIR__ . '/../../utils/helpers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>Système de Gestion de la Qualité</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Select2 via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- DataTables via CDN -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            padding: 0;
            z-index: 100;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            transition: all 0.3s;
        }
        
        .sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width) + 60px);
        }
        
        .sidebar-header {
            padding: 1rem;
            background: var(--primary-gradient);
            color: white;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand i {
            margin-right: 10px;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu-item {
            padding: 0.5rem 1.5rem;
            display: block;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu-item.active:hover {
            background-color: #dfe6f1;
            color: #0d6efd;
        }
        
        .sidebar-menu-item.active {
            background-color: #e9ecef;
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1rem;
            transition: all 0.3s;
        }
        
        .main-content.expanded {
            margin-left: 60px;
        }
        
        /* Topbar */
        .topbar {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown-toggle {
            background: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .user-dropdown-toggle img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.5rem 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
            display: none;
        }
        
        .user-dropdown-menu.show {
            display: block;
        }
        
        .user-dropdown-item {
            padding: 0.5rem 1rem;
            display: block;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .user-dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .user-dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Cards */
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eaeaea;
            padding: 1rem;
        }
        
        .primary-gradient {
            background: var(--primary-gradient);
            color: white;
        }
        
        /* Form controls */
        .form-label.required-field::after {
            content: " *";
            color: red;
        }
        
        /* DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        .collapse {
            transition: height 0.3s ease;
        }
        
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<?php if(isset($_SESSION['user_id'])): ?>
    <!-- Sidebar sera inclus ici -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <button id="toggle-sidebar" class="toggle-sidebar" aria-label="Ouvrir/fermer le menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-dropdown">
                <button class="user-dropdown-toggle" id="user-dropdown-toggle">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=random" alt="User Avatar">
                    <span><?= h($_SESSION['user_name']) ?></span>
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
                <div class="user-dropdown-menu" id="user-dropdown-menu">
                    <a href="#" class="user-dropdown-item">
                        <i class="fas fa-user"></i> Mon profil
                    </a>
                    <a href="index.php?controller=notification" class="user-dropdown-item">
                        <i class="fas fa-bell"></i> Notifications
                        <?php if(isset($notificationCount) && $notificationCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $notificationCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="user-dropdown-item">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="index.php?controller=auth&action=logout" class="user-dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Alerts -->
            <?php include_once 'views/includes/alerts.php'; ?>
<?php endif; ?>