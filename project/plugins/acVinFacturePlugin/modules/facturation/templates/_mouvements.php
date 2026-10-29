<a name="mouvements"></a>
<h3 style="margin-top: 30px;">Mouvements en attente de facturation</h3>
<table class="table table-bordered table-striped">
  <thead>
      <tr>
          <th class="col-xs-2">Document / Version</th>
          <th class="col-xs-1">Date</th>
          <th class="col-xs-5">Cotisation</th>
          <th class="col-xs-1">Quantite</th>
          <th class="col-xs-1">Prix unit.</th>
          <?php $displayTva = false;
            foreach ($mouvements as $keyMvt => $mvt):
              $valueMvt = (isset($mvt->value))? $mvt->value : $mvt;
               ?>
               <?php if ($valueMvt->tva > '0'): ?>
                   <?php $displayTva = true;?>
                   <th class="col-xs-1">Tva</th>
                   <?php break;?>
               <?php endif; ?>
          <?php endforeach;?>
          <th class="col-xs-1">Prix HT</th>
      </tr>
  </thead>
  <tbody>

<?php foreach ($mouvements as $keyMvt => $mvt):
    $valueMvt = (isset($mvt->value))? $mvt->value : $mvt;
     ?>
  <tr>
      <td><a href="<?php echo url_for("declaration_doc", array("id" => $mvt->id))?>" ><?php echo $valueMvt->type;?><?php if($valueMvt->version): ?>&nbsp;<?php echo $valueMvt->version;?><?php endif; ?>&nbsp;<?php echo $valueMvt->campagne;?></a></td>
      <td><?php echo format_date($valueMvt->date, "dd/MM/yyyy", "fr_FR"); ?></td>
      <td><?php echo $valueMvt->type_libelle ?> <?php echo $valueMvt->detail_libelle ?></td>
      <td class="text-right"><?php echo echoFloat($valueMvt->quantite); ?>&nbsp;<small class="text-muted"><?php if(isset($valueMvt->unite)): ?><?php echo $valueMvt->unite ?><?php else: ?>&nbsp;&nbsp;<?php endif; ?></small></td>
      <td class="text-right"><?php echo echoFloat($valueMvt->taux); ?>&nbsp;€</td>
      <?php if ($displayTva): ?>
          <td class="text-right"><?php echo echoFloat($valueMvt->tva * 100, 0, 0); ?>&nbsp;%</td>
      <?php endif;?>
      <td class="text-right"><?php echo echoFloat($valueMvt->taux * $valueMvt->quantite); ?>&nbsp;€</td>
  </tr>
<?php endforeach; ?>
<?php if(!count($mouvements)): ?>
      <tr>
          <td colspan="7">Aucun mouvement en attente de facturation</td>
      </tr>
<?php endif; ?>
</tbody>
</table>
