<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Suivi de dégustation</h2>
  <h3> <small></small></h3>
</div>


<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-4">Prélèvements</div>
            <div class="col-xs-8 text-right">
              <div class="dropdown btn-group">
                <button class="btn btn-xs btn-default dropdown-toggle" type="button" data-toggle="dropdown">PDF&nbsp;&nbsp;<span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li><a href="<?php echo url_for('degustation_etiquette_pdf', $degustation) ?>">Étiquettes</a></li>
                  <li><a href="<?php echo url_for('degustation_fiche_echantillons_preleves_pdf', $degustation) ?>">Fiche tournée prélevement</a></li>
                </ul>
              </div>
            </div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-8">
            <div class="row">
              <div class="col-xs-6">
                <strong class="lead"><?php echo $infosDegustation["nbLots"]; ?></strong> <strong>lots au total</strong> prévus dans la dégustation<br/>
              </div>
              <div class="col-xs-6">
              </div>
            </div>
            <div class="row">
              <div class="col-xs-6">
                <strong class="lead"><?php echo $infosDegustation["nbLotsRestantAPrelever"]; ?></strong> <strong>lots</strong> restant à prélever chez
              </div>
              <div class="col-xs-6">
                <strong><span class="lead"><?php echo $infosDegustation["nbAdherentsLotsRestantAPrelever"]; ?></span> adhérents</strong>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-6">
                <strong class="lead"><?php echo $infosDegustation["nbLotsPreleves"]; ?></strong> <strong>lots</strong> déjà prélevés chez
              </div>
              <div class="col-xs-6">
                <strong><span class="lead"><?php echo $infosDegustation["nbAdherentsPreleves"]; ?></span> adhérents</strong>
              </div>
            </div>
          </div>
          <div class="col-xs-4 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_preleve', $degustation) ?>" >&nbsp;Prélévement des lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
<div class="col-xs-6">
<div class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">Tables des échantillons</h2>
  </div>
  <div class="panel-body">
    <div class="row">
      <div class="col-xs-7">
        <strong class="lead"><?php echo $infosDegustation["nbTables"]; ?></strong> Tables prévues :</br>
        <?php if($infosDegustation["nbTables"]): ?>
          <?php foreach ($degustation->getTablesWithFreeLots() as $numTable => $table): ?>
            <strong class="lead"><?php echo DegustationClient::getNumeroTableStr($numTable); ?></strong> <strong><?php echo count($table->lots); ?> lots</strong><?php if($numTable < count($degustation->getTablesWithFreeLots())):?>, <?php endif;?>
          <?php endforeach; ?>
          </br>
        <?php else: ?>
          <strong>Aucune tables</strong></br>
        <?php endif; ?>
        <strong class="lead"><?php echo ($infosDegustation["nbFreeLots"])? $infosDegustation["nbFreeLots"] : 'Aucun' ?></strong> <strong>Échantillon<?php echo ($infosDegustation["nbFreeLots"]>1)? 's' : '' ?></strong> sans table
      </div>
      <div class="col-xs-5 text-right">
        <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_organisation_table', $degustation) ?>" >&nbsp;Échantillons par table&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
      </div>
    </div>
  </div>
</div>
</div>
<div class="col-xs-6">
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
        <div class="col-xs-5 text-right">
          <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_degustateurs_confirmation', $degustation) ?>" >&nbsp;Confirmation dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
        </div>
      </div>
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
        <h2 class="panel-title">Conformité des échantillons</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <strong class="lead"><?php echo $infosDegustation["nbLotsConformes"]; ?></strong> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> <?php echo ($infosDegustation["nbLotsConformes"]>1)? 'échantillons <strong>conformes</strong>' : 'échantillon <strong>conforme</strong>' ?><br/>
            <strong class="lead"><?php echo $infosDegustation["nbLotsNonConformes"]; ?></strong> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> <?php echo ($infosDegustation["nbLotsNonConformes"]>1)? 'échantillons <strong>non conformes</strong>' : 'échantillon <strong>non conforme</strong>' ?>
          </div>
          <div class="col-xs-5 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_resultats', $degustation) ?>" >&nbsp;Résultats échantillons&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
