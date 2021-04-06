<?php use_helper('Float') ?>
<?php use_helper("Date") ?>



<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Dégustateurs confirmés</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
            	<th class="col-xs-3">Collège</th>
        			<th class="col-xs-7">Membre</th>
        			<th class="col-xs-2">Présent sur</th>
              </tr>
        	</thead>
        	<tbody>
                <?php foreach ($degustation->getDegustateursConfirmes() as $compte_id => $degustateur): ?>
                    <tr>
                        <td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($degustateur->getParent()->getKey()) ?></td>
                        <td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $compte_id)) ?>" target="_blank"><?php echo $degustateur->get('libelle','') ?></a></td>
                  <td class="text-center">
                        <?php if($degustateur->exist('numero_table') && !is_null($degustateur->numero_table)): ?>Table n° <?php echo $degustateur->numero_table; ?><?php endif; ?>
                    </td>
                </tr>
        		<?php endforeach;?>
        	</tbody>
        </table>
	</div>
</div>

<div class="row row-margin row-button">
		<div class="col-xs-12 text-right">
			<a class="btn btn-default" href="<?php echo url_for('degustation_presences', $degustation) ?>" ><span class=" glyphicon glyphicon-user"></span>&nbsp;&nbsp;Présence des dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
		</div>
	</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Lots à déguster</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
                    <th class="col-xs-3">Opérateur</th>
            		<th class="col-xs-1">Logement</th>
            		<th class="col-xs-3">Produit (millésime)</th>
            		<th class="col-xs-1">Volume</th>
            		<th class="col-xs-2">Destination (date)</th>
            		<th class="col-xs-2">Statut</th>

                </tr>
        	</thead>
        	<tbody>
            	<?php foreach ($degustation->lots as $lot): ?>
            	<tr>
            		<td><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $lot->declarant_identifiant)) ?>" target="_blank"><?php echo $lot->declarant_nom; ?></a></td>
    				<td><?php echo $lot->numero_logement_operateur; ?></td>
    				<td><?php echo $lot->produit_libelle; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
    				<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
    				<td><?php echo MouvementLotView::getDestinationLibelle($lot); ?><?php if ($lot->destination_date): ?>&nbsp;(<?php echo ucfirst(format_date($lot->destination_date, "dd/MM/yyyy", "fr_FR")); ?>)<?php endif; ?></td>

            		<td><?php echo Lot::getLibelleStatut($lot->statut); ?></td>

            	</tr>
            	<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("degustation"); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>

    <div class="col-xs-4 text-center">
		<a href="#" class="btn btn-default"><span class="glyphicon glyphicon-file"></span>&nbsp;Etiquettes</a>
    </div>

    <div class="col-xs-4 text-right">
      <a class="btn btn-default" href="<?php echo url_for('degustation_resultats', $degustation) ?>" ><span class="glyphicon glyphicon-glass"></span>&nbsp;&nbsp;Résultats lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
    </div>
</div>
