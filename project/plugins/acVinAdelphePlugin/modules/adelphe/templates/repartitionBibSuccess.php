<?php use_helper('Float'); ?>
<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'repartition_bib', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Répartition du volume <small>conditionné en BIB</small></h2>
</div>

<p>Veuillez répartir votre volume conditonné</p>

<form action="<?php echo url_for("adelphe_repartition_bib", $adelphe) ?>" method="post" class="form-horizontal">

  <?php echo $form->renderHiddenFields(); ?>

  <div class="row row-margin">

    <div class="col-xs-12">
      <div class="form-group">
        <?php echo $form["conditionnement_bib"]->renderError(); ?>
        <label class="col-xs-4 control-label">Conditionnez vous du volume en BIB</label>
        <div class="col-xs-4" style="padding-right: 5px">
          <?php echo $form['conditionnement_bib']->render(); ?>
        </div>
      </div>
    </div>

    <div id="bloc_bib" style="display:none;">

      <div class="col-xs-12">
        <div class="form-group">
          <?php echo $form["repartition_bib"]->renderError(); ?>
          <label class="col-xs-4 control-label">Connaissez vous la répartition BIB / Bouteille</label>
          <div class="col-xs-4" style="padding-right: 5px">
            <?php echo $form['repartition_bib']->render(); ?>
          </div>
        </div>
      </div>

      <div id="bloc_repartition" style="display:none;">

        <div class="col-xs-12">
          <div class="form-group">
            <label class="col-xs-4 control-label">Votre volume conditonné total</label>
            <div class="col-xs-2 text-right" style="padding: 7px 18px 0 12px;" id="volume_conditionne_total" data-value="<?php echo $adelphe->volume_conditionne_total ?>"><strong><?php echo sprintFloat($adelphe->volume_conditionne_total) ?></strong></div>
            <div class="col-xs-1 text-left" style="padding: 7px 0 0;">
               <span class="text-muted">hl</span>
            </div>
          </div>
        </div>

        <div class="col-xs-12">
          <div class="form-group">
            <?php echo $form["volume_conditionne_bib"]->renderError(); ?>
            <label class="col-xs-4 control-label">Volume conditonné en BIB</label>
            <div class="col-xs-2" style="padding-right: 5px">
              <?php echo $form['volume_conditionne_bib']->render(); ?>
            </div>
            <div class="col-xs-1 text-left" style="padding: 7px 0 0;">
               <span class="text-muted">hl</span>
            </div>
          </div>
        </div>

        <div class="col-xs-12">
          <div class="form-group">
            <label class="col-xs-4 control-label">Ou</label>
          </div>
        </div>

        <div class="col-xs-12">
          <div class="form-group">
            <?php echo $form["taux_conditionne_bib"]->renderError(); ?>
            <label class="col-xs-4 control-label">Taux conditionné en BIB</label>
            <div class="col-xs-2" style="padding-right: 5px">
              <?php echo $form['taux_conditionne_bib']->render(); ?>
            </div>
            <div class="col-xs-1 text-left" style="padding: 7px 0 0;">
               <span class="text-muted">%</span>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
  <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("adelphe_volume_conditionne", $adelphe); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
    <div class="col-xs-6 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
  </div>
</form>
<script type="text/javascript">
  if(document.getElementById('adelphe_conditionnement_bib_1').checked) {
    document.getElementById('bloc_bib').style.display = 'block';
  }
  if(document.getElementById('adelphe_repartition_bib_1').checked) {
    document.getElementById('bloc_repartition').style.display = 'block';
  }
  document.getElementById('adelphe_conditionnement_bib_0').addEventListener('change', function(e) {
      document.getElementById('bloc_bib').style.display = 'none';
      document.getElementById('adelphe_repartition_bib_0').checked = true;
  });
  document.getElementById('adelphe_conditionnement_bib_1').addEventListener('change', function(e) {
      document.getElementById('bloc_bib').style.display = 'block';
  });
  document.getElementById('adelphe_repartition_bib_0').addEventListener('change', function(e) {
      document.getElementById('bloc_repartition').style.display = 'none';
  });
  document.getElementById('adelphe_repartition_bib_1').addEventListener('change', function(e) {
      document.getElementById('bloc_repartition').style.display = 'block';
  });
  document.getElementById('adelphe_volume_conditionne_bib').addEventListener('keyup', function(e) {
    const volTotal = document.getElementById('volume_conditionne_total').getAttribute('data-value');
    const vol = document.getElementById('adelphe_volume_conditionne_bib').value;
    const tx = vol / volTotal * 100;
    if (tx > 0) {
      document.getElementById('adelphe_taux_conditionne_bib').value = Math.round(tx);
    }
  });
  document.getElementById('adelphe_taux_conditionne_bib').addEventListener('keyup', function(e) {
    const volTotal = document.getElementById('volume_conditionne_total').getAttribute('data-value');
    const tx = document.getElementById('adelphe_taux_conditionne_bib').value;
    const vol =  volTotal * tx / 100;
    if (vol > 0) {
      document.getElementById('adelphe_volume_conditionne_bib').value = parseFloat(vol).toFixed(2);
    }
  });
</script>
