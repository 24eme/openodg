<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application == 'loire') {
    $t = new lime_test(1);
    $t->ok(true, "Pas d'habilitation pour loire");
    return;
}
$t = new lime_test(37);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des habilitations précédentes
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$t->comment("Création d'un doc dans le passé");
$date = '2007-10-01';
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($viti->identifiant, $date);
$habilitation->save();

$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id est bien construit ".$habilitation->_id );
$t->is(count($habilitation->declaration), 0, "l'habilitation est vierge de produit");
$t->is(count($habilitation->historique), 0, "l'habilitation est vierge d'historique");
$t->is($habilitation->isLectureSeule(), false, "l'habilitation n'est pas en lecture seule");


$produitConfig = null;
foreach($habilitation->getProduitsConfig($configuration) as $p) {
    $produitConfig = $p;
    break;
}

$t->comment("Ajout de produit");

$habProduit = $habilitation->addProduit($produitConfig->getHash());
$habilitation->save();

$t->is(count($habilitation->declaration), 1, "l'ajout de produit créer bien un produit dans le noeud déclaration");
$produit = $habilitation->get($produitConfig->getHash());
$t->ok($produit, "le produit ajouté a bien la hash choisie");
$t->is($produit->getLibelle(), $produitConfig->getLibelleComplet(), "Le libellé du produit a été enregistré dans le doc");
$t->is(count($produit->activites), count(HabilitationConfiguration::getInstance()->getActivites()), "La liste d'activité a été initialisé à ".count(HabilitationConfiguration::getInstance()->getActivites()));
$t->is(count($habilitation->historique), 1, "l'ajout du produit a créé un historique");

$activiteKey = HabilitationClient::ACTIVITE_PRODUCTEUR;
$activite = $produit->activites->get($activiteKey);

$t->comment("Ajout d'activité");

$habProduit->activites[$activiteKey]->updateHabilitation(HabilitationClient::STATUT_DEMANDE_HABILITATION, "dossier reçu");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->date, $date, "l'activité a été changée");
$t->is(count($habilitation->historique), 2, "la modification de l'activité a été enregistrée dans l'historique");

$t->comment("Changement d'activité");

$habProduit->updateHabilitation($activiteKey, HabilitationClient::STATUT_HABILITE , "INAO OK");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->statut, HabilitationClient::STATUT_HABILITE, "le statut de l'activité a été changée");
$t->is(count($habilitation->historique), 3, "la modification de l'activité a été enregistrée dans l'historique");
$t->is($habilitation->historique[2]->statut, HabilitationClient::STATUT_HABILITE, "Le statut est écrit dans l'historique");

$t->comment('Habilitation à une autre date');
$date = '2010-10-01';
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($viti->identifiant, $date);
$habilitation->save();
$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id est bien construit ".$habilitation->_id );
$t->is(count($habilitation->declaration), 1, "l'habilitation n'est pas vierge de produit");
$t->is(count($habilitation->historique), 0, "l'habilitation est vierge d'historique");
$t->is($habilitation->isLectureSeule(), false, "l'habilitation n'est pas en lecture seule");
$t->is($habilitation->getPrevious()->isLectureSeule(), true, "l'habilitation précédente est en lecture seule");

$habProduit = $habilitation->get($produitConfig->getHash());
$habProduit->activites[$activiteKey]->updateHabilitation(HabilitationClient::STATUT_SUSPENDU , "ne respecte pas le cahier des charges");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->date, $date, "la date de l'activité été changée");
$t->is($habProduit->activites[$activiteKey]->statut, HabilitationClient::STATUT_SUSPENDU, "le statut de l'activité été changée");
$t->is(count($habilitation->historique), 1, "la modification de l'activité a été enregistrée dans l'historique");
$full = $habilitation->getFullHistorique();
$t->is(count($full), 4, "l'historique complet contient toutes les modifications");
$t->is($full[count($full)-1]->date, $habilitation->historique[0]->date, "l'historique complet est dans le bon ordre");
$full = $habilitation->getFullHistoriqueReverse();
$t->is($full[0]->date, $habilitation->historique[0]->date, "l'historique complet invsersé est dans le bon ordre");

