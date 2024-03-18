<?php use_helper('Date') ?>

<?php include_partial('pmc/breadcrumb', array('pmc' => $pmc )); ?>
<?php if (isset($form)): ?>
        <form role="form" class="form-horizontal" action="<?php echo url_for('pmc_visualisation', $pmc) ?>" method="post" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Mise en Circulation <?php echo ($pmc->isNonConformite()) ? PMCNCClient::SUFFIX : '' ?> du <?php echo $pmc->getDateFr(); ?>
    <?php if($pmc->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($pmc->validation && $pmc->validation !== true): ?> reçue le <?php echo format_date($pmc->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($pmc->validation): ?>
    <small class="pull-right" style="font-size:50%">Télédéclaration<?php if($pmc->validation && $pmc->validation !== true): ?> signée le <?php echo format_date($pmc->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($pmc->validation_odg): ?> et approuvée le <?php echo format_date($pmc->validation_odg, "dd/MM", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
    <h4 class="mt-5 mb-0"><?php echo $pmc->declarant->nom; ?><span class="text-muted"> (<?php echo $pmc->declarant->famille; ?>)</span></h4>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$pmc->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$pmc->isMaster()): ?>
    <div class="alert alert-info">
      Ce n'est pas la <a class="" href="<?php echo ($pmc->getMaster()->isValidee())? url_for('pmc_visualisation', $pmc->getMaster()) :  url_for('pmc_edit', $pmc->getMaster()) ?>"><strong>dernière version</strong></a> de la déclaration, le tableau récapitulatif n'est donc pas à jour.

    </div>
<?php endif; ?>

<?php if($pmc->validation && !$pmc->validation_odg && $sf_user->isAdminODG()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong><?php if (! $pmc->isNonConformite()): ?> par l'ODG <?php endif ?>
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('pmc/pointsAttentions', array('pmc' => $pmc, 'validation' => $validation, 'noLink' => true)); ?>
<?php endif; ?>

<?php if ($pmc->type == PMCClient::TYPE_MODEL): ?>
<h2>Synthèse des produits par millesimes</h2>
<?php include_component('degustation', 'syntheseCommercialise', ['identifiant' => $pmc->identifiant, 'millesimes' => $pmc->getMillesimes(), 'region' => $sf_user->getRegion(), 'restant' => true]) ?>
<?php endif; ?>

<hr/>

<?php include_partial('pmc/recap', array('pmc' => $pmc, 'form' => $form, 'dr' => $dr)); ?>

<hr />
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", array('identifiant' => $pmc->identifiant, 'campagne' => $pmc->campagne)); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>


    <div class="col-xs-4 text-center">
        <?php if($pmc->validation): ?>
            <a href="<?php echo url_for("pmc_export_pdf", $pmc) ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF
            </a>
        <?php else: ?>
            <a class="btn btn-default" href="<?php echo url_for('pmc_delete', $pmc) ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette déclaration ?')" ><span class="glyphicon glyphicon-trash"></span>&nbsp;Supprimer le brouillon</a>
        <?php endif; ?>
    </div>

    <div class="col-xs-4 text-right">
        <div class="btn-group">
        <?php if ($pmc->validation && !$pmc->validation_odg && $sf_user->isAdminODG() && !$pmc->hasLotsUtilises()): ?>
            <a class="btn btn-default" href="<?php echo url_for('pmc_devalidation', $pmc) ?>" onclick="return confirm('Êtes-vous sûr de vouloir réouvrir cette déclaration ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir</a>
        <?php elseif ($pmc->validation_odg && $sf_user->isAdminODG() && !$pmc->hasLotsUtilises()): ?>
            <a class="btn btn-default btn-sm" href="<?php echo url_for('pmc_devalidation', $pmc) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider cette déclaration ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php elseif ($pmc->validation_odg && $pmc->hasLotsUtilises()): ?>
            <button type="button" disabled="disabled" title="Les lots de ce documents ont été dégusté, la dévalidation n'est pas possible" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</button>
        <?php endif; ?>
        <?php if(!$pmc->validation): ?>
                <a href="<?php echo url_for("pmc_edit", $pmc) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$pmc->validation_odg && ( $sf_user->isAdminODG() ||
                                                 ($sf_user->hasPMCAdmin() && PMCConfiguration::getInstance()->hasValidationOdgRegion() && !$pmc->isValidateOdgByRegion($regionParam))
                                               )): ?>
        <?php $params = array("sf_subject" => $pmc, "service" => isset($service) ? $service : null); if($regionParam): $params=array_merge($params,array('region' => $regionParam)); endif; ?>
        <div class="col-xs-6 text-right">
            <button type="button" name="validateOdg" id="btn-validation-document" data-target="#pmc-confirmation-validation" <?php if($validation->hasErreurs() && $pmc->isTeledeclare() && (!$sf_user->isAdminODG() || $validation->hasFatales())): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</button>
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<?php if (isset($form)): ?>
</form>
<?php endif; ?>
<?php include_partial('pmc/popupConfirmationValidation', array('approuver' => false)); ?>
