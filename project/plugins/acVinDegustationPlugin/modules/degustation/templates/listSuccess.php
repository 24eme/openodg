<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">
  <div class="pull-right">
      <?php if ($sf_user->hasDrevAdmin()): ?>
      <form method="GET" class="form-inline" action="">
          Campagne :
          <select class="select2SubmitOnChange form-control" name="campagne">
              <?php for($i=ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); $i > ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() - 5; $i--): ?>
                  <option <?php if($campagne == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
              <?php endfor; ?>
          </select>
          <button type="submit" class="btn btn-default">Changer</button>
      </form>
      <?php else: ?>
          <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?>-<?php echo $campagne + 1 ?></span>
      <?php endif; ?>
  </div>
  <h2>Historique des lots de <?php echo $etablissement->getNom(); ?> (<?php echo $campagne; ?>)</h2>
</div>
<?php if (count($lots)): ?>
  <div class="row">
    <table class="table table-condensed table-striped">
      <thead>
        <th class="col-sm-1">N° Lot</th>
        <th class="col-sm-1">N° Dossier</th>
        <th class="col-sm-1">Date</th>
        <th class="col-sm-3">Appellation</th>
        <th class="col-sm-1 text-center">Dossier</th>
        <th class="col-sm-1 text-center">Degust.</th>
        <th class="col-sm-1 text-center">Table</th>
        <th class="col-sm-1 text-center">Dégusté</th>
        <th class="col-sm-2 text-right"></th>
      </thead>
      <tbody>
        <?php foreach($lots as $lotKey => $lotDocs): ?>
          <?php foreach($lotDocs as $id_doc => $lot): ?>
              <tr>
                <td><?php echo $lot->numero_archive;  ?></td>
                <td><?php echo $lot->numero_dossier;  ?></td>
                <td ><strong><?php echo Date::francizeDate($lot->date); ?></strong></td>
                <td><strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?><strong class="pull-right">&nbsp;<?php echo echoFloat($lot->volume); ?>&nbsp;hl</strong></small></td>
                <td class="text-center"><a class="btn btn-xs btn-success" href="<?php echo url_for($lot->dossier_type.'_visualisation',$lot->dossier_origine)?>"><?php echo $lot->dossier_libelle; ?></a></td>
                <td class="text-center" >
                  <?php if($lot->degustation): ?>
                    <a class="btn btn-xs btn-<?php echo $lot->degustation_color?>" href="<?php echo url_for($lot->degustation_step_route,$lot->degustation)?>"><?php echo $lot->degustation_libelle; ?></a>

                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if(isset($lot->numero_table_step_route)): ?>
                    <a class="btn btn-xs btn-<?php echo $lot->numero_table_color?>"
                      href="<?php echo ($lot->numero_table)? url_for($lot->numero_table_step_route , array('id' => $lot->degustation->_id, 'numero_table' => $lot->numero_table))
                      : url_for($lot->numero_table_step_route, array('id' => $lot->degustation->_id)); ?>">
                      <?php echo ($lot->numero_table)? "Table ".DegustationClient::getNumeroTableStr($lot->numero_table) : "Choisir"; ?>
                    </a>
                  <?php endif; ?>

                </td>
                <td class="text-center">
                  <?php if(isset($lot->resultat_step_route)): ?>
                    <a class="btn btn-xs btn-<?php echo $lot->resultat_color ?>"
                      href="<?php echo url_for($lot->resultat_step_route , array('id' => $lot->degustation->_id, 'numero_table' => $lot->numero_table))."#".$lot->degustation_anchor; ?>">
                      <?php if(is_null($lot->conformite)):
                        echo "Resultat";
                        elseif($r = Lot::$shortLibellesConformites[$lot->conformite]):
                          echo $r;
                          else :
                            echo "Conforme";
                          endif;
                          ?>
                        </a>
                      <?php endif; ?>
                    </td>
                    <td class=" text-right">
                      <?php if($lot->chgtdenom): ?>
                      <a class="btn btn-xs btn-success"
                      href="<?php echo  url_for('chgtdenom_visualisation' , array('id' => $lot->chgtdenom))  ?>">ChgDenom&nbsp;</a>
                    <?php endif; ?>
                      <a class="btn btn-xs btn-default"
                      href="<?php echo  url_for('degustation_lot' , array('id' => preg_replace("/^([0-9]{5}).+/","$1",$lot->numero_archive), 'campagne' => $lot->campagne))  ?>">detail&nbsp;<span class="glyphicon glyphicon-chevron-right"></span>
                    </a>
                  </td>
                  </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tbody>
            </table>
          <?php endif; ?>
        </div>
