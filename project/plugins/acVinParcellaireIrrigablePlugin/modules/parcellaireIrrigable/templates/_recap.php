<?php foreach ($parcellaireIrrigable->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	<h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-2">Produit</th>
                <th class="col-xs-1">Commune</th>
                <th class="col-xs-1">Section / Num.</th>
                <th class="col-xs-1">Cépage</th>
                <th class="col-xs-1">Surface</th>
                <th class="col-xs-1">Année de plantation</th>
                <th class="col-xs-2">Type de matériel</th>
                <th class="col-xs-2">Type de ressource</th>
                <th class="col-xs-2">Observations</th>
            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
		?>
			<tr>
				<td><?php echo $parcelle->getProduitLibelle(); ?></td>
				<td><?php echo $parcelle->commune; ?></td>
            	<td><?php echo $parcelle->section; ?> / <?php echo $parcelle->numero_parcelle; ?></td>
            	<td><?php echo $parcelle->cepage;  ?></td>
            	<td> <?php printf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $parcelle->superficie); ?></td>
            	<td><?php echo $parcelle->campagne_plantation; ?></td>
            	<td><?php echo $parcelle->materiel; ?></td>
            	<td><?php echo $parcelle->ressource; ?></td>
            	<td><?php echo $parcelle->observations; ?></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>
