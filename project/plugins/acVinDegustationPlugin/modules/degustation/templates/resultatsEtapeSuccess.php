<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_RESULTATS)); ?>


<div class="page-header no-border">
  <h2>Conformité des échantillons / Présence des dégustateurs</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Conformité des échantillons</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <strong class="lead"><?php echo $infosDegustation["nbLotsConformes"]; ?></strong> <?php echo ($infosDegustation["nbLotsConformes"]>1)? 'échantillons <strong>conformes</strong>' : 'échantillon <strong>conforme</strong>' ?><br/>
            <strong class="lead"><?php echo $infosDegustation["nbLotsNonConformes"]; ?></strong> <?php echo ($infosDegustation["nbLotsNonConformes"]>1)? 'échantillons <strong>non conformes</strong>' : 'échantillon <strong>non conforme</strong>' ?>
          </div>
          <div class="col-xs-12 text-right">
            <a id="btn_resultats" class="btn btn-default btn-sm" href="<?php echo url_for('degustation_resultats', $degustation) ?>" >&nbsp;Résultats échantillons&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">Présence des dégustateurs</h2>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-xs-7">
            <strong class="lead"><?php echo $infosDegustation["nbDegustateursATable"]; ?></strong> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> <strong>présent<?php echo ($infosDegustation["nbDegustateursATable"]>1)? 's' : '' ?></strong> à une table<br/>
            <strong class="lead"><?php echo $infosDegustation["nbDegustateursSansTable"]; ?></strong> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> <strong>non attablé<?php echo ($infosDegustation["nbDegustateursSansTable"]>1)? 's' : '' ?></strong>
          </div>
          <div class="col-xs-12 text-right">
            <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_presences', $degustation) ?>" >&nbsp;Présence des dégustateurs&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

	<div class="row row-button">
				<div class="col-xs-4"><a href="<?php echo url_for("degustation_anonymats_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
				<div class="col-xs-4 text-center">
				</div>
				<div class="col-xs-4 text-right"><a id="btn_suivant" class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_notifications_etape', $degustation) ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></div>
		</div>
