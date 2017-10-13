<?php include_partial('travauxmarc/breadcrumb', array('travauxmarc' => $travauxmarc )); ?>
<?php include_partial('travauxmarc/step', array('step' => 'validation', 'travauxmarc' => $travauxmarc)) ?>

<div class="page-header">
    <h2>Validation de votre déclaration</h2>
</div>

<form id="validation-form" role="form" action="<?php echo url_for("travauxmarc_validation", $travauxmarc) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

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

    <?php if ($validation->hasPoints()): ?>
        <?php include_partial('travauxmarc/pointsAttentions', array('travauxmarc' => $travauxmarc, 'validation' => $validation)); ?>
    <?php endif; ?>

    <?php include_partial('travauxmarc/recap', array('travauxmarc' => $travauxmarc)); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("travauxmarc_distillation", $travauxmarc) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("travauxmarc_export_pdf", $travauxmarc) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#modal-confirmation-validation" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>

</form>

<?php include_partial('travauxmarc/popupConfirmationValidation'); ?>
