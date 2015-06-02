<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php include_partial('degustation/step', array('tournee' => $tournee, 'active' => TourneeEtapes::ETAPE_VALIDATION)); ?>

<div class="page-header no-border">
    <h2><?php echo $tournee->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($tournee); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->operateurs) ?>&nbsp;opérateurs sélectionnés</div></h2>

</div>
<?php if ($validation->hasPoints()): ?>
    <?php include_partial('degustation/pointsAttentions', array('tournee' => $tournee, 'validation' => $validation)); ?>
<?php endif; ?>

<form action="<?php echo url_for('degustation_validation', $tournee); ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php include_partial('degustation/recap', array('tournee' => $tournee)); ?>
    

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_prelevements', $tournee) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        
        <button type="submit" onclick="return confirm('Étes-vous sur de valider et envoyer les mails aux intervenants ?')" class="btn btn-default btn-lg btn-upper">Valider et envoyer les mails&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
    </div>
</div>
</form>