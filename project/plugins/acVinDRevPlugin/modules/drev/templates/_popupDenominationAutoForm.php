<div class="modal fade" id="drevDenominationAuto" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true" <?php if(!$drev->exist('denomination_auto')): ?>data-auto="true" data-backdrop="static" data-keyboard="false"<?php endif; ?> >
	<div class="modal-dialog" role="document">
		<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal" onsubmit='if ($("div.checkbox-group.required :checkbox:checked").length <= 0) { alert("Merci de sélectionner au moins une des options"); return false; }'>
			<?php echo $form->renderHiddenFields(); ?>
			<?php echo $form->renderGlobalErrors(); ?>
			<div class="modal-content">
					<div class="modal-header">
						<?php if($drev->exist('denomination_auto')): ?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><?php endif; ?>
						<h4 class="modal-title" id="myModalLabel">Avez-vous des certifications&nbsp;?</h4>
					</div>
					<div class="modal-body">
						<p>Pour vous faciliter la saisie de cette DRev, merci de nous indiquer si vous revendiquez des volumes en :</p>
						<div class="form-group row" style="margin-bottom: 0;">
							<div class="col-xs-12">
								<span class="error"><?php echo $form['denomination_auto']->renderError() ?></span>
								<div class="form-inline">
	  								<div class="controls-row checkbox-group required">
										<?php echo $form['denomination_auto']->render(array("data-placeholder" => "Sélectionnez un choix")) ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
						<button class="btn btn-success btn pull-right" type="submit">Valider</button>
					</div>
			</div>
		</form>
	</div>
</div>
