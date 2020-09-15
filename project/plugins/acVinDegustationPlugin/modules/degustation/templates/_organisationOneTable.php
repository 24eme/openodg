<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<div class="alert alert-info" role="alert">
	<h3>Synthèse table <?php echo $numero_table; ?></h3>
	<table class="table table-condensed">
			<thead>
				<tr>
					<th class="col-xs-9">Appellation couleur cepage</th>
					<th class="col-xs-3">nblots</th>
				</tr>
			</thead>
			<tbody id="synthese">
			<?php foreach ($syntheseLots as $hash => $lotsProduit): ?>
				<tr class="vertical-center cursor-pointer" data-hash="<?php echo $hash; ?>" >
					<td><?php echo $lotsProduit->libelle ?></td>
					<td class="nblots"><?php echo count($lotsProduit->lots) ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>


	<form action="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $numero_table)) ?>" method="post" class="form-horizontal degustation">
		<?php echo $form->renderHiddenFields(); ?>
		<div class="bg-danger">
			<?php echo $form->renderGlobalErrors(); ?>
		</div>

		<table class="table table-bordered table-condensed table-striped">
			<thead>
				<tr>
					<th class="col-xs-9">Lots</th>
					<th class="col-xs-3">Table <?php echo $numero_table; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($form->getTableLots() as $lot):
					$name = $form->getWidgetNameFromLot($lot);
					if (isset($form[$name])):
						?>
						<tr class="vertical-center cursor-pointer">
							<td>
								<div class="row">
									<div class="col-xs-5 text-right"><?php echo $lot->declarant_nom.' ('.$lot->numero.')'; ?></div>
									<div class="col-xs-5 text-right"><?php echo $lot->produit_libelle;?></div>
									<div class="col-xs-2 text-right"><?php echo ($lot->millesime)? ' ('.$lot->millesime.')' : ''; ?></div>
								</div>
							</td>
							<td class="text-center" data-hash="<?php echo $lot->produit_hash; ?>" data-libelle-produit="<?php echo $lot->produit_libelle; ?>">
								<div style="margin-bottom: 0;" class="form-group <?php if($form[$name]->hasError()): ?>has-error<?php endif; ?>">
									<?php echo $form[$name]->renderError() ?>
									<div class="col-xs-12">
										<?php echo $form[$name]->render(array('class' => "bsswitch ajax", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
									</div>
								</div>
							</td>
						</tr>
					<?php  endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="row row-margin row-button">
			<div class="col-xs-4"><a href="<?php echo url_for("degustation_visualisation_prelevement", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
			<div class="col-xs-4 text-center">
			</div>
			<div class="col-xs-4 text-right">
				<button type="submit" class="btn btn-success btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button>
			</div>
		</div>
	</form>
