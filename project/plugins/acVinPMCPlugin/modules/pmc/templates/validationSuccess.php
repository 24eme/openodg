<?php include_partial('pmc/breadcrumb', array('pmc' => $pmc )); ?>
<?php include_partial('pmc/step', array('step' => 'validation', 'pmc' => $pmc)) ?>

<form role="form" class="form-horizontal" action="<?php echo url_for('pmc_validation', $pmc) ?>#engagements" method="post" id="validation-form">

<?php if ($pmc->type == PMCClient::TYPE_MODEL): ?>
<div class="page-header no-border" style="position:relative;">
    <h2>Validation de votre déclaration</h2>
    <?php if(isset($form["date"])): ?>
    <div class="form-group">
        <?php echo $form["date"]->renderError() ?>
        <?php echo $form["date"]->renderLabel("Date de réception du document :", array("class" => "col-sm-10 control-label")); ?>
        <div class="col-sm-2 pull-right">
            <div class="input-group date-picker">
                <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif ?>

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if($validation->hasPoints()): ?>
        <?php include_partial('pmc/pointsAttentions', array('pmc' => $pmc, 'validation' => $validation)); ?>
    <?php endif; ?>

    <?php if($sf_user->hasPMCAdmin()): ?>
      <?php include_partial('pmc/recap', array('pmc' => $pmc, 'form' => $form)); ?>
    <?php else:?>
      <?php include_partial('pmc/recap', array('pmc' => $pmc)); ?>
    <?php endif; ?>

	<?php  if (!$pmc->isPapier() && count($validation->getPoints(PMCValidation::TYPE_ENGAGEMENT)) > 0): ?>
    	<?php include_partial('pmc/engagements', array('pmc' => $pmc, 'validation' => $validation, 'form' => $form)); ?>
    <?php endif; ?>
    <hr />
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("pmc_lots", $pmc); ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="button" id="btn-validation-document" data-target="#pmc-confirmation-validation" <?php if($validation->hasErreurs() && $pmc->isTeledeclare() && (!$sf_user->isAdminODG() || $validation->hasFatales())): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper ">
                <span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider<?php if ($sf_user->isAdmin()): ?> et approuver<?php endif ?> la déclaration
            </button>
        </div>
    </div>
</form>
<?php include_partial('pmc/popupConfirmationValidation', array('approuver' => true, 'pmc' => $pmc)); ?>
