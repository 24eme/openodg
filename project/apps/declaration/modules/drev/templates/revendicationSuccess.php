<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post">
	<div class="frame">	
		<?php echo $form->renderHiddenFields() ?>
	    <?php echo $form->renderGlobalErrors() ?>
	    <p>Veuillez saisir les informations des AOC revendiquées dans la déclaration de récolte de l'année</p>
		<div class="row">
			<div class="col-xs-3 col-xs-offset-9 text-center">
				<span class="label label-primary">Informations issues de la DR</span>
			</div>
		</div>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="col-xs-5 small">Appellation revendiquée</th>
					<th class="col-xs-2 small text-center">Superficie totale<br /><small>(ares)</small></th>
					<th class="col-xs-2 small text-center">Volume revendiqué<br /><small>(hl)</small></th>
					<th class="col-xs-1 small text-center"><small>Volume total</small></th>
					<th class="col-xs-1 small text-center"><small>Volume sur place</small></th>
					<th class="col-xs-1 small text-center"><small>Usages industriels</small></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					foreach ($form['produits'] as $key => $embedForm) : 
						$produit = $drev->get($key)
				?>
					<tr>
						<td><?php echo $produit->getLibelleComplet() ?></td>
						<td>
							<div class="form-group">
								<div class="col-xs-10 col-xs-offset-1">
									<span class="text-danger"><?php echo $embedForm['total_superficie']->renderError() ?></span>
									<?php echo $embedForm['total_superficie']->render(array('class' => 'form-control text-right')) ?>
								</div>
							</div>
						</td>
						<td>
							<div class="form-group">
								<div class="col-xs-10 col-xs-offset-1">
									<span class="text-danger"><?php echo $embedForm['volume_revendique']->renderError() ?></span>
									<?php echo $embedForm['volume_revendique']->render(array('class' => 'form-control text-right')) ?>
								</div>
							</div>
						</td>
						<?php if(!$produit->dr->volume_sur_place): ?>
							<td class=""></td>
							<td></td>
							<td></td>
						<?php else: ?>
							<td class="text-right small">
								<?php echoFloat($produit->dr->volume_total); ?>
								<small class="text-muted">hl</small>
							</td>
							<td class="text-right small">
								<?php echoFloat($produit->dr->volume_sur_place); ?>
								<small class="text-muted">hl</small>
							</td>
							<td class="text-right small">
								<?php echoFloat($produit->dr->usages_industriels_total); ?>
								<small class="text-muted">hl</small>
							</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="row row-margin">
		<div class="col-xs-4"><a href="" class="btn btn-primary btn-lg btn-block btn-prev">Étape précendente</a></div>
		<div class="col-xs-4 col-xs-offset-4"><button type="submit" class="btn btn-primary btn-lg btn-block btn-next">Étape suivante</button></div>
	</div>
</form>