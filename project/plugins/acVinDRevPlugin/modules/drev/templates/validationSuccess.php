<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" class="form-inline" action="<?php echo url_for('drev_validation', $drev) ?>#engagements" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if(isset($form["date"])): ?>
    <div class="row">
        <?php if ($form["date"]->getError()): ?>
            <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
        <?php endif; ?>
        <div class="form-group <?php if ($form["date"]->getError()): ?>has-error<?php endif; ?>">
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
        <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>
    <?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form, 'dr' => $dr)); ?>
	<?php  if (!$drev->isPapier() && ! $sf_user->isAdmin() && count($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)) > 0): ?>
    	<?php include_partial('drev/engagements', array('drev' => $drev, 'validation' => $validation, 'form' => $form)); ?>
    <?php endif; ?>

    <?php if (DrevConfiguration::getInstance()->hasDegustation() && isset($form["date_degustation_voulue"])): ?>
        <h3>Controle</h3>
        <?php echo $form["date_degustation_voulue"]->renderError(); ?>
        <div class="form-group" style="margin-bottom: 20px;">
            Date de controle des vins souhaitée :
            <div class="input-group date-picker-week">
            <?php echo $form["date_degustation_voulue"]->render(array("class" => "form-control", "placeholder" => "Date souhaitée", "required" => "true")); ?>
            <div class="input-group-addon">
                <span class="glyphicon-calendar glyphicon"></span>
            </div>
            </div>
        </div>
    <?php endif ?>

    <div style="padding-top: 10px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo ($drev->isModificative())? url_for("drev_lots", $drev) : url_for("drev_revendication", array('sf_subject' => $drev, 'prec' => true)); ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <div class="btn-group">
                <?php if ($sf_user->hasDrevAdmin() && $drev->hasDocumentDouanier()): ?>
                <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default <?php if(!$drev->hasDocumentDouanier()): ?>disabled<?php endif; ?>" >
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
                </a>
                <?php endif; ?>
                <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF de la DRev
                </a>
            </div>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document-drev" data-target="#drev-confirmation-validation" <?php if($validation->hasErreurs() && $drev->isTeledeclare() && !$sf_user->hasDrevAdmin()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper" onclick="if ($('#validation-form')[0].reportValidity()){ $('#drev-confirmation-validation').modal('toggle') }"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
</form>
<?php include_partial('drev/popupConfirmationValidation', array('approuver' => true)); ?>
