<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireIrrigable]); ?>
<?php else: ?>
    <?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>
<?php endif; ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'validation', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header no-border">
    <h2>Validation des parcelles irrigables sur l'exploitation</h2>
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

    <?php if (ParcellaireConfiguration::getInstance()->hasEngagements() && count($validation->getEngagements()) > 0) : ?>
    <br/>
        <h3> Engagement à ne pas irriguer</h3>
        <div class="alert" role="alert" id="engagements"  style="padding-top:0;">
            <div class="form-group">
                <?php foreach ($validation->getEngagements() as $engagement): ?>
                    <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?> has-error <?php endif; ?>">
                        <?php if ($form['engagement_' . $engagement->getCode()]->getError()): ?>
                            <div class="alert alert-danger" role="alert"> <?php echo $form['engagement_' . $engagement->getCode()]->renderError(); ?></div>
                        <?php endif; ?>
                        <div class="checkbox">
                            <label>
                                <?php echo $form['engagement_' . $engagement->getCode()]->render(); ?>
                                <?php echo $engagement->getMessage(); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>

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
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#parcellaireirrigable-confirmation-validation" <?php if (isset($validation) && $validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider votre déclaration</button>
        </div>
    </div>
    <?php if (!isset($validation) || !$validation->hasErreurs()): ?>
	<?php include_partial('parcellaireIrrigable/popupConfirmationValidation', array('form' => $form)); ?>
	<?php endif; ?>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireIrrigable]); ?>
<?php endif; ?>

<?php if(isset($form["signataire"]) && $form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireirrigable-confirmation-validation').modal('show')
</script>
<?php endif; ?>
