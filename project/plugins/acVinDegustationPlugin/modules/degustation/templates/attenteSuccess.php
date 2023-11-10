<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<?php if(isset($lotsTournee)): ?>
<div class="page-header no-border">
    <h3><span class="label label-primary"><?= count($lotsTournee) ?></span> Lots en attente de tournée de prélevement</h3>
</div>
<?php include_partial('degustation/lots', array('lots' => $lotsTournee)); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h3><span class="label label-primary"><?= count($lotsDegustation) ?></span> Lots<?php if(isset($lotsTournees)): ?> prélevés <?php endif; ?> en attente de dégustation</h3>
</div>
<?php include_partial('degustation/lots', array('lots' => $lotsDegustation)); ?>
