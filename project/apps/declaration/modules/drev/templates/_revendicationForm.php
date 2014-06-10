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
			foreach ($form['produits'] as $key => $embedForm) : 
				$produit = $drev->get($key)
		?>
			<tr>
				<td><?php echo $produit->getLibelleComplet() ?></td>
				<td class="text-center">
					<span class="text-danger"><?php echo $embedForm['total_superficie']->renderError() ?></span>
					<?php echo $embedForm['total_superficie']->render(array('class' => 'text-right')) ?>
				</td>
				<td class="text-center">
					<span class="text-danger"><?php echo $embedForm['volume_revendique']->renderError() ?></span>
					<?php echo $embedForm['volume_revendique']->render(array('class' => 'text-right')) ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>