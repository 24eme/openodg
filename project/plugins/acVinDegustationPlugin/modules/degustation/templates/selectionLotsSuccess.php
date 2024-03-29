<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des lots <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>
<div class="alert alert-info" role="alert">
  <table class="table table-condensed">
    <tbody>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total de <strong>lots à prélever&nbsp;:</strong></td>
        <td class="col-xs-9"><strong id="nbLotsSelectionnes"><?php echo $infosDegustation["nbLots"]; ?></strong></td>
      </tr>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total <strong>d'adhérents à prélever&nbsp;:</strong></td>
        <td class="col-xs-9"><strong id="nbAdherentsAPrelever"><?php echo $infosDegustation["nbAdherents"]; ?></strong></td>
      </tr>
    </tbody>
  </table>
</div>

<p>Sélectionnez l'ensemble des lots à prélever pour la dégustation</p>
<form action="<?php echo url_for("degustation_selection_lots", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Degustation voulue<br/> à partir du</th>
                <th class="col-xs-3">Opérateur</th>
                <th class="col-xs-1">Provenance</th>
                <th class="col-xs-1">Logement</th>
                <th class="col-xs-5">Produit (millésime, spécificité)</th>
                <th class="col-xs-1">Volume</th>
                <th class="col-xs-1">À prélever?</th>
            </tr>
        </thead>
		<tbody>
        <?php $dates = $form->getDateDegustParDrev();
            foreach ($form['lots'] as $key => $lotForm):
                $lot = $form->getLot($key);
                $lot->type_document = substr($lot->id_document, 0, 4);
            ?>
          <tr class="vertical-center cursor-pointer" data-adherent="<?php echo $lot->declarant_identifiant ?>">
            <td><?php echo DateTime::createFromFormat('Ymd', $dates[$lot->id_document])->format('d/m/Y') ?></td>
            <td><?php echo $lot->declarant_nom; ?></td>
            <td>
              <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));  ?>">
                <?php echo ucfirst(strtolower($lot->type_document)).' n°&nbsp;'.$lot->numero_dossier; ?>
              </a>
            </td>
            <td><?php echo $lot->numero_logement_operateur; ?></td>
            <td><?php echo showProduitCepagesLot($lot, false) ?></td>
            <td class="edit text-right">
              <?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small>
              <a title="Modifier le volume" href="<?php echo url_for("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id)); ?>">
                <i class="glyphicon glyphicon-share-alt"></i>
              </a>
            </td>
            <td class="text-center" data-hash="<?php echo $lot->declarant_nom; ?>">
              <div style="margin-bottom: 0;" class="form-group <?php if($form['lots'][$key]['preleve']->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form['lots'][$key]['preleve']->renderError() ?>
                  <div class="col-xs-12">
                    <?php echo $form['lots'][$key]['preleve']->render(array('class' => "degustation bsswitch", "data-preleve-adherent" => $lot->declarant_identifiant, "data-preleve-lot" => $lot->unique_id, 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                  </div>
              </div>
            </td>
          </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation") ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
