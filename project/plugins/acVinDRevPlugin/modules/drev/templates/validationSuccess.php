<?php use_helper('Date') ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" class="form-horizontal" action="<?php echo url_for('drev_validation', $drev) ?>#engagements" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if($validation->hasPoints()): ?>
        <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>
    <?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form, 'dr' => $dr)); ?>
	<?php  if (count($validation->getEngagements()) > 0): ?>
    	<?php include_partial('drev/engagements', array('drev' => $drev, 'validation' => $validation, 'form' => $form)); ?>
    <?php elseif($sf_user->isAdmin()) : ?>
        <?php if($drev->exist('documents') && count($drev->documents->toArray(true, false)) ): ?>
            <hr />
            <h3>&nbsp;Engagement(s)&nbsp;</h3>
            <?php foreach($drev->documents as $docKey => $doc): ?>
                    <p>&nbsp;<span style="font-family: Dejavusans">☑</span>
                <?php
                    if($doc->exist('libelle') && $doc->libelle):
                        $libelle = preg_replace("#&gt;#",">",$doc->libelle);
                        $libelle = preg_replace("#&lt;#","<",$libelle);
                        echo($libelle);
                    else:
                        echo($drev->documents->getEngagementLibelle($docKey));
                    endif;
                ?></p>

            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php if($sf_user->isAdmin()): ?>
        <p id="notNeedToEngage" class="hidden"></p>
        <hr />
        <h3>Validation</h3>
<?php if (isset($form["date_depot"])): ?>
        <?php echo $form["date_depot"]->renderError(); ?>
        <div class="form-group" style="margin-bottom: 20px;">
            <?php echo $form["date_depot"]->renderLabel("Date de dépot ou de réception :", array("class" => "col-xs-3 control-label")); ?>
            <div class="input-group date-picker-week col-xs-3">
            <?php echo $form["date_depot"]->render(array("class" => "form-control", "placeholder" => "", "required" => "true")); ?>
            <div class="input-group-addon">
                <span class="glyphicon-calendar glyphicon"></span>
            </div>
            </div>
            <?php echo $form["saisie_papier"]->renderLabel("Saisie papier :", array("class" => "col-xs-3 control-label")); ?>
            <div class="">
            <?php echo $form["saisie_papier"]->render(array("class" => "checkbox mt-2", "placeholder" => "")); ?>
            </div>
        </div>
<?php elseif($drev->isTeledeclare()): ?>
<p>DRev télédéclarée signée le <?php echo format_date($drev->getDateDepot(), "dd/MM/yyyy", "fr_FR"); ?></p>
<?php endif; ?>
    <?php endif; ?>
    <hr />
    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo ($drev->isModificative())? url_for("drev_lots", $drev) : url_for("drev_revendication", array('sf_subject' => $drev, 'prec' => true)); ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <div class="btn-group">
                <?php if ($sf_user->hasDrevAdmin() && $drev->getDocumentDouanier()): ?>
                <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default" >
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
                </a>
                <?php endif; ?>
                <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF de la DRev
                </a>
            </div>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-target="#drev-confirmation-validation" <?php if($validation->hasErreurs() && $drev->isTeledeclare() && (!$sf_user->hasDrevAdmin() || $validation->hasFatales())): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper">
                <span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;<?php if ($sf_user->isAdmin()): ?>Valider et Approuver<?php else: ?>Valider la déclaration<?php endif; ?>
            </button>
        </div>
    </div>
    <?php include_partial('drev/popupConfirmationValidation', array('approuver' => true)); ?>
</form>
