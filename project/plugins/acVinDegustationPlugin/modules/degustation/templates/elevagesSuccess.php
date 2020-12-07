<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des lots en élevages</h2>
</div>

<div class="row">
    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Opérateur</th>
                <th class="col-xs-1">Logement</th>
                <?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                <th class="col-xs-4">Produit (millésime)</th>
                <th class="col-xs-1">Spécificité</th>
              <?php else: ?>
                <th class="col-xs-5">Produit (millésime)</th>
              <?php endif ?>
                <th class="col-xs-1">Volume</th>
            </tr>
        </thead>
		<tbody>
		<?php
			foreach ($lotsElevages as $lot):
        $lot = $lot->value;
		?>
			<tr class="vertical-center cursor-pointer" >
        <td><?php echo $lot->declarant_nom; ?></td>
				<td><?php echo $lot->numero_cuve; ?></td>
				<td><?php echo $lot->produit_libelle; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
        <?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
          <td><?php echo (isset($lot->specificite))? $lot->specificite : null; ?></td>
        <?php endif ?>
        <td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
      </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>

</div>
