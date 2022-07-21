<div class="modal modal-page" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="" role="form" class="form-horizontal">
                <?php echo $form->renderHiddenFields(); ?>
            	<?php echo $form->renderGlobalErrors(); ?>
				<div class="modal-header">
					<a href="<?php echo url_for('drev_revendication_superficie', $drev) ?>" class="close">&times;</a>
					<h4 class="modal-title" id="myModalLabel">Modifier le label du produit</h4>
				</div>
				<div class="modal-body">
                    <div class="form-group row">
                        <div class="col-xs-10 col-xs-offset-1 lead" style="margin-bottom: 0;"><?php echo $produit->getLibelle() ?></div>
                    </div>
					<span class="error"><?php echo $form['denomination_auto']->renderError() ?></span>
					<div class="form-group row" style="margin-bottom: 0;">
						<div class="col-xs-10 col-xs-offset-1">
						<?php echo $form['denomination_auto']->render(array("required" => false)) ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="<?php echo url_for('drev_revendication_superficie', $drev) ?>" class="btn btn-default btn pull-left">Annuler</a>
					<button type="submit" class="btn btn-primary btn pull-right">Modifier</button>
				</div>
			</form>
		</div>
	</div>
</div>
