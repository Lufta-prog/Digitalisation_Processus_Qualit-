<?php
require_once 'config/constant.php';

// Sécurisation des inputs
$currentController = isset($_GET['controller']) ? htmlspecialchars($_GET['controller'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'dashboard';
$currentAction = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';

// Vérification des droits
$userIsAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';

// Fonction helper pour vérifier l'état actif
function isActive($controller, $action = null) {
    global $currentController, $currentAction;
    return $currentController === $controller && ($action === null || $currentAction === $action);
}

// Détection des sous-menus actifs
$isChecklist = ($currentController === 'checklist');
$isSettings = in_array($currentController, ['activity', 'client', 'project', 'templates', 'users']);
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-clipboard-check"></i>
            <span>Controle Qualité</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Menu principal - toujours visible -->
        <a href="index.php?controller=dashboard" 
           class="sidebar-menu-item <?= isActive('dashboard') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> 
            <span>Tableau de bord</span>
        </a>

       <!-- <a href="index.php?controller=delivrables" 
           class="sidebar-menu-item <?= isActive('delivrables') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> 
            <span>Livrables</span>
        </a> -->
        <a href="index.php?controller=qualityGate" 
           class="sidebar-menu-item <?= isActive('qualityGate') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> 
            <span>Quality_Gate</span>
        </a>
        <a href="index.php?controller=qualityglobal" 
           class="sidebar-menu-item <?= isActive('qualityglobal') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> 
            <span>Qualité Globale</span>
        </a>
        <!-- Menu déroulant Checklists -->
        <div class="sidebar-menu-dropdown">
            <a href="#checklistSubmenu" 
               class="sidebar-menu-item dropdown-toggle <?= $isChecklist ? 'active' : '' ?>" 
               data-bs-toggle="collapse" 
               aria-expanded="<?= $isChecklist ? 'true' : 'false' ?>">
                <i class="fas fa-check-square"></i> 
                <span>Trames Checklists</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu <?= $isChecklist ? 'show' : '' ?>" id="checklistSubmenu">
                <a href="index.php?controller=checklist&action=index" 
                   class="sidebar-submenu-item <?= isActive('checklist', 'index') ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> 
                    <span>Liste des checklists</span>
                </a>
                <a href="index.php?controller=checklist&action=create" 
                   class="sidebar-submenu-item <?= isActive('checklist', 'create') ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i> 
                    <span>Créer une checklist</span>
                </a>
                <a href="index.php?controller=checklist&action=delete" 
                    class="sidebar-submenu-item <?= isActive('checklist', 'delete') ? 'active' : '' ?>">
                        <i class="fas fa-trash"></i> Supprimer une checklist
                    </a>
            </div>
        </div>

        <!-- <a href="index.php?controller=statistics" 
           class="sidebar-menu-item <?= isActive('statistics') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> 
            <span>Statistiques</span>
        </a> -->

        <!-- Menu Admin seulement -->
        <?php if ($userIsAdmin): ?>
            <!-- Menu déroulant Administration -->
            <div class="sidebar-menu-dropdown">
                <a href="#adminSubmenu" 
                   class="sidebar-menu-item dropdown-toggle <?= $isSettings ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" 
                   aria-expanded="<?= $isSettings ? 'true' : 'false' ?>">
                    <i class="fas fa-cog"></i> 
                    <span>Gestion des Données</span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </a>
                <div class="collapse sidebar-submenu <?= $isSettings ? 'show' : '' ?>" id="adminSubmenu">
                    <a href="index.php?controller=activity" 
                       class="sidebar-submenu-item <?= isActive('activity') ? 'active' : '' ?>">
                        <i class="fas fa-tasks"></i> 
                        <span>Activités</span>
                    </a>
                    <a href="index.php?controller=customers" 
                       class="sidebar-submenu-item <?= isActive('customers') ? 'active' : '' ?>">
                        <i class="fas fa-briefcase"></i> 
                        <span>Clients</span>
                    </a>
                    <a href="index.php?controller=projects" 
                       class="sidebar-submenu-item <?= isActive('projects') ? 'active' : '' ?>">
                        <i class="fas fa-folder-open"></i> 
                        <span>Projets</span>
                    </a>
                    <a href="index.php?controller=templates" 
                       class="sidebar-submenu-item <?= isActive('templates') ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i> 
                        <span>Modèles</span>
                    </a>
                    <a href="index.php?controller=users" 
                       class="sidebar-submenu-item <?= isActive('users') ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> 
                        <span>Utilisateurs</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Styles de base */
.sidebar-menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.sidebar-menu-item:hover,
.sidebar-menu-item.active {
    background-color: #f8f9fa;
    border-left-color: var(--primary-color);
    color: var(--primary-color);
}

.sidebar-menu-item i {
    margin-right: 0.75rem;
    width: 1.25rem;
    text-align: center;
}

/* Sous-menus */
.sidebar-submenu {
    padding-left: 2.5rem;
    background-color: #f8f9fa;
}

.sidebar-submenu-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    color: #495057;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.sidebar-submenu-item:hover,
.sidebar-submenu-item.active {
    color: var(--primary-color);
    background-color: #e9ecef;
}

.sidebar-submenu-item i {
    font-size: 0.8rem;
    margin-right: 0.5rem;
}

/* Flèches des menus déroulants */
.dropdown-arrow {
    margin-left: auto;
    transition: transform 0.3s;
}

.dropdown-toggle[aria-expanded="true"] .dropdown-arrow {
    transform: rotate(180deg);
}

/* Structure des menus déroulants */
.sidebar-menu-dropdown {
    margin-bottom: 0.25rem;
}
</style>