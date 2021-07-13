<?php include_partial('conditionnement/breadcrumb', array('conditionnement' => $conditionnement )); ?>
<?php include_partial('conditionnement/step', array('step' => 'validation', 'conditionnement' => $conditionnement)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" class="form-inline" action="<?php echo url_for('conditionnement_validation', $conditionnement) ?>#engagements" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if(isset($form["date"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date"]->hasError()): ?>
                <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date"]->renderLabel("Date de réception du document :", array("class" => "col-xs-6 control-label")); ?>
            <div class="col-xs-6">
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

    <?php if($validation->hasPoints()): ?>
        <?php include_partial('conditionnement/pointsAttentions', array('conditionnement' => $conditionnement, 'validation' => $validation)); ?>
    <?php endif; ?>

    <?php if($sf_user->isAdmin()): ?>
      <?php include_partial('conditionnement/recap', array('conditionnement' => $conditionnement, 'form' => $form)); ?>
    <?php else:?>
      <?php include_partial('conditionnement/recap', array('conditionnement' => $conditionnement)); ?>
    <?php endif; ?>

	<?php  if (!$conditionnement->isPapier() && count($validation->getPoints(ConditionnementValidation::TYPE_ENGAGEMENT)) > 0): ?>
    	<?php include_partial('conditionnement/engagements', array('conditionnement' => $conditionnement, 'validation' => $validation, 'form' => $form)); ?>
    <?php endif; ?>

    <?php if (ConditionnementConfiguration::getInstance()->hasDegustation()): ?>
        <h3>Dégustation</h3>
        <div class="form-group" style="margin-bottom: 20px;">
            Les vins seront prêt à être dégustés à partir du :
            <div class="input-group">
            <?php echo date("d/m/Y"); ?>
            </div>
        </div>
    <?php endif ?>

    <div style="padding-top: 10px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("conditionnement_lots", $conditionnement); ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="button" id="btn-validation-document-conditionnement" data-toggle="modal" data-target="#conditionnement-confirmation-validation" <?php if($validation->hasErreurs() && $conditionnement->isTeledeclare() && !$sf_user->hasConditionnementAdmin()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
</form>
<?php include_partial('conditionnement/popupConfirmationValidation', array('approuver' => true)); ?>
