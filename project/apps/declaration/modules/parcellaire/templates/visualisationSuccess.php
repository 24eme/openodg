<?php use_helper('Date') ?>

<div class="page-header">
    <h2>Visualisation de votre parcellaire <?php echo $parcellaire->campagne; ?>
    <?php if($parcellaire->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($parcellaire->validation && $parcellaire->validation !== true): ?> reçue le <?php echo format_date($parcellaire->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php elseif($parcellaire->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($parcellaire->validation && $parcellaire->validation !== true): ?> validée le <?php echo format_date($parcellaire->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php endif; ?>
    </h2>
</div>

<?php include_partial('parcellaire/recap', array('parcellaire' => $parcellaire)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("home") ?><?php endif; ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <?php if(!$parcellaire->validation): ?>
    <div class="col-xs-4 text-right">
            <a href="<?php echo url_for("parcellaire_edit", $parcellaire) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
    </div>
    <?php elseif(!$parcellaire->validation_odg && $sf_user->isAdmin()): ?>
    <div class="col-xs-4 text-right">
            <!--<button type="submit" class="btn btn-danger btn-lg btn-upper"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Refuser</button>-->
            <a href="<?php echo url_for("parcellaire_validation_admin", array("sf_subject" => $parcellaire, "service" => isset($service) ? $service : null)) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
    </div>
    <?php endif; ?>
</div>