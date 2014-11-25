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
                <?php foreach ($lists[$statut] as $doc): ?>
                            <a class="list-group-item col-xs-12 <?php if ($doc->key[2] && !$doc->key[3] && !$doc->key[6]): ?>list-group-item-success<?php endif; ?> <?php if ($doc->key[2] && !$doc->key[3] && $doc->key[6]): ?><?php endif; ?>" href="<?php echo url_for("admin_doc", array("id" => $doc->id, "service" => url_for('admin', array("doc_type" => $type, 'doc_campagne' => $campagne, 'doc_statut' => $statut)))) ?>">
                            <span class="col-xs-2 text-muted">
                                <?php if ($doc->key[3]): ?>
                                    <?php echo format_date($doc->key[3], "dd/MM/yyyy", "fr_FR"); ?>
                                <?php elseif ($doc->key[2]): ?>
                                    <?php echo format_date($doc->key[2], "dd/MM/yyyy", "fr_FR"); ?>
                                <?php elseif (!$doc->key[2] && $doc->key[4]): ?>
                                    <?php echo $doc->key[4] ?>
                                <?php endif; ?>
                            </span>
                            <span class="col-xs-6"><?php echo $doc->key[7] ?>&nbsp;-&nbsp;<span class="text-muted"><?php echo $doc->key[5] ?></span></span>
                            <?php if ($doc->key[2] && !$doc->key[3] && $doc->key[6]): ?>
                                <span class="text-warning col-xs-4 text-right"><?php echo $doc->key[6] ?>&nbsp;pièce(s) en attente</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucune déclaration</p>
        <?php endif; ?>
</div>
</div>
