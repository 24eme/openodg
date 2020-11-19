<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, 'options' => array('route' => 'degustation_preleve', 'nom' => 'Prélevements réalisés'))); ?>

<div class="page-header no-border">
  <h2>Échantillons prélevés</h2>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>

<?php include_partial('degustation/synthese', array('degustation' => $degustation, 'infosDegustation' => $infosDegustation)); ?>

<p>Sélectionner les lots qui ont été prélevés</p>
<form action="<?php echo url_for("degustation_preleve", $degustation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Opérateur</th>
                <th class="col-xs-1">Logement</th>
                <th class="col-xs-3">Produit (millésime)</th>
                <th class="col-xs-1">Volume</th>
                <th class="col-xs-1">Prélevé</th>
            </tr>
        </thead>
		<tbody>
		<?php foreach ($form['lots'] as $key => $formLot): ?>
            <?php $lot = $degustation->lots->get($key); ?>
			<tr class="vertical-center cursor-pointer">
                <td><?php echo $lot->declarant_nom; ?></td>
                <td class="edit"><?= $lot->numero_cuve ?>
                  <?php if (! $lot->isLeurre()): ?>
                    <span class="pull-right">
                      <a title="Modifier le logement" href="<?php echo url_for('degustation_preleve_update_logement', ['id' => $degustation->_id, 'lot' => $key]) ?>"><i class="glyphicon glyphicon-pencil"></i></a>
                    </span>
                  <?php endif; ?>
                </td>
				<td><?php echo $lot->produit_libelle; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
        <td class="text-right edit">
          <?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small>&nbsp;&nbsp;
          <a title="Modifier le lot dans la DRev" href="<?php echo url_for('degustation_update_lot', ['id' => $degustation->_id, 'lot' => $key]) ?>"><i class="glyphicon glyphicon-pencil"></i></a>
        </td>
            	<td class="text-center">
                    <div style="margin-bottom: 0;" class="<?php if($formLot->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $formLot['preleve']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $formLot['preleve']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
	</table>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_visualisation", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
