<div class="row">
		<div class="col-xs-4">
			
			<div class="panel panel-default">
  				<div class="panel-heading">
  					<h2 class="panel-title">DREV de l'année</h2>
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
                            
                            <div class="panel-heading">
  					<h2 class="panel-title">DREVMARC de l'année</h2>
  				</div>
				<div class="panel-body">
						<?php if ($drevmarc): ?>
							<p>
								<a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_edit', $drevmarc) ?>">Continuer</a>
							</p>
							<p>
								<a class="btn btn-sm btn-danger pull-right" href="<?php echo url_for('drevmarc_delete', $drevmarc) ?>">Supprimer</a>
							</p>
						<?php else: ?>
							<p>
								<a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('drevmarc_create', $etablissement) ?>">Démarrer</a>
							</p>
						<?php endif; ?>
				</div>
			</div>
		</div>
</div>