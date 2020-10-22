<div class="alert alert-info col-sm-12" role="alert">
  <h4>Lot n° <strong><?php echo $lot->numero; ?></strong></h4>
  <table class="table table-condensed" style="margin: 0;">
    <tbody>
      <tr>
        <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
      </tr>
      <tr>
        <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="col-sm-12" style="margin-bottom: 20px;">
  <div class="text-center">
    <strong>Devient</strong><br />
    <span class="glyphicon glyphicon-chevron-down"></span>
  </div>
</div>

<?php if (!$lot->isChgtTotal()): ?>
  <div class="alert col-sm-6" style="background-color: #f8f8f8; border: 1px solid #e7e7e7;">
    <h4>Lot n° <strong><?php echo $lot->numero; ?></strong></h4>
    <table class="table table-condensed" style="margin: 0;">
      <tbody>
        <tr>
          <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
        </tr>
        <tr>
          <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume - $lot->changement_volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
        </tr>
      </tbody>
    </table>
  </div>
<?php endif; ?>
  <div class="alert col-sm-<?php if (!$lot->isChgtTotal()): ?>6<?php else: ?>12<?php endif; ?>" style="background-color: #f8f8f8; border: 1px solid #e7e7e7;">
    <h4>Lot n° <strong><?php echo $lot->numero; ?></strong><?php if (!$lot->isChgtTotal()): ?>bis<?php endif; ?></h4>
    <table class="table table-condensed" style="margin: 0;">
      <tbody>
        <tr>
          <?php if ($lot->isDeclassement()): ?>
          <td style="border: none;"> <strong>Produit déclassé</strong></td>
          <?php else: ?>
          <td style="border: none;">Produit : <strong><?php echo $lot->changement_produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
          <?php endif; ?>
        </tr>
        <tr>
          <td style="border: none;">Volume : <strong><?php echo ($lot->changement_volume > 0)? echoFloat($lot->changement_volume) : echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
        </tr>
      </tbody>
    </table>
  </div>
