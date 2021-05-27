<div class="modal fade" id="parcellaireaffectation-confirmation-validation" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Validation de votre affectations</h4>
				</div>
				<div class="modal-body">
					<?php if(isset($form["signataire"])): ?>
                    <p>Pour confirmer la validation, merci de nous indiquer vos nom et prénom.</p>
				    <div class="row">
				        <div class="form-group <?php if ($form["signataire"]->hasError()): ?>has-error<?php endif; ?>">
				            <?php if ($form["signataire"]->hasError()): ?>
				                <div class="alert alert-danger" role="alert"><?php echo $form["signataire"]->getError(); ?></div>
				            <?php endif; ?>
				            <?php echo $form["signataire"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
				            <div class="col-xs-7">
								<?php echo $form["signataire"]->render(array("class" => "form-control", "placeholder" => "Votre prénom et votre nom")); ?>
				            </div>
				        </div>
				    </div>
                    <?php else: ?>
				    Confirmez-vous la validation de cette déclaration ?
                    <?php endif; ?>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<a id="submit-confirmation-validation" class="btn btn-success btn pull-right">Confirmer</a>
				</div>
		</div>
	</div>
</div>
