<?php use_helper('Date') ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $drap]); ?>
<?php else: ?>
    <?php include_partial('drap/breadcrumb', array('drap' => $drap)); ?>
<?php endif; ?>

<?php include_component('declaration', 'parcellairesLies', array('obj' => $drap)); ?>

<div class="page-header no-border">
    <h2>Identification des parcelles en renonciation à produire
    <?php if($drap->isAuto()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration générée automatiquement depuis les déclarations précédentes <?php if($drap->validation && $drap->validation !== true): ?> le <?php echo format_date($drap->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
        <?php elseif($drap->isPapier()): ?>
        <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($drap->validation && $drap->validation !== true): ?> reçue le <?php echo format_date($drap->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
            <?php elseif($drap->validation): ?>
            <small class="pull-right">Télédéclaration<?php if($drap->validation && $drap->validation !== true): ?> validée le <?php echo format_date($drap->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?>
    <?php endif; ?>
  </small>
    </h2>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$drap->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if(!$drap->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php include_partial('drap/recap', array('drap' => $drap)); ?>
<?php if($drap->observations): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo nl2br($drap->observations); ?>
                     </div>
            </div>
        </div>
   </div>
<?php endif; ?>


<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $drap->identifiant, 'campagne' => $drap->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('drap_export_pdf', $drap) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>

    <div class="col-xs-4 text-right">
        <?php if ($drap->validation && ParcellaireSecurity::getInstance($sf_user, $drap->getRawValue())->isAuthorized(ParcellaireSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-default pull-right" href="<?php echo url_for('drap_devalidation', $drap) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider votre déclaration ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php endif; ?>
    </div>
    <div class="col-xs-4 text-right">
        <?php if(!$drap->validation): ?>
                <a href="<?php echo url_for("drap_edit", $drap) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
                <?php elseif(!$drap->validation_odg && $sf_user->isAdmin()): ?>
                <a onclick='return confirm("Êtes vous sûr de vouloir approuver cette déclaration ?");' href="<?php echo url_for("drap_validation_admin", array("sf_subject" => $drap, "service" => null)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
        <?php endif; ?>
    </div>
</div>


<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $drap]); ?>
<?php endif; ?>
