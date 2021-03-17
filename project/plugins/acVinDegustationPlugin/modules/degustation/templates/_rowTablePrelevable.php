<td><?php echo $lot->declarant_nom; ?></td>
<td><?php echo substr($lot->id_document, 0, 4) ?></td>
<td><?php echo $lot->numero_logement_operateur; ?></td>
<td><?php echo showProduitLot($lot->getRawValue()) ?></td>
<td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
