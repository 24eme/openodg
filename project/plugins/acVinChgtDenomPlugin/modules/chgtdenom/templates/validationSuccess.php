<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>
<?php include_partial('chgtdenom/step', array('step' => 'validation', 'chgtDenom' => $chgtDenom)) ?>


    <div class="page-header no-border">
      <h2><?php if ($chgtDenom->isDeclassement()): ?>Déclassement<?php else: ?>Changement de dénomination<?php endif; ?> <?php if (!$chgtDenom->isChgtTotal()): ?>partiel<?php endif; ?></h2>
      <h3><small></small></h3>
    </div>

    <?php echo include_partial('global/flash'); ?>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>



    <form role="form" action="<?php echo url_for("chgtdenom_validation", $chgtDenom) ?>" method="post" class="form-horizontal" id="validation-form">
      <?php if($sf_user->isAdmin()): ?>
        <?php include_partial('chgtdenom/recap', array('chgtDenom' => $chgtDenom, 'form' => $form)); ?>
      <?php else:?>
        <?php include_partial('chgtdenom/recap', array('chgtDenom' => $chgtDenom)); ?>
      <?php endif; ?>
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <div style="margin-top: 20px;" class="row row-margin row-button">
            <div class="col-xs-6">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_edition', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" id="btn-validation-document" data-toggle="modal" data-target="#confirmation-validation" class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
            </div>
        </div>
    </form>
<?php include_partial('chgtdenom/popupConfirmationValidation'); ?>
