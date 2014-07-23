<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_alsace', 'drev' => $drev)) ?>

<form method="post" action="" role="form">
	<div class="tab-content">
		<div class="tab-pane active">
			<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace (AOC Alsace Communale et Lieu-dit inclus).</p>
			
			<p>Un lot doit correspondre au maximum à 4 récipients et au maximum à 2000 hl.</p>

		   	<?php include_partial('drev/lotsForm', array('drev' => $drev, 'form' => $form)); ?>

		   	<?php if ($ajoutForm->hasProduits()): ?>
				<button class="btn btn-default" data-toggle="modal" data-target="#popupForm" type="button">Ajouter un produit&nbsp;<span class="eleganticon icon_plus"></span></button>
			<?php endif; ?>

			<div class="row row-margin">
                <div class="col-xs-6">
                    <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-default"><span class="eleganticon arrow_carrot-left"></span>Retourner à l'organisation</a>
                </div>
                
                <div class="col-xs-6 text-right">
                    <button type="submit" class="btn btn-default">Valider et répartir les lots suivant<span class="eleganticon arrow_carrot-right"></span></button>
                </div>
            </div>
		</div>
	</div>

	<div class="row row-margin">
        <div class="col-xs-4"><a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précendente</a></div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('prelevement' => $prelevement, 'form' => $ajoutForm)); ?>
