<div class="page-header no-border">
    <h2>Déclaration de Revendication Marc d'Alsace de Gewurztraminer <?php echo $drevmarc->campagne; ?></h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
    
<?php if(!$drevmarc->validation): ?>
    <div class="alert alert-warning">
        La saisie de cette déclaration n'est pas terminée elle est en cours d'édition.
    </div>
<?php elseif(!$drevmarc->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-success">
        Cette déclaration n'a pas encore été validée par l'AVA
    </div>
<?php endif; ?>

<?php include_partial('drevmarc/recap', array('drevmarc' => $drevmarc)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
        <a href="<?php echo url_for("drevmarc_export_pdf", $drevmarc) ?>" class="btn btn-warning btn-lg">
            <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
        </a>
    </div>
<?php if (!$drevmarc->validation): ?>
    <div class="col-xs-4 text-right">
        <a href="<?php echo url_for("drevmarc_edit", $drevmarc) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
    </div>
<?php elseif (!$drevmarc->validation_odg && $sf_user->isAdmin()): ?>
    <div class="col-xs-4 text-right">
        <a href="<?php echo url_for("drevmarc_validation_admin", $drevmarc) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
    </div>
<?php endif; ?>
</div>


