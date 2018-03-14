<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'validation', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header no-border">
    <h2>Validation de vos parcelles irrigables sur votre exploitation</h2>
</div>

<?php if (isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('parcellaireIrrigable/pointsAttentions', array('parcellaireIrrigable' => $parcellaireIrrigable, 'validation' => $validation)); ?>
<?php endif; ?>

<form role="form" action="<?php echo url_for('parcellaireirrigable_validation', $parcellaireIrrigable) ?>" method="post" id="validation-form">
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
                <div class="input-group date-picker">
                    <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include_partial('parcellaireIrrigable/recap', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="form-group <?php if ($form["observations"]->hasError()): ?>has-error<?php endif; ?>">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo $form['observations']->renderError(); ?>
                        <?php echo $form['observations']->render(); ?>
                     </div>
                 </div>
             </div>
        </div>
   </div>
    <div style="padding-top: 10px;" class="row row-margin row-button">
        <div class="col-xs-4">
        	<a href="<?php echo url_for("parcellaireirrigable_irrigations", $parcellaireIrrigable) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireirrigable_export_pdf', $parcellaireIrrigable) ?>" class="btn btn-primary">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#parcellaireirrigable-confirmation-validation" <?php if (isset($validation) && $validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider vos parcelles irrigables</button>
        </div>
    </div>
    <?php if (!isset($validation) || !$validation->hasErreurs()): ?>
	<?php include_partial('parcellaireIrrigable/popupConfirmationValidation', array('form' => $form)); ?>
	<?php endif; ?>
</form>
<?php if($form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireirrigable-confirmation-validation').modal('show')
</script>
<?php endif; ?>
