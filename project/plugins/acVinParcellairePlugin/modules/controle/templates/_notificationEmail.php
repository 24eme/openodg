<?php use_helper('Lot') ?>
<?php use_helper('Date') ?>
Bonjour,

Suite au contrôle de votre exploitation, veuillez en trouver ci-joint le compte rendu.

Le potentiel de production devra être respecté pour la prochaine récolte.

Bien cordialement,
<br/>
<?php echo $agent->prenom .'&nbsp;'. $agent->nom ."\n"?>
<i><?php echo $agent->fonction; ?></i>
Mob. : <?php echo $agent->getTelephoneMobile() ."\n"; ?>
Tel. : <?php echo $agent->getTelephoneBureau() ."\n"; ?>
<a href="<?php echo $agent->getSiteInternet(); ?>">syndicat-cotesdeprovence.com</a>
