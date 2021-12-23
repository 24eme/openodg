<?php use_helper('Date') ?>

<?php include_partial('conditionnement/breadcrumb', array('conditionnement' => $conditionnement )); ?>
<?php if (isset($form)): ?>
        <form role="form" class="form-inline" action="<?php echo url_for('conditionnement_visualisation', $conditionnement) ?>" method="post" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Conditionnement du <?php echo format_date( $conditionnement->date); ?>
    <?php if($conditionnement->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($conditionnement->validation && $conditionnement->validation !== true): ?> reçue le <?php echo format_date($conditionnement->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($conditionnement->validation): ?>
    <small class="pull-right" style="font-size:50%">Télédéclaration<?php if($conditionnement->validation && $conditionnement->validation !== true): ?> signée le <?php echo format_date($conditionnement->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($conditionnement->validation_odg): ?> et approuvée le <?php echo format_date($conditionnement->validation_odg, "dd/MM", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
    <h4 class="mt-5 mb-0"><?php echo $conditionnement->declarant->nom; ?><span class="text-muted"> (<?php echo $conditionnement->declarant->famille; ?>)</span></h4>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$conditionnement->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$conditionnement->isMaster()): ?>
    <div class="alert alert-info">
      Ce n'est pas la <a class="" href="<?php echo ($conditionnement->getMaster()->isValidee())? url_for('conditionnement_visualisation', $conditionnement->getMaster()) :  url_for('conditionnement_edit', $conditionnement->getMaster()) ?>"><strong>dernière version</strong></a> de la déclaration, le tableau récapitulatif n'est donc pas à jour.

    </div>
<?php endif; ?>

<?php if($conditionnement->validation && !$conditionnement->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('conditionnement/pointsAttentions', array('conditionnement' => $conditionnement, 'validation' => $validation, 'noLink' => true)); ?>
<?php endif; ?>

<?php include_partial('conditionnement/recap', array('conditionnement' => $conditionnement, 'form' => $form, 'dr' => $dr)); ?>

<?php if (ConditionnementConfiguration::getInstance()->hasDegustation()): ?>
    <h3>Controle</h3>
    <p style="margin-bottom: 30px;">Date de controle souhaitée : <?php echo ($conditionnement->exist('date_degustation_voulue') && $conditionnement->date_degustation_voulue) ? date_format(date_create($conditionnement->validation), 'd/m/Y') : '<i>non saisi</i>';?></p>
<?php endif ?>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", array('identifiant' => $conditionnement->identifiant, 'campagne' => $conditionnement->campagne)); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>


    <div class="col-xs-4 text-center">
        <div class="btn-group">
            <a href="<?php echo url_for("conditionnement_export_pdf", $conditionnement) ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF
            </a>
        </div>
    </div>

    <div class="col-xs-4 text-right">
        <div class="btn-group">
        <?php if ($conditionnement->validation && ConditionnementSecurity::getInstance($sf_user, $conditionnement->getRawValue())->isAuthorized(ConditionnementSecurity::DEVALIDATION) && !$conditionnement->hasLotsUtilises()):
                if (!$conditionnement->validation_odg): ?>
                    <a class="btn btn-default" href="<?php echo url_for('conditionnement_devalidation', $conditionnement) ?>" onclick="return confirm('Êtes-vous sûr de vouloir réouvrir cette Conditionnement ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir</a>
                <?php elseif ($sf_user->isAdmin()): ?>
                        <a class="btn btn-default btn-sm" href="<?php echo url_for('conditionnement_devalidation', $conditionnement) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider ce conditionnement ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
                <?php endif; ?>
        <?php endif; ?>
        <?php if(!$conditionnement->validation): ?>
                <a href="<?php echo url_for("conditionnement_edit", $conditionnement) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$conditionnement->validation_odg && ( $sf_user->isAdmin() ||
                                                 ($sf_user->hasConditionnementAdmin() && ConditionnementConfiguration::getInstance()->hasValidationOdgRegion() && !$conditionnement->isValidateOdgByRegion($regionParam))
                                               )): ?>
        <?php $params = array("sf_subject" => $conditionnement, "service" => isset($service) ? $service : null); if($regionParam): $params=array_merge($params,array('region' => $regionParam)); endif; ?>
        <div class="col-xs-6 text-right">
            <button type="button" name="validateOdg" id="btn-validation-document-conditionnement" data-toggle="modal" data-target="#conditionnement-confirmation-validation" <?php if($validation->hasErreurs() && $conditionnement->isTeledeclare() && !$sf_user->hasTransactionAdmin()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</button>
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<?php if (isset($form)): ?>
</form>
<?php endif; ?>
<?php include_partial('conditionnement/popupConfirmationValidation', array('approuver' => false)); ?>
