<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post">
	<?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
	<div class="row">
		<div class="col-md-12">
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th class="col-md-3">Appellation revendiquée</th>
						<th class="text-center col-md-2">Superficie totale <small class="text-muted">(ares)</small></th>
						<th class="text-center col-md-2">Volume revendiqué <small class="text-muted">(hl)</small></th>
						<th class="text-center col-md-1 info">Volume sur place</th>
						<th class="text-center col-md-1 info">Volume total </th>
						<th class="text-center col-md-1 info">Usages Industriels</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						foreach ($form['produits'] as $key => $embedForm) : 
							$produit = $drev->get($key)
					?>
						<tr>
							<td><?php echo $produit->getLibelleComplet() ?></td>
							<td class="text-center">
								<span class="text-danger"><?php echo $embedForm['total_superficie']->renderError() ?></span>
								<?php echo $embedForm['total_superficie']->render(array('class' => 'text-right')) ?>
							</td>
							<td class="text-center">
								<span class="text-danger"><?php echo $embedForm['volume_revendique']->renderError() ?></span>
								<?php echo $embedForm['volume_revendique']->render(array('class' => 'text-right')) ?>
							</td>
							<?php if(!$produit->dr->volume_sur_place): ?>
								<td colspan="" class="text-center"></td>
								<td colspan="" class="text-center"></td>
								<td colspan="" class="text-center"></td>
							<?php else: ?>
								<td class="text-center text-info">
									<?php echoFloat($produit->dr->volume_sur_place); ?> <small class="text-muted">(hl)</small>
								</td>
								<td class="text-center text-info">
									<?php echoFloat($produit->dr->volume_total); ?> <small class="text-muted">(hl)</small>
								</td>
								<td class="text-center text-info">
									<?php echoFloat($produit->dr->usages_industriels_total); ?> <small class="text-muted">(hl)</small>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<a href="" class="btn btn-primary btn-lg pull-left disabled">Étape précedente</a>
	<button type="submit" class="btn btn-primary btn-lg pull-right">Étape suivante</button>
</form>