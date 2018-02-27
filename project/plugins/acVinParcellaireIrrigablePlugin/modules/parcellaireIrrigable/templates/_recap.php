<?php foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	<h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-3">Parcelle</th>
                <th class="col-xs-3">Cépage</th>
                <th class="col-xs-2">Matériel</th>
                <th class="col-xs-2">Ressource</th>
                <th class="col-xs-2">Observations</th>
            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
		?>
			<tr>
				<td><?php echo $parcelle->getIdentificationParcelleLibelle(ESC_RAW); ?></td>
				<td><?php echo $parcelle->getIdentificationCepageLibelle(ESC_RAW); ?></td>
            	<td><?php echo $parcelle->materiel; ?></td>
            	<td><?php echo $parcelle->ressource; ?></td>
            	<td><?php echo $parcelle->observations; ?></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>
