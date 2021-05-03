<table class="table table-condensed table-striped">
<thead>
    <th class="col-xs-1">Date</th>
    <th class="col-xs-2 text-center">N°Dos. / Archive / Prov.</th>
    <th class="col-xs-3">Opérateur</th>
    <th class="col-xs-1 text-right">Volume</th>
    <th class="col-xs-4">Produit</th>
    <th class="col-xs-1">&nbsp;</th>
    <th class="col-xs-1">&nbsp;</th>
</thead>
<tbody>
<?php foreach($lots as $key => $lot): ?>
  <tr>
      <td><?php echo format_date($lot->date, "dd/MM/yyyy", "fr_FR");  ?></td>
      <td class="text-center">
          <?php echo $lot->numero_dossier;  ?> /
          <?php echo $lot->numero_archive;  ?> /
          <?php echo substr($lot->id_document, 0, 4); ?>
      </td>
      <td><?php echo $lot->declarant_nom; ?></td>
      <td class="text-right"><?php echo $lot->volume; ?>&nbsp;hl</td>
      <td>
          <?php echo showOnlyProduit($lot->getRawValue(), false) ?>
          <span class="text-muted">N°&nbsp;<?php echo $lot->numero_logement_operateur; ?></span><br/>
          <span class="text-muted"><?php echo showOnlyCepages($lot->getRawValue(), false) ?>&nbsp;</span>
      </td>
      <td>
          <?php echo showLotStatusCartouche($lot->statut); ?>
      </td>
      <td>
            <a class="btn btn-default btn-xs" href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id)) ?>">Historique&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
      </td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
