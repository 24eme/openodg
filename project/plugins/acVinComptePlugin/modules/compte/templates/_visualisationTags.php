<?php foreach ($compte->tags as $type_tag => $selected_tags) : ?>
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-xs-2 text-muted"><?php echo ucfirst($type_tag) ?>&nbsp;:</div>
            <div class="col-xs-10">
                <?php foreach ($selected_tags as $t): ?>
                    <?php $targs['tags'] = implode(',', array($type_tag . ':' . $t)); ?>
                    <div class="btn-group">
                        <a class="btn btn-sm <?php if($type_tag == "automatique"): ?>btn-link<?php endif; ?> <?php if($type_tag == "metier"): ?>btn-info<?php endif; ?> <?php if($type_tag == "manuel"): ?>btn-default<?php endif; ?>" href="<?php echo url_for('compte_search', $targs) ?>"><?php echo ucfirst(str_replace('_', ' ', $t)) ?></a>
                        <?php $targs['tag'] = $t; ?>
                        <?php $targs['q'] = $compte->identifiant ?>
                        <?php if ($type_tag == 'manuel'): ?><a class="btn btn-sm btn-default" href="<?php echo url_for('compte_removetag', $targs) ?>"><span class="glyphicon glyphicon-trash"></span></a><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
