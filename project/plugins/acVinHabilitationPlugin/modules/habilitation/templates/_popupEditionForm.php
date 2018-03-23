<div class="modal fade" id="<?php echo $idPopup ?>" role="dialog" aria-labelledby="Editer cette Activité" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
				<?php $idKey = $details->getHashForKey(); ?>
        		<?php echo $editForm->renderHiddenFields(); ?>
        		<?php echo $editForm->renderGlobalErrors(); ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Editer cette activité</h4>
				</div>
				<div class="modal-body">
					<div class="row form-group">
						<div class="col-xs-4 text-right control-label">
							Produit :
						</div>
						<div class="col-xs-6 text-left form-control-static">
							<strong><?php echo $produitCepage->getLibelleComplet(); ?></strong>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-4  text-right control-label">
							Activité :
						</div>
						<div class="col-xs-6 text-left form-control-static">
							<strong><?php echo HabilitationClient::$activites_libelles[$details->getKey()]; ?></strong>
						</div>
					</div>
					<div class="row form-group">
						<span class="error"><?php echo $editForm['statut_'.$idKey]->renderError(); ?></span>
						<div class="col-xs-4 control-label  text-right">
							Statut :
						</div>
						<div class="col-xs-6">
							<?php echo $editForm['statut_'.$idKey]->render(array("data-placeholder" => "Séléctionnez un statut", "style" => "width: 100%;", "class" => "select2 select2-offscreen select2autocomplete", "required" => true)); ?>
						</div>
					</div>
					<div class="row form-group" >
							<span class="error"><?php echo $editForm['date_'.$idKey]->renderError(); ?></span>
							<div class="col-xs-4 control-label text-right">
								Date :
							</div>
							<div class="col-xs-6" >
								<div class="input-group date-picker">
										<?php echo $editForm['date_'.$idKey]->render(array('placeholder' => "Date", "required" => "required" ,"class" => "form-control")); ?>
										<div class="input-group-addon">
												<span class="glyphicon-calendar glyphicon"></span>
										</div>
								</div>
							</div>
					</div>
					<div class="row form-group">
						<span class="error"><?php echo $editForm['commentaire_'.$idKey]->renderError(); ?></span>
						<div class="col-xs-4 control-label text-right">
							Commentaire :
						</div>
						<div class="col-xs-6">
							<?php echo $editForm['commentaire_'.$idKey]->render(array("data-placeholder" => "Séléctionnez un statut", "class" => "form-control", "required" => false)); ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Modifier</button>
				</div>
			</form>
		</div>
	</div>
</div>
