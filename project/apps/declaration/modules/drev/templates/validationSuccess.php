<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" action="<?php echo url_for('drev_validation', $drev) ?>#engagements" method="post" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php if($validation->hasPoints()): ?>
            <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
        <?php endif; ?>
        <?php include_partial('drev/recap', array('drev' => $drev)); ?>
    	<?php  if (count($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)) > 0): ?>
        	<?php include_partial('drev/engagements', array('drev' => $drev, 'validation' => $validation, 'form' => $form)); ?>
        <?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <?php if(!$drev->isNonConditionneur()): ?>
                <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
            <?php else: ?>
                <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#drev-confirmation-validation" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
</form>
<?php include_partial('drev/popupConfirmationValidation'); ?>