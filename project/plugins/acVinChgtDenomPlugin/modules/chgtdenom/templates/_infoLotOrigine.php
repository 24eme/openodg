<?php use_helper('Lot') ?>

<div class="alert col-sm-12 mb-4" role="alert" style="position: relative; background-color: #f8f8f8; border: 1px solid #e7e7e7; <?php if($opacity): ?>opacity: 0.7;<?php endif; ?>">
<?php if ($lot->hasDocumentOrigine()): ?>
  <a href="<?php  echo url_for('degustation_lot_modification', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id));  ?>" style="position: absolute; right:6px; top: 12px;" class="btn btn-sm btn-link transparence-sm btn-modifier-lot" title="Modifier le lot"><span class="glyphicon glyphicon-pencil"></span></a>
<?php endif; ?>
  <h4>
      <a href="<?php echo url_for('degustation_lot_historique',array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id)); ?>">
      Dossier <?php echo $lot->campagne; ?> n°&nbsp;<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°&nbsp;<strong><?php echo $lot->numero_archive; ?></strong>
      </a>
  </h4>

  <table class="table table-condensed" style="margin: 0;">
    <tbody>
      <tr>
        <td style="border: none;">Logement : <strong><?php echo $lot->numero_logement_operateur; ?></strong></td>
      </tr>
      <tr>
        <td style="border: none;">Produit : <strong><?php echo showProduitCepagesLot($lot) ?></strong></td>
      </tr>
      <tr>
        <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
      </tr>
    <?php if ($lot->adresse_logement): ?>
      <tr>
        <td style="border: none;">Adresse de prélèvement : <strong><?php echo $lot->adresse_logement; ?></strong></td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
