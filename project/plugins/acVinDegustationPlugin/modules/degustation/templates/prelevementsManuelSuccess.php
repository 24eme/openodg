<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_PRELEVEMENT_MANUEL)); ?>

<?php echo include_partial('global/flash'); ?>

<form role="form" action="<?php echo url_for("degustation_prelevements_manuel_etape", $degustation) ?>" method="post" id="form_degustation_lots" class="form-horizontal">

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php $operateur = null; ?>
<?php foreach($form['lots'] as $key => $lotForm): ?>
    <?php $lot = $degustation->lots->get($key); ?>
    <?php if ($operateur !== $lot->declarant_nom) : ?>
        <?php $operateur = $lot->declarant_nom ?>
        <h4>Lots de l'opérateur : <?php echo $lot->declarant_nom ?></h4>
    <?php endif ?>
    <?php include_partial('degustation/lotForm', array('form' => $lotForm, 'lot' => $lot)); ?>
<?php endforeach ?>
</form>

<div class="row row-margin row-button">
    <div class="col-xs-4">
    </div>
    <div class="col-xs-4 text-center">
    </div>
    <div class="col-xs-4 text-right">
        <button form="form_degustation_lots" id="lots_degustation_valide" type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
    </div>
</div>
