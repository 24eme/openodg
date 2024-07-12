<?php use_helper('Date') ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php else: ?>
    <?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>
<?php endif; ?>

<?php include_component('declaration', 'parcellairesLies', array('obj' => $parcellaireManquant)); ?>

<div class="page-header no-border">
    <h2>Déclaration de pieds morts ou manquants
    <?php if($parcellaireManquant->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($parcellaireManquant->validation && $parcellaireManquant->validation !== true): ?> reçue le <?php echo format_date($parcellaireManquant->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php elseif($parcellaireManquant->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($parcellaireManquant->validation && $parcellaireManquant->validation !== true): ?> validée le <?php echo format_date($parcellaireManquant->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$parcellaireManquant->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$parcellaireManquant->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php include_partial('parcellaireManquant/recap', array('parcellaireManquant' => $parcellaireManquant)); ?>
<?php if($parcellaireManquant->observations): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo nl2br($parcellaireManquant->observations); ?>
                     </div>
            </div>
        </div>
   </div>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireManquant->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-2 text-center">
            <a href="<?php echo url_for('parcellairemanquant_export_pdf', $parcellaireManquant) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>

    <div class="col-xs-offset-2 col-xs-3 text-right">
        <?php if(!$parcellaireManquant->validation): ?>
                <a href="<?php echo url_for("parcellairemanquant_edit", $parcellaireManquant) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$parcellaireManquant->validation_odg && $sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir approuver cette déclaration ?");' href="<?php echo url_for("parcellairemanquant_validation_admin", array("sf_subject" => $parcellaireManquant, "service" => null)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
        <?php endif; ?>
        <?php if ($parcellaireManquant->validation && ParcellaireSecurity::getInstance($sf_user, $parcellaireManquant->getRawValue())->isAuthorized(ParcellaireSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-default pull-right" href="<?php echo url_for('parcellairemanquant_devalidation', $parcellaireManquant) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider votre declaration de pieds manquants ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php endif; ?>
