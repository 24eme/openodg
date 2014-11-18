    <div class="col-xs-4">
        <?php if(count($history) > 0): ?>  	
        <div class="block_declaration panel panel-primary equal-height">
            <div class="panel-heading">
                <h3>Historique</h3>
            </div>
            <ul class="list-group">
            <?php foreach ($history as $drev): ?>
            	<?php if ($drev->type == DRevMarcClient::TYPE_MODEL): ?>
            	<li class="list-group-item">
                    <a class="btn btn-link btn-primary" href="<?php echo url_for('drevmarc_visualisation', $drev) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Marc d'Alsace Gewurzt. <?php echo $drev->campagne ?></a>
                </li>
            	<?php elseif($drev->type == DRevClient::TYPE_MODEL): ?>
                <li class="list-group-item">
                    <a class="btn btn-link btn-primary" href="<?php echo url_for('drev_visualisation', $drev) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Appellations viticoles <?php echo $drev->campagne ?></a>
                </li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>