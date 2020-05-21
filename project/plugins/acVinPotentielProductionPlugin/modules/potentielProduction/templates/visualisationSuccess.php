<?php 
use_helper('Date');
use_helper('PotentielProduction');
use_helper('Compte');
?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?> (<?php echo $societe->identifiant ?>)</a></li>
    <li><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?></a></li>
    <li class="active"><a href="<?php echo url_for('potentielproduction_visualisation', array('identifiant' => $etablissement->identifiant)); ?>">Potentiel de production</a></li>
</ol>

<div class="page-header no-border">
    <h2>Potentiel maximum de production Côtes de Provence</h2>
</div>
<?php foreach ($superficies as $appellation => $items): ?>
	<?php if (count($items) > 0): ?>
    <?php if($appellation == 'CDP') { $items = [0 => $items]; } ?>
    <?php foreach ($items as $couleur => $superficie): ?>
	<?php if (count($superficie) > 0): ?>
    <?php if (!$superficie['TOTAL']) continue; ?>
    <?php if ($couleur): ?>
    	<h3><?php echo echoAppellation($appellation).' '.ucfirst(strtolower($couleur)); ?></h3>
    <?php else: ?>
    	<h3><?php echo echoAppellation($appellation); ?></h3>
    <?php endif; ?>
	<div class="row">
		<div class="col-md-6">
			<table class="table table-bordered table-striped table-condensed">
				<tr>
					<th>Cépages</th>
					<th class="text-center">Superficie&nbsp;<small class="text-muted">(ha)</small></th>
				</tr>
				<?php include_partial('detailsCepages', ['cepages' => $superficie['principaux']]); ?>
				<?php if (isset($superficie['principaux']) && $superficie['principaux']['TOTAL'] > 0): ?>
				<tr>
					<td class="text-right"><strong>Total principaux</strong></td>
					<td class="text-right"><strong><?php echo $superficie['principaux']['TOTAL'] ?></strong>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endif; ?>
				<?php include_partial('detailsCepages', ['cepages' => $superficie['secondairesNoirs']]); ?>
				<?php include_partial('detailsCepages', ['cepages' => $superficie['secondairesBlancsVermentino']]); ?>
				<?php include_partial('detailsCepages', ['cepages' => $superficie['secondairesBlancsAutres']]); ?>
				<?php 
				    $secondairesNoirs = (isset($superficie['secondairesNoirs']) && $superficie['secondairesNoirs']['TOTAL'])? $superficie['secondairesNoirs']['TOTAL'] : 0;
				    $secondairesBlancs = (isset($superficie['secondairesBlancsVermentino']) && $superficie['secondairesBlancsVermentino']['TOTAL'])? $superficie['secondairesBlancsVermentino']['TOTAL'] : 0;
				    $secondairesBlancs += (isset($superficie['secondairesBlancsAutres']) && $superficie['secondairesBlancsAutres']['TOTAL'])? $superficie['secondairesBlancsAutres']['TOTAL'] : 0;
				?>
				<?php if ($secondairesNoirs > 0 || $secondairesBlancs > 0): ?>
				<tr>
					<td class="text-right"><strong>Total secondaires</strong></td>
					<td class="text-right"><strong><?php echo round($secondairesNoirs + $secondairesBlancs, 4) ?></strong>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<?php endif; ?>
				<?php if ($secondairesNoirs > 0 && $secondairesBlancs > 0): ?>
				<tr>
					<td class="text-right">dont noirs</td>
					<td class="text-right"><?php echo $secondairesNoirs ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
				<tr>
					<td class="text-right">dont blancs</td>
					<td class="text-right"><?php echo $secondairesBlancs ?>&nbsp;<small class="text-muted">ha</small></td>
				</tr>
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
			<?php 
			     $revendicable = ($couleur)? $donnees[$appellation][$couleur]['revendicables'] : $donnees[$appellation]['revendicables'];
			     $declassement = ($couleur)? $donnees[$appellation][$couleur]['declassements'] : $donnees[$appellation]['declassements'];
			     $revendicableTotal = 0;
			     $declassementTotal = 0;
			?>
			<table class="table table-bordered table-striped table-condensed">
				<?php 
				    foreach (PotentielProductionProvenceGenerator::$categories as $cat): 
				        if ((isset($revendicable[$cat]) && $revendicable[$cat] > 0) || (isset($declassement[$cat]) && $declassement[$cat] > 0)): 
				        $revendicableTotal += (isset($revendicable[$cat]) && $revendicable[$cat] > 0)? $revendicable[$cat] : 0;
				        $declassementTotal += (isset($declassement[$cat]) && $declassement[$cat] > 0)? $declassement[$cat] : 0;
				?>
				<tr>
					<td><?php echoThRevendicable('revendicable', $cat) ?> <small class="text-muted">(ha)</small></td>
					<td class="text-right text-success"><?php echo isset($revendicable[$cat])? $revendicable[$cat] : 0; ?></td>
				</tr>
				<tr>
					<td><?php echoThRevendicable('declassement', $cat) ?> <small class="text-muted">(ha)</small></td>
					<td class="text-right text-danger"><?php echo isset($declassement[$cat])? $declassement[$cat] : 0; ?></td>
				</tr>
				<?php endif; endforeach; ?>
				<?php 
				    if ((isset($revendicable['secondaires']) && $revendicable['secondaires'] > 0) || (isset($declassement['secondaires']) && $declassement['secondaires'] > 0)): 
				    $revendicableTotal += (isset($revendicable['secondaires']) && $revendicable['secondaires'] > 0)? $revendicable['secondaires'] : 0;
				    $declassementTotal += (isset($declassement['secondaires']) && $declassement['secondaires'] > 0)? $declassement['secondaires'] : 0;
				?>
				<tr>
					<td><?php echoThRevendicable('revendicable', 'secondaires') ?> <small class="text-muted">(ha)</small></td>
					<td class="text-right text-success"><?php echo isset($revendicable['secondaires'])? $revendicable['secondaires'] : 0; ?></td>
				</tr>
				<tr>
					<td><?php echoThRevendicable('declassement', 'secondaires') ?> <small class="text-muted">(ha)</small></td>
					<td class="text-right text-danger"><?php echo isset($declassement['secondaires'])? $declassement['secondaires'] : 0; ?></td>
				</tr>
				<?php endif; ?>
			</table>
			
			<table class="table table-bordered table-striped table-condensed">
				<tr>
					<th>Superfice totale revendicable <small class="text-muted">(ha)</small></th>
					<td class="text-right text-success"><strong><?php echo round($revendicableTotal, 4) ?></strong></td>
				</tr>
				<tr>
					<th>Cépages principaux et secondaires non revendicables <small class="text-muted">(ha)</small></th>
					<td class="text-right text-danger"><strong><?php echo round($declassementTotal, 4) ?></strong></td>
				</tr>
			</table>
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
<?php endforeach; ?>
