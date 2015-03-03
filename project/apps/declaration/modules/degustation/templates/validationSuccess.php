<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_VALIDATION)); ?>

<div class="page-header no-border">
    <h2><?php echo $degustation->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($degustation); ?></span>&nbsp;<div class="btn btn-info btn-sm active"><?php echo count($degustation->operateurs) ?>&nbsp;opérateurs sélectionnés</div></h2>

</div>
<?php if ($validation->hasPoints()): ?>
    <?php include_partial('degustation/pointsAttentions', array('degustation' => $degustation, 'validation' => $validation)); ?>
<?php endif; ?>

<form action="<?php echo url_for('degustation_validation', $degustation); ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>
    

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_prelevements', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        
        <button type="submit" class="btn btn-default btn-lg btn-upper">Valider&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
    </div>
</div>
</form>