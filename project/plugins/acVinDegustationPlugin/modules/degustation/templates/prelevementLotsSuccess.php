<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Prélèvement des lots</h2>
</div>
<p>Sélectionnez l'ensemble des lots à prélever pour la dégustation</p>
<form action="<?php echo url_for("degustation_prelevement_lots", $degustation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-3">Ressortissant</th>
        		<th class="col-xs-1">Lot</th>
        		<th class="col-xs-3">Produit (millésime)</th>
        		<th class="col-xs-1">Volume</th>
        		<th class="col-xs-3">Destination (date)</th>
                <th class="col-xs-1">Prélever?</th>

            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($form->getLotsPrelevables() as $key => $lot):
			if (isset($form['lots'][$key])):
		?>
			<tr class="vertical-center">
                <td><?php echo $lot->declarant_nom; ?></td>
				<td><?php echo $lot->numero; ?></td>
				<td><?php echo $lot->produit_libelle; ?><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
				<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
				<td><?php echo MouvementLotView::getDestinationLibelle($lot); ?><?php if ($lot->destination_date): ?>&nbsp;(<?php echo ucfirst(format_date($lot->destination_date, "dd/MM/yyyy", "fr_FR")); ?>)<?php endif; ?></td>
            	<td class="text-center">
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
