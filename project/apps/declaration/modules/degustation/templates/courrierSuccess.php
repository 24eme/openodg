<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $degustation->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($degustation); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($degustation->operateurs) ?>&nbsp;Opérateurs</div></h2>
</div>

<?php if ($degustation->date < date('Y-m-d')): ?>
    <h2>Notes obtenues&nbsp;<div class="btn btn-default btn-sm"><?php echo count($degustation->getNotes()); ?>&nbsp;vins dégustés</div></h2> 
    <?php include_partial('degustation/notes', array('degustation' => $degustation, 'form' => $form)); ?>
<?php endif; ?>

