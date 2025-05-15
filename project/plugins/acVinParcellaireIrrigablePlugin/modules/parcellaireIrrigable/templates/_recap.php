<?php use_helper('Float'); ?>
    <table class="table table-bordered table-condensed table-striped tableParcellaire">
		<thead>
        	<tr>
            <?php if (ParcellaireConfiguration::getInstance()->hasIrrigableMaterielRessource() === false ): ?>
                <th class="col-xs-3">Commune</th>
                <th class="col-xs-3">Lieu-dit</th>
                <th class="col-xs-1 text-center">Section / N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1 text-center">Année plantat°</th>
                <th class="col-xs-2 text-right">Surf. <span class="text-muted small">(<?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?>)</span></th>
			<?php else: ?>
                <th class="col-xs-1">Commune</th>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1 text-center">Section / N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1 text-center">Année plantat°</th>
                <th class="col-xs-1 text-right">Surf. <span class="text-muted small">(<?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?>)</span></th>
                <th class="col-xs-2 text-center">Type de matériel</th>
                <th class="col-xs-2 text-center">Type de ressource</th>
			<?php endif; ?>
            </tr>
		</thead>
		<tbody>
<?php
$somme_superficie = 0;
foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles):
			foreach ($parcelles as $parcelle):
		?>
			<tr class="vertical-center">
                <td><?php echo $commune; ?></td>
				<td><?php echo $parcelle->lieu; ?></td>
				<td class="text-center"><?php echo $parcelle->section; ?> <?php echo $parcelle->numero_parcelle; ?></td>
				<td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
				<td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
                <?php if (ParcellaireConfiguration::getInstance()->isAres()): ?>
                    <td class="text-right"><?php echoFloatFr($parcelle->getSuperficie(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)); ?></td>
                <?php else: ?>
                    <td class="text-right"><?php echoFloatFr($parcelle->getSuperficie()); ?></td>
                <?php endif ?>
                <?php $somme_superficie += $parcelle->superficie; ?>
                <?php if (ParcellaireConfiguration::getInstance()->hasIrrigableMaterielRessource()): ?>
                    <td class="text-center"><?php echo $parcelle->materiel; ?></td>
                    <td class="text-center"><?php echo $parcelle->ressource; ?></td>
                <?php endif; ?>
            </tr>
        <?php  endforeach; ?>
<?php  endforeach; ?>
</tbody>
<tfooter>
    <tr>
        <th colspan="5">Total superficie</th>
        <?php if (ParcellaireConfiguration::getInstance()->isAres()): ?>
            <th class="text-right"><?php echoFloatFr($somme_superficie * 100, 2); ?></th>
        <?php else: ?>
            <th class="text-right"><?php echoFloatFr($somme_superficie, 4); ?></th>
        <?php endif ?>
        <?php if (ParcellaireConfiguration::getInstance()->hasIrrigableMaterielRessource()): ?>
            <th colspan="2">&nbsp;</th>
        <?php endif ?>

    <tr>
<tfooter>
</table>
