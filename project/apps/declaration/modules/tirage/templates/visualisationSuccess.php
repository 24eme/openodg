<?php use_helper('Date') ?>

<div class="page-header no-border">
    <h2>Déclaration de Tirage <?php echo $tirage->campagne; ?>
    <br />
    <?php if($tirage->isPapier()): ?>
    <small><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($tirage->validation && $tirage->validation !== true): ?> reçue le <?php echo format_date($tirage->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php elseif($tirage->validation): ?>
    <small>Télédéclaration<?php if($tirage->validation && $tirage->validation !== true): ?> validée le <?php echo format_date($tirage->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php endif; ?>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
    
<?php if(!$tirage->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if($tirage->validation && !$tirage->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'AVA
    </div>
<?php endif; ?>

<?php include_partial('tirage/recap', array('tirage' => $tirage)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
        <a href="<?php echo url_for("tirage_export_pdf", $tirage) ?>" class="btn btn-warning btn-lg">
            <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
        </a>
    </div>
<?php if (!$tirage->validation): ?>
    <div class="col-xs-4 text-right">
        <a href="<?php echo url_for("tirage_edit", $tirage) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
    </div>
<?php elseif (!$tirage->validation_odg && $sf_user->isAdmin()): ?>
    <div class="col-xs-4 text-right">
        <a href="<?php echo url_for("tirage_validation_admin", $tirage) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
    </div>
<?php endif; ?>
</div>


