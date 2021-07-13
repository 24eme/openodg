<div class="modal fade" id="popupTableTriForm" role="dialog" aria-labelledby="Modifier le tri" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
                <?php echo $form->renderHiddenFields(); ?>
            	<?php echo $form->renderGlobalErrors(); ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Modifier le tri des lots</h4>
				</div>
				<div class="modal-body">
                    <div class="alert alert-warning">
                        <p>La modification des paramètres de tri va impacter le tri sur toutes les tables</p>
                    </div>
					<?php for($i = 0 ; isset($form['tri_'.$i]) ; $i++): ?>
					<span class="error"><?php echo $form['tri_'.$i]->renderError() ?></span>
					<div class="form-group row">
						<?php echo $form['tri_'.$i]->renderLabel(null, array("class" => "col-xs-3 control-label")); ?>
						<div class="col-xs-6">
						<?php echo $form['tri_'.$i]->render(array("data-placeholder" => "Sélectionnez premier tri", "class" => "form-control")) ?>
						</div>
					</div>
					<?php endfor; ?>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Modifier le tri</button>
				</div>
			</form>
		</div>
	</div>
</div>
