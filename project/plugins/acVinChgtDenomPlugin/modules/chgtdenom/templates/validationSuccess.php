<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>


    <div class="page-header no-border">
      <h2><?php if ($chgtDenom->isDeclassement()): ?>Déclassement<?php else: ?>Changement de dénomination<?php endif; ?> <?php if (!$chgtDenom->isTotal()): ?>partiel<?php endif; ?></h2>
      <h3><small></small></h3>
    </div>

    <?php echo include_partial('global/flash'); ?>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>


    <?php if($validation->hasPoints()): ?>
        <?php include_partial('chgtdenom/pointsAttentions', array('chgtDenom' => $chgtDenom, 'validation' => $validation)); ?>
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
            <div class="col-xs-4">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_edition', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
            </div>
            <div class="col-xs-4 text-center">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_delete', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-remove"></span> Supprimer la déclaration</a>
            </div>
            <div class="col-xs-4 text-right">
                <button type="button" id="btn-validation-document-chgtDenom" data-toggle="modal" data-target="#chgtDenom-confirmation-validation" class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
            </div>
        </div>
    </form>

<?php foreach($chgtDenom->lots as $k => $lot): ?>
  <?php if(!$chgtDenom->isValide()):
        $formL = new ChgtDenomLogementForm($lot->getRawValue());
  ?>
    <div class="modal fade" id="modal_lot_<?php echo $k ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form role="form" action="<?php echo url_for("chgtdenom_logement", array("sf_subject" => $chgtDenom, 'key' => "ind$k")) ?>" method="post" class="form-horizontal">
            <?php echo $formL->renderHiddenFields(); ?>
            <?php echo $formL->renderGlobalErrors(); ?>
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel">Modification du logement <strong><?php echo $lot->numero_logement_operateur ?></strong></h4>
            </div>
            <div class="modal-body">
              <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo $formL['numero_logement_operateur']->renderLabel("Nouveau logement", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-8">
                                  <?php echo $formL['numero_logement_operateur']->render(); ?>
                            </div>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-success pull-right">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
<?php include_partial('chgtdenom/popupConfirmationValidation'); ?>
