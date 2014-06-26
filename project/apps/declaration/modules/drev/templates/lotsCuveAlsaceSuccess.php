<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_alsace', 'drev' => $drev)) ?>

<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace (AOC Alsace Communale et Lieu-dit inclus).</p>
<p>Un lot doit correspondre au maximum à 4 récipients et au maximum à 2000 hl.</p>

<form method="post" action="" role="form" class="form-horizontal">

   	<?php include_partial('drev/lotsForm', array('drev' => $drev, 'form' => $form)); ?>

   	<?php if ($ajoutForm->hasProduits()): ?>
	<p class="clearfix">
	    <a class="btn btn-success pull-left" data-toggle="modal" data-target="#popupForm">
	        <span class="glyphicon glyphicon-plus"> Ajouter un produit</span>
	    </a>
	</p>
	<?php endif; ?>
	
	<p class="clearfix">
		<a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-warning pull-left">Retourner à l'organisation</a>
		<button type="submit" class="btn btn-warning pull-right">Valider et répartir les lots suivant</button>
	</p>
	<p class="clearfix">
		<a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
		<button type="submit" href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</button>
	</p>
</form>

<?php include_partial('drev/popupAjoutForm', array('prelevement' => $prelevement, 'form' => $ajoutForm)); ?>
