<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>


<div class="page-header no-border">
    <h2>Sélection des dégustateurs</h2>
</div>
<p>Sélectionnez l'ensemble des dégustateurs en vue de leurs participations à la dégustation</p>
<form action="<?php echo url_for("degustation_selection_degustateurs", $degustation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    
    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>
	
	<?php foreach ($form->getDegustateursByColleges() as $college => $comptes): ?>
	<h3><?php echo $college ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
        		<th class="col-xs-4">Membre</th>
        		<th class="col-xs-7">Adresse</th>
                <th class="col-xs-1">Sélectionner?</th>

            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($comptes as $compte):
			if (isset($form['degustateurs'][$compte->_id])):
		?>
			<tr class="vertical-center">
				<td><?php echo $compte->nom_a_afficher ?></td>
				<td><?php echo $compte->adresse; ?><?php if ($compte->adresse_complementaire) : ?> <?php echo $compte->adresse_complementaire ?><?php endif ?> <span <?php if($compte->insee): ?>title="<?php echo $compte->insee ?>"<?php endif; ?>><?php echo $compte->code_postal; ?></span> <?php echo $compte->commune; ?> <?php if($compte->pays): ?><small class="text-muted">(<?php echo $compte->pays; ?>)</small><?php endif; ?></td>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form['degustateurs'][$compte->_id]['selectionne']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form['degustateurs'][$compte->_id]['selectionne']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form['degustateurs'][$compte->_id]['selectionne']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
	<?php endforeach; ?>
	
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_prelevement_lots", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
