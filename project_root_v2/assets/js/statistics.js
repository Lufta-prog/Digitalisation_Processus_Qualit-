$(document).ready(function() {
    // Toggle filters section
    $("#toggle-filters").click(function() {
        $("#filters-card").toggle();
    });
    
    // Load activities based on selected BU
    $("#bu_id").change(function() {
        var buId = $(this).val();
        if (buId) {
            $.ajax({
                url: 'index.php?controller=statistics&action=getActivitiesByBu',
                type: 'GET',
                data: { bu_id: buId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var activities = response.activities;
                        var options = '<option value="">Toutes les activités</option>';
                        
                        $.each(activities, function(index, activity) {
                            options += '<option value="' + activity.ID_Activity + '">' + activity.Name_Activity + '</option>';
                        });
                        
                        $("#activity_id").html(options);
                    }
                },
                error: function() {
                    alert('Erreur lors du chargement des activités');
                }
            });
        } else {
            // Reset activities dropdown if no BU selected
            var options = '<option value="">Toutes les activités</option>';
            $("#activity_id").html(options);
        }
    });
    
    // Date range validation
    $("#statistics-filter-form").submit(function(e) {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        
        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert("La date de début doit être antérieure à la date de fin");
            }
        }
    });
});