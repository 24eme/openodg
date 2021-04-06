<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des lots en élevages</h2>
</div>

<?php include_partial('degustation/lots', array('lots' => $lotsElevages)); ?>