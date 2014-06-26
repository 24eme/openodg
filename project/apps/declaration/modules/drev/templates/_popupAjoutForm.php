<div class="modal fade" id="popupForm" tabindex="-1" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo url_for("drev_lots_ajout", array("sf_subject" => $drev, "cuve" => $form->getObject()->getKey())) ?>" role="form" class="form-horizontal">
				<?php echo $form->renderGlobalErrors() ?>
				<?php echo $form->renderHiddenFields() ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Ajouter un produit</h4>
				</div>
				<div class="modal-body">
					<span class="error"><?php echo $form['hashref']->renderError() ?></span>
					<div class="form-group">
						<?php echo $form['hashref']->renderLabel() ?>
						<?php echo $form['hashref']->render() ?>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn-lg pull-left" data-dismiss="modal">Close</a>
					<button type="submit" class="btn btn-primary btn-lg pull-right">Save changes</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function () {
	$( "#<?php echo $form['hashref']->renderId() ?>" ).combobox();
});
</script>