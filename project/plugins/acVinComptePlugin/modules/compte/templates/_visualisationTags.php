<?php $hasManuel = false; ?>
    <div style="margin-bottom: 10px;" class="row">
      <?php foreach ($compte->tags as $type_tag => $selected_tags) : ?>
        <div class="col-xs-2 text-muted"><?php echo ucfirst($type_tag) ?>&nbsp;:</div>
            <div class="col-xs-10">
                <?php foreach ($selected_tags as $t): ?>
                    <?php $targs['tags'] = implode(',', array($type_tag . ':' . $t)); ?>
                    <div class="btn-group">
                        <a class="btn btn-sm <?php if($type_tag == "automatique"): ?>btn-link<?php endif; ?> <?php if($type_tag == "groupes"): ?>btn-default<?php endif; ?> <?php if($type_tag == "manuel"): ?>btn-default<?php endif; ?>"
                          href="<?php if($type_tag == 'groupes'){ echo url_for('compte_groupes', array("groupeName" => $t)); }else{ echo url_for('compte_search', $targs); } ?>">
                          <?php echo ucfirst(str_replace('_', ' ', $t)) ?>
                        </a>
                        <?php $targs['tag'] = $t; ?>
                        <?php $targs['q'] = $compte->identifiant ?>
                        <?php if ($type_tag == 'manuel'): ?><a class="btn btn-sm btn-default" href="<?php echo url_for('compte_removetag', $targs) ?>"><span class="glyphicon glyphicon-trash"></span></a><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <?php if($type_tag == 'manuel'): ?>
              <?php $hasManuel = true; ?>
              <div class="btn-group">
                <form class="form_ajout_tag" action="<?php echo url_for('compte_addtag', array("q" => $compte->identifiant, "tags" => "")); ?>" method="GET">
                  <div class="input-group input-group-sm col-xs-12">
                    <input id="creer_tag" name="tag" class="tags form-control" type="text" />
                    <input type="hidden" name="q" value="<?php echo $compte->identifiant;?>"/>
                    <input type="hidden" name="tags" value=""/>
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="submit">&nbsp;<span class="glyphicon glyphicon-plus"></span></button>
                    </span>
                  </div>
                </form>
              </div>
            <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if(!$hasManuel): ?>
        <div class="col-xs-2 text-muted">Manuel&nbsp;:</div>
        <div class="col-xs-10">
            <div class="btn-group">
              <form class="form_ajout_tag" action="<?php echo url_for('compte_addtag', array("q" => $compte->identifiant, "tags" => "")); ?>" method="GET">
                <div class="input-group input-group-sm col-xs-12">
                  <input id="creer_tag" name="tag" class="tags form-control" type="text" />
                  <input type="hidden" name="q" value="<?php echo $compte->identifiant;?>"/>
                  <input type="hidden" name="tags" value=""/>
                  <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">&nbsp;<span class="glyphicon glyphicon-plus"></span></button>
                  </span>
                </div>
              </form>
            </div>
        </div>
      <?php endif; ?>
    </div>
