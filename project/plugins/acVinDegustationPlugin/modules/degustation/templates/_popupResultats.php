<div class="modal fade" id="popupResultat_<?php echo $name; ?>" role="dialog" aria-labelledby="Popup des résultats" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Resultat de la dégustation</h4>
                    <h6>Opérateur : <?= $lot->declarant_nom ?></h6>
                    <h6>Produit : <?= $lot->produit_libelle ?> <?= ($lot->details)
                                                                    ? '<small class="text-muted"> - ' . $lot->details . '</small>'
                                                                    : '' ?>
                        <?= $lot->millesime ?>
                    </h6>
                    <?php if ($lot->specificite): ?>
                        <h6>Spécificité : <?= $lot->specificite ?></h6>
                    <?php endif ?>
				</div>
				<div class="modal-body">
					<span class="error"><?php echo $form['conformite_'.$name]->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
					<?php echo $form['conformite_'.$name]->renderLabel() ?>
					</div>
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['conformite_'.$name]->render(array("data-placeholder" => "Sélectionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
						</div>
					</div>
					<span class="error"><?php echo $form['motif_'.$name]->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
					<?php echo $form['motif_'.$name]->renderLabel() ?>
					</div>
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['motif_'.$name]->render(array("placeholder" => "Motif de conformité/non conformité de l'échantillon", "class" => "form-control", "required" => false)) ?>
						</div>
					</div>
					<span class="error"><?php echo $form['observation_'.$name]->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['observation_'.$name]->renderLabel() ?>
						</div>
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['observation_'.$name]->render(array("placeholder" => "Observation complémentaire", "class" => "form-control", "required" => false)) ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right" name="popup" value="1">Valider</button>
				</div>
		</div>
	</div>
</div>
