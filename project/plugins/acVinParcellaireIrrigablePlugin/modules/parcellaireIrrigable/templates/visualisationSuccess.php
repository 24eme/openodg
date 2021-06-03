<?php use_helper('Date') ?>

<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<div class="page-header no-border">
    <h2>Identification des parcelles irrigables 
    <?php if($parcellaireIrrigable->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($parcellaireIrrigable->validation && $parcellaireIrrigable->validation !== true): ?> reçue le <?php echo format_date($parcellaireIrrigable->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($parcellaireIrrigable->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($parcellaireIrrigable->validation && $parcellaireIrrigable->validation !== true): ?> validée le <?php echo format_date($parcellaireIrrigable->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$parcellaireIrrigable->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$parcellaireIrrigable->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php include_partial('parcellaireIrrigable/recap', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>
<?php if($parcellaireIrrigable->observations): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo nl2br($parcellaireIrrigable->observations); ?>
                     </div>
            </div>
        </div>
   </div>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireIrrigable->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-2 text-center">
            <a href="<?php echo url_for('parcellaireirrigable_export_pdf', $parcellaireIrrigable) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>

    <div class="col-xs-2 text-right">
        <?php if ($parcellaireIrrigable->validation && ParcellaireSecurity::getInstance($sf_user, $parcellaireIrrigable->getRawValue())->isAuthorized(ParcellaireSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-xs btn-default pull-right" href="<?php echo url_for('parcellaireirrigable_devalidation', $parcellaireIrrigable) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider votre parcellaire irrigable ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php endif; ?>
    </div>
    <div class="col-xs-3 text-right">
        <?php if(!$parcellaireIrrigable->validation): ?>
                <a href="<?php echo url_for("parcellaireirrigable_edit", $parcellaireIrrigable) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$parcellaireIrrigable->validation_odg && $sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir approuver cette déclaration ?");' href="<?php echo url_for("parcellaireirrigable_validation_admin", array("sf_subject" => $parcellaireIrrigable, "service" => null)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
        <?php endif; ?>
    </div>
</div>
