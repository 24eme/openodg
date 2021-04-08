<?php
$infos = sfConfig::get('app_facture_emetteur');
$app = strtoupper(sfConfig::get('sf_app'));
$signature = $infos[$app]['service_facturation'];
$telephone = $infos[$app]['telephone'];
$email = $infos[$app]['email'];
?>
<?php echo $signature; ?>
--
mailto:<?php echo $email ;?>
<?echo $telephone; ?>