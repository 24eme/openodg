<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Lots en attente</h2>
</div>

<ul class="nav nav-tabs" style="margin-bottom: 15px;">
    <?php if(isset($lotsTournee)): ?>
    <li class="<?php if($active == "tournee"): ?>active<?php endif; ?>"><a href="<?= url_for('degustation_attente', ['active' => 'tournee']) ?>">Lots en attente de tournée de prélevement <span class="badge"><?= count($lotsTournee) ?></span></a></li>
    <?php endif; ?>
    <li class="<?php if($active != "tournee"): ?>active<?php endif; ?>"><a href="<?= url_for('degustation_attente', ['active' => 'degustation']) ?>"> Lots<?php if(isset($lotsTournees)): ?> prélevés <?php endif; ?> en attente de dégustation <span class="badge"><?= count($lotsDegustation) ?></span></a></li>
</ul>

<?php if($active == "tournee"): ?>
    <?php include_partial('degustation/lots', array('lots' => $lotsTournee)); ?>
<?php else: ?>
    <?php include_partial('degustation/lots', array('lots' => $lotsDegustation)); ?>
<?php endif; ?>
