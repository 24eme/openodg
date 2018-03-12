<?php foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	<h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
		        <th class="col-xs-2">Lieu-dit</th>
		        <th class="col-xs-1" style="text-align: right;">Sect°</th>
		        <th class="col-xs-1">N° parcelle</th>
		        <th class="col-xs-2">Cépage</th>
		        <th class="col-xs-1">Année plantat°</th>
		        <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
		        <th class="col-xs-2">Type de matériel</th>
		        <th class="col-xs-2">Type de ressource</th>
            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
		?>
			<tr class="vertical-center">
				<td><?php echo $parcelle->lieu; ?></td>
				<td style="text-align: right;"><?php echo $parcelle->section; ?></td>
				<td><?php echo $parcelle->numero_parcelle; ?></td>
				<td><?php echo $parcelle->cepage; ?></td>
				<td><?php echo $parcelle->campagne_plantation; ?></td>
				<td style="text-align: right;"><?php echo $parcelle->superficie; ?></td>
            	<td><?php echo $parcelle->materiel; ?></td>
            	<td><?php echo $parcelle->ressource; ?></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>
