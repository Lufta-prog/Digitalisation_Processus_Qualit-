/**
 * JS for Dashboard functionality
 */

// Initialize Select2 with search capability
function initializeSelect2() {
    $('.select2').each(function() {
        const placeholder = $(this).attr('placeholder') || 'Sélectionner...';
        
        $(this).select2({
            placeholder: placeholder,
            allowClear: true,
            language: {
                noResults: function() {
                    return "Aucun résultat trouvé";
                },
                searching: function() {
                    return "Recherche en cours...";
                }
            },
            width: '100%'
        });
    });
    
    // Add "Select All" option for filter dropdowns
    $('.select-all-option').on('click', function(e) {
        e.preventDefault();
        const targetSelect = $(this).data('target');
        $(`#${targetSelect} option`).prop('selected', true);
        $(`#${targetSelect}`).trigger('change');
    });
}

// Initialize DataTables with export options
function initializeDataTables() {
    $('#monthlyStatisticsTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/French.json'
        },
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 25,
        buttons: [
            {
                extend: 'copy',
                text: 'Copier',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'csv',
                text: 'CSV',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'excel',
                text: 'Excel',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'print',
                text: 'Imprimer',
                className: 'btn btn-sm btn-secondary'
            }
        ]
    });
}

// Handle filter form submission with validation
function setupFilterForm() {
    $('#filterForm').on('submit', function(e) {
        // Optional validation if needed
        
        // Add spinner to submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...');
        submitBtn.prop('disabled', true);
        
        // Form will submit normally
    });
}

// Dynamic chart colors based on performance thresholds
function getColorForPerformance(value) {
    if (value >= 90) {
        return 'rgba(40, 167, 69, 0.8)'; // Green for good
    } else if (value >= 70) {
        return 'rgba(255, 193, 7, 0.8)'; // Yellow for medium
    } else {
        return 'rgba(220, 53, 69, 0.8)'; // Red for poor
    }
}

// Initialize tooltips
function initTooltips() {
    $('[data-toggle="tooltip"]').tooltip();
}

// Document ready function
$(document).ready(function() {
    initializeSelect2();
    initializeDataTables();
    setupFilterForm();
    initTooltips();
    
    // Handle tab change to redraw charts
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        $('.chart-container canvas').each(function() {
            if (typeof $(this).data('chart') !== 'undefined') {
                $(this).data('chart').update();
            }
        });
    });
});