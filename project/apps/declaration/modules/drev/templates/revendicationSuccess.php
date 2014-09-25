<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Revendication</h2>
</div>

<?php include_partial('drev/stepRevendication', array('drev' => $drev)) ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
	<?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <p>Veuillez saisir les informations des AOC revendiquées dans la déclaration de récolte de l'année</p>
    <?php if ($sf_user->hasFlash('notice')): ?>
        <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
        <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>
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
				<th class="col-xs-2 text-center">Superficie totale<br /></th>
				<th class="col-xs-2 text-center">Volume&nbsp;revendiqué<br /><small class="text-muted"> VT/SGN Inclus</small></th>
				<th class="col-xs-1 small text-center">Volume total</th>
				<th class="col-xs-1 small text-center">Volume sur place</th>
				<th class="col-xs-1 small text-center">Usages industriels</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($form['produits'] as $key => $embedForm) : 
					$produit = $drev->get($key)
			?>
				<tr>
					<td><?php echo $produit->getLibelleComplet() ?></td>
					<td>
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<span class="text-danger"><?php echo $embedForm['superficie_revendique']->renderError() ?></span>
								<?php echo $embedForm['superficie_revendique']->render(array('class' => 'form-control text-right input-rounded' , 'placeholder' => "ares")) ?>
							</div>
						</div>
					</td>
					<td>
						<div class="form-group">
							<div class="col-xs-10 col-xs-offset-1">
								<span class="text-danger"><?php echo $embedForm['volume_revendique']->renderError() ?></span>
								<?php echo $embedForm['volume_revendique']->render(array('class' => 'form-control text-right input-rounded', 'placeholder' => "hl")) ?>
							</div>
						</div>
					</td>
					<?php if(!$produit->detail->volume_sur_place): ?>
						<td class=""></td>
						<td></td>
						<td></td>
					<?php else: ?>
						<td class="text-right text-muted">
							<?php echoFloat($produit->detail->volume_total); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
						<td class="text-right text-muted">
							<?php echoFloat($produit->detail->volume_sur_place); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
						<td class="text-right text-muted">
							<?php echoFloat($produit->detail->usages_industriels_total); ?>&nbsp;<small class="text-muted">hl</small>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			<?php if ($ajoutForm->hasProduits()): ?>
			<tr>
				<td>
					<button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une appellation</button>
			    </td>
    			<td></td><td></td><td></td><td></td><td></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<div class="row row-margin">
		<div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
		<div class="col-xs-6 text-right">
			<button type="submit" class="btn btn-default btn-lg btn-upper">Valider <small>et saisir les cépages</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
		</div>
	</div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
