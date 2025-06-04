<?php use_helper('Date') ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<ol class="breadcrumb hidden-print">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $campagne)); ?>" ><?php echo $campagne ?></a>
</ol>

<?php if ($sf_user->hasDrevAdmin()): ?>
<div class="hidden-print">
<?php include_partial('etablissement/formChoice', array('form' => $formEtablissement, 'action' => url_for('degustation_etablissement_selection'))); ?>
</div>
<?php endif; ?>

<div class="page-header no-border">
  <div class="pull-right">
      <?php if ($sf_user->hasDrevAdmin()): ?>
      <form method="GET" class="form-inline hidden-print" action="">
          Campagne :
          <select class="select2SubmitOnChange form-control" name="campagne">
              <?php foreach($campagnes as $campagne_i): ?>
                  <option <?php if($campagne == $campagne_i): ?>selected="selected"<?php endif; ?> value="<?php echo $campagne_i; ?>"><?php echo $campagne_i; ?></option>
              <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-default">Changer</button>
      </form>
      <?php else: ?>
          <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?></span>
      <?php endif; ?>
  </div>
  <h2>Historique des lots de <?php echo $etablissement->getNom(); ?> (<?php echo $campagne; ?>)</h2>
</div>

<?php if(!$sf_user->hasCredential(AppUser::CREDENTIAL_OI)): ?>
<h3>Synthèse de la commercialisation</h3>

<?php include_component('degustation', 'syntheseCommercialise', ['identifiant' => $etablissement->identifiant, 'campagnes' => [$campagne], 'region' => $sf_user->getRegion()]) ?>

<?php endif; ?>

<?php if (count($mouvements)): ?>
      <table class="table table-condensed table-striped">
        <thead>
          <th class="col-sm-1">Date</th>
          <th class="col-sm-1 hidden-print">Campagne</th>
          <th class="col-sm-1 hidden-print">Origine</th>
          <th class="col-sm-2 text-center">N°&nbsp;Dossier /
          N°&nbsp;Archive</th>
          <th class="col-sm-4">Libellé</th>
          <th class="col-sm-1 text-right">Volume</th>
          <?php if ($sf_user->hasDrevAdmin()): ?>
          <th class="col-sm-1 text-center">Document</th>
          <?php endif; ?>
          <th class="col-sm-2">Dernière&nbsp;étape</th>
          <th class="col-sm-1 text-right hidden-print"></th>
        </thead>
        <tbody>
          <?php foreach($mouvements as $lotKey => $mouvement): ?>
                <tr>
                  <td><?php echo format_date($mouvement->value->date, "dd/MM/yyyy", "fr_FR");  ?></td>
                  <td class="hidden-print"><?php echo $mouvement->value->campagne;  ?></td>
                  <td class="hidden-print"><?php echo clarifieTypeDocumentLibelle($mouvement->value->initial_type);  ?></td>
                  <td class="text-center"><?php echo $mouvement->value->numero_dossier;  ?> /
                  <?php echo $mouvement->value->numero_archive;  ?></td>
                  <td><?php  echo str_replace(array("(", ")"), array("<span class='text-muted'> - ", "</span>"), $mouvement->value->libelle);  ?></td>
                  <td class="text-right"><?php echo echoFloat($mouvement->value->volume);  ?>&nbsp;<small class="text-muted">hl</span></td>
                  <?php if ($sf_user->hasDrevAdmin()): ?>
                  <td class="text-center">
                      <a href="<?php  echo url_for(strtolower($mouvement->value->document_type).'_visualisation', array('id' => $mouvement->value->document_id));  ?>">
                          <?php echo $mouvement->value->document_type;  ?>
                      </a>
                  </td>
                  <td>
                      <?php  echo showLotStatusCartouche($mouvement->value, false);  ?>&nbsp;<?php  echo showSummerizedLotPublicStatusCartouche($mouvement->value, true);  ?>
                  <?php else: ?>
                  </td>
                  <td><?php echo showLotPublicStatusCartouche($mouvement->value, false);  ?></td>
                  <?php endif; ?>
                  <td class="text-right hidden-print">
                  <?php if ($sf_user->isAdminODG() || !MouvementLotHistoryView::isWaitingLotNotification($mouvement->value)): ?>
                      <a class="btn btn-xs btn-default btn-historique" href="<?php  echo url_for('degustation_lot_historique', array('identifiant' => $etablissement->identifiant, 'unique_id' => $mouvement->value->lot_unique_id));  ?>">Historique&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
                  <?php endif; ?>
                  </td>

              </tr>
                  <?php endforeach; ?>
              <tbody>
              </table>
      <?php endif; ?>
