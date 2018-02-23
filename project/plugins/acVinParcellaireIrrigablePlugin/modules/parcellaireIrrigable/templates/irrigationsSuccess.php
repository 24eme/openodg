<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'irrigations', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <small>Merci de déclarer vos parcelles irrigables</small></h2>
</div>

<form action="<?php echo url_for("parcellaireirrigable_irrigations", $parcellaireIrrigable) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    
    <?php foreach ($parcellaireIrrigable->declaration as $key => $value): ?>
	<h3><?php echo $value->libelle; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-4">Parcelle</th>
                <th class="col-xs-1">Année de plantation</th>
                <th class="col-xs-2">Type de matériel</th>
                <th class="col-xs-2">Type de ressource</th>
                <th class="col-xs-3">Observations</th>
            </tr>
		</thead>
		<tbody>
		<?php 
			foreach ($value->detail as $subkey => $subvalue): 
			if (isset($form[$key][$subkey])):
		?>
			<tr >
				<td class="col-xs-4"><?php echo $subvalue->commune; ?> - <?php echo $subvalue->section;  ?> / <?php echo $subvalue->numero_parcelle;  ?> - <?php echo $subvalue->cepage;  ?> <?php printf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $subvalue->superficie); ?></td>
            	<td class="col-xs-1"><?php echo $subvalue->campagne_plantation; ?></td>
            	<td class="col-xs-2">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$key][$subkey]['materiel']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$key][$subkey]['materiel']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$key][$subkey]['materiel']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax', "placeholder" => "Ajouter un matériel", "data-new" => "ajouter", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getMateriels($form[$key][$subkey]['materiel']->getValue())))) ?>
                        </div>
                    </div>
            	</td>
            	<td class="col-xs-2">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$key][$subkey]['ressource']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$key][$subkey]['ressource']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$key][$subkey]['ressource']->render(array('class' => 'form-control select2 select2-offscreen select2permissifNoAjax', "placeholder" => "Ajouter une ressource", "data-new" => "ajouter", "data-choices" => json_encode(ParcellaireIrrigableClient::getInstance()->getRessources($form[$key][$subkey]['ressource']->getValue())))) ?>
                        </div>
                    </div>
            	</td>
            	<td class="col-xs-3">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$key][$subkey]['observations']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$key][$subkey]['observations']->renderError() ?>
                        <div class="col-xs-12">
                        	<?php echo $form[$key][$subkey]['observations']->render(array('class' => 'form-control')) ?>
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