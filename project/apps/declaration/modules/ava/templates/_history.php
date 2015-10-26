<?php if(count($history) > 0): ?>
<h2>Historique des déclarations</h2>
<div class="list-group">
<?php foreach ($history as $drev): ?>
	<?php if ($drev->type == DRevMarcClient::TYPE_MODEL): ?>
        <a class="list-group-item" href="<?php echo url_for('drevmarc_visualisation', $drev) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp; Revendication de Marc d'Alsace Gewurztraminer <?php echo $drev->campagne ?></a>
	<?php elseif($drev->type == DRevClient::TYPE_MODEL): ?>
        <a class="list-group-item" href="<?php echo url_for('drev_visualisation', $drev) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Revendication des appellations viticoles <?php echo $drev->campagne ?></a>
    <?php elseif($drev->type == ParcellaireClient::TYPE_MODEL): ?>
        <a class="list-group-item" href="<?php echo url_for('parcellaire_visualisation', $drev) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Affectation parcellaire <?php echo $drev->campagne ?></a>
    <?php endif; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>
