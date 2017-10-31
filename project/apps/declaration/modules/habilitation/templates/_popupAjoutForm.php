<div class="modal fade" id="popupAjoutProduitForm" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal" novalidate >
                <?php echo $form->renderHiddenFields(); ?>
            	<?php echo $form->renderGlobalErrors(); ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Ajouter un produit</h4>
				</div>
				<div class="modal-body">
					<div class="form-group row">
						<span class="error"><?php echo $form['hashref']->renderError() ?></span>
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['hashref']->render(array("data-placeholder" => "Séléctionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
						</div>
					</div>
					<div class="form-group row">
						<span class="error"><?php echo $form['activites']->renderError() ?></span>
						<div class="col-xs-10 col-xs-offset-1">
							<?php $activitesWidget = $form['activites']; ?>
							<ul class="list-unstyled" >
								<?php foreach($activitesWidget->getWidget()->getChoices() as $key => $option): ?>
									<li>
										<div class="row">
											<div class="col-xs-6 text-left">
												<?php echo HabilitationClient::$activites_libelles[$key]; ?>
											</div>
											<div class="col-xs-3 text-left">
												<input class="acheteur_checkbox" type="checkbox" id="<?php echo $activitesWidget->renderId() ?>_<?php echo $key ?>" name="<?php echo $activitesWidget->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($activitesWidget->getValue()) && in_array($key, $activitesWidget->getValue())): ?>checked="checked"<?php endif; ?> />
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<div class="form-group row">
						<span class="error"><?php echo $form['date']->renderError() ?></span>
						<div class="col-xs-10 col-xs-offset-1">
							<div class="input-group date-picker">
								<?php echo $form['date']->render(array('placeholder' => "Date", "required" => false ,"class" => "col-xs-12")) ?>
								<div class="input-group-addon">
										<span class="glyphicon-calendar glyphicon"></span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<span class="error"><?php echo $form['statut']->renderError() ?></span>
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['statut']->render(array("data-placeholder" => "Séléctionnez un statut", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Ajouter le produit</button>
				</div>
			</form>
		</div>
	</div>
</div>
