<table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
  <thead>
    <tr>
      <th rowspan="2" style="width:35%"></th>
      <th rowspan="2" style="width:15%"><small>Synthèse</small></th>
      <th colspan="<?php echo count($synthese); ?>" style="width:20%"><small>Résultat</small></th>
    </tr>
    <tr>
      <th><small><?php echo $lotsDegustes[0]->isLibelleAcceptable() ? 'A' : 'C';?></small></th>
<?php if (count($synthese) > 2): ?>
    <th><small><?php echo $lotsDegustes[0]->isLibelleAcceptable() ? 'AD' : 'CD';?></small></th>
<?php endif; ?>
      <th><small><?php echo $lotsDegustes[0]->isLibelleAcceptable() ? 'NA' : 'NC';?></small></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th><small>Nombre de lots</small></th>
      <td><small><?php echo array_sum($synthese_somme['declarants']->getRawValue()) ?></small></td>
      <?php foreach ($synthese as $key => $s): ?>
      <td><small><?php echo array_sum($s['declarants']->getRawValue()); ?></small></td>
      <?php endforeach; ?>
    </tr>
    <tr>
      <th><small>Volumes total (hl)</small></th>
      <td><small><?php echo $synthese_somme['volume']; ?></small></td>
      <?php foreach ($synthese as $key => $s): ?>
      <td><small><?php echo $s['volume']; ?></small></td>
      <?php endforeach; ?>
    </tr>
    <tr>
      <th><small>Nombre d'opérateurs</small></th>
      <td><small><?php echo count($synthese_somme['declarants']) ?></small></td>
      <?php foreach ($synthese as $key => $s): ?>
      <td><small><?php echo count($s['declarants']); ?></small></td>
      <?php endforeach; ?>
    </tr>
  </tbody>
</table>
