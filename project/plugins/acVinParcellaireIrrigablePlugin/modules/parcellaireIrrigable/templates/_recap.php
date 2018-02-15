<?php foreach ($parcellaireIrrigable->declaration as $key => $value): ?>
	<h3><?php echo $value->libelle; ?></h3>
    <table class="table table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-2">Commune</th>
                <th class="col-xs-2">Section / Num.</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1">Surface</th>
                <th class="col-xs-1">Année de plantation</th>
                <th class="col-xs-1">Type de matériel</th>
                <th class="col-xs-1">Type de ressource</th>
                <th class="col-xs-2">Observations</th>
            </tr>
		</thead>
		<tbody>
		<?php 
			foreach ($value->detail as $subkey => $subvalue):
		?>
			<tr >
				<td class="col-xs-2"><?php echo $subvalue->commune; ?></td>
            	<td class="col-xs-2"><?php echo $subvalue->section; ?> / <?php echo $subvalue->numero_parcelle; ?></td>
            	<td class="col-xs-2"><?php echo $subvalue->cepage;  ?></td>
            	<td class="col-xs-1"> <?php printf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $subvalue->superficie); ?></td>
            	<td class="col-xs-1"><?php echo $subvalue->annee_plantation; ?></td>
            	<td class="col-xs-1"><?php echo $subvalue->materiel; ?></td>
            	<td class="col-xs-1"><?php echo $subvalue->ressource; ?></td>
            	<td class="col-xs-2"><?php echo $subvalue->observations; ?></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>