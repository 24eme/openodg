<?php use_helper('Date') ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_component('declaration', 'parcellairesLies', array('obj' => $parcellaireAffectation)); ?>

<div class="page-header no-border">
    <h2>Déclaration d'affectation parcellaire
    <?php if($parcellaireAffectation->isAuto()): ?>
        <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration générée automatiquement
        <?php if($parcellaireAffectation->validation && $parcellaireAffectation->validation !== true): ?> le <?php echo format_date($parcellaireAffectation->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
        sur la base des déclarations précédentes
    <?php elseif($parcellaireAffectation->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($parcellaireAffectation->validation && $parcellaireAffectation->validation !== true): ?> validée le <?php echo format_date($parcellaireAffectation->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if(!$parcellaireAffectation->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$parcellaireAffectation->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/recap', array('parcellaireAffectation' => $parcellaireAffectation, 'coop' => $coop)); ?>
<div class="row">
    <div class="col-xs-10"></div>
    <div class="col-xs-2 mb-2">
        <a href="<?php echo url_for('parcellaire_potentiel_visualisation', array('id' => $parcellaireAffectation->getParcellaire()->_id)); ?>">Voir le détail du potentiel</a>
    </div>
</div>
<?php include_component('parcellaire', 'syntheseParCepages', array('parcellaire' => $parcellaireAffectation)); ?>

<?php if($parcellaireAffectation->observations): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo nl2br($parcellaireAffectation->observations); ?>
                     </div>
            </div>
        </div>
   </div>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireAffectation->identifiant, 'campagne' => $parcellaireAffectation->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-2 text-center">
            <a href="<?php echo url_for('parcellaireaffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>

    <div class="col-xs-2 text-right">
        <?php if (!isset($coop) && $parcellaireAffectation->validation && ParcellaireSecurity::getInstance($sf_user, $parcellaireAffectation->getRawValue())->isAuthorized(ParcellaireSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-xs btn-default pull-right" href="<?php echo url_for('parcellaireaffectation_devalidation', $parcellaireAffectation) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider votre declaration d\'affectation parcellaire ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php endif; ?>
    </div>
    <div class="col-xs-3 text-right">
        <?php if(!$parcellaireAffectation->validation): ?>
                <a href="<?php echo url_for("parcellaireaffectation_edit", $parcellaireAffectation) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>
