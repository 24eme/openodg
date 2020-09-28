<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Suivie dégustation</h2>
  <h3><?php echo $degustation->getLieuNom(); ?> <small><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H:m", "fr_FR") ?></small></h3>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Prélevements</h3>
    <div class="well">
    <?php echo $infosDegustation["nbLotsRestantAPreleve"]; ?> / <?php echo $infosDegustation["nbLots"]; ?> lots restant à prélevés<br/>
    de <?php echo $infosDegustation["nbAdherentsLotsRestantAPreleve"]; ?> adhérents
    </div>
	</div>
  <div class="col-xs-12 text-right">
    <a class="btn btn-default" href="<?php echo url_for('degustation_preleve', $degustation) ?>" >&nbsp;Prélévement des lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  </div>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Convocations</h3>
      <div class="well">
      <?php foreach ($infosDegustation["degustateurs"] as $college => $indicateurs): ?>
        <?php echo $indicateurs["confirmes"]; ?> / <?php echo $indicateurs["total"]; ?> <?php echo $college; ?> confirmés<br/>
      <?php endforeach; ?>
      </div>
	</div>
  <div class="col-xs-12 text-right">
    <a class="btn btn-default" href="<?php echo url_for('degustation_degustateurs_confirmation', $degustation) ?>" >&nbsp;Confirmation dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  </div>
</div>


<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Tables des lots</h3>
    <div class="well">
    <?php echo $infosDegustation["nbTables"]; ?> Tables</br>
    <?php echo $infosDegustation["nbFreeLots"] ?> lots sans table
  </div>
	</div>
  <div class="col-xs-12 text-right">
    <a class="btn btn-default" href="<?php echo url_for('degustation_organisation_table', $degustation) ?>" >&nbsp;Répartition des lots par table&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  </div>
</div>

<div class="row row-condensed">
	<div class="col-xs-12">
    <h3>Présence des dégustateurs</h3>
    <div class="well">
    <?php echo $infosDegustation["nbDegustateursATable"]; ?> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> présent à une table<br/>
    <?php echo $infosDegustation["nbDegustateursSansTable"]; ?> / <?php echo $infosDegustation["nbDegustateursConfirmes"]; ?> non attablés
	</div>
</div>
  		<div class="col-xs-12 text-right">
  			<a class="btn btn-default" href="<?php echo url_for('degustation_presences', $degustation) ?>" ><span class=" glyphicon glyphicon-user"></span>&nbsp;&nbsp;Présence des dégustateurs&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  		</div>

</div>


<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Conformités des lots</h3>
    <div class="well">
    <?php echo $infosDegustation["nbLotsConformes"]; ?> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> lots conformes<br/>
    <?php echo $infosDegustation["nbLotsNonConformes"]; ?> / <?php echo $infosDegustation["nbLotsDegustes"]; ?> lots non conformes
    </div>
	</div>
  <div class="col-xs-12 text-right">
    <a class="btn btn-default" href="<?php echo url_for('degustation_resultats', $degustation) ?>" ><span class="glyphicon glyphicon-glass"></span>&nbsp;&nbsp;Résultats lots&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  </div>
</div>

<?php //include_partial('degustation/recapDegustation', array('degustation' => $degustation)); ?>
