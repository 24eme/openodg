<?php include_partial('admin/menu', array('active' => 'constats')); ?>

<?php use_helper('Date'); ?>
<?php use_javascript("degustation.js?201505150308", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<ul class="nav nav-tabs">
  <li role="presentation" class="active"><a href="#"><?php echo date('Y-m-d') ?></a></li>
</ul>

<ul class="nav nav-tabs">
    <?php foreach($tournees->tourneesJournee as $t): ?>
        <li role="presentation" class="active"><a href="#"><?php echo $t->tournee->appellation ?></a></li>
    <?php endforeach; ?>
</ul>

<h3>Liste des rendez-vous à planifier</h3>
<ul class="list-group">
<?php foreach($rdvsPris as $rdv): ?>
    <li class="list-group-item"><?php echo $rdv->value->raison_sociale ?> <?php echo $rdv->value->commune ?> <?php echo $rdv->value->heure ?> <?php echo $rdv->value->type_rendezvous ?></li>
<?php endforeach ?>
</ul>

<ul class="list-group">
<?php foreach($tournee->rendezvous as $rdv): ?>
<li class="list-group-item"><?php echo $rdv->compte_raison_sociale ?> <?php echo $rdv->compte_commune ?> <?php echo $rdv->heure_reelle
 ?> <?php echo $rdv->type_rendezvous ?></li>
<?php endforeach; ?>
</ul>
