<?php include_partial('tirage/step', array('step' => 'validation', 'tirage' => $tirage)) ?>
<div class="page-header">
    <h2>Validation de votre déclaration de Tirage A.O.C. Crémant d'Alsace</h2>
</div>

<form id="validation-form" role="form" action="<?php echo url_for("tirage_validation", $tirage) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

   
    <?php if ($validation->hasPoints()): ?>
        <?php include_partial('tirage/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>

    <div class="row row-margin">
        <div class="col-xs-12">
            <?php include_partial('tirage/recap', array('tirage' => $tirage)); ?>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("tirage_lots", $tirage) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("tirage_export_pdf", $tirage) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#tirage-confirmation-validation" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>

</form>
<?php include_partial('tirage/popupConfirmationValidation'); ?>