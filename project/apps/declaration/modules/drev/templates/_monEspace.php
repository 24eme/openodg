<div class="row">
		<div class="col-xs-4">
			
			<div class="panel panel-default">
  				<div class="panel-heading">
  					<h2 class="panel-title">Déclaration de l'année</h2>
  				</div>
				<div class="panel-body">
						<?php if ($drev): ?>
							<p>
								<a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_edit', $drev) ?>">Continuer</a>
							</p>
							<p>
								<a class="btn btn-sm btn-danger pull-right" href="<?php echo url_for('drev_delete', $drev) ?>">Supprimer</a>
							</p>
						<?php else: ?>
							<p>
								<a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drev_create', $etablissement) ?>">Démarrer</a>
							</p>
						<?php endif; ?>
				</div>
			</div>
		</div>
</div>