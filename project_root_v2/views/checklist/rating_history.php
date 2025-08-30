<?php
$title = 'Historique des Checklists';

include_once 'views/includes/header.php';
?>
<!-- views/checklist/rating_history.php -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history"></i> Historique de La Checklist
            <small class="text-muted">
            <small><?= htmlspecialchars($checklist['ID_CLC']) ?></small>
        </h1>
        <a href="javascript:history.back()" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails des cotations</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Itération</th>
                            <th>Date</th>
                            <th>Effectué par</th>
                            <th>Cotation</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $record): ?>
                            <tr>
                                <td><?= $record['iteration'] ?></td>
                                <td><?= $record['formatted_date'] ?></td>
                                <td><?= htmlspecialchars($record['rated_by']) ?></td>
                                <td><?= $record['rating_badge'] ?></td>
                                <td><?= $record['summary'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucun historique disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>