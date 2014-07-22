<div class="row">
		<div class="col-xs-4">
			
			<div class="panel panel-default">
  				<div class="panel-heading">
  					<h2 class="panel-title">Déclaration de l'année</h2>
  				</div>
				<div class="panel-body">
					<form id="drev-choices" action="" method="post">
						<p><strong>Vous souhaitez :</strong></p>
						<?php if ($drev): ?>
						<div class="form-group">
							<div class="radio">
								<label>
	    							<input type="radio" name="choice" value="<?php echo url_for('drev_edit', $drev) ?>" checked="checked" />
	    							Continuer ma déclaration en cours
	  							</label>
  							</div>
  							<div class="radio">
								<label>
	    							<input type="radio" name="choice" value="<?php echo url_for('drev_delete', $drev) ?>" />
	    							Supprimer ma déclaration en cours
	  							</label>
  							</div>
						</div>
						<?php else: ?>
						<div class="form-group">
							<div class="radio">
								<label>
	    							<input type="radio" name="choice" value="<?php echo url_for('@drev_create') ?>" checked="checked" />
	    							Démarrer une déclaration vierge
	  							</label>
	  						</div>
	  						<div class="radio disabled">
								<label>
	    							<input type="radio" name="choice" disabled="disabled" value="" />
	    							Démarrer à partir de la déclaration de récolte
	  							</label>
	  						</div>
	  						<div class="radio disabled">
								<label>
	    							<input type="radio" name="choice" disabled="disabled" value="" />
	    							Démarrer d'une déclaration d'une année précédente
	  							</label>
	  						</div>
						</div>
						<?php endif; ?>
						<div class="form-group">
							<div class="col-xs-offset-8 col-xs-4">
								<button class="btn btn-default btn-block" type="submit">Valider</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
</div>