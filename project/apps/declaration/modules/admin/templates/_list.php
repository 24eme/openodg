<?php echo use_helper("Date"); ?>

<ul class="nav nav-tabs">
    <li role="presentation" <?php if ($type == "DRev" && $campagne == "2014"): ?>class="active"<?php endif ?>>
        <a href="<?php echo url_for('admin', array("doc_type" => "DRev", 'doc_campagne' => $campagne)) ?>">DRev <?php echo $campagne; ?></a></li>
    <li role="presentation" <?php if ($type == "DRevMarc" && $campagne == "2014"): ?>class="active"<?php endif ?>>
        <a href="<?php echo url_for('admin', array("doc_type" => "DRevMarc", 'doc_campagne' => $campagne)) ?>">DRev Marc <?php echo $campagne; ?></a></li>
</ul>

<ul class="nav nav-pills">
    <?php foreach ($lists as $key => $list): ?>
        <li <?php if ($key == $statut): ?>class="active"<?php endif; ?>><a href="<?php echo url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $key)) ?>"><?php echo $statuts_libelle[$key] ?> <span class="badge"><?php echo count($list) ?></span></a></li>
    <?php endforeach; ?>
</ul>

<div class="row" style="margin-top: 20px;">
    <di class="col-xs-12">
        <?php if (count($lists[$statut]) > 0): ?>
            <div class="list-group">
                <?php foreach ($lists[$statut] as $drev_or_drevmarc): ?>
                    <?php if ($drev_or_drevmarc->key[0] == "DRevMarc") : ?>
                        <a class="list-group-item" href="<?php echo url_for("drevmarc_visualisation", array("id" => $drev_or_drevmarc->id, "service" => url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $statut)))) ?>">
                        <?php endif; ?>
                        <?php if ($drev_or_drevmarc->key[0] == "DRev") : ?>
                            <a class="list-group-item" href="<?php echo url_for("drev_visualisation", array("id" => $drev_or_drevmarc->id, "service" => url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $statut)))) ?>">
                            <?php endif; ?>
                            <span class="col-xs-2 text-muted">
                                <?php if ($drev_or_drevmarc->key[3]): ?>
                                    <?php echo format_date($drev_or_drevmarc->key[3], "dd/MM/yyyy", "fr_FR"); ?>
                                <?php elseif ($drev_or_drevmarc->key[2]): ?>
                                    <?php echo format_date($drev_or_drevmarc->key[2], "dd/MM/yyyy", "fr_FR"); ?>
                                <?php elseif (!$drev_or_drevmarc->key[2] && $drev_or_drevmarc->key[4]): ?>
                                    <?php echo $drev_or_drevmarc->key[4] ?>
                                <?php endif; ?>
                            </span>
                            <?php echo $drev_or_drevmarc->key[6] ?>&nbsp;-&nbsp;<span class="text-muted"><?php echo $drev_or_drevmarc->key[5] ?></span>
                        </a>
                    <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucune déclaration</p>
        <?php endif; ?>
</div>
</div>
