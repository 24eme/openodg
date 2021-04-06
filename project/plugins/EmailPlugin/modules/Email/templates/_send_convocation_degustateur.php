<?php use_helper('Date'); ?>Bonjour,

Une dégustation aura lieu le <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "HH").'h'.format_date($degustation->date, "mm"); ?> (<?php echo $degustation->getLieuNom()." - ".preg_replace("/.+—[ ]*/", "", $degustation->lieu); ?>).

Êtes vous disponible pour y participer en tant que <?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?>?

Pour confirmer votre présence, cliquez sur le lien suivant :

<?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant
], true); ?>


Si vous ne pouvez pas venir à cette dégustation, merci de nous indiquer votre absence en cliquant sur le lien ci dessous :

<?php echo sfContext::getInstance()->getRouting()->generate('degustation_convocation_auth', [
    'id' => $degustation->_id,
    'auth' => DegustationClient::generateAuthKey($degustation->_id, $identifiant),
    'college' => $college,
    'identifiant' => $identifiant,
    'presence' => '0'
], true); ?>


Bonne journée,

<?php echo include_partial('Email/footerMail'); ?>
