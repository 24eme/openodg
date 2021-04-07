<?php use_helper('Date');
$app = strtoupper(sfConfig::get('sf_app'));
$infos = sfConfig::get('app_facture_emetteur');
$signature = $infos[$app]['service_facturation'];
?>Madame, Monsieur,

Nous vous invitions à participer à la prochaine commission de dégustation des vins IGP qui aura lieu le <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "HH").'h'.format_date($degustation->date, "mm"); ?> <?php echo $degustation->getLieuNom()." - ".preg_replace("/.+—[ ]*/", "", $degustation->lieu); ?>


Pour le bon déroulé de cette dégustation nous vous remercions de bien vouloir nous confirmer votre présence en cliquant sur le lien :

<?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant
], true); ?>


Si vous ne pouvez pas venir : <?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant,
    'presence' => '0'
], true); ?>


Dans l'attente du plaisir de vous accueillir, recevez, Madame, Monsieur, nos plus cordiales salutations.

<?php echo $signature; ?>
