<div class="alert alert-info col-sm-12" role="alert">
  <h4>Dossier n°<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°<strong><?php echo $lot->numero_archive; ?></strong></h4>
  <table class="table table-condensed" style="margin: 0;">
    <tbody>
      <tr>
        <td style="border: none;">Logement : <strong><?php echo $lot->numero_cuve; ?></td>
      </tr>
      <tr>
        <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<?php echo $lot->millesime; ?>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
      </tr>
      <tr>
        <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
      </tr>
    </tbody>
  </table>
</div>
