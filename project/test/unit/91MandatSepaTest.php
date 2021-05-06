<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$societe = $viti->getSociete();
$socVitiCompte = $societe->getMasterCompte();

$oldmandat = MandatSepaClient::getInstance()->findLastBySociete($societe->identifiant);
if ($oldmandat) {
    MandatSepaClient::getInstance()->delete($oldmandat);
}
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}


$t = new lime_test(34);

$t->is(MandatSepaConfiguration::getInstance()->getMentionAutorisation(), 'En signant ce formulaire de mandat, vous autorisez (A) le Syndicat des Vins IGP à envoyer des instructions à votre banque pour débiter votre compte, et (B) votre banque à débiter votre compte conformément aux instructions du Syndicat des Vins IGP.', 'autorisation correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMentionRemboursement(), 'Vous bénéficiez d\'un droit à remboursement par votre banque selon les conditions décrites dans la convention que vous avez passée avec elle. Toute demande de remboursement doit être présentée dans les 8 semaines suivant la date de débit de votre compte ou sans tarder et au plus tard dans les 13 mois en cas de prélèvement non autorisé.', 'remboursement correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMentionDroits(), 'Vos droits concernant le présent mandat sont expliqués dans un document que vous pouvez obtenir auprès de votre banque.', 'droits correctement configuré');

$t->is(MandatSepaConfiguration::getInstance()->getMandatSepaIdentifiant(), '** ID ICS **', 'ics correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMandatSepaNom(), 'Syndicat des Vins IGP', 'nom correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMandatSepaAdresse(), '22 Avenue Henri Pontier', 'adresse correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMandatSepaCodePostal(), '13626', 'cp correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMandatSepaCommune(), 'Aix-en-Provence', 'commune correctement configuré');

$mandatSepa = MandatSepaClient::getInstance()->createDoc($societe);
$mandatSepa->constructId();
$mandatSepa->save();
$mandaid = $mandatSepa->_id;
$mandatSepa = MandatSepaClient::getInstance()->find($mandaid);

$t->is($mandatSepa->date, date('Y-m-d'), 'date de creation = date du jour');
$t->is($mandatSepa->is_signe, 0, 'mandat non signe');
$t->is($mandatSepa->debiteur->frequence_prelevement, MandatSepaClient::FREQUENCE_PRELEVEMENT_RECURRENT, 'fréquence = récurrent');

$t->is($mandatSepa->mention_autorisation, MandatSepaConfiguration::getInstance()->getMentionAutorisation(), 'autorisation conforme à la configuration');
$t->is($mandatSepa->mention_remboursement, MandatSepaConfiguration::getInstance()->getMentionRemboursement(), 'remboursement conforme à la configuration');
$t->is($mandatSepa->mention_droits, MandatSepaConfiguration::getInstance()->getMentionDroits(), 'droits conforme à la configuration');

$t->is($mandatSepa->creancier->identifiant_ics, MandatSepaConfiguration::getInstance()->getMandatSepaIdentifiant(), 'ics conforme à la configuration');
$t->is($mandatSepa->creancier->nom, MandatSepaConfiguration::getInstance()->getMandatSepaNom(), 'nom conforme à la configuration');
$t->is($mandatSepa->creancier->adresse, MandatSepaConfiguration::getInstance()->getMandatSepaAdresse(), 'adresse conforme à la configuration');
$t->is($mandatSepa->creancier->code_postal, MandatSepaConfiguration::getInstance()->getMandatSepaCodePostal(), 'cp conforme à la configuration');
$t->is($mandatSepa->creancier->commune, MandatSepaConfiguration::getInstance()->getMandatSepaCommune(), 'commune conforme à la configuration');

$t->is($mandatSepa->debiteur->identifiant_rum, $societe->identifiant, 'rum conforme à la societe');
$t->is($mandatSepa->debiteur->nom, $societe->raison_sociale, 'nom conforme à la societe');
$t->is($mandatSepa->debiteur->adresse, $societe->siege->adresse, 'adresse conforme à la societe');
$t->is($mandatSepa->debiteur->code_postal, $societe->siege->code_postal, 'cp conforme à la societe');
$t->is($mandatSepa->debiteur->commune, $societe->siege->commune, 'commune conforme à la societe');

$mandatSepa->constructId();
$id = 'MANDATSEPA-'.$societe->getIdentifiant().'-'.date('Ymd');
$t->is($mandatSepa->_id, $id, 'identifiant de mandat SEPA conforme');
