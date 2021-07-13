<div class="modal fade" id="popupMailDegustateurs" role="dialog" aria-labelledby="Modifier le tri" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Email de convocation aux dégustateurs</h4>
				</div>
				<div class="modal-body">
                    <pre><?php include_partial('Email/send_convocation_degustateur', array('degustation' => $degustation, 'identifiant' => $identifiant, 'college' => $college)); ?></pres>
				</div>
				<div class="modal-footer">
					<a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
					<button type="submit" class="btn btn-success btn pull-right">Ok</button>
				</div>
		</div>
	</div>
</div>
