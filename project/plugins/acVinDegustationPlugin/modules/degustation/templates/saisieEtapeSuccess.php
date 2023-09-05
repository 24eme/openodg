<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => TourneeDegustationEtapes::ETAPE_SAISIE)); ?>

<?php echo include_partial('global/flash'); ?>

<form role="form" action="<?php echo url_for("degustation_saisie_etape", $degustation) ?>" method="post" id="form_degustation_lots" class="form-horizontal">

<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<?php $operateur = null; ?>
<?php foreach($form['lots'] as $key => $lotForm): ?>
    <?php $lot = $degustation->lots->get($key); ?>
    <?php if (! $operateur): ?>
        <h3>Lots de l'opérateur : <?php echo $lot->declarant_nom ?></h3>
    <?php elseif ($operateur !== $lot->declarant_identifiant) : ?>
        <div class="row">
          <div class="col-xs-12">
            <a href="<?php echo url_for('degustation_ajout_lot_saisie', [
                'id' => $degustation->_id,
                'operateur' => $operateur
            ]) ?>" class="btn btn-default pull-right">
                    <span class="glyphicon glyphicon-plus"></span>
                    Ajouter un lot
            </a>
          </div>
        </div>

        <hr/>

        <h3>Lots de l'opérateur : <?php echo $lot->declarant_nom ?></h3>
    <?php endif ?>
    <?php $operateur = $lot->declarant_identifiant ?>
    <?php include_partial('degustation/lotForm', array('form' => $lotForm, 'lot' => $lot)); ?>
<?php endforeach ?>
    <div class="row" style="margin-bottom: 1rem">
      <div class="col-xs-12">
        <a href="<?php echo url_for('degustation_ajout_lot_saisie', [
            'id' => $degustation->_id,
            'operateur' => $operateur
        ]) ?>" class="btn btn-default pull-right">
                <span class="glyphicon glyphicon-plus"></span>
                Ajouter un lot
        </a>
      </div>
    </div>
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
