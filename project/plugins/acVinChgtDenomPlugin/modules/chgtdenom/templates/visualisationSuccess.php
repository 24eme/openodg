<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>


    <div class="page-header no-border">
      <h2><?php if ($lot->isDeclassement()): ?>Déclassement<?php else: ?>Changement de dénomination<?php endif; ?> <?php if ($lot->isChgtTotal()): ?>Total<?php else: ?>Partiel<?php endif; ?></h2>
      <h3><small></small></h3>
    </div>

    <?php include_partial('chgtdenom/recap', array('lot' => $lot)); ?>
    <?php if (isset($form)): ?>
    <form role="form" action="<?php echo url_for("chgtdenom_visualisation", $chgtDenom) ?>" method="post" class="form-horizontal" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
          <div class="row">
              <div class="col-md-12 text-right">
                <label>
                  <?php echo $form['deguster']->render() ?>
                  <?php echo $form['deguster']->renderLabel('A déguster') ?>
                </label>
              </div>
          </div>
          <div style="margin-top: 20px;" class="row row-margin row-button">
              <div class="col-xs-12 text-right">
                  <button type="submit" id="btn-validation-document" data-toggle="modal" data-target="#confirmation-validation" class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Approuver le changement</button>
              </div>
          </div>
      </form>
      <?php include_partial('chgtdenom/popupConfirmationApprobation'); ?>
    <?php endif; ?>
    <?php if ($chgtDenom->isApprouve()): ?>
    <p class="text-success text-right"><strong>Modification du lot approuvé par l'ODG le <?php echo format_date($chgtDenom->validation_odg, 'dd/MM/yyyy') ?></strong></p>
    <?php endif; ?>
