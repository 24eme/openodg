<div class="alert col-sm-12" role="alert" style="background-color: #f8f8f8; border: 1px solid #e7e7e7; <?php if($opacity): ?>opacity: 0.7;<?php endif; ?>">
  <h4>Dossier <?php echo $lot->campagne; ?> n°&nbsp;<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°&nbsp;<strong><?php echo $lot->numero_archive; ?></strong></h4>
  <table class="table table-condensed" style="margin: 0;">
    <tbody>
      <tr>
        <td style="border: none;">Logement : <strong><?php echo $lot->numero_logement_operateur; ?></td>
      </tr>
      <tr>
        <td style="border: none;">Produit : <strong><?php echo showProduitLot($lot) ?></small></td>
      </tr>
      <tr>
        <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
      </tr>
    </tbody>
  </table>
</div>
