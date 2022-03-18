<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test(26);

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date));
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des docs précédents
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = TransactionClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->constructId();
$drev->storeDeclarant();

$produits = $drev->getConfigProduits();
$nbProduit = 0;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $drev->addProduit($produit->getHash());
    $nbProduit++;
    if ($nbProduit == 2) {
      break;
    }
}
$i=1;
foreach($drev->lots as $lot) {
$lot->id_document = $drev->_id;
$lot->millesime = $campagne;
$lot->numero_logement_operateur = $i;
$lot->volume = 50;
$lot->affectable = true;
$lot->destination_type = null;
$lot->destination_date = ($campagne+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$i++;
}
$drev->validate();
$drev->validateOdg();
$drev->save();

$lots = [];
foreach ($drev->lots as $lotdrev) {
    $lots[$lotdrev->getUniqueId()] = $lotdrev;
}

$doc = new Degustation();
$doc->date = $degust_date;
$doc->save();
$doc->setLots($lots);

foreach ($doc->getLots() as $lot) {
    $lot->numero_table = 1;
    $lot->setIsPreleve();
}
$doc->addLeurre($doc->lots[0]->getProduitHash(), null, date('Y'), 1);

$t->comment('Résultat de conformité / non conformité');
$lotConformes = $doc->getLotsConformesOrNot(true);
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($doc->getLots()), 3, "Il y a 3 lots");
$t->is(count($lotConformes), 0, 'Aucun lot n\'est considéré comme "CONFORME"');
$t->is(count($lotNonConformes), 0, 'Aucun lot n\'est considéré comme "NON CONFORME"');

$options = array('numero_table' => 1);
$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "CONFORME",
    'conformite_1' => "CONFORME",
    'conformite_2' => "CONFORME"
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot(true);
$t->is(count($lotConformes), 2, 'Les 2 lots sont "CONFORMES", le 3eme étant un leurre');
$t->is($doc->lots[0]->statut, Lot::STATUT_CONFORME, 'Le statut du lot 1 est "'.Lot::STATUT_CONFORME.'"');
$t->is($doc->lots[1]->statut, Lot::STATUT_CONFORME, 'Le statut du lot 2 est "'.Lot::STATUT_CONFORME.'"');

$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$motif = "Taux de sucrosité";
$obs = "A requalifier";
$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "CONFORME",
    'conformite_1' => "NONCONFORME_MAJEUR",
    'conformite_2' => "CONFORME",
    'motif_1' => $motif,
    'observation_1' => $obs
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot();
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($lotConformes), 1, '1 lot est "CONFORME"');
$t->is(count($lotNonConformes), 1, 'Un lot est considéré comme "NON CONFORME"');
$t->is($doc->lots[1]->statut, Lot::STATUT_NONCONFORME, 'Le statut du lot 2 est "'.Lot::STATUT_NONCONFORME.'"');

foreach ($lotNonConformes as $lot) {
  $t->is($lot->motif, $motif , 'Le motif de non conformité est "'.$motif.'"');
  $t->is($lot->observation, $obs, 'L\'observation de non conformité est "'.$obs.'"');
}

$doc = DegustationClient::getInstance()->find($doc->_id);
$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$motif = "Terne";
$obs = "A diluer";
$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "NONCONFORME_MINEUR",
    'conformite_1' => "CONFORME",
    'conformite_2' => "CONFORME",
    'motif_0' => $motif,
    'observation_0' => $obs
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot(true);
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($lotConformes), 1, '1 lot est "CONFORME"');
$t->is(count($lotNonConformes), 1, 'Un lot est considéré comme "NON CONFORME"');
$t->is($doc->lots[0]->statut, Lot::STATUT_NONCONFORME, 'Le statut du lot 1 est "'.Lot::STATUT_NONCONFORME.'"');

foreach ($lotNonConformes as $lot) {
  $t->is($lot->motif, $motif , 'Le motif de non conformité est "'.$motif.'"');
  $t->is($lot->observation, $obs, 'L\'observation de non conformité est "'.$obs.'"');
}


$etbIdentifiant = $viti->identifiant;
$t->comment('Envoie de mail de notification à '.$etbIdentifiant);

$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok(!$mailEnvoye, 'Le mail de resultats n\'a pas été envoyé');

$date_envoie = date('Y-m-d H:i:s');
$doc->setMailEnvoyeEtablissement($etbIdentifiant,$date_envoie);
$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok($mailEnvoye, 'Le mail de resultats a été envoyé');

$doc->save();

$lotNonConforme = current($lotNonConformes);
$t->is(count(DegustationClient::getInstance()->getManquements()), 1, 'Il apparait en manquement');

$lotNonConforme->recours_oc = true;
$lotNonConforme->statut = Lot::STATUT_RECOURS_OC;
$doc->save();

$t->ok($lotNonConforme->getMouvement(Lot::STATUT_RECOURS_OC), 'Le lot a le mouvement de recours');

try {
    $lotNonConforme->setConformiteLot(Lot::CONFORMITE_CONFORME);
    $t->fail('Exception si on change un lot en recours oc');
} catch (sfException $e) {
    $t->pass('Exception si on change un lot en recours oc');
}

$t->comment('Notifications');
$doc->setMailEnvoyeEtablissement($etbIdentifiant, 0);
$t->is($doc->isMailEnvoyeEtablissement($etbIdentifiant), false, 'On remet les mails à non envoyé');

sfConfig::set('app_secret', 'test_secret');

$authKey = UrlSecurity::generateAuthKey($doc->_id, $etbIdentifiant);

$t->is(strlen($authKey), 10, "Génération de la clé de chiffrement");
$t->ok(UrlSecurity::verifyAuthKey($authKey, $doc->_id, $etbIdentifiant), "La clé de chiffrement est vérifié");
$t->ok(UrlSecurity::verifyAuthKey($authKey."a", $doc->_id, $etbIdentifiant), "La clé de chiffrement est vérifié que sur les 10 premiers caractères");
$t->ok(!UrlSecurity::verifyAuthKey("a".$authKey, $doc->_id, $etbIdentifiant), "La clé n'est pas valide");
