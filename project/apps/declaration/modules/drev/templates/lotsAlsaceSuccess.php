<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_alsace', 'drev' => $drev)) ?>


<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace (AOC Alsace Communale et Lieu-dit inclus).</p>
<p>Un lot doit correspondre au maximum à 4 récipients et au maximum à 2000 hl.</p>

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



	<div class="form-group">
		<a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-warning pull-left">Retourner à l'organisation</a>
    	<button type="submit" class="btn btn-warning pull-right">Valider et répartir les lots suivant</button>
    </div>
</form>

<a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
<a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</a>


