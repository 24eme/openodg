<?php $hasManuel = false; ?>
<?php $modifiable = !isset($modifiable) || $modifiable; ?>
    <div style="margin-bottom: 10px;">
      <?php if (SocieteConfiguration::getInstance()->hasGroupes()): ?>
      <div class="row" style="margin-bottom: 10px;">
        <div class="col-xs-2 text-muted">Groupes&nbsp;:</div>
        <div class="col-xs-10">
            <?php foreach($compte->getGroupesSortedNom() as $key => $grp) : ?>
                <?php if($modifiable): ?>
              <div class="btn-group" style="padding-bottom : 3px;">
                <a class="btn btn-sm btn-default" href="<?php echo url_for('compte_groupe', array("groupeName" => str_replace('.','!',sfOutputEscaper::unescape($grp['nom'])))); ?>"><?php echo $grp['nom']; ?></a>
                <a class="btn btn-sm btn-primary" href="<?php echo url_for('compte_groupe', array("groupeName" => str_replace('.','!',sfOutputEscaper::unescape($grp['nom'])))); ?>"><?php echo $grp['fonction']; ?></a>
                <a class="btn btn-sm btn-default" href="<?php echo url_for('compte_removegroupe', array("groupeName" => str_replace('.','!',sfOutputEscaper::unescape($grp['nom'])), "identifiant" => $compte->identifiant, "retour" => "visu")); ?>"><span class="glyphicon glyphicon-trash"/></a>
              </div>
              <?php else: ?>
                  <div class="btn-group" style="padding-bottom : 3px;">
                    <button class="btn btn-sm btn-default"><?php echo $grp['nom']; ?></button>
                    <button class="btn btn-sm btn-primary"><?php echo $grp['fonction']; ?></button>
                  </div>
              <?php endif; ?>
              <br/>
            <?php endforeach; ?>
            <?php if(isset($formAjoutGroupe) && $modifiable): ?>
              <form id="form_ajout_groupe" method="GET" class="form-horizontal" action="<?php echo url_for('compte_addingroupe',array('identifiant'=> $compte->getIdentifiant())); ?>">
                  <?php echo $formAjoutGroupe->renderHiddenFields() ?>
                  <?php echo $formAjoutGroupe->renderGlobalErrors() ?>
                  <div class="btn-group">
                    <input type="hidden" name="compte_groupe_ajout[id_compte]" value="COMPTE-<?php echo $compte->identifiant;?>"/>
                    <div class="input-group input-group-sm col-xs-12">
                      <input id="ajout_groupe" name="groupe" class="tags form-control select2 select2permissifNoAjax" placeholder="Ajouter un le compte dans un groupe" data-choices='<?php echo json_encode(CompteClient::getInstance()->getAllTagsGroupes($compte->groupes),JSON_HEX_APOS); ?>'    type="text">
                      <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">&nbsp;<span class="glyphicon glyphicon-plus"></span></button>
                      </span>
                    </div>
                    <input type="hidden" name="retour" value="<?php echo url_for('compte_visualisation', $compte) ?>"/>
                </div>
              </form>
             <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php foreach ($compte->tags as $type_tag => $tags) : ?>
          <?php if ($type_tag === "groupes" || $type_tag === "manuel"): ?>
            <?php continue; ?>
          <?php endif ?>

          <div class="row" style="margin-bottom: 10px;">
            <div class="col-xs-2 text-muted">
              <?php echo ucfirst($type_tag) ?> :
            </div>
            <div class="col-xs-10">
              <?php foreach ($tags as $t): ?>
                <?php if (! $modifiable): ?>
                  <small style="margin-right: 5px"><?php echo ucfirst(str_replace('_', ' ', $t)) ?></small>
                  <?php continue ?>
                <?php endif ?>

                <div class="btn-group">
                  <?php $tagClasses = ["btn", "btn-sm"]; ?>
                  <?php if ($type_tag === "automatique") { $tagClasses[] = "btn-link"; } ?>

                  <a class="<?php echo implode(" ", $tagClasses) ?>"
                     href="<?php echo url_for('compte_search', ['tags' => implode(',', array($type_tag . ':' . $t))]); ?>"
                  >
                    <?php echo ucfirst(str_replace('_', ' ', $t)) ?>
                  </a>
                </div>
              <?php endforeach // tags => t ?>
            </div>
          </div>
      <?php endforeach // type_tag => tags ?>

      <div class="row" style="margin-bottom: 10px">
        <div class="col-xs-2 text-muted">Manuel :</div>
        <div class="col-xs-10">
          <?php $tagsManuels = array_key_exists("manuel", $compte->tags->getRawValue()->toArray(true, false)) ? $compte->tags["manuel"] : []; ?>
          <?php foreach ($tagsManuels as $t): ?>
            <?php if (! $modifiable): ?>
              <small style="margin-right: 5px"><?php echo ucfirst(str_replace('_', ' ', $t)) ?></small>
              <?php continue; ?>
            <?php endif ?>

            <div class="btn-group">
              <a class="btn btn-sm btn-default"
                 href="<?php echo url_for('compte_search', ['tags' => implode(',', array($type_tag . ':' . $t))]); ?>"
              >
                <?php echo ucfirst(str_replace('_', ' ', $t)) ?>
              </a>
              <a class="btn btn-sm btn-default"
                 href="<?php echo url_for('compte_removetag', [
                   'q' => "doc.identifiant:".$compte->identifiant,
                   'tag' => $t,
                   'retour'=>url_for('compte_visualisation', $compte)
                 ]) ?>"
              >
                <span class="glyphicon glyphicon-trash"></span>
              </a>
            </div>
          <?php endforeach ?>

          <?php if ($modifiable): ?>
            <div class="btn-group">
              <?php if ($compte->isSuspendu() || $compte->getSociete()->isSuspendu()): ?>
                <span class='text-muted'>Ajout de tag impossible pour un contact archivé</span>
              <?php else: ?>
                <form class="form-inline form_ajout_tag" action="<?php echo url_for('compte_addtag', ["q" => "doc.identifiant:".$compte->identifiant]); ?>" method="GET">
                  <div class="input-group-sm">
                    <input id="creer_tag" name="tag" class="p-0 tags select2 form-control select2permissifNoAjax" placeholder="Ajouter un tag (liste permissive)"
                           data-choices='<?php echo json_encode(CompteClient::getInstance()->getAllTagsManuel()); ?>'
                           type="text" required="required" />
                    <button class="btn btn-sm btn-default" type="submit"><span class="glyphicon glyphicon-plus"></span></button>
                  </div>
                  <input type="hidden" name="q" value="doc.identifiant:<?php echo $compte->identifiant;?>" />
                  <input type="hidden" name="retour" value="<?php echo url_for('compte_visualisation', $compte) ?>" />
                </form>
              <?php endif; ?>
            </div>
          <?php endif ?>
        </div>
      </div>

    </div>
