<?php use_helper('Date') ?>

<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

<?php include_partial('parcellaireAffectation/step', array('step' => 'validation', 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<form role="form" action="<?php echo url_for('parcellaireaffectation_validation', $parcellaireAffectation) ?>" method="post" id="validation-form">
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
    
    <div class="page-header no-border">
        <h2>Déclaration d'affectation parcellaire de l'AOC de Côtes de Provence</h2>
    </div>

    <?php include_partial('parcellaireAffectation/recap', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

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
        	<a href="<?php echo url_for("parcellaireaffectation_affectations", $parcellaireAffectation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireaffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-primary">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#parcellaireaffectation-confirmation-validation" <?php if (isset($validation) && $validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider vos affectations</button>
        </div>
    </div>
    <?php if (!isset($validation) || !$validation->hasErreurs()): ?>
	<?php include_partial('parcellaireAffectation/popupConfirmationValidation', array('form' => $form)); ?>
	<?php endif; ?>
</form>
<?php if(isset($form["signataire"]) && $form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireaffectation-confirmation-validation').modal('show')
</script>
<?php endif; ?>
