<?php use_helper('Float'); ?>
    <table class="table table-bordered table-condensed table-striped tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-1">Commune</th>
		        <th class="col-xs-2">Lieu-dit</th>
		        <th class="col-xs-1 text-center">Section / N° parcelle</th>
		        <th class="col-xs-3">Produit</th>
		        <th class="col-xs-1">Année plantat°</th>
		        <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
		        <th class="col-xs-1">Densité</th>
		        <th class="col-xs-1">% de pieds manquants <span class="text-muted">(si&nbsp;+&nbsp;de&nbsp;20%)</span></th>

            </tr>
		</thead>
		<tbody>
<?php
$somme_superficie = 0;
foreach ($parcellaireManquant->declaration->getParcellesByCommune() as $commune => $parcelles):
			foreach ($parcelles as $parcelle):
		?>
			<tr class="vertical-center">
                <td><?php echo $commune; ?></td>
				<td><?php echo $parcelle->lieu; ?></td>
				<td class="text-center"><?php echo $parcelle->section; ?> <?php echo $parcelle->numero_parcelle; ?></td>
				<td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
				<td><?php echo $parcelle->campagne_plantation; ?></td>
				<td style="text-align: right;"><?php echo echoFloat($parcelle->superficie); $somme_superficie += $parcelle->superficie; ?></td>
            	<td><?php echo $parcelle->densite; ?></td>
            	<td><?php echo $parcelle->pourcentage; ?></td>
            </tr>
        <?php  endforeach; ?>
<?php  endforeach; ?>
</tbody>
<tfooter>
    <tr>
        <th colspan="5">Total superficie</th>
        <th class="text-right"><?php echoFloat($somme_superficie); ?></th>
        <th colspan="2">&nbsp;</th>
    <tr>
<tfooter>
</table>
