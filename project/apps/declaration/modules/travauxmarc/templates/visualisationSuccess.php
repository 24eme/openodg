<?php use_helper('Date') ?>

<?php include_partial('travauxmarc/breadcrumb', array('travauxmarc' => $travauxmarc )); ?>

<div class="page-header no-border">
    <h2>Déclaration d'ouverture des travaux de distillation <?php echo $travauxmarc->campagne; ?>
    <br />
    <?php if($travauxmarc->isPapier()): ?>
    <small><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($travauxmarc->validation && $travauxmarc->validation !== true): ?> reçue le <?php echo format_date($travauxmarc->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
<?php elseif($travauxmarc->validation): ?>
    <small>Télédéclaration<?php if($travauxmarc->validation && $travauxmarc->validation !== true): ?> validée le <?php echo format_date($travauxmarc->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php endif; ?>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if(!$travauxmarc->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if($travauxmarc->validation && !$travauxmarc->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'AVA
    </div>
<?php endif; ?>

<?php include_partial('travauxmarc/recap', array('travauxmarc' => $travauxmarc)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", $travauxmarc->getEtablissementObject()) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-2 text-center">
        <a href="<?php echo url_for("travauxmarc_export_pdf", $travauxmarc) ?>" class="btn btn-warning btn-lg">
            <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
        </a>
    </div>
    <div class="col-xs-5 text-right">
        <div class="btn-group">
            <?php if (TravauxMarcSecurity::getInstance($sf_user, $travauxmarc->getRawValue())->isAuthorized(TravauxMarcSecurity::DEVALIDATION)): ?>
                <a class="btn btn-default-step btn-lg" onclick='return confirm("Étes vous sûr de vouloir dévalider cette déclaration")' href="<?php echo url_for('travauxmarc_devalidation', $travauxmarc) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
            <?php endif; ?>
            <?php if(TravauxMarcSecurity::getInstance($sf_user, $travauxmarc->getRawValue())->isAuthorized(TravauxMarcSecurity::EDITION)): ?>
                <a href="<?php echo url_for("travauxmarc_edit", $travauxmarc) ?>" class="btn btn-default-step btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
            <?php endif; ?>
            <?php if(TravauxMarcSecurity::getInstance($sf_user, $travauxmarc->getRawValue())->isAuthorized(TravauxMarcSecurity::VALIDATION_ADMIN)): ?>
                <a href="<?php echo url_for("travauxmarc_validation_admin", $travauxmarc) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
            <?php endif; ?>
        </div>
    </div>
</div>
