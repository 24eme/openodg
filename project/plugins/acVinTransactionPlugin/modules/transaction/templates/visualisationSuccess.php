<?php use_helper('Date') ?>

<?php include_partial('transaction/breadcrumb', array('transaction' => $transaction )); ?>
<?php if (isset($form)): ?>

    <form role="form" class="form-inline" action="<?php echo url_for('transaction_visualisation', $transaction) ?>" method="post" id="validation-form">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Vrac export <small>du <?php echo format_date($transaction->getDate(), 'dd/MM/yyyy'); ?></small>
    <?php if($transaction->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($transaction->validation && $transaction->validation !== true): ?> reçue le <?php echo format_date($transaction->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($transaction->validation): ?>
    <small class="pull-right" style="font-size:50%">Télédéclaration<?php if($transaction->validation && $transaction->validation !== true): ?> signée le <?php echo format_date($transaction->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($transaction->validation_odg): ?> et approuvée le <?php echo format_date($transaction->validation_odg, "dd/MM", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
    <h4 class="mt-5 mb-0"><?php echo $transaction->declarant->nom; ?><span class="text-muted"> (<?php echo $transaction->declarant->famille; ?>)</span></h4>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if(!$transaction->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$transaction->isMaster()): ?>
    <div class="alert alert-info">
      Ce n'est pas la <a class="" href="<?php echo ($transaction->getMaster()->isValidee())? url_for('transaction_visualisation', $transaction->getMaster()) :  url_for('transaction_edit', $transaction->getMaster()) ?>"><strong>dernière version</strong></a> de la déclaration, le tableau récapitulatif n'est donc pas à jour.

    </div>
<?php endif; ?>

<?php if($transaction->validation && !$transaction->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('transaction/pointsAttentions', array('transaction' => $transaction, 'validation' => $validation, 'noLink' => true)); ?>
<?php endif; ?>

<?php include_partial('transaction/recap', array('transaction' => $transaction, 'form' => $form, 'dr' => $dr)); ?>

<?php if (TransactionConfiguration::getInstance()->hasDegustation()): ?>
    <h3>Dégustation</h3>
    <p style="margin-bottom: 30px;">Les vins seront prêt à être dégustés à partir du : <?php echo ($transaction->date_degustation_voulue)     ? date_format(date_create($transaction->validation), 'd/m/Y') : null;?></p>
<?php endif ?>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", array('identifiant' => $transaction->identifiant, 'campagne' => $transaction->campagne)); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>


    <div class="col-xs-4 text-center">
        <div class="btn-group">
            <a href="<?php echo url_for("transaction_export_pdf", $transaction) ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;PDF
            </a>
        </div>
    </div>

    <div class="col-xs-4 text-right">
        <div class="btn-group">
        <?php if ($transaction->validation && TransactionSecurity::getInstance($sf_user, $transaction->getRawValue())->isAuthorized(TransactionSecurity::DEVALIDATION) && !$transaction->hasLotsUtilises()):
                if (!$transaction->validation_odg): ?>
                    <a class="btn btn-default" href="<?php echo url_for('transaction_devalidation', $transaction) ?>" onclick="return confirm('Êtes-vous sûr de vouloir réouvrir ce vrac export ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir</a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(!$transaction->validation): ?>
                <a href="<?php echo url_for("transaction_edit", $transaction) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$transaction->validation_odg && ( $sf_user->isAdmin() ||
                                                 ($sf_user->hasTransactionAdmin() && TransactionConfiguration::getInstance()->hasValidationOdgRegion() && !$transaction->isValidateOdgByRegion($regionParam))
                                               )): ?>
        <?php $params = array("sf_subject" => $transaction, "service" => isset($service) ? $service : null); if($regionParam): $params=array_merge($params,array('region' => $regionParam)); endif; ?>
        <div class="col-xs-6 text-right">
            <button type="button" name="validateOdg" id="btn-validation-document-transaction" data-toggle="modal" data-target="#transaction-confirmation-validation" <?php if($validation->hasErreurs() && $transaction->isTeledeclare() && !$sf_user->hasTransactionAdmin()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</button>
        </div>

        <?php endif; ?>
        </div>
    </div>
</div>
<?php if (isset($form)): ?>
</form>
<?php endif; ?>
<?php include_partial('transaction/popupConfirmationValidation', array('approuver' => false)); ?>
