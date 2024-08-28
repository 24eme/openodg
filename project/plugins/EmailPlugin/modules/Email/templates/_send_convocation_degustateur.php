<?php use_helper('Date');
$infos = FactureConfiguration::getInstance()->getInfos();
?>Madame, Monsieur,

Nous vous invitons à participer à la prochaine commission de dégustation qui aura lieu le <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "HH").'h'.format_date($degustation->date, "mm"); ?> <?php echo $degustation->getLieuNom()." - ".preg_replace("/.+—[ ]*/", "", $degustation->lieu); ?>


Pour le bon déroulé de cette dégustation nous vous remercions de bien vouloir nous confirmer votre présence ou votre absence en cliquant sur le lien :

<?php echo url_for('degustation_convocation_presence', [
    'id' => $degustation->_id,
    'auth' => UrlSecurity::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant
], true); ?>


Dans l'attente du plaisir de vous accueillir, recevez, Madame, Monsieur, nos plus cordiales salutations.

<?php echo include_partial('Email/footerMail'); ?>
