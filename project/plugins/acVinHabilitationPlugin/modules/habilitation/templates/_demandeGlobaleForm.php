<div class="modal fade modal-page modal-demande" aria-labelledby="Créer une demande" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="" role="form" class="form-horizontal" novalidate>
				<div class="modal-header">
					<a href="<?php echo url_for("habilitation_declarant", $form->getEtablissementChais()) ?>" class="close" aria-hidden="true">&times;</a>
					<h4 class="modal-title" id="myModalLabel">Créer une demande globale</h4>
				</div>
				<div class="modal-body">
                    <?php include_partial('habilitation/demandeForm', array('form' => $form)); ?>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" href="<?php echo url_for("habilitation_declarant", $form->getEtablissementChais()) ?>">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Créer les demandes</button>
				</div>
			</form>
		</div>
	</div>
</div>
