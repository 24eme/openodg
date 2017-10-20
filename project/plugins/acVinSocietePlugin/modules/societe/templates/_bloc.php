<?php use_helper('Compte') ?>
<?php $compte = $societe->getMasterCompte(); ?>
<h4 style="margin-top: 0;"><span class="glyphicon glyphicon-calendar"></span> <a href="<?php echo url_for('societe_visualisation', $societe) ?>"><?php echo $societe->raison_sociale ?></a></h4>
<?php if($societe->code_comptable_client): ?>
    <span class="col-xs-3 text-muted">Comptable&nbsp;:</span><span class="col-xs-9"><?php echo $societe->code_comptable_client; ?></span>
<?php endif; ?>
<?php if ($compte->isSuspendu()): ?>
    <span class="label label-default"><?php echo $compte->statut; ?></span>
<?php endif; ?>

<?php include_partial('compte/blocCoordonnees', array('compte' => $compte, 'forceCoordonnee' => true)); ?>
