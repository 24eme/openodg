<?php if($approuver): ?>
<div class="modal fade" id="transaction-confirmation-validation" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Validation de votre déclaration</h4>
				</div>
				<div class="modal-body">
					<p>Confirmez-vous la validation de votre Déclaration de vrac export ?</p>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<a id="submit-confirmation-validation" class="btn btn-success btn pull-right">Confirmer</a>
				</div>
		</div>
	</div>
</div>
<?php else: ?>
	<div class="modal fade" id="transaction-confirmation-validation" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Validation de déclaration</h4>
					</div>
					<div class="modal-body">
						<p>Êtes vous sûr de vouloir approuver cette déclaration ?</p>
					</div>
					<div class="modal-footer">
						<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
						<a id="submit-confirmation-validation" class="btn btn-success btn pull-right">approuver</a>
					</div>
			</div>
		</div>
	</div>
<?php endif; ?>
