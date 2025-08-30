<?php include_once 'views/includes/header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-plus me-2"></i> Ajouter un Nouvel Utilisateur
    </h1>
    <a href="index.php?controller=users" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
</div>

<!-- Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 primary-gradient">
        <h6 class="m-0 font-weight-bold text-white">Informations de l'Utilisateur</h6>
    </div>
    <div class="card-body">
        <form action="index.php?controller=users&action=store" method="post" id="createUserForm" data-validate="true">
            <div class="row">
                <!-- Informations Personnelles -->
                <div class="col-lg-6">
                    <div class="card mb-4 border-left-primary h-100">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user-circle me-2"></i>Informations Personnelles
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="Fname_User" class="form-label required-field">Prénom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="Fname_User" name="Fname_User" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="Lname_User" class="form-label required-field">Nom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="Lname_User" name="Lname_User" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="Email_User" class="form-label required-field">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="Email_User" name="Email_User" placeholder="email@segulagrp.com" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="Password_User" class="form-label required-field">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="Password_User" name="Password_User" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 8 caractères recommandé</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informations Professionnelles -->
                <div class="col-lg-6">
                    <div class="card mb-4 border-left-success h-100">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="User_Function" class="form-label">Fonction</label>
                                <select class="form-select select2" id="User_Function" name="User_Function">
                                    <option value="">Aucune fonction</option>
                                    <?php foreach ($functions as $function): ?>
                                        <option value="<?= $function['ID_Function'] ?>">
                                            <?= htmlspecialchars($function['Name_Function']) ?> - 
                                            <?= htmlspecialchars($function['Name_Activity']) ?> - 
                                            <?= htmlspecialchars($function['Name_BU']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="User_Level" class="form-label required-field">Niveau Utilisateur</label>
                                <select class="form-select" id="User_Level" name="User_Level" required>
                                    <option value="">Sélectionner un niveau</option>
                                    <option value="Consultant">Consultant</option>
                                    <option value="Pilot">Pilote</option>
                                    <option value="Manager">Manager</option>
                                </select>
                                <div class="form-text">
                                    <ul class="small mb-0 ps-3 mt-1">
                                        <li><strong>Consultant</strong>: Accès aux livrables assignés</li>
                                        <li><strong>Pilote</strong>: Gestion des livrables</li>
                                        <li><strong>Manager</strong>: Accès complet aux fonctionnalités</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="User_Type" class="form-label required-field">Type Utilisateur</label>
                                <select class="form-select" id="User_Type" name="User_Type" required>
                                    <option value="Normal" selected>Normal</option>
                                    <option value="Admin">Administrateur</option>
                                </select>
                                <div class="form-text">
                                    <span class="text-danger">Attention :</span> Les administrateurs ont accès à toutes les fonctionnalités y compris la gestion des utilisateurs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php?controller=users" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('Password_User');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Sélectionner une option',
            allowClear: true
        });
    });
</script>

<?php include_once 'views/includes/footer.php'; ?>