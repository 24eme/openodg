<?php use_helper('Float') ?>
<table class="table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th class="col-md-6">Appellation revendiquée</th>
			<th class="text-center col-md-3">Superficie totale <small class="text-muted">(ares)</small></th>
			<th class="text-center col-md-3">Volume revendiqué <small class="text-muted">(hl)</small></th>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach ($drev->declaration->getProduits(true) as $key => $produit) : 
				$produit = $drev->get($key)
		?>
			<tr>
				<td><?php echo $produit->getLibelleComplet() ?></td>
				<td class="text-center"><?php echoFloat($produit->total_superficie) ?></td>
				<td class="text-center"><?php echoFloat($produit->volume_revendique) ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>