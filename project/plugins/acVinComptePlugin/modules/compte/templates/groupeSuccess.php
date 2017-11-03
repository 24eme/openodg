<?php use_helper('Compte'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li>Groupes</li>
    <li class="active"><a href="<?php echo url_for('compte_groupe', array("groupeName" => $groupeName)); ?>"><?php echo str_replace('_',' ',$groupeName); ?></a></li>
</ol>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default">
          <div class="panel-heading">
              <div class="row">
                  <div class="col-xs-11 ">
                      <h4>Détail du groupe « <?php echo str_replace('_',' ',$groupeName); ?> »</h4>
                  </div>
                  <div class="col-xs-1 ">
                    <a href="<?php echo url_for('compte_search', array('tags' => $filtre)) ; ?>"><span class="glyphicon glyphicon-search"></span></a> &nbsp;
                    <a href="<?php echo url_for('compte_search_csv', array('tags' => $filtre)) ; ?>"><span class="glyphicon glyphicon-export"></span></a>
                  </div>
              </div>
            </div>
            <div class="panel-body">
              <div class="list-group" id="list-item">
              <?php foreach($results as $res): ?>
                <?php $data = $res->getData(); ?>
                      <?php $societe_informations = (isset($data['doc']['societe_informations'])) ? $data['doc']['societe_informations'] : null; ?>
                      <div class="list-group-item <?php if ($data['doc']['statut'] != 'ACTIF') echo 'disabled'; ?>">
                          <div class="row">
                          <div class="col-xs-8">
                              <?php if($data['doc']['compte_type'] == 'INTERLOCUTEUR'): ?><small class="text-muted"><span class="glyphicon glyphicon-calendar"></span> <?php if(isset($societe_informations['raison_sociale'])): echo $societe_informations['raison_sociale']; endif; ?></small><br/><?php endif; ?>
                              <span class="lead"><span class="<?php echo comptePictoCssClass($data['doc']) ?>"></span></span>
                              <a class="lead" href="<?php echo url_for('compte_visualisation', array('identifiant' => $data['doc']['identifiant'])); ?>"><?php echo $data['doc']['nom_a_afficher']; ?></a> <span class="text-muted"><?php echo $data['doc']['identifiant']; ?></span>
                              </span>
                         </div><div class="col-xs-4 text-right">
                               <small class="text-muted label label-primary"><?php echo $data['doc']['groupes'][$groupeName] ?></small>
                          </div>
                        </div>
                      </div>
              <?php endforeach; ?>
            </div>
        </div>
  </div>
</div>
