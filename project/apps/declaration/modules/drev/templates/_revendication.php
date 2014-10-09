<?php use_helper('Float') ?>
<h3>Revendication</h2>
<table class="table table-striped">
	<thead>
		<tr>
			<th class="col-md-6">Appellation</th>
			<?php if(!$drev->isNonRecoltant()): ?>
			<th class="text-center col-md-3">Superficie totale</th>
			<?php endif; ?>
			<th class="text-center col-md-3">Vol. revendiqué</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach ($drev->declaration->getProduits(true) as $key => $produit) : 
				$produit = $drev->get($key)
		?>
			<tr>
				<td><?php echo $produit->getLibelleComplet() ?></td>
				<?php if(!$drev->isNonRecoltant()): ?>
				<td class="text-center"><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">(ares)</small></td>
				<?php endif; ?>
				<td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">(hl)</small></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
