<?php $hasManuel = false; ?>
    <div style="margin-bottom: 10px;">
      <div class="row" style="margin-bottom: 10px;">
        <div class="col-xs-2 text-muted">Groupes&nbsp;:</div>
        <div class="col-xs-10">
            <?php foreach($compte->groupes as $nom => $fonction) : ?>
              <div class="btn-group">
                <a class="btn btn-sm btn-default" href="<?php echo url_for('compte_groupe', array("groupeName" => $nom)); ?>"><?php echo $nom; ?></a>
                <a class="btn btn-sm btn-primary" href="<?php echo url_for('compte_groupe', array("groupeName" => $nom)); ?>"><?php echo $fonction; ?></a>
              </div>
            <?php endforeach; ?>&nbsp;
            <?php if(!count($compte->groupes)): ?>
                <span class="text-muted">Aucun</span>
            <?php endif;?>
        </div>
      </div>
      <?php foreach ($compte->tags as $type_tag => $tags) :
        if ($type_tag == 'groupes') {continue;}
        ?>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-xs-2 text-muted"><?php echo ucfirst($type_tag) ?>&nbsp;:</div>
          <div class="col-xs-10">
            <?php foreach ($tags as $t): ?>
                <?php $targs['tags'] = implode(',', array($type_tag . ':' . $t)); ?>
                <div class="btn-group">
                    <a class="btn btn-sm <?php if($type_tag == "automatique"): ?>btn-link<?php endif; ?> <?php if($type_tag == "manuel"): ?>btn-default<?php endif; ?>"
                      href="<?php echo url_for('compte_search', $targs); ?>">
                      <?php echo ucfirst(str_replace('_', ' ', $t)) ?>
                    </a>
                    <?php $targs['tag'] = $t; ?>
                    <?php $targs['q'] = $compte->identifiant ?>
                    <?php if ($type_tag == 'manuel'): ?><a class="btn btn-sm btn-default" href="<?php echo url_for('compte_removetag', $targs) ?>"><span class="glyphicon glyphicon-trash"></span></a><?php endif; ?></span>
                </div>
            <?php endforeach; ?>
            <?php if($type_tag == 'manuel'): ?>
              <?php $hasManuel = true; ?>
                <div class="btn-group">
                  <form class="form_ajout_tag" action="<?php echo url_for('compte_addtag', array("q" => $compte->identifiant, "tags" => "")); ?>" method="GET">
                    <div class="input-group input-group-sm col-xs-12">
                      <input id="creer_tag" name="tag" class="tags form-control" placeholder="Ajouter un tag" type="text" />
                      <input type="hidden" name="q" value="<?php echo $compte->identifiant;?>"/>
                      <input type="hidden" name="tags" value=""/>
                      <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">&nbsp;<span class="glyphicon glyphicon-plus"></span></button>
                      </span>
                    </div>
                  </form>
                </div>
            <?php endif; ?>&nbsp;
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(!$hasManuel): ?>
      <div class="row" style="margin-bottom: 5px;">
        <div class="col-xs-2 text-muted">Manuel&nbsp;:</div>
        <div class="col-xs-10">
            <div class="btn-group">
              <form class="form_ajout_tag" action="<?php echo url_for('compte_addtag', array("q" => $compte->identifiant, "tags" => "")); ?>" method="GET">
                <div class="input-group input-group-sm col-xs-12">
                  <input id="creer_tag" name="tag" class="tags form-control" placeholder="Ajouter un tag" type="text" />
                  <input type="hidden" name="q" value="<?php echo $compte->identifiant;?>"/>
                  <input type="hidden" name="tags" value=""/>
                  <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">&nbsp;<span class="glyphicon glyphicon-plus"></span></button>
                  </span>
                </div>
              </form>
            </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
