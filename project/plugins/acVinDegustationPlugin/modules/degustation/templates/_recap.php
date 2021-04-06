<?php use_helper('Float') ?>
<?php use_helper("Date") ?>



<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Dégustateurs prévus</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
            		<th class="col-xs-4">Collège</th>
        			<th class="col-xs-8">Membre</th>

                </tr>
        	</thead>
        	<tbody>
        		<?php foreach ($degustation->degustateurs as $college => $degustateurs): ?>
	        		<?php foreach ($degustateurs as $id => $degustateur): ?>
	        		<tr>
								<td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
	        			<td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $id)) ?>" target="_blank"><?php echo $degustateur->get('libelle','') ?></a></td>

							</tr>
        		<?php endforeach;?>
        		<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Lots prévus</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
								<?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
									<th class="col-xs-2">Opérateur</th>
	            		<th class="col-xs-1">Logement</th>
	            		<th class="col-xs-4">Produit (millésime)</th>
                	<th class="col-xs-1">Spécificité</th>
	              <?php else: ?>
									<th class="col-xs-3">Opérateur</th>
	            		<th class="col-xs-1">Logement</th>
	            		<th class="col-xs-4">Produit (millésime)</th>
	              <?php endif ?>
            		<th class="col-xs-1">Volume</th>
            		<th class="col-xs-3">Statut</th>

                </tr>
        	</thead>
        	<tbody>
            	<?php foreach ($degustation->lots as $lot): ?>
            	<tr>
            		<td><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $lot->declarant_identifiant)) ?>" target="_blank"><?php echo $lot->declarant_nom; ?></a></td>
    				<td><?php echo $lot->numero_logement_operateur; ?></td>
    				<td><?php echo $lot->produit_libelle; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
						<?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                        <td><?php echo $lot->specificite; ?> <?= $lot->isSecondPassage() ? ', '.$lot->getTextPassage() : "" ?></td>
		        <?php endif ?>
						<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
            		<td><?php echo Lot::getLibelleStatut($lot->statut); ?></td>

            	</tr>
            	<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>
