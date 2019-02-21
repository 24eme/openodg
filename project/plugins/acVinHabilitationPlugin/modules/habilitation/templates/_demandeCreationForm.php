<div class="modal fade modal-page modal-demande" aria-labelledby="Créer une demande" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="" role="form" class="form-horizontal" novalidate>
				<div class="modal-header">
					<a href="<?php echo url_for("habilitation_declarant", $etablissement) ?>" class="close" aria-hidden="true">&times;</a>
					<h4 class="modal-title" id="myModalLabel">Créer une demande</h4>
				</div>
				<div class="modal-body">
                    <?php include_partial('habilitation/demandeForm', array('form' => $form)); ?>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" href="<?php echo url_for("habilitation_declarant", $etablissement) ?>">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Créer la demande</button>
				</div>
			</form>
		</div>
	</div>
</div>