$t->comment("Habilitation d'une autre activité à une nouvelle date supérieur à la dernière");
$date = '2012-10-01';
HabilitationClient::getInstance()->updateAndSaveHabilitation($viti->identifiant, $produitConfig->getHash(), $date, array(HabilitationClient::ACTIVITE_VINIFICATEUR), HabilitationClient::STATUT_DEMANDE_HABILITATION);

$id = "HABILITATION-".$viti->identifiant."-".str_replace("-", "", $date);
$habilitation = HabilitationClient::getInstance()->find($id);

$t->is($habilitation->_id, $id, "L'habilitation a été crée à la bonne date ".$id);

$idLast = $id;

$t->comment("Insertion d'une habilitation entre deux autres");

$habilitationLastBefore = HabilitationClient::getInstance()->find($idLast);

$date = '2011-10-01';
HabilitationClient::getInstance()->updateAndSaveHabilitation($viti->identifiant, $produitConfig->getHash(), $date, array($activiteKey), HabilitationClient::STATUT_HABILITE);

$id = "HABILITATION-".$viti->identifiant."-".str_replace("-", "", $date);
$habilitation = HabilitationClient::getInstance()->find($id);

$t->is($habilitation->_id, $id, "L'habilitation a été crée à la bonne date ".$id);
$t->ok($habilitation->isLectureSeule(), "L'habilitation est en lecture seule");

$habilitationLast = HabilitationClient::getInstance()->find($idLast);

$t->is($habilitationLast->get($produitConfig->getHash())->activites->get($activiteKey)->statut, HabilitationClient::STATUT_HABILITE, "Le statut a été répliqué sur la dernière habilitation");
$t->is($habilitationLast->get($produitConfig->getHash())->activites->get($activiteKey)->date, $date, "La date a été répliqué sur la dernière habilitation");
$t->is(count($habilitationLast->historique), count($habilitationLastBefore->historique), "La ligne d'historique n'a pas été créé dans la dernière habilitation");

$t->comment("Insertion d'une habilitation au début avec perte de logique");
$date = '2009-10-01';

try {
HabilitationClient::getInstance()->updateAndSaveHabilitation($viti->identifiant, $produitConfig->getHash(), $date, array($activiteKey), HabilitationClient::STATUT_RETRAIT);
    $t->fail("L'habitation n'a pas pu être créer car il y a une perte de logique");
} catch (Exception $e) {
    $t->pass("L'habitation n'a pas pu être créer car il y a une perte de logique");
}

$t->comment("Contrôle d'habilitation");

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);

$produitConfigComplet = null;
foreach($habilitation->getConfiguration()->getProduits() as $p) {
    $produitConfigComplet = $p;
    break;
}

$t->ok($habilitation->containHashProduit($produitConfig->getHash()), "La hash produit de l'habilitation strict est reconnu");
$t->ok($habilitation->containHashProduit($produitConfigComplet->getHash()), "La hash de produit complète est reconnu");
$t->ok($habilitation->containHashProduit("/appellations/".$produitConfigComplet->getAppellation()->getKey()), "Une partie de la hash produit est reconnu");
$t->ok(!$habilitation->containHashProduit("/hashquinexistepas/".$produitConfigComplet->getAppellation()->getKey()), "La hash n'est pas reconnu");

$drevConfig = sfConfig::get('drev_configuration_drev');
$drevConfigOrigin = $drevConfig;
$drevConfig['odg']['TEST'] = array('produits' => array("/appellations/".$produitConfigComplet->getAppellation()->getKey()));
$drevConfig = sfConfig::set('drev_configuration_drev', $drevConfig);

$t->ok(HabilitationClient::getInstance()->isRegionInHabilitation($viti->identifiant, "TEST"), "L'habilitation fait partie de la région");

sfConfig::set('drev_configuration_drev', $drevConfigOrigin);
