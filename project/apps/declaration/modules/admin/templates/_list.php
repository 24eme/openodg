<?php echo use_helper("Date"); ?>

<ul class="nav nav-tabs">
    <?php foreach($lists as $key_list => $list): ?>
    <li role="presentation" <?php if ($key_list == $current_key_list): ?>class="active"<?php endif ?>>
        <a href="<?php echo url_for('admin', array("docs" => $key_list)) ?>"><?php echo $key_list ?><br />
            &nbsp;<span class="glyphicon glyphicon-globe"></span>&nbsp;<?php echo $list['stats']['global']['nb_teledeclares'] ?>
            &nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo $list['stats']['global']['nb_papiers'] ?>
            <?php if($list['stats']['global']['nb_can_be_validate']): ?>
            &nbsp;<span class="label label-success"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;<?php echo $list['stats']['global']['nb_can_be_validate'] ?></span>
            <?php endif; ?>
        </a></li>
    <?php endforeach; ?>
</ul>

<ul class="nav nav-pills">
    <?php foreach ($lists[$current_key_list]["statuts"] as $key => $list): ?>
        <li <?php if ($key == $statut): ?>class="active"<?php endif; ?>><a href="<?php echo url_for('admin', array("docs" => $current_key_list, 'doc_statut' => $key)) ?>"><?php echo $statuts_libelle[$key] ?>
            &nbsp;<span class="glyphicon glyphicon-globe"></span>&nbsp;<?php echo $lists[$current_key_list]['stats'][$key]['nb_teledeclares'] ?>
            <?php if($lists[$current_key_list]['stats'][$key]['nb_papiers']): ?>
            &nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo $lists[$current_key_list]['stats'][$key]['nb_papiers'] ?>
            <?php endif; ?>
            <?php if($lists[$current_key_list]['stats'][$key]['nb_can_be_validate']): ?>
            &nbsp;<span class="label label-success"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;<?php echo $lists[$current_key_list]['stats'][$key]['nb_can_be_validate'] ?></span>
            <?php endif; ?>
            </a></li>
    <?php endforeach; ?>
</ul>

<div class="row" style="margin-top: 20px;">
    <di class="col-xs-12">
        <?php if (count($lists[$current_key_list]['statuts'][$statut]) > 0): ?>
            <div class="list-group">
                <?php foreach ($lists[$current_key_list]['statuts'][$statut] as $doc): ?>
                            <a class="list-group-item col-xs-12 <?php if ($doc->key[2] && !$doc->key[3] && !$doc->key[6]): ?>list-group-item-success<?php endif; ?> <?php if ($doc->key[2] && !$doc->key[3] && $doc->key[6]): ?><?php endif; ?>" href="<?php echo url_for("admin_doc", array("id" => $doc->id, "service" => url_for('admin', array("docs" => $key_list, 'doc_statut' => $statut)))) ?>">
                            <span class="col-xs-2 text-muted">
                                <?php if ($doc->key[2]): ?>
                                    <?php echo format_date($doc->key[2], "dd/MM/yyyy", "fr_FR"); ?><br />
                                <?php endif; ?>
                            </span>
                            <span class="col-xs-6"><?php if($doc->key[7]): ?>
                                    <span class="glyphicon glyphicon-file"></span>
                                <?php endif; ?><?php echo $doc->key[8] ?>&nbsp;-&nbsp;<span class="text-muted"><?php echo $doc->key[5] ?></span></span>
                            <?php if ($doc->key[2] && !$doc->key[3] && $doc->key[6]): ?>
                                <span class="text-warning col-xs-4 text-right"><?php echo $doc->key[6] ?>&nbsp;pièce(s) en attente</span>
                            <?php endif; ?>
                            <?php if ($doc->key[3]): ?>
                                <span class="text-success col-xs-4 text-right">Validé le <?php echo  format_date($doc->key[3], "dd/MM/yyyy", "fr_FR"); ?></span>
                            <?php endif; ?>
                            <?php if (!$doc->key[2]): ?>
                                <span class="text-warning col-xs-4 text-right">Étape <?php echo $doc->key[4] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucune déclaration</p>
        <?php endif; ?>
</div>
</div>
