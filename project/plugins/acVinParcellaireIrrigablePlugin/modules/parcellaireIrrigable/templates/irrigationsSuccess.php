<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'irrigations', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <small>Merci de déclarer vos parcelles irrigables</small></h2>
</div>

<form action="<?php echo url_for("parcellaireirrigable_irrigations", $parcellaireIrrigable) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	<h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-6">Parcelle</th>
                <th class="col-xs-2">Type de matériel</th>
                <th class="col-xs-2">Type de ressource</th>
                <th class="col-xs-2">Observations</th>
            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
                $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
			if (isset($form[$produitKey][$parcelle->getKey()])):
		?>
			<tr class="vertical-center">
				<td><?php echo $parcelle->getProduitLibelle(); ?> - <?php echo $parcelle->commune; ?> - <?php echo $parcelle->section;  ?> / <?php echo $parcelle->numero_parcelle;  ?> - <?php echo $parcelle->cepage;  ?> - <?php echo $parcelle->campagne_plantation;  ?> - <?php printf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $parcelle->superficie); ?></td>
            	<td>
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['materiel']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['materiel']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$produitKey][$parcelle->getKey()]['materiel']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax', "placeholder" => "Ajouter un matériel", "data-new" => "ajouter", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getMateriels($form[$produitKey][$parcelle->getKey()]['materiel']->getValue())))) ?>
                        </div>
                    </div>
            	</td>
            	<td>
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['ressource']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['ressource']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$produitKey][$parcelle->getKey()]['ressource']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax', "placeholder" => "Ajouter une ressource", "data-new" => "ajouter", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getRessources($form[$produitKey][$parcelle->getKey()]['ressource']->getValue())))) ?>
                        </div>
                    </div>
            	</td>
            	<td>
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['observations']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['observations']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$produitKey][$parcelle->getKey()]['observations']->render(array('class' => 'form-control')) ?>
                        </div>
                    </div>
            	</td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>

	<div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("parcellaireirrigable_parcelles", $parcellaireIrrigable); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
