<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>

<div class="page-header no-border">
    <h2><?php echo $degustation->appellation_libelle; ?>&nbsp;<span class="small"><?php echo getDatesPrelevements($degustation); ?></span>&nbsp;<div class="btn btn-default btn-sm"><?php echo count($degustation->operateurs) ?>&nbsp;Opérateurs</div></h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>

<?php if ($degustation->date < date('Y-m-d')): ?>
    <h2>Notes obtenues&nbsp;<div class="btn btn-default btn-sm"><?php echo count($degustation->getNotes()); ?>&nbsp;vins dégustés</div>
        <a class="btn btn-warning btn-sm btn-upper" href="<?php echo url_for('degustation_generation_courriers', $degustation); ?>"><span class="glyphicon glyphicon-repeat"></span>&nbsp;Générer les courriers</a>
        <a class="pull-right btn btn-default btn-sm btn-upper" href="<?php echo url_for('degustation_courriers', $degustation); ?>">Choisir types courriers<span class="eleganticon arrow_carrot-right"></span></a>
    </h2> 
    <?php include_partial('degustation/notes', array('degustation' => $degustation)); ?>
<?php endif; ?>

<div class="row row-margin">
    <div class="col-xs-6 text-left">
        <a class="btn btn-primary btn-lg btn-upper" href="<?php echo url_for('degustation') ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-6 text-right">
        <?php if ($degustation->validation && $degustation->date > date('Y-m-d')): ?>
            <a class="btn btn-warning btn-lg" href="<?php echo url_for('degustation_organisation', $degustation) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Modifier l'organisation des tournées</a>
        <?php endif; ?>
    </div>
</div>