<div class="modal fade" id="drevDenominationAuto" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true" <?php if(!$drev->exist('denomination_auto')): ?>data-auto="true"<?php endif; ?> >
	<div class="modal-dialog" role="document">
		<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
			<?php echo $form->renderHiddenFields(); ?>
			<?php echo $form->renderGlobalErrors(); ?>
			<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Êtes vous certifié en Agriculture Biologique&nbsp;?</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12" style="font-weight: normal; ">
								<br/>
									<i>Pour vous faciliter la saisie de cette Drev, merci de nous indiquer si vous revendiquez du bio :</i>
								<br/>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-xs-12">
								<br/>
								<span class="error"><?php echo $form['denomination_auto']->renderError() ?></span>
								<div class="form-inline">
	  								<div class="controls-row">
										<?php echo $form['denomination_auto']->render(array("data-placeholder" => "Séléctionnez un choix",  "required" => true)) ?>
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
