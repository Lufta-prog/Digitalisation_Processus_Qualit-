/**
 * Scripts personnalisés pour le système de gestion de la qualité
 */
// Fonction utilitaire pour afficher les erreurs
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    let feedback = field.nextElementSibling;
    
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    
    // Affiche les erreurs sous forme de liste à puces
    if (message.includes("\n")) {
        const errors = message.split("\n");
        feedback.innerHTML = `<ul>${errors.map(err => `<li>${err}</li>`).join('')}</ul>`;
    } else {
        feedback.textContent = message;
    }
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.remove();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Initialisation des tableaux de données (DataTables)
     */
    const initDataTables = () => {
        const tables = document.querySelectorAll('.datatable');
        
        if (tables.length === 0) return;
        
        tables.forEach(table => {
            // Options de base pour tous les tableaux
            const options = {
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-success',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-primary',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimer',
                        className: 'btn btn-sm btn-secondary',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    }
                ]
            };
            
            // Vérifier si le tableau a des options personnalisées
            const customOptions = table.dataset.options ? JSON.parse(table.dataset.options) : {};
            
            // Fusionner les options de base avec les options personnalisées
            const mergedOptions = {...options, ...customOptions};
            
            // Initialiser DataTable
            $(table).DataTable(mergedOptions);
        });
    };
    
    /**
     * Initialisation de Select2 pour les champs de sélection
     */
    const initSelect2 = () => {
        const selects = document.querySelectorAll('.select2');
        
        if (selects.length === 0) return;
        
        $(selects).each(function() {
            const options = {
                theme: 'bootstrap-5',
                width: '100%',
                language: 'fr'
            };
            
            // Options supplémentaires pour les sélections multiples
            if (this.multiple) {
                options.placeholder = 'Sélectionnez un ou plusieurs éléments';
                options.allowClear = true;
            }
            
            $(this).select2(options);
        });
    };
    
    /**
     * Gestion des formulaires
     */
    const handleForms = () => {
        const forms = document.querySelectorAll('form[data-validate=true]');
        
        if (forms.length === 0) return;
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // Validation des champs obligatoires
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        
                        // Créer un message d'erreur si besoin
                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'Ce champ est obligatoire';
                            field.parentNode.insertBefore(feedback, field.nextSibling);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                        
                        // Validation spécifique pour les dates
                        if (field.type === 'date') {
                            if (!validateDateField(field)) {
                                isValid = false;
                            }
                        }
                    }
                });
                
                // Validation supplémentaire pour les dates non obligatoires mais remplies
                form.querySelectorAll('input[type="date"]:not([required])').forEach(field => {
                    if (field.value.trim() && !validateDateField(field)) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Notification
                    showNotification('Veuillez corriger les erreurs du formulaire', 'danger');
                    
                    // Faire défiler jusqu'au premier champ invalide
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Validation en temps réel
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('change', function() {
                    if (field.hasAttribute('required')) {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                            
                            // Validation spécifique pour les dates
                            if (field.type === 'date') {
                                validateDateField(field);
                            }
                        }
                    }
                });
            });
        });
    };
    
    /**
     * Affiche une notification à l'utilisateur
     * @param {string} message Le message à afficher
     * @param {string} type Le type de notification (success, danger, warning, info)
     */
    const showNotification = (message, type = 'info') => {
        // Créer une div pour la notification
        const notification = document.createElement('div');
        notification.className = `toast align-items-center text-white bg-${type} border-0`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        
        // Structure interne de la notification
        notification.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Ajouter la notification au conteneur
        let container = document.querySelector('.toast-container');
        
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Afficher la notification
        const toast = new bootstrap.Toast(notification, { autohide: true, delay: 5000 });
        toast.show();
        
        // Supprimer la notification après sa disparition
        notification.addEventListener('hidden.bs.toast', function() {
            notification.remove();
        });
    };
    
    /**
     * Gestion des formulaires de filtrage
     */
    const handleFilterForms = () => {
        const forms = document.querySelectorAll('.filter-form');
        
        if (forms.length === 0) return;
        
        forms.forEach(form => {
            // Réinitialisation des filtres
            const resetButton = form.querySelector('.reset-filters');
            
            if (resetButton) {
                resetButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Réinitialiser tous les champs
                    form.querySelectorAll('input, select').forEach(field => {
                        field.value = '';
                    });
                    
                    // Réinitialiser les Select2
                    form.querySelectorAll('.select2').forEach(select => {
                        $(select).val(null).trigger('change');
                    });
                    
                    // Soumettre le formulaire
                    form.submit();
                });
            }
            
            // Soumission automatique lors des changements
            form.querySelectorAll('select.auto-submit').forEach(select => {
                select.addEventListener('change', function() {
                    form.submit();
                });
            });
        });
    };
    
    /**
     * Gestion des suppressions avec confirmation
     */
    const handleDeleteConfirmations = () => {
        const deleteButtons = document.querySelectorAll('.delete-confirm');
        
        if (deleteButtons.length === 0) return;
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const url = this.getAttribute('href');
                const name = this.dataset.name || 'cet élément';
                
                // Créer et afficher la modal de confirmation
                const modal = `
                    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirmation de suppression</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir supprimer ${name} ?</p>
                                    <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <a href="${url}" class="btn btn-danger">Supprimer</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Ajouter la modal au document
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = modal;
                document.body.appendChild(modalContainer);
                
                // Afficher la modal
                const modalElement = document.getElementById('deleteConfirmModal');
                const bsModal = new bootstrap.Modal(modalElement);
                bsModal.show();
                
                // Supprimer la modal après sa fermeture
                modalElement.addEventListener('hidden.bs.modal', function() {
                    modalElement.remove();
                });
            });
        });
    };
    
    /**
     * Initialisation des tooltips et popovers
     */
    const initTooltipsAndPopovers = () => {
        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipTriggerList.length > 0) {
            [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
        
        // Popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    };

   // Ajoutez ces fonctions avant l'écouteur DOMContentLoaded

/**
 * Valide un commentaire selon des règles strictes
 */
function validateComment(comment, status) {
    const trimmedComment = comment.trim();
    const errors = [];
    
    // Règles universelles
    if (trimmedComment.length > 200) {
        errors.push("Le commentaire ne doit pas dépasser 200 caractères");
    }
    if (/[<>{}[\];'"`]/.test(trimmedComment)) {
        errors.push("Caractères spéciaux non autorisés");
    }

    // Règles conditionnelles (sans mots-clés imposés)
    if (status !== 'OK') {
        if (trimmedComment.length < 10) {
            errors.push("Le commentaire doit contenir au moins 10 caractères");
        }

        if (status === 'NOK' && trimmedComment.length < 10) {
            errors.push("Pour le statut NOK, veuillez expliquer le problème");
        }

        if (status === 'NA' && trimmedComment.length < 10) {
            errors.push("Pour le statut NA, veuillez expliquer la raison");
        }
    }

    return {
        valid: errors.length === 0,
        message: errors.join("\n") // Combine les messages avec sauts de ligne
    };
}

