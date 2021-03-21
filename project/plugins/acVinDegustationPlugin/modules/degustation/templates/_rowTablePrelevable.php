<td><?php echo $lot->declarant_nom; ?></td>
<td>
  <a href="<?php  echo url_for(strtolower(strtok($lot->id_document, '-')).'_visualisation', array('id' => $lot->id_document));  ?>">
    <?php echo $lot->provenance ?>
  </a>
</td>
<td><?php echo $lot->numero_logement_operateur; ?></td>
<td><?php echo showProduitLot($lot->getRawValue()) ?></td>
<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
