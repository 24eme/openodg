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
					<div class="row form-group">
						<div class="col-xs-4 text-right control-label">
							Produit :
						</div>
						<div class="col-xs-6">
							<span class="error"><?php echo $form['hashref']->renderError() ?></span>
							<?php echo $form['hashref']->render(array("data-placeholder" => "Séléctionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete", "required" => true)) ?>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-4 text-right control-label">
							Activité :
						</div>
						<div class="col-xs-6">
							<span class="error"><?php echo $form['activites']->renderError() ?></span>
							<?php $activitesWidget = $form['activites']; ?>
								<?php foreach($activitesWidget->getWidget()->getChoices() as $key => $option): ?>
									<div class="checkbox">
										<label>
											<input class="acheteur_checkbox" type="checkbox" id="<?php echo $activitesWidget->renderId() ?>_<?php echo $key ?>" name="<?php echo $activitesWidget->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($activitesWidget->getValue()) && in_array($key, $activitesWidget->getValue())): ?>checked="checked"<?php endif; ?> />&nbsp;&nbsp;<?php echo HabilitationClient::$activites_libelles[$key]; ?>
										</label>
								  	</div>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-4 text-right control-label">
							Activités :
						</div>
						<div class="col-xs-6">
							<span class="error"><?php echo $form['date']->renderError() ?></span>
							<div class="input-group date-picker">
								<?php echo $form['date']->render(array('placeholder' => "Date", "required" => false ,"class" => "form-control")) ?>
								<div class="input-group-addon">
										<span class="glyphicon-calendar glyphicon"></span>
								</div>
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-4 text-right control-label">
							Statut :
						</div>
						<div class="col-xs-6">
							<span class="error"><?php echo $form['statut']->renderError() ?></span>
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
