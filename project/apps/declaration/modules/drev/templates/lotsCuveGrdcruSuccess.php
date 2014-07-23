<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_grdcru', 'drev' => $drev)) ?>

<form method="post" action="" role="form">
	<div class="tab-content">
		<div class="tab-pane active">

			<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace Grand Cru.</p>
			<?php include_partial('drev/lotsForm', array('drev' => $drev, 'form' => $form)); ?>
			
			<?php if ($ajoutForm->hasProduits()): ?>
				<button class="btn btn-default" data-toggle="modal" data-target="#popupForm" type="button">Ajouter un produit&nbsp;<span class="eleganticon icon_plus"></span></button>
			<?php endif; ?>

			<div class="row row-margin">
                <div class="col-xs-6">
                    <a href="<?php echo url_for("drev_lots", $prelevement) ?>" class="btn btn-default"><span class="eleganticon arrow_carrot-left"></span>Retourner à la répartition des lots précédents</a>
                </div>
            </div>
		</div>
	</div>
	<div class="row row-margin">
		<div class="col-xs-4"><a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précédente</a></div>
		<div class="col-xs-4 col-xs-offset-4"><button type="submit" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-right pull-right"></span>Étape suivante</button></div>
	</div>
</form>

<?php include_partial('drev/popupAjoutForm', array('prelevement' => $prelevement, 'form' => $ajoutForm)); ?>

