<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_grdcru', 'drev' => $drev)) ?>


<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace Grand Cru.</p>

<?php if ($sf_user->hasFlash('notice')): ?>
<p class="bg-success"><?php echo $sf_user->getFlash('notice') ?></p>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
<p class="bg-danger"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    
    <table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th class="col-md-6">Cépages</th>
				<th class="text-center col-md-3">Lots Hors VT / SGN</th>
				<th class="text-center col-md-3">Lots VT / SGN</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				foreach ($form['produits'] as $key => $embedForm) : 
					$produit = $drev->get($key);
			?>
				<tr>
					<td><?php echo $produit->getLibelle() ?></td>
					<td class="text-center">
						<span class="text-danger"><?php echo $embedForm['nb_hors_vtsgn']->renderError() ?></span>
						<?php echo $embedForm['nb_hors_vtsgn']->render(array('class' => 'text-right')) ?>
					</td>
					<td class="text-center">
						<span class="text-danger"><?php echo $embedForm['nb_vtsgn']->renderError() ?></span>
						<?php echo $embedForm['nb_vtsgn']->render(array('class' => 'text-right')) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ($ajoutForm->hasProduits()): ?>
	<p class="clearfix">
		<a class="btn btn-success pull-left" data-toggle="modal" data-target="#popupForm">
			<span class="glyphicon glyphicon-plus"> Ajouter un produit</span>
		</a>
	</p>
	<?php endif; ?>
	<p class="clearfix">
		<a href="<?php echo url_for("drev_lots_alsace", $drev) ?>" class="btn btn-warning pull-left">Retourner à la répartition des lots précédents</a>
		<button type="submit" class="btn btn-warning pull-right">Valider</button>
	</p>
	<p class="clearfix">
		<a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
		<button type="submit" href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</button>
	</p>
</form>

<?php include_partial('drev/popupAjoutForm', array('drev' => $drev, 'callBackUrl' => 'drev_lots_grdcru_ajout', 'form' => $ajoutForm)); ?>

