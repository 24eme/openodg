<?php use_helper('Float') ?>
<?php use_helper("Date") ?>

<div class="page-header no-border">
    <h2><?php if(!$degustation->isValidee()): ?>Validation<?php else: ?>Visualisation<?php endif; ?> de la dégustation du <?php echo format_date($degustation->date, "P", "fr_FR") ?></h2><span class="text-muted"><?php echo $degustation->lieu ?></span>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Dégustateurs</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
                    <?php if ($degustation->isValidee()) : ?>
                    <th class="col-xs-1"></th>
                    <?php endif; ?>
            		<th class="col-xs-2">Collège</th>
        			<th class="col-xs-6">Membre</th>
                    <?php if ($degustation->isValidee()) : ?>
                    <th class="col-xs-3">Actions</th>
                    <?php endif; ?>
                </tr>
        	</thead>
        	<tbody>
        		<?php foreach ($degustation->degustateurs as $college => $degustateurs): ?>
        		<?php foreach ($degustateurs as $id => $degustateur): ?>
        		<tr>
                    <?php if ($degustation->isValidee()) : ?>
                    <td class="text-center"><p><span class="glyphicon glyphicon-question-sign"></span></p></td>
                    <?php endif; ?>
        			<td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
        			<td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $id)) ?>" target="_blank"><?php echo $degustateur ?></a></td>
                    <?php if ($degustation->isValidee()) : ?>
                    <td class="text-center"><a href="#" class="btn btn-default"><span class="glyphicon glyphicon-ok"></span>&nbsp;Confirmer</a> &nbsp; <a href="#" class="btn btn-default"><span class="glyphicon glyphicon-remove"></span>&nbsp;Refuser</a></td>
                    <?php endif; ?>
        		</tr>
        		<?php endforeach;?>
        		<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Lots à déguster</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
                    <th class="col-xs-3">Ressortissant</th>
            		<th class="col-xs-1">Lot</th>
            		<th class="col-xs-3">Produit (millésime)</th>
            		<th class="col-xs-1">Volume</th>
            		<?php if($degustation->isValidee()): ?>
            		<th class="col-xs-2">Statut</th>
            		<?php endif; ?>
                </tr>
        	</thead>
        	<tbody>
            	<?php foreach ($degustation->lots as $lot): ?>
            	<tr>
            		<td><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $lot->declarant_identifiant)) ?>" target="_blank"><?php echo $lot->declarant_nom; ?></a></td>
    				<td><?php echo $lot->numero; ?></td>
    				<td><?php echo $lot->produit_libelle; ?><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
    				<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
            		<?php if($degustation->isValidee()): ?>
            		<td><?php echo Lot::getLibelleStatut($lot->statut); ?></td>
            		<?php endif; ?>
            	</tr>
            	<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>
