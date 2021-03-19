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
<?php if (count($mouvements)): ?>
    <div class="row">
      <table class="table table-condensed table-striped">
        <thead>
          <th class="col-sm-1">Document</th>
          <th class="col-sm-1">N° Dossier</th>
          <th class="col-sm-1">N° Archive</th>
          <th class="col-sm-5">Appellation</th>
          <th class="col-sm-2">Dernière étape</th>
          <th class="col-sm-1 text-right">Detail</th>
        </thead>
        <tbody>
          <?php foreach($mouvements as $lotKey => $mouvement): ?>
                <tr>
                  <td>
                      <a href="<?php  echo url_for(strtolower($mouvement->value->document_type).'_visualisation', array('id' => $mouvement->value->document_id));  ?>">
                          <?php echo $mouvement->value->document_type;  ?>
                      </a>
                  </td>
                  <td><?php echo $mouvement->value->numero_dossier;  ?></td>
                  <td><?php echo $mouvement->value->numero_archive;  ?></td>
                  <td><?php  echo $mouvement->value->libelle;  ?></td>
                  <td><?php  echo Lot::$libellesStatuts[$mouvement->value->statut];  ?></td>
                  <td class="text-right"><a class="btn btn-xs btn-default" href="<?php  echo url_for('degustation_lot_historique', array('identifiant' => $etablissement->identifiant,'numero_dossier' => $mouvement->value->numero_dossier,'numero_archive' => $mouvement->value->numero_archive));  ?>">détail<span class="glyphicon glyphicon-chevron-right"></span></a></td>

              </tr>
                  <?php endforeach; ?>
              <tbody>
              </table>
          </div>
      <?php endif; ?>
