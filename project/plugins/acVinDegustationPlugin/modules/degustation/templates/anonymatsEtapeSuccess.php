<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_ANONYMATS)); ?>


<div class="page-header no-border">
  <h2>Anonymats des lots de la degustation</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Anonymats</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <?php if(count($degustation->getLotsNonAttables())): ?>
        <div class="row">
            <div class="col-xs-12 text">
              <p class="alert alert-warning">
                  <span class="glyphicon glyphicon-warning-sign"> </span>
                  Vous avez <strong><?php $nb = count($degustation->getLotsNonAttables()); echo $nb==1 ? "$nb lot qui n'est pas attablé." : "$nb lots qui ne sont pas attablés."; ?> </strong>
              </p>
            </div>
        </div>
        <?php endif ?>
        <div class="row">
            <?php if($degustation->isAnonymized()): ?>
              <div class="col-xs-12">

                La dégustation est actuellement <strong>anonymisée</strong>.
                <br/>
            </div>

          <?php else: ?>
            <div class="col-xs-12">
              La dégustation n'est <strong>pas encore anonymisée</strong> actuellement.<br/>
            </div>
          <?php endif; ?>
          <?php if(!$degustation->isAnonymized()): ?>
            <div class="col-xs-12">
              Veuillez cliquer sur le bouton d'anonymisation ci-dessous pour effectuer l'anonymat.<br/>
            </div>
          <?php endif; ?>
          <div class="col-xs-12 text-right">
            <br/>
            <?php if($degustation->isAnonymized()): ?>
              <br/>
              <a class="btn btn-default btn-sm" style="opacity:0.5" href="<?php echo url_for('degustation_desanonymize', $degustation) ?>" onclick="confirm('Voulez-vous retirer l\'anonymat?')" >&nbsp;Retirer l'anonymat&nbsp;<span class="glyphicon glyphicon-eye-open"></span></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row row-button">
  <div class="col-xs-4">
      <?php if(!$degustation->isAnonymized()): ?>
          <a href="<?php echo url_for('degustation_tables_etape', $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
      <?php endif; ?>
  </div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right">
      <?php if(!$degustation->isAnonymized()): ?>
       <a class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_anonymize', $degustation) ?>" onclick="confirm('Voulez-vous confirmer l\'anonymat?')" >&nbsp;Anonymiser&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
   <?php else : ?>
      <a class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_commission_etape', $degustation) ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  <?php endif; ?>
  </div>
</div>
