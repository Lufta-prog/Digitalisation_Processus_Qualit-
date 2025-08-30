<?php if(isset($_SESSION['user_id'])): ?>
        </div>
    </div>
<?php endif; ?>

<!-- Core JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Essential UI functionality
    $(document).ready(function() {
        // Toggle sidebar
        $('#toggle-sidebar').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
            $('#main-content').toggleClass('expanded');
        });
        
        // User dropdown
        $('#user-dropdown-toggle').on('click', function(e) {
            e.stopPropagation();
            $('#user-dropdown-menu').toggleClass('show');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            var dropdown = $('#user-dropdown-menu');
            var toggle = $('#user-dropdown-toggle');
            
            if (dropdown.length && toggle.length && !toggle.is(e.target) && 
                toggle.has(e.target).length === 0 && !dropdown.is(e.target) && 
                dropdown.has(e.target).length === 0) {
                dropdown.removeClass('show');
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Delete confirmation
        $('.delete-confirm').on('click', function(e) {
            e.preventDefault();
            var name = $(this).data('name');
            var url = $(this).data('href');
            
            if (confirm('Êtes-vous sûr de vouloir supprimer ' + name + ' ?')) {
                window.location.href = url;
            }
        });
        
        // This function will be available but won't automatically run
        // Individual pages can call it if needed
        window.initializeCommonComponents = function() {
            // Initialize Select2 for elements with class 'select2'
            if ($.fn.select2) {
                $('.select2').each(function() {
                    if ($(this).data('select2') === undefined) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Sélectionner...',
                            allowClear: true
                        });
                    }
                });
            }
            
            // Initialize DataTables
            if ($.fn.DataTable) {
                $('table.datatable').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                            },
                            responsive: true,
                            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
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
                        });
                    }
                });
            }
        };
        
        // Customer -> Project dropdown dependency
        if ($('#customer_id').length && $('#project_id').length) {
            $('#customer_id').on('change', function() {
                var customerId = $(this).val();
                var projectSelect = $('#project_id');
                
                projectSelect.prop('disabled', true);
                projectSelect.empty().append('<option value="">Sélectionner un projet</option>');
                
                if (customerId) {
                    $.ajax({
                        url: 'index.php?controller=delivrables&action=getProjectsByCustomer',
                        type: 'POST',
                        data: { customer_id: customerId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.projects && response.projects.length > 0) {
                                $.each(response.projects, function(i, project) {
                                    projectSelect.append($('<option>', {
                                        value: project.ID_Project,
                                        text: project.Name_Project
                                    }));
                                });
                            }
                            
                            projectSelect.prop('disabled', false);
                            
                            if (projectSelect.hasClass('select2') && $.fn.select2) {
                                try {
                                    projectSelect.select2('destroy');
                                    projectSelect.select2({
                                        theme: 'bootstrap-5',
                                        width: '100%',
                                        placeholder: "Sélectionner un projet...",
                                        allowClear: true
                                    });
                                } catch (e) {
                                    console.error('Erreur lors de la réinitialisation de Select2:', e);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erreur AJAX:', error);
                            projectSelect.prop('disabled', false);
                        }
                    });
                } else {
                    projectSelect.prop('disabled', false);
                    
                    if (projectSelect.hasClass('select2') && $.fn.select2) {
                        try {
                            projectSelect.select2('destroy');
                            projectSelect.select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: "Sélectionner un projet...",
                                allowClear: true
                            });
                        } catch (e) {
                            console.error('Erreur lors de la réinitialisation de Select2:', e);
                        }
                    }
                }
            });
        }
    });
</script>

<!-- Custom JS - Load this last -->
<script src="assets/js/scripts.js"></script>
</body>
</html>