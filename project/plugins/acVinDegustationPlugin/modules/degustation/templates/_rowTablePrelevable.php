<td><?php echo $lot->declarant_nom; ?></td>
<td>
  <a href="<?php  echo url_for(strtolower(strtok($lot->id_document, '-')).'_visualisation', array('id' => $lot->id_document));  ?>">
    <?php echo $lot->type_document; ?>
  </a>
</td>
<td><?php echo $lot->numero_logement_operateur; ?></td>
<td><?php echo showProduitLot($lot->getRawValue()) ?></td>
<td class="edit text-right">
  <?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small>
  <a title="Modifier le volume" href="<?php echo url_for("declaration_doc", ['id' => $lot->id_document_provenance]); ?>">
    <i class="glyphicon glyphicon-share-alt"></i>
  </a>
</td>
