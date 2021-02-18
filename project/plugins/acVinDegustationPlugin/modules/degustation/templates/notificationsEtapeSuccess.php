<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_NOTIFICATIONS)); ?>


<div class="page-header no-border">
  <h2>Notifications aux opérateurs</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Notifications</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <div class="row">
              <div class="col-xs-7">
              </div>
                <div class="col-xs-12 text-right">
                  <a class="btn btn-default btn-sm" href="<?php echo url_for('degustation_notifications', $degustation) ?>" >&nbsp;Notifications des résultats&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
                </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="row row-button">
  <div class="col-xs-4"><a href="<?php echo url_for('degustation_resultats_etape', $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right"><a class="btn btn-primary btn-upper" href="<?php echo url_for('degustation') ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></div>
</div>
