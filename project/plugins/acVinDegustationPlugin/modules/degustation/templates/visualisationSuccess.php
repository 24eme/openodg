<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Suivi de dégustation</h2>
  <h3> <small></small></h3>
</div>

<?php include_partial('degustation/synthese', array('degustation' => $degustation, 'infosDegustation' => $infosDegustation)); ?>

<div class="row">
  <div class="col-xs-6">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">Prélèvements</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-8">
            <strong class="lead"><?php echo $infosDegustation["nbLotsRestantAPreleve"]; ?></strong> <strong>lots</strong> restant à prélever <br/><strong><span class="lead"><?php echo $infosDegustation["nbAdherentsLotsRestantAPreleve"]; ?></span> adhérents</strong> restant à prélever
            <br/>&nbsp;
          </div>
          <div class="col-xs-4 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_preleve', $degustation) ?>" >&nbsp;Prélévement des lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xs-6">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">Convocations</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <?php foreach ($infosDegustation["degustateurs"] as $college => $indicateurs): ?>
              <strong class="lead"><?php echo $indicateurs["confirmes"]; ?></strong> / <?php echo $indicateurs["total"]; ?> <strong><?php echo $college; ?></strong> confirmés<br/>
            <?php endforeach; ?>
          </div>
          <div class="col-xs-5 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_degustateurs_confirmation', $degustation) ?>" >&nbsp;Confirmation dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">Tables des lots</h2>
  </div>
  <div class="panel-body">
    <div class="row">
      <div class="col-xs-7">
        <strong class="lead"><?php echo $infosDegustation["nbTables"]; ?></strong> Tables</br>
        <strong class="lead"><?php echo ($infosDegustation["nbFreeLots"])? $infosDegustation["nbFreeLots"] : 'Auncun' ?></strong> <strong>lot<?php echo ($infosDegustation["nbFreeLots"]>1)? 's' : '' ?></strong> sans table
      </div>
      <div class="col-xs-5 text-right">
        <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_organisation_table', $degustation) ?>" >&nbsp;Répartition des lots par table&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">Présence des dégustateurs</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <strong class="lead"><?php echo $infosDegustation["nbDegustateursATable"]; ?></strong> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> <strong>présent<?php echo ($infosDegustation["nbDegustateursATable"]>1)? 's' : '' ?></strong> à une table<br/>
            <strong class="lead"><?php echo $infosDegustation["nbDegustateursSansTable"]; ?></strong> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> <strong>non attablé<?php echo ($infosDegustation["nbDegustateursSansTable"]>1)? 's' : '' ?></strong>
          </div>
          <div class="col-xs-5 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_presences', $degustation) ?>" >&nbsp;Présence des dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xs-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">Conformité des lots</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <strong class="lead"><?php echo $infosDegustation["nbLotsConformes"]; ?></strong> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> <?php echo ($infosDegustation["nbLotsConformes"]>1)? 'lots <strong>conformes</strong>' : 'lot <strong>conforme</strong>' ?><br/>
            <strong class="lead"><?php echo $infosDegustation["nbLotsNonConformes"]; ?></strong> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> <?php echo ($infosDegustation["nbLotsNonConformes"]>1)? 'lots <strong>non conformes</strong>' : 'lot <strong>non conforme</strong>' ?>
          </div>
          <div class="col-xs-5 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_resultats', $degustation) ?>" >&nbsp;Résultats lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
