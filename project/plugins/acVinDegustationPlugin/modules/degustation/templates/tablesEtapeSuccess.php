<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TABLES)); ?>


<div class="page-header no-border">
  <h2>Organisation des tables</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Tables des échantillons</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
          <?php if(!intval($infosDegustation["nbTables"])): ?>
          <div class="row">
              <div class="col-xs-12 text">
                <p class="alert alert-warning">
                    <span class="glyphicon glyphicon-warning-sign"></span>&nbsp;Vous n'avez aucune table de prévue
                </p>
              </div>
          </div>
      <?php endif; ?>
        <div class="row">
          <div class="col-xs-12">
            <strong>Organisation des tables</strong>
            <br/>
            <br/>
          </div>
        </div>

        <div class="row">
          <div class="col-xs-8">
            <strong class="lead"><?php echo $infosDegustation["nbTables"]; ?></strong> Tables prévues :</br>
            <?php if($infosDegustation["nbTables"]): ?>
              <ul class="lots-by-table">
              <?php foreach ($degustation->getTablesWithFreeLots() as $numTable => $table): ?>
                <?php if(DegustationClient::getNumeroTableStr($numTable) !== false): ?>
                  <li>
                    <strong class="lead"><?php echo DegustationClient::getNumeroTableStr($numTable); ?></strong>
                    <strong><?php echo count($table->lots); ?> lots</strong>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
              </ul>
          <?php else: ?>
            <strong>Aucune tables</strong>
          <?php endif; ?>
          </br>
          <strong class="lead"><?php echo ($infosDegustation["nbFreeLots"])? $infosDegustation["nbFreeLots"] : 'Aucun' ?></strong> <strong>Échantillon<?php echo ($infosDegustation["nbFreeLots"]>1)? 's' : '' ?></strong> sans table
        </div>
        <div class="col-xs-12 text-right">
            <a id="btn_organisation_table" class="btn btn-default btn-sm" href="<?php echo url_for('degustation_organisation_table', $degustation) ?>" >&nbsp;Échantillons par table&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<div class="row row-button">
  <div class="col-xs-4"><a href="<?php echo url_for("degustation_prelevements_etape",$degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right">
    <a id="btn_suivant" class="btn btn-primary btn-upper" <?php echo (!intval($infosDegustation["nbTables"]))? 'disabled="disabled"' : ''; ?>
         href="<?php echo (intval($infosDegustation["nbTables"]))? url_for('degustation_anonymats_etape', $degustation) : "#"; ?>">Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>

  </div>
</div>
