<?php use_helper('Date') ?>
<?php use_helper('PotentielProduction') ?>

<?php foreach ($superficies as $appellation => $items): ?>
	<?php if (count($items) > 0): ?>
	<div class="page-header no-border">
        <h2>Potentiel de production <?php echoAppellation($appellation) ?></h2>
    </div>
    <?php if($appellation == 'CDP') { $items = [0 => $items]; } ?>
    <?php foreach ($items as $couleur => $superficie): ?>
	<?php if (count($superficie) > 0): ?>
    <?php if ($couleur): ?>
    	<h2><?php echo ucfirst(strtolower($couleur)); ?></h2>
    <?php endif; ?>
    <?php if (!$superficie['TOTAL']) continue; ?>
	<div class="row">
		<div class="col-md-6">
			<table class="table table-bordered table-striped table-condensed">
				<tr>
					<th>Cépages</th>
					<th class="text-center">Superficie&nbsp;<small class="text-muted">(ha)</small></th>
				</tr>
				<?php if (isset($superficie['principaux']) && $superficie['principaux']['TOTAL'] > 0): ?>
				<?php foreach ($superficie['principaux'] as $cepage => $surface): if ($cepage == 'TOTAL' || !$surface) { continue; } ?>
				<tr>
					<td><?php echo $cepage ?></td>
					<td class="text-right"><?php echo $surface ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td class="text-right"><strong>Total principaux</strong></td>
					<td class="text-right"><strong><?php echo $superficie['principaux']['TOTAL'] ?></strong>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endif; ?>
				<?php if (isset($superficie['secondaires']) && $superficie['secondaires']['TOTAL'] > 0): ?>
				<?php $blancs = 0; $noirs = 0; ?>
				<?php foreach ($superficie['secondaires'] as $cepage => $surface): if ($cepage == 'TOTAL' || !$surface) { continue; } ?>
				<?php if (strtoupper(substr(trim($cepage), -1)) == 'B') $blancs += $surface; else $noirs += $surface; ?>
				<tr>
					<td><?php echo $cepage ?></td>
					<td class="text-right"><?php echo $surface ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td class="text-right"><strong>Total secondaires</strong></td>
					<td class="text-right"><strong><?php echo $superficie['secondaires']['TOTAL'] ?></strong>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php if ($noirs > 0 && $blancs > 0): ?>
				<tr>
					<td class="text-right">dont noirs</td>
					<td class="text-right"><?php echo $noirs ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<tr>
					<td class="text-right">dont blancs</td>
					<td class="text-right"><?php echo $blancs ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endif; ?>
				<?php endif; ?>
				<?php if (isset($superficie['TOTAL'])): ?>
				<tr>
					<td class="text-right"><strong>Total encépagement</strong></td>
					<td class="text-right"><strong><?php echo $superficie['TOTAL'] ?></strong>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endif; ?>
			</table>
		</div>
		<div class="col-md-6">
		
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
<?php endforeach; ?>


<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
</div>
