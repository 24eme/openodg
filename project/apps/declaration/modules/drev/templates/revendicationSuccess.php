<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Revendication</h2>
</div>

<?php include_partial('drev/stepRevendication', array('drev' => $drev)) ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
	<?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <p>Veuillez saisir les informations des AOC revendiquées dans la déclaration de récolte de l'année</p>
	<div class="row">
		<div class="col-xs-3 col-xs-offset-9 text-center">
			<span class="label label-primary">Informations issues de la DR</span>
		</div>
	</div>
	<p></p>
	<table class="table table-striped">
		<thead>
			<tr>
				<th class="col-xs-5">Appellation revendiquée</th>
				<th class="col-xs-2 text-center">Superficie totale<br /><small>(ares)</small></th>
				<th class="col-xs-2 text-center">Volume&nbsp;revendiqué<br /><small>(hl)</small></th>
				<th class="col-xs-1 small text-center">Volume total</th>
				<th class="col-xs-1 small text-center">Volume sur place</th>
				<th class="col-xs-1 small text-center">Usages industriels</th>
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
								<span class="text-danger"><?php echo $embedForm['superficie_revendique']->renderError() ?></span>
								<?php echo $embedForm['superficie_revendique']->render(array('class' => 'form-control text-right input-rounded')) ?>
							</div>
						</div>
					</td>
					<td>
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<span class="text-danger"><?php echo $embedForm['volume_revendique']->renderError() ?></span>
								<?php echo $embedForm['volume_revendique']->render(array('class' => 'form-control text-right input-rounded')) ?>
							</div>
						</div>
					</td>
					<?php if(!$produit->volume_sur_place): ?>
						<td class=""></td>
						<td></td>
						<td></td>
					<?php else: ?>
						<td class="text-right text-muted">
							<?php echoFloat($produit->volume_total); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
						<td class="text-right text-muted">
							<?php echoFloat($produit->volume_sur_place); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
						<td class="text-right text-muted">
							<?php echoFloat($produit->usages_industriels_total); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="row row-margin">
		<div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précédente</a></div>
		<div class="col-xs-6 text-right">
			<button type="submit" class="btn btn-default">Valider et saisir les données par cépage<span class="eleganticon arrow_carrot-right"></span></button>
		</div>
	</div>
</form>
