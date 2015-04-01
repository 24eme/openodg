<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $tournee->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($tournee); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($tournee->operateurs) ?>&nbsp;Opérateurs</div></h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php include_partial('degustation/recap', array('tournee' => $tournee)); ?>

<?php if($tournee->date < date('Y-m-d')): ?>
<?php include_partial('degustation/notes', array('tournee' => $tournee)); ?>
<?php endif; ?>

<div class="row row-margin">
    <div class="col-xs-6 text-left">
        <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for('degustation') ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-6 text-right">
    <?php if($tournee->isTourneeTerminee()): ?>
        <a class="btn btn-warning btn-lg" href="<?php echo url_for('degustation_affectation', $tournee) ?>"><span class="glyphicon glyphicon-list-alt"></span>&nbsp;&nbsp;Affecter les vins</a>
    <?php else: ?>
        <a class="btn btn-warning btn-lg" href="<?php echo url_for('degustation_organisation', $tournee) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Modifier l'organisation des tournées</a>
    <?php endif; ?>
    </div>
</div>