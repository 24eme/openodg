<?php echo use_helper("Date"); ?>

<ul class="nav nav-tabs">
  <li role="presentation" <?php if($type == "DRev" && $campagne == "2014"): ?>class="active"<?php endif ?>>
    <a href="<?php echo url_for('admin', array("doc_type" => "DRev", 'doc_campagne' => "2014")) ?>">DRev 2014</a></li>
  <li role="presentation" <?php if($type == "DRevMarc" && $campagne == "2014"): ?>class="active"<?php endif ?>>
    <a href="<?php echo url_for('admin', array("doc_type" => "DRevMarc", 'doc_campagne' => "2014")) ?>">DRev Marc 2014</a></li>
</ul>

<ul class="nav nav-pills">
    <?php foreach($lists as $key => $list): ?>
        <li <?php if($key == $statut): ?>class="active"<?php endif; ?>><a href="<?php echo url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $key)) ?>"><?php echo $statuts_libelle[$key] ?> <span class="badge"><?php echo count($list) ?></span></a></li>
    <?php endforeach; ?>
</ul>

<div class="row" style="margin-top: 20px;">
    <di class="col-xs-12">
        <?php if(count($lists[$statut]) > 0): ?>
        <div class="list-group">
                <?php foreach($lists[$statut] as $drev): ?>
                    <a class="list-group-item" href="<?php echo url_for("drev_visualisation", array("id" => $drev->id, "service" => url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $statut)))) ?>">
                    <span class="col-xs-2 text-muted">
                    <?php if($drev->key[3]): ?>
                        <?php echo format_date($drev->key[3], "dd/MM/yyyy", "fr_FR"); ?>
                    <?php elseif($drev->key[2]): ?>
                        <?php echo format_date($drev->key[2], "dd/MM/yyyy", "fr_FR"); ?>
                    <?php elseif(!$drev->key[2] && $drev->key[4]): ?>
                        <?php echo $drev->key[4] ?>
                    <?php endif; ?>
                    </span>
                    <?php echo $drev->key[6] ?>&nbsp;-&nbsp;<span class="text-muted"><?php echo $drev->key[5] ?></span>
                    </a>
                <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Aucune déclaration</p>
        <?php endif; ?>
    </div>
</div>
