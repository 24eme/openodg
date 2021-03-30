<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <div class="pull-right">
      <?php if ($sf_user->hasDrevAdmin()): ?>
      <form method="GET" class="form-inline" action="">
          Campagne :
          <select class="select2SubmitOnChange form-control" name="campagne">
              <?php for($i=ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); $i > ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() - 5; $i--): ?>
                  <option <?php if($campagne == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i; ?>-<?php echo $i+1 ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
              <?php endfor; ?>
          </select>
          <button type="submit" class="btn btn-default">Changer</button>
      </form>
      <?php else: ?>
          <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?></span>
      <?php endif; ?>
    </div>
    <h2>Liste des manquements à traiter</h2>
</div>

<div class="row">
    <div class="form-group col-xs-10">
      <input id="hamzastyle" type="hidden" data-placeholder="Sélectionner un filtre" data-hamzastyle-container=".table_manquements" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle form-control">
    </div>
</div>

<br/>

<div class="row">
<table class="table table-condensed table_manquements" >
<thead>
    <th>Num. dossier</th>
    <th>Déclarant</th>
    <th>Appellation</th>
    <th>Manquement</th>
    <th>Traitement</th>
</thead>
<tbody>
<?php foreach($manquements as $keyLot => $m):
    $words = json_encode([$m->produit_libelle,$m->declarant_identifiant,Lot::$libellesConformites[$m->conformite],$m->declarant_nom,$m->numero_dossier,$m->millesime], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    ?>
    <tr class="<?php if($m->recours_oc): ?>list-group-item-warning<?php endif;?> hamzastyle-item" data-words='<?= $words ?>' >
        <td><span class="text-muted"><?php echo $m->numero_dossier; ?></span></td>
        <td><?php echo $m->declarant_nom; ?></td>
        <td><?php echo showProduitLot($m->getRawValue()) ?><small> - <span class="text-right"><?php echo formatFloat($m->volume); ?>&nbsp;hl</span></small></td>

        <td><?php echo ($m->conformite) ? Lot::$libellesConformites[$m->conformite] : null; ?><br/><small class="text-muted"><?php echo $m->motif; ?></small>
            <?php if($m->recours_oc): ?><span class="label label-warning">Dégust. OC</span><?php endif;?></td>
        <td>
            <div class="dropdown">
              <a class="btn btn-default btn-xs" href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $m->declarant_identifiant, 'unique_id' => $m->unique_id)) ?>">Historique&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
              </ul>
            </div>
        </td>
        <td>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php use_javascript('hamza_style.js'); ?>