/**
 * Gère le changement de statut
 */
function handleStatusChange(select) {
    const row = select.closest('tr');
    if (!row) return;

    const commentField = row.querySelector('.comments-field');
    if (!commentField) return;

    // Réinitialisation complète
    clearFieldError(commentField);
    
    // Réinitialiser l'état modifié lors du changement de statut
    commentField.dataset.modified = 'false';
    
    // Mise à jour des contraintes
    if (select.value === 'OK') {
        commentField.required = false;
        commentField.placeholder = "Commentaire optionnel";
    } else {
        commentField.required = true;
        commentField.placeholder = `Commentaire obligatoire (${
            select.value === 'NOK' ? 'Décrivez le problème' : 'Expliquez la non-applicabilité'
        })`;
    }

    // Ne pas valider immédiatement lors du changement de statut
    // La validation se fera lors de la modification du champ
}

 /**
 * Valide un champ commentaire
 */
function validateCommentField(element) {
    const row = element.closest('tr');
    const select = row.querySelector('.item-status');
    const textarea = row.querySelector('.comments-field');
    if (!select || !textarea) return true;

    clearFieldError(textarea);
    
    const status = select.value;
    const comment = textarea.value.trim();
    
    // Ne valider que si le champ a été modifié ou si c'est une soumission
    if (textarea.dataset.modified === 'true' || document.activeElement === document.getElementById('submitButton')) {
        const validation = validateComment(comment, status);
        
        if (!validation.valid) {
            showFieldError(textarea, validation.message);
            return false;
        }
    }
    return true;
}

