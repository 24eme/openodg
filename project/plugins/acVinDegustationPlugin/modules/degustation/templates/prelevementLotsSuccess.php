<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Prélèvement des lots <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>
<div class="alert alert-info" role="alert">
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?></h3>
  <h4>Lieu : <strong><?php echo $degustation->getLieuNom(); ?></strong></h4>
  <h4>Nombre de lots maximum : <strong><?php echo $degustation->getMaxLots(); ?></strong></h4>
  <table class="table table-condensed">
    <tbody>
      <tr class="vertical-center">
        <td class="col-xs-4" >Nombre total de <strong>lots prélevables&nbsp;:</strong></td>
        <td class="col-xs-8"><strong><?php echo $infosDegustation["nbLotsPrelevable"]; ?></strong></td>
      </tr>
      <tr class="vertical-center">
        <td class="col-xs-4" >Nombre de <strong>lots à prélever (sélectionnés)&nbsp;:</strong></td>
        <td class="col-xs-8"><strong class="nbLotsSelectionnes"><?php echo $infosDegustation["nbAdherents"]; ?></strong></td>
      </tr>
      <tr class="vertical-center">
        <td class="col-xs-4" >Nombre <strong>d'adhérents prélevés&nbsp;:</strong></td>
        <td class="col-xs-8"><strong class="nbAdherents"><?php echo $infosDegustation["nbAdherents"]; ?></strong></td>
      </tr>
    </tbody>
  </table>
</div>

<p>Sélectionnez l'ensemble des lots à prélever pour la dégustation</p>
<form action="<?php echo url_for("degustation_prelevement_lots", $degustation) ?>" method="post" class="form-horizontal degustation prelevements">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Degustation voulue à partir du</th>
                <th class="col-xs-3">Opérateur</th>
                <th class="col-xs-1">Logement</th>
                <?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                <th class="col-xs-4">Produit (millésime)</th>
                <th class="col-xs-1">Spécificité</th>
              <?php else: ?>
                <th class="col-xs-5">Produit (millésime)</th>
              <?php endif ?>
                <th class="col-xs-1">Volume</th>
                <th class="col-xs-1">Prélever?</th>
            </tr>
        </thead>
		<tbody>
		<?php
            $dates = $form->getDateDegustParDrev();
			foreach ($form->getLotsPrelevables() as $key => $lot):
			if (isset($form['lots'][$key])):
		?>
			<tr class="vertical-center cursor-pointer" >
        <td><?php echo DateTime::createFromFormat('Ymd', $dates[$lot->id_document])->format('d/m/Y') ?></td>
        <td><?php echo $lot->declarant_nom; ?></td>
				<td><?php echo $lot->numero_cuve; ?></td>
				<td><?php echo $lot->produit_libelle; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
        <?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
          <td><?php echo $lot->specificite; ?></td>
        <?php endif ?>
        <td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
            	<td class="text-center" data-hash="<?php echo $lot->declarant_nom; ?>">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form['lots'][$key]['preleve']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form['lots'][$key]['preleve']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form['lots'][$key]['preleve']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            </tr>
        <?php  endif; endforeach; ?>
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
