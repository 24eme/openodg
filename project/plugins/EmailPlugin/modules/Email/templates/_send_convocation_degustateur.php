<?php use_helper('Date'); ?>Bonjour,

Vous êtes convié en tant que degustateur en qualité de <?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?> à la dégustation du <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm"); ?>.

Celle ci se tiendra <?php echo $degustation->getLieuNom(); ?>


Vous trouverez ci-dessous le lien pour confirmer votre présence à cette dégustation :

<?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant
], true); ?>


Si toutefois vous ne pouvez pas venir à cette  dégustation, vous pouvez indiquer votre absence ci dessous :

<?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant,
    'presence' => '0'
], true); ?>


Bonne journée,

<?php echo include_partial('Email/footerMail'); ?>
