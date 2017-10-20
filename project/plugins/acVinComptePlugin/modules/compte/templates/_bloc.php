<?php use_helper('Compte') ?>
<h5 style="margin-top: 0;"><span class="glyphicon glyphicon-user"></span> <a href="<?php echo url_for('compte_visualisation', $compte) ?>"><?php echo ($compte->nom_a_afficher) ? $compte->nom_a_afficher : $compte->nom; ?></a></h5>
<?php if($compte->fonction): ?>
    <span class="col-xs-3 text-muted">Fonction&nbsp;:</span><span class="col-xs-9"><?php echo $compte->fonction; ?></span>
<?php endif; ?>
<?php if ($compte->isSuspendu()): ?>
    <span class="label label-default"><?php echo $compte->statut; ?></span>
<?php endif; ?>

<?php include_partial('compte/blocCoordonnees', array('compte' => $compte)); ?>