// Fonction pour valider la date côté client
function validateDate(dateString) {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset time to compare dates only
    
    const selectedDate = new Date(dateString);
    selectedDate.setHours(0, 0, 0, 0);
    
    return selectedDate > today;
}

// Fonction pour mettre à jour l'attribut min dynamiquement
function updateMinDate() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    $('#expected_date').attr('min', minDate);
}

// Fonction pour valider la date dans les formulaires
function validateDateField(dateField) {
    clearFieldError(dateField);
    
    if (!dateField.value.trim()) {
        if (dateField.required) {
            showFieldError(dateField, 'Ce champ est obligatoire');
            return false;
        }
        return true;
    }
    
    if (!validateDate(dateField.value)) {
        showFieldError(dateField, 'La date doit être postérieure à aujourd\'hui');
        return false;
    }
    
    return true;
}
// Ajoutez cet événement pour suivre les modifications
document.querySelectorAll('.comments-field').forEach(textarea => {
    textarea.dataset.modified = 'false';
    textarea.addEventListener('input', function() {
        this.dataset.modified = 'true';
        validateCommentField(this);
    });
});

// Modifiez la fonction initChecklistPage comme suit :
function initChecklistPage(options = {}) {
    const config = {
        formId: options.formId || 'checklistForm',
        statusClass: options.statusClass || 'item-status',
        commentClass: options.commentClass || 'comments-field'
    };

    // Initialisation des compteurs
    document.querySelectorAll(`.${config.commentClass}`).forEach(textarea => {
        textarea.dataset.modified = 'false';
        textarea.addEventListener('input', function() {
            this.dataset.modified = 'true';
            validateCommentField(this);
        });
    });
    

    // Gestion des statuts
    document.querySelectorAll(`.${config.statusClass}`).forEach(select => {
        handleStatusChange(select);
        select.addEventListener('change', function() {
            handleStatusChange(this);
        });
    });

    // Validation en temps réel
    document.querySelectorAll(`.${config.commentClass}`).forEach(textarea => {
        textarea.addEventListener('input', function() {
            validateCommentField(this);
        });
        textarea.addEventListener('blur', function() {
            validateCommentField(this);
        });
    });

    // Validation à la soumission
    const form = document.getElementById(config.formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const errorMessages = [];
            
            this.querySelectorAll(`.${config.statusClass}`).forEach(select => {
                const validation = validateCommentField(select);
                if (!validation) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Veuillez corriger les erreurs avant soumission', 'danger');
                
                const firstError = this.querySelector('.is-invalid');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
    /**
     * Gestion des champs de date
     */
    const handleDateFields = () => {
        const dateFields = document.querySelectorAll('input[type="date"][min]');
        
        if (dateFields.length === 0) return;
        
        dateFields.forEach(field => {
            // Validation lors du changement
            field.addEventListener('change', function() {
                validateDateField(this);
            });
            
            // Validation lors de la perte de focus
            field.addEventListener('blur', function() {
                validateDateField(this);
            });
        });
        
        // Mettre à jour les dates minimales quotidiennement
        setInterval(updateMinDate, 86400000); // Mise à jour quotidienne
    };
}
    // Initialiser tous les composants
    initDataTables();
    initSelect2();
    handleForms();
    handleFilterForms();
    handleDeleteConfirmations();
    initTooltipsAndPopovers();
    initChecklistPage();
    handleDateFields(); // ← AJOUTEZ CETTE LIGNE
    
    // Mettre à jour la date minimale au chargement
    updateMinDate();

    // Vérification de l'état des champs des statuts après le chargement
    const statusFields = document.querySelectorAll('.item-status');
    statusFields.forEach(field => {
        console.log(`[DEBUG] Champ ID: ${field.name}, Disabled: ${field.disabled}`);
    });
});