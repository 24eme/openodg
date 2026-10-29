<div class="panel panel-default" style="min-height: 160px">
  <div class="panel-heading">
    <h2 class="panel-title">Convocations des dégustateurs</h2>
  </div>
  <div class="panel-body">
    <div class="row">
      <div class="col-xs-7">
        <?php foreach ($infosDegustation["degustateurs"] as $college => $indicateurs): ?>
          <strong class="lead"><?php echo $indicateurs["confirmes"]; ?></strong> / <?php echo $indicateurs["total"]; ?> <strong><?php echo $college; ?></strong> confirmés<br/>
        <?php endforeach; ?>
      </div>
      <div class="col-xs-12 text-right">
        <a id="btn_confirmation_degustateurs" class="btn btn-default btn-sm" href="<?php echo url_for('degustation_degustateurs_confirmation', $degustation) ?>" >&nbsp;Confirmation dégustateurs&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
      </div>
    </div>
  </div>
</div>