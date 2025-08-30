<?php
/**
 * Dashboard View
 * Main dashboard with filters and charts
 */

 $pageTitle = 'Liste des Livrables';
 if (session_status() === PHP_SESSION_NONE) session_start();
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
     header('Location: index.php?controller=auth&action=login');
     exit;
 }
 include_once 'views/includes/header.php';
?>


<style>
    .stats-card {
        transition: transform 0.3s;
        border-radius: 10px;
        overflow: hidden;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .chart-container {
        min-height: 400px;
        position: relative;
    }
    .select2-container {
        width: 100% !important;
    }
    .period-selector .btn {
        border-radius: 20px;
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
    .export-buttons {
        margin-bottom: 15px;
    }
    .target-line {
        border-top: 2px dashed #dc3545;
        position: absolute;
        width: 100%;
        z-index: 10;
    }
    .donut-chart-container {
        height: 150px;
        position: relative;
    }
    .donut-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .hidden-download-link {
        display: none;
    }
</style>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">Quality Control Dashboard</h1>
            
            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form id="dashboard-filters" method="GET" class="row g-3">
                        <!-- Keep the original action parameter if it exists -->
                        <?php if(isset($_GET['action'])): ?>
                        <input type="hidden" name="action" value="<?= htmlspecialchars($_GET['action']) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="period" value="<?= isset($period) ? $period : 'month' ?>" id="period-input">
                        
                        <div class="col-md-3">
                            <label for="bu_id" class="form-label">Business Unit</label>
                            <select class="form-select select2" id="bu_id" name="bu_id">
                                <option value="">All Business Units</option>
                                <?php foreach ($businessUnits as $bu): ?>
                                    <option value="<?= $bu['ID_BU'] ?>" <?= isset($businessUnitId) && $businessUnitId == $bu['ID_BU'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($bu['Name_BU']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select class="form-select select2" id="project_id" name="project_id">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['ID_Project'] ?>" <?= isset($projectId) && $projectId == $project['ID_Project'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project['Name_Project']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="activity_id" class="form-label">Activity</label>
                            <select class="form-select select2" id="activity_id" name="activity_id">
                                <option value="">All Activities</option>
                                <?php foreach ($activities as $activity): ?>
                                    <option value="<?= $activity['ID_Activity'] ?>" <?= isset($activityId) && $activityId == $activity['ID_Activity'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($activity['Name_Activity']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select select2" id="customer_id" name="customer_id">
                                <option value="">All Customers</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['ID_Customer'] ?>" <?= isset($customerId) && $customerId == $customer['ID_Customer'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($customer['Name_Customer']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Active Projects</h6>
                            <h2 class="mb-0"><?= $activeProjectsCount ?></h2>
                        </div>
                        <i class="fas fa-project-diagram fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Consultants</h6>
                            <h2 class="mb-0"><?= $consultantsCount ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total Deliverables</h6>
                            <h2 class="mb-0"><?= $deliverablesCount ?></h2>
                        </div>
                        <i class="fas fa-file-alt fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Period Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="period-selector d-flex justify-content-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-<?= $period == 'week' ? 'primary' : 'outline-primary' ?> period-btn" data-period="week">Weekly</button>
                            <button type="button" class="btn btn-<?= $period == 'month' ? 'primary' : 'outline-primary' ?> period-btn" data-period="month">Monthly</button>
                            <button type="button" class="btn btn-<?= $period == 'year' ? 'primary' : 'outline-primary' ?> period-btn" data-period="year">Yearly</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">FTR Segula</h6>
                    <div class="donut-chart-container">
                        <div id="ftrSegulaDonut"></div>
                        <div class="donut-label">
                            <h3 class="mb-0"><?= $performanceSummary['ftr_segula_percent'] ?>%</h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-success"><?= $performanceSummary['ftr_segula_ok'] ?> OK</small> / 
                        <small class="text-danger"><?= $performanceSummary['ftr_segula_nok'] ?> NOK</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">FTR Customer</h6>
                    <div class="donut-chart-container">
                        <div id="ftrCustomerDonut"></div>
                        <div class="donut-label">
                            <h3 class="mb-0"><?= $performanceSummary['ftr_customer_percent'] ?>%</h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-success"><?= $performanceSummary['ftr_customer_ok'] ?> OK</small> / 
                        <small class="text-danger"><?= $performanceSummary['ftr_customer_nok'] ?> NOK</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">OTD Segula</h6>
                    <div class="donut-chart-container">
                        <div id="otdSegulaDonut"></div>
                        <div class="donut-label">
                            <h3 class="mb-0"><?= $performanceSummary['otd_segula_percent'] ?>%</h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-success"><?= $performanceSummary['otd_segula_ok'] ?> OK</small> / 
                        <small class="text-danger"><?= $performanceSummary['otd_segula_nok'] ?> NOK</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">OTD Customer</h6>
                    <div class="donut-chart-container">
                        <div id="otdCustomerDonut"></div>
                        <div class="donut-label">
                            <h3 class="mb-0"><?= $performanceSummary['otd_customer_percent'] ?>%</h3>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-success"><?= $performanceSummary['otd_customer_ok'] ?> OK</small> / 
                        <small class="text-danger"><?= $performanceSummary['otd_customer_nok'] ?> NOK</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- FTR Segula Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>FTR Segula Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" id="ftrSegulaChart"></div>
                </div>
            </div>
        </div>
        
        <!-- FTR Customer Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>FTR Customer Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" id="ftrCustomerChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- OTD Segula Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>OTD Segula Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" id="otdSegulaChart"></div>
                </div>
            </div>
        </div>
        
        <!-- OTD Customer Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>OTD Customer Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" id="otdCustomerChart"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Deliverables Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Deliverables</h5>
                    <div class="export-buttons">
                        <button class="btn btn-sm btn-outline-success" id="exportExcel">
                            <i class="fas fa-file-excel me-1"></i> Export to Excel
                        </button>
                        <button class="btn btn-sm btn-outline-primary" id="exportCSV">
                            <i class="fas fa-file-csv me-1"></i> Export to CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="deliverablesTable">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Project</th>
                                    <th>Customer</th>
                                    <th>Leader</th>
                                    <th>Delivery Date</th>
                                    <th>FTR Segula</th>
                                    <th>OTD Segula</th>
                                    <th>FTR Customer</th>
                                    <th>OTD Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($recentDeliverables) && count($recentDeliverables) > 0): ?>
                                    <?php foreach ($recentDeliverables as $deliverable): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($deliverable['Description_Topic']) ?></td>
                                            <td><?= htmlspecialchars($deliverable['Name_Project']) ?></td>
                                            <td><?= htmlspecialchars($deliverable['Name_Customer']) ?></td>
                                            <td><?= htmlspecialchars($deliverable['Leader_Name']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($deliverable['Real_Date'])) ?></td>
                                            <td>
                                                <?php if ($deliverable['FTR_Segula'] === 'OK'): ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php elseif ($deliverable['FTR_Segula'] === 'NOK'): ?>
                                                    <span class="badge bg-danger">NOK</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">NA</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($deliverable['OTD_Segula'] === 'OK'): ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php elseif ($deliverable['OTD_Segula'] === 'NOK'): ?>
                                                    <span class="badge bg-danger">NOK</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">NA</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($deliverable['FTR_Customer'] === 'OK'): ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php elseif ($deliverable['FTR_Customer'] === 'NOK'): ?>
                                                    <span class="badge bg-danger">NOK</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">NA</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($deliverable['OTD_Customer'] === 'OK'): ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php elseif ($deliverable['OTD_Customer'] === 'NOK'): ?>
                                                    <span class="badge bg-danger">NOK</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">NA</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No recent deliverables found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="deliverablesPagination">
                                <!-- Pagination will be generated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden download link for CSV export -->
    <a id="downloadLink" class="hidden-download-link"></a>
</div>

<!-- jQuery, Bootstrap, and Plotly -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.plot.ly/plotly-2.20.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true
        });
        
        // Setup charts
        setupCharts();
        
        // Setup period buttons
        setupPeriodButtons();
        
        // Setup pagination
        setupPagination();
        
        // Setup export buttons
        setupExportButtons();
    });
    
    // Setup period buttons
    function setupPeriodButtons() {
        $('.period-btn').on('click', function() {
            const period = $(this).data('period');
            $('#period-input').val(period);
            $('#dashboard-filters').submit();
        });
    }

    // Setup all charts
    function setupCharts() {
        // Make sure chartData exists
        const labels = <?= isset($chartData) && isset($chartData['labels']) ? json_encode($chartData['labels']) : '[]' ?>;
        const ftrSegulaData = <?= isset($chartData) && isset($chartData['ftrSegula']) ? json_encode($chartData['ftrSegula']) : '[]' ?>;
        const ftrCustomerData = <?= isset($chartData) && isset($chartData['ftrCustomer']) ? json_encode($chartData['ftrCustomer']) : '[]' ?>;
        const otdSegulaData = <?= isset($chartData) && isset($chartData['otdSegula']) ? json_encode($chartData['otdSegula']) : '[]' ?>;
        const otdCustomerData = <?= isset($chartData) && isset($chartData['otdCustomer']) ? json_encode($chartData['otdCustomer']) : '[]' ?>;
        
        // Blue theme colors
        const blueColor = 'rgb(54, 162, 235)';
        const lightBlueColor = 'rgb(75, 192, 192)';
        const darkBlueColor = 'rgb(25, 118, 210)';
        const royalBlueColor = 'rgb(65, 105, 225)';
        
        // Summary donut charts using Plotly
        setupDonutChart('ftrSegulaDonut', <?= isset($performanceSummary) ? $performanceSummary['ftr_segula_percent'] : 0 ?>, blueColor);
        setupDonutChart('ftrCustomerDonut', <?= isset($performanceSummary) ? $performanceSummary['ftr_customer_percent'] : 0 ?>, lightBlueColor);
        setupDonutChart('otdSegulaDonut', <?= isset($performanceSummary) ? $performanceSummary['otd_segula_percent'] : 0 ?>, darkBlueColor);
        setupDonutChart('otdCustomerDonut', <?= isset($performanceSummary) ? $performanceSummary['otd_customer_percent'] : 0 ?>, royalBlueColor);
        
        // Performance bar charts using Plotly
        setupBarChart('ftrSegulaChart', labels, ftrSegulaData, 'FTR Segula (%)', blueColor);
        setupBarChart('ftrCustomerChart', labels, ftrCustomerData, 'FTR Customer (%)', lightBlueColor);
        setupBarChart('otdSegulaChart', labels, otdSegulaData, 'OTD Segula (%)', darkBlueColor);
        setupBarChart('otdCustomerChart', labels, otdCustomerData, 'OTD Customer (%)', royalBlueColor);
    }
    
    // Setup donut chart using Plotly
    function setupDonutChart(elementId, percentage, color) {
        const data = [{
            values: [percentage, 100 - percentage],
            labels: ['Complete', 'Remaining'],
            type: 'pie',
            hole: 0.7,
            marker: {
                colors: [color, 'rgb(220, 220, 220)']
            },
            showlegend: false,
            hoverinfo: 'none',
            textinfo: 'none'
        }];
        
        const layout = {
            height: 150,
            width: 150,
            margin: {
                l: 0,
                r: 0,
                t: 0,
                b: 0
            },
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0)'
        };
        
        const config = {
            displayModeBar: false,
            responsive: true
        };
        
        Plotly.newPlot(elementId, data, layout, config);
    }
    
    // Setup bar chart using Plotly
    function setupBarChart(elementId, labels, data, title, color) {
        const chartData = [{
            x: labels,
            y: data,
            type: 'bar',
            marker: {
                color: color
            },
            name: title
        }];
        
        const layout = {
            title: {
                text: title,
                font: {
                    size: 16
                }
            },
            xaxis: {
                title: {
                    text: '<?= isset($period) ? ucfirst($period) : "Period" ?>'
                }
            },
            yaxis: {
                title: {
                    text: 'Percentage (%)'
                },
                range: [0, 110]
            },
            shapes: [{
                type: 'line',
                x0: -0.5,
                y0: 100,
                x1: labels.length - 0.5,
                y1: 100,
                line: {
                    color: 'rgb(220, 53, 69)',
                    width: 2,
                    dash: 'dash'
                }
            }],
            annotations: [{
                x: labels.length - 0.5,
                y: 100,
                xref: 'x',
                yref: 'y',
                text: 'Target: 100%',
                showarrow: false,
                font: {
                    family: 'Arial',
                    size: 12,
                    color: 'rgb(220, 53, 69)'
                },
                bgcolor: 'rgba(255, 255, 255, 0.8)',
                bordercolor: 'rgb(220, 53, 69)',
                borderwidth: 1,
                borderpad: 4,
                align: 'right'
            }]
        };
        
        const config = {
            responsive: true,
            displayModeBar: true,
            displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d', 'select2d', 'autoScale2d'],
            toImageButtonOptions: {
                format: 'png',
                filename: title,
                height: 500,
                width: 700,
                scale: 2
            }
        };
        
        Plotly.newPlot(elementId, chartData, layout, config);
    }

    // Setup pagination
    function setupPagination() {
        const itemsPerPage = 10;
        const $table = $('#deliverablesTable');
        const $rows = $table.find('tbody tr');
        const totalItems = $rows.length;
        
        if (totalItems <= itemsPerPage) {
            $('#deliverablesPagination').hide();
            return;
        }
        
        // Hide all rows initially
        $rows.hide();
        
        // Show first page
        $rows.slice(0, itemsPerPage).show();
        
        // Calculate total pages
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        // Generate pagination HTML
        let paginationHTML = '';
        paginationHTML += '<li class="page-item disabled"><a class="page-link" href="#" data-page="prev">Previous</a></li>';
        
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === 1 ? 'active' : '';
            paginationHTML += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        
        paginationHTML += '<li class="page-item"><a class="page-link" href="#" data-page="next">Next</a></li>';
        
        // Set pagination HTML
        $('#deliverablesPagination').html(paginationHTML);
        
        // Handle pagination clicks
        $('#deliverablesPagination').on('click', '.page-link', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const page = $this.data('page');
            const $activeItem = $('.pagination .active');
            const currentPage = parseInt($activeItem.find('.page-link').data('page'));
            
            let targetPage = currentPage;
            
            if (page === 'prev') {
                if (currentPage > 1) {
                    targetPage = currentPage - 1;
                } else {
                    return; // Already on first page
                }
            } else if (page === 'next') {
                if (currentPage < totalPages) {
                    targetPage = currentPage + 1;
                } else {
                    return; // Already on last page
                }
            } else {
                targetPage = page;
            }
            
            // Update active page
            $('.pagination .page-item').removeClass('active');
            $(`.pagination .page-item a[data-page="${targetPage}"]`).parent().addClass('active');
            
            // Update prev/next buttons
            if (targetPage === 1) {
                $('.pagination .page-item a[data-page="prev"]').parent().addClass('disabled');
            } else {
                $('.pagination .page-item a[data-page="prev"]').parent().removeClass('disabled');
            }
            
            if (targetPage === totalPages) {
                $('.pagination .page-item a[data-page="next"]').parent().addClass('disabled');
            } else {
                $('.pagination .page-item a[data-page="next"]').parent().removeClass('disabled');
            }
            
            // Show appropriate rows
            const startIdx = (targetPage - 1) * itemsPerPage;
            const endIdx = Math.min(startIdx + itemsPerPage, totalItems);
            
            $rows.hide();
            $rows.slice(startIdx, endIdx).show();
        });
    }
    
    // Setup export buttons
    function setupExportButtons() {
        // Export to Excel
        $('#exportExcel').on('click', function() {
            exportTableToExcel();
        });
        
        // Export to CSV
        $('#exportCSV').on('click', function() {
            exportTableToCSV();
        });
    }
    
    // Export table to Excel
    function exportTableToExcel() {
        try {
            const $table = $('#deliverablesTable');
            const tableData = [];
            
            // Get headers
            const headers = [];
            $table.find('thead th').each(function() {
                headers.push($(this).text().trim());
            });
            tableData.push(headers);
            
            // Get row data
            $table.find('tbody tr').each(function() {
                const rowData = [];
                $(this).find('td').each(function() {
                    // Check if cell has a badge
                    const $badge = $(this).find('.badge');
                    if ($badge.length) {
                        rowData.push($badge.text().trim());
                    } else {
                        rowData.push($(this).text().trim());
                    }
                });
                tableData.push(rowData);
            });
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(tableData);
            
            // Create workbook and add the worksheet
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Deliverables');
            
            // Write to a file and trigger download
            XLSX.writeFile(wb, 'deliverables_report.xlsx');
        } catch (e) {
            console.error('Error exporting to Excel:', e);
            alert('Failed to export to Excel. Please try again.');
        }
    }

    // Export table to CSV
    function exportTableToCSV() {
        try {
            const $table = $('#deliverablesTable');
            let csvContent = '';
            
            // Get headers
            const headers = [];
            $table.find('thead th').each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csvContent += headers.join(',') + '\n';
            
            // Get row data
            $table.find('tbody tr').each(function() {
                const rowData = [];
                $(this).find('td').each(function() {
                    // Check if cell has a badge
                    const $badge = $(this).find('.badge');
                    if ($badge.length) {
                        rowData.push('"' + $badge.text().trim() + '"');
                    } else {
                        rowData.push('"' + $(this).text().trim() + '"');
                    }
                });
                csvContent += rowData.join(',') + '\n';
            });
            
            // Create blob
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            
            // Create download link
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'deliverables_report.csv');
            link.style.visibility = 'hidden';
            
            // Append to document, click and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            setTimeout(function() {
                URL.revokeObjectURL(url);
            }, 100);
        } catch (e) {
            console.error('Error exporting to CSV:', e);
            alert('Failed to export to CSV. Please try again.');
        }
    }
</script>

<?php include_once 'views/includes/footer.php'; ?>
</body>
</html>