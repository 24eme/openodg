<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des lots en attente de dégustation : <span class=""><?= count($lotsPrelevables) ?></span></h2>
</div>
<?php include_partial('degustation/lots', array('lots' => $lotsPrelevables)); ?>
