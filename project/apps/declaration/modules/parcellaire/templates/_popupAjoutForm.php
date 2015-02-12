<div class="modal fade" id="<?php if(!isset($html_id)): ?>popupForm<?php else: ?><?php echo $html_id ?><?php endif; ?>" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
                <?php echo $form->renderHiddenFields(); ?>
            	<?php echo $form->renderGlobalErrors(); ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Ajouter une parcelle</h4>
				</div>
				<div class="modal-body">
					<span class="error"><?php echo $form['commune']->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['commune']->render(array("data-placeholder" => "Saisissez une commune", "class" => "form-control", "required" => true)) ?>
						</div>
					</div>
					<span class="error"><?php echo $form['section']->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['section']->render(array("placeholder" => "Saisissez une section", "class" => "form-control", "required" => true)) ?>
						</div>
					</div>
                                        <span class="error"><?php echo $form['numero_parcelle']->renderError() ?></span>
					<div class="form-group row">
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['numero_parcelle']->render(array("placeholder" => "Saisissez un numéro de parcelle", "class" => "form-control", "required" => true)) ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-default btn pull-right">Ajouter le produit</button>
				</div>
			</form>
		</div>
	</div>
</div>
