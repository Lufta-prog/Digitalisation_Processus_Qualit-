<?php
require_once 'config/constant.php';

// Sécurisation des inputs
$currentController = isset($_GET['controller']) ? htmlspecialchars($_GET['controller'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'dashboard';
$currentAction = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
// Vérification des droits
$userIsAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';

// Détection des sous-menus actifs
$isChecklist = ($currentController === 'checklist');
$isReports = in_array($currentController, ['performance', 'quality', 'planning']);
$isSettings = in_array($currentController, ['business_unit', 'activity', 'function', 'client', 'project', 'templates']);

// Fonction helper pour vérifier l'état actif
function isActive($controller, $action = null) {
    global $currentController, $currentAction;
    return $currentController === $controller && ($action === null || $currentAction === $action);
}

// Configuration des menus
$mainMenu = [
    'dashboard' => ['icon' => 'fa-tachometer-alt', 'label' => 'Tableau de bord'],
    'delivrables' => ['icon' => 'fa-clipboard-list', 'label' => 'Livrables'],
    'qualityGate' => ['icon' => 'fa-clipboard-check', 'label' => 'Quality Gates'],
    'statistics' => ['icon' => 'fa-chart-pie', 'label' => 'Statistiques']
];

$adminMenu = [
    'users' => ['icon' => 'fa-users', 'label' => 'Utilisateurs']
];
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-clipboard-check"></i>
            <span>Quality Control</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Menu principal -->
        <?php foreach ($mainMenu as $controller => $item): ?>
            <a href="index.php?controller=<?= $controller ?>" 
               class="sidebar-menu-item <?= isActive($controller) ? 'active' : '' ?>"
               aria-current="<?= isActive($controller) ? 'page' : 'false' ?>">
                <i class="fas <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>

        <!-- Trames Checklists avec sous-menu -->
        <a href="#checklistSubmenu" 
           class="sidebar-menu-item <?= $isChecklist ? 'active' : '' ?>" 
           data-bs-toggle="collapse" 
           aria-expanded="<?= $isChecklist ? 'true' : 'false' ?>">
            <i class="fas fa-check-square"></i> Trames Checklists
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse sidebar-submenu <?= $isChecklist ? 'show' : '' ?>" id="checklistSubmenu">
            <a href="index.php?controller=checklist&action=index" 
               class="sidebar-submenu-item <?= isActive('checklist', 'index') ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Liste des checklists
            </a>
            <a href="index.php?controller=checklist&action=create" 
               class="sidebar-submenu-item <?= isActive('checklist', 'create') ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Créer une checklist
            </a>
            <a href="index.php?controller=checklist&action=close" 
               class="sidebar-submenu-item <?= isActive('checklist', 'close') ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> Clôturer une checklist
            </a>
            <a href="index.php?controller=checklist&action=delete" 
               class="sidebar-submenu-item <?= isActive('checklist', 'delete') ? 'active' : '' ?>">
                <i class="fas fa-trash"></i> Supprimer une checklist
            </a>
        </div>

        <!-- Menu admin -->
        <?php if ($userIsAdmin): ?>
            <?php foreach ($adminMenu as $controller => $item): ?>
                <a href="index.php?controller=<?= $controller ?>" 
                   class="sidebar-menu-item <?= isActive($controller) ? 'active' : '' ?>">
                    <i class="fas <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Rapports avec sous-menu -->
        <a href="#reportsSubmenu" 
           class="sidebar-menu-item <?= $isReports ? 'active' : '' ?>" 
           data-bs-toggle="collapse" 
           aria-expanded="<?= $isReports ? 'true' : 'false' ?>">
            <i class="fas fa-chart-bar"></i> Rapports
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse sidebar-submenu <?= $isReports ? 'show' : '' ?>" id="reportsSubmenu">
            <a href="index.php?controller=performance" 
               class="sidebar-submenu-item <?= isActive('performance') ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            <a href="index.php?controller=quality" 
               class="sidebar-submenu-item <?= isActive('quality') ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check"></i> Qualité
            </a>
            <a href="index.php?controller=planning" 
               class="sidebar-submenu-item <?= isActive('planning') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> Planning
            </a>
        </div>

        <!-- Paramètres (visible seulement pour les admins) -->
        <?php if ($userIsAdmin): ?>
            <a href="#settingsSubmenu" 
               class="sidebar-menu-item <?= $isSettings ? 'active' : '' ?>" 
               data-bs-toggle="collapse" 
               aria-expanded="<?= $isSettings ? 'true' : 'false' ?>">
                <i class="fas fa-cog"></i> Paramètres
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse sidebar-submenu <?= $isSettings ? 'show' : '' ?>" id="settingsSubmenu">
                <a href="index.php?controller=business_unit" 
                   class="sidebar-submenu-item <?= isActive('business_unit') ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> Business Units
                </a>
                <a href="index.php?controller=activity" 
                   class="sidebar-submenu-item <?= isActive('activity') ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> Activités
                </a>
                <a href="index.php?controller=templates" 
                   class="sidebar-submenu-item <?= isActive('template') ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> Modèles
                </a>
                <a href="index.php?controller=customers" 
                   class="sidebar-submenu-item <?= isActive('customers') ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i> Clients
                </a>
                <a href="index.php?controller=projects" 
                   class="sidebar-submenu-item <?= isActive('project') ? 'active' : '' ?>">
                    <i class="fas fa-folder-open"></i> Projets
                </a>
                <a href="index.php?controller=templates" 
                   class="sidebar-submenu-item <?= isActive('templates') ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> Modèles
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Le CSS devrait être dans un fichier séparé -->
<style>
.sidebar-submenu {
    padding-left: 2rem;
}
.sidebar-submenu-item {
    padding: 0.5rem 1rem;
    display: block;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
}
.sidebar-submenu-item:hover,
.sidebar-submenu-item.active {
    background-color: #e9ecef;
    color: var(--primary-color);
}
</style>