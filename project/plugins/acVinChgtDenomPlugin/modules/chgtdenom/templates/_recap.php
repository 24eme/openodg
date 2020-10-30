<div class="alert alert-info col-sm-12" role="alert">
  <h4>Logement n° <strong><?php echo $chgtDenom->getMvtLot()->numero; ?></strong></h4>
  <table class="table table-condensed" style="margin: 0;">
    <tbody>
      <tr>
        <td style="border: none;">Produit : <strong><?php echo $chgtDenom->getMvtLot()->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $chgtDenom->getMvtLot()->details; ?></small></td>
      </tr>
      <tr>
        <td style="border: none;">Volume : <strong><?php echo echoFloat($chgtDenom->getMvtLot()->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
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

<?php foreach($chgtDenom->lots as $lot): ?>
  <div class="alert col-sm-<?php if (count($chgtDenom->lots) == 1): ?>12<?php else: ?>6<?php endif; ?>" style="background-color: #f8f8f8; border: 1px solid #e7e7e7;">
    <h4>Logement n° <strong><?php echo $lot->numero; ?></strong></h4>
    <table class="table table-condensed" style="margin: 0;">
      <tbody>
        <tr>
          <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
        </tr>
        <tr>
          <td style="border: none;">Volume : <strong><?php echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
        </tr>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
