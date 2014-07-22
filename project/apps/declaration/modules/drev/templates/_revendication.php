<?php use_helper('Float') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Revendication</h2>
    </div>
    <div class="panel-body">
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="col-md-6 small">Appellation</th>
					<th class="text-center col-md-3 small">Superficie totale</th>
					<th class="text-center col-md-3 small">Vol. revendiqué</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					foreach ($drev->declaration->getProduits(true) as $key => $produit) : 
						$produit = $drev->get($key)
				?>
					<tr>
						<td><?php echo $produit->getLibelleComplet() ?></td>
						<td class="text-center"><?php echoFloat($produit->total_superficie) ?> <small class="text-muted">(ares)</small></td>
						<td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">(hl)</small></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
