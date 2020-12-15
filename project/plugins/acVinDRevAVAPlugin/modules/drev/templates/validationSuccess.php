<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" action="<?php echo url_for('drev_validation', $drev) ?>#engagements" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if(isset($form["date"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date"]->hasError()): ?>
                <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
            <div class="col-xs-4">
                <div class="input-group date-picker-all-days">
                    <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($validation->hasPoints()): ?>
        <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>
    <?php include_partial('drev/recap', array('drev' => $drev)); ?>
	<?php  if (!$drev->isPapier() && count($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)) > 0): ?>
    	<?php include_partial('drev/engagements', array('drev' => $drev, 'validation' => $validation, 'form' => $form)); ?>
    <?php endif; ?>
    <?php if(isset($form['commentaire'])): ?>
        <h3>Commentaire interne <small>(seulement visible par l'ODG)</small></h3>
        <?php echo $form['commentaire']->render(array('class' => 'form-control text-left', "")) ?>
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
            <?php if ($validation->hasErreurs() && $sf_user->isAdmin()): ?>
                <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#drev-confirmation-validation" class="btn btn-default btn-lg btn-upper" onclick="confirm('Êtes vous sûr de vouloir valider cette DRev avec des points bloquant ?')"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
            <?php else : ?>
                <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#drev-confirmation-validation" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
            <?php endif ?>
        </div>
    </div>
</form>
<?php include_partial('drev/popupConfirmationValidation'); ?>
