<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">
  <h2>Les lots de <?php echo $etablissement->getNom(); ?></h2>
</div>
<?php if (count($lots)): ?>
  <div class="row">
    <table class="table table-condensed table-striped">
      <thead>
        <th class="col-sm-1">N° Lot</th>
        <th class="col-sm-1">N° Dossier</th>
        <th class="col-sm-2">Date</th>
        <th class="col-sm-4">Appellation</th>
        <th class="col-sm-1 text-center">Dossier</th>
        <th class="col-sm-1 text-center">Degust.</th>
        <th class="col-sm-1 text-center">Table</th>
        <th class="col-sm-1 text-center">Dégusté</th>
      </thead>
      <tbody>
        <?php foreach($lots as $lotKey => $lotDocs): ?>
          <?php foreach($lotDocs as $id_doc => $lotSteps): ?>
            <?php foreach($lotSteps as $step => $lot): ?>
              <tr>
                <td><?php echo $lot->numero_dossier;  ?></td>
                <td><?php echo $lot->numero_archive;  ?></td>
                <td ><strong><?php echo Date::francizeDate($lot->date); ?></strong></td>
                <td><strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?><strong class="pull-right">&nbsp;<?php echo echoFloat($lot->volume); ?>&nbsp;hl</strong></small></td>
                <td class="text-center"><a class="btn btn-xs btn-success" href="<?php echo url_for($lot->dossier_type.'_visualisation',$lot->dossier_origine)?>"><?php echo $lot->dossier_libelle; ?></a></td>
                <td class="text-center" >
                  <?php if($lot->degustation): ?>
                    <a class="btn btn-xs btn-<?php echo $lot->degustation_color?>" href="<?php echo url_for($lot->degustation_step_route,$lot->degustation)?>"><?php echo $lot->degustation_libelle; ?></a>
                  <?php else: ?>
                    <span class="glyphicon glyphicon-remove-sign text-muted"></span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if(isset($lot->numero_table_step_route)): ?>
                    <a class="btn btn-xs btn-<?php echo $lot->numero_table_color?>"
                      href="<?php echo ($lot->numero_table)? url_for($lot->numero_table_step_route , array('id' => $lot->degustation->_id, 'numero_table' => $lot->numero_table))
                      : url_for($lot->numero_table_step_route, array('id' => $lot->degustation->_id)); ?>">
                      <?php echo ($lot->numero_table)? "Table ".$lot->numero_table : "Choisir"; ?>
                    </a>
                  <?php else: ?>
                    <span class="glyphicon glyphicon-remove-sign text-muted"></span>
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
                      <?php else: ?>
                        <span class="glyphicon glyphicon-remove-sign text-muted"></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
            <tbody>
            </table>
          <?php endif; ?>
        </div>
