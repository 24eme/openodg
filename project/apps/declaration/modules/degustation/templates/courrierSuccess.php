<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $tournee->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($tournee); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->operateurs) ?>&nbsp;Opérateurs</div></h2>
</div>

<?php if ($tournee->date < date('Y-m-d')): ?>
    <h2>Notes obtenues&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->getNotes()); ?>&nbsp;vins dégustés</div></h2> 
    <?php include_partial('degustation/notes', array('tournee' => $tournee, 'form' => $form)); ?>
<?php endif; ?>

