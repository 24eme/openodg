<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $degustation->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($degustation); ?></span>&nbsp;<div class="btn btn-info btn-sm active"><?php echo count($degustation->operateurs) ?>&nbsp;opérateurs sélectionnés</div></h2>
</div>

<?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>