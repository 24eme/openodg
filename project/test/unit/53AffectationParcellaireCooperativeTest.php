<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (!in_array($application, array('provence'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire coopérative activé");
    return;
}

$t = new lime_test();

$t->comment("Création de l'établissement coop");

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_parcellaire_coop') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
      foreach(ParcellaireAffectationClient::getInstance()->getHistory(str_replace("COMPTE-", "", $v->id)."01", '9999', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
          $affectationParcellaire = ParcellaireAffectationClient::getInstance()->find($k);
          $affectationParcellaire->delete(false);
      }
    }
}

$societe = SocieteClient::getInstance()->createSociete("société coop test", SocieteClient::TYPE_OPERATEUR);
$societe->save();
$compte = $societe->getMasterCompte();
$compte->add('droits', array('teledeclaration'));
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_parcellaire_coop');
$compte->save();
$coop = $societe->createEtablissement(EtablissementFamilles::FAMILLE_COOPERATIVE);
$coop->nom = "établissement coop test";
$coop->cvi = "7523700201";
$coop->save();

$t->ok($coop->_rev, "Création de la cave coop ".$coop->_id);

$t->comment("Création des établissements apporteurs");

$vitis = array();
for($i = 1; $i <= 6; $i++) {
    $societe = SocieteClient::getInstance()->createSociete("société viti test", SocieteClient::TYPE_OPERATEUR);
    $societe->save();
    $compte = $societe->getMasterCompte();
    $compte->add('droits', array('teledeclaration'));
    $compte->addTag('test', 'test');
    $compte->addTag('test', 'test_parcellaire_coop');
    $compte->save();
    $viti = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
    $viti->nom = "établissement coop test";
    $viti->cvi = "752370030".$i;
    $viti->save();
    $t->ok($viti->_rev, "Création du viti ".$viti->_id);
    $vitis[] = $viti;
}

$coop->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $vitis[0]->_id);
$coop->save();

$coop->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $vitis[5]->_id);
$coop->save();

$t->comment("Création de la SV11");

$campagne = (date('Y')-1)."";
$campagneAffectation = $campagne + 1;
$sv11 = SV11Client::getInstance()->find("SV11-".$coop->identifiant."-".$campagne, acCouchdbClient::HYDRATE_JSON);
if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv11_douane.csv');

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if($produit->getRendement() <= 0) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }
    break;
}
$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
$csvContent = str_replace(array("%code_inao_1%","%libelle_produit_1%","%code_inao_2%","%libelle_produit_2%"), array($produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate);
foreach($vitis as $key => $viti) {
    $csvContent = str_replace("%cvi_".($key+1)."%", $viti->cvi, $csvContent);
}
file_put_contents($csvTmpFile, $csvContent);

$t->comment("utilise le fichier test/data/sv11_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$sv11 = SV11Client::getInstance()->createDoc($coop->identifiant, $campagne);
$sv11->setLibelle("SV11 $campagne issue de Prodouane (Papier)");
$sv11->setDateDepot("$campagne-12-15");
$sv11->save();
$sv11->storeFichier($csvTmpFile);
$sv11->save();

$t->ok($sv11->_rev, "Création de la sv11 ".$sv11->_id);
$t->ok(count($sv11->getCsv()), "Le csv a au moins une ligne");

$t->comment("Gestion des apporteurs de la cave coop");

$apporteurs = $sv11->getApporteurs();

$t->is(count($apporteurs), count($vitis) - 1, "Il y a ".(count($vitis) - 1)." apporteurs dans la sv11");

foreach($vitis as $viti) {
    if($viti->_id == $vitis[5]->_id) {
        continue;
    }
    $t->ok($apporteurs[$viti->_id], "L'apporteur ".$viti->_id." est présent");
}

$formApporteurs = new SV11ApporteursForm($sv11);

$t->is(count($formApporteurs->getFormFieldSchema()), count($vitis), "Il y a les 6 apporteurs dans le form");
$defaults = array();
foreach($vitis as $viti) {
    $defaults[$viti->_id] = 1;
}
$t->is($formApporteurs->getDefaults(), $defaults, "Tous les apporteurs sont cochés par défaut");
$values = $defaults;
unset($values[$vitis[0]->_id]);
unset($values[$vitis[1]->_id]);
$formApporteurs->bind($values);

$t->ok($formApporteurs->isValid(), "Le formulaire est valide");

$formApporteurs->save();

$coop = EtablissementClient::getInstance()->find($coop->_id);

foreach($vitis as $viti) {
    if($viti->_id == $vitis[0]->_id) {
        $viti = EtablissementClient::getInstance()->find($viti->_id);
        $t->ok(!$viti->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop->_id), "La liaison entre le viti ".$viti->_id." et la cave coop ".$coop->_id." a été supprimé");
        $t->ok(!$coop->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $viti->_id), "La liaison entre la cave coop ".$coop->_id." et le viti ".$viti->_id." a été supprimé");
        continue;
    }
    if($viti->_id == $vitis[1]->_id) {
        $viti = EtablissementClient::getInstance()->find($viti->_id);
        $t->ok(!$viti->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop->_id), "La liaison entre le viti ".$viti->_id." et la cave coop ".$coop->_id." n'a pas été créé");
        $t->ok(!$coop->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $viti->_id), "La liaison entre la cave coop ".$coop->_id." et le viti ".$viti->_id." n'a pas été créé");
        continue;
    }

    $viti = EtablissementClient::getInstance()->find($viti->_id);
    $t->ok($viti->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop->_id), "La liaison entre le viti ".$viti->_id." et la cave coop ".$coop->_id." a été créé");
    $t->ok($coop->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $viti->_id), "La liaison entre la cave coop ".$coop->_id." et le viti ".$viti->_id." a été créé");
}

$coop = EtablissementClient::getInstance()->find($coop->_id);
$t->is(count($coop->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR)), count($vitis) - 2, "La coopérative a ".(count($vitis) - 2)." coopérateurs");

$liaisons = $coop->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR);

$t->comment("Saisie des affectations parcellaire des coopérateurs");

$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$commune = current($communes);
$code_commune = key($communes);

foreach($liaisons as $liaison) {
    $identifiant = str_replace("ETABLISSEMENT-", "", $liaison->id_etablissement);
    $affectationParcellaire = ParcellaireAffectationClient::getInstance()->findOrCreate($identifiant, $campagneAffectation);
    $t->is($affectationParcellaire->_id, "PARCELLAIREAFFECTATION-".$identifiant."-".$campagneAffectation, "L'id de l'affectation parcellaire de l'apporteur ".$affectationParcellaire->_id);
    $t->ok(!$affectationParcellaire->_rev, "Le document est nouveau et n'a pas de révision");

    $form = new ParcellaireAffectationCoopSaisieForm($affectationParcellaire, $coop);
    if (sfConfig::get('app_document_validation_signataire')) {
        $t->is($form->getDefaults()['signataire'], $coop->raison_sociale, "Le signataire est initialisé par défaut");
    }

    $form->bind(array('_revision' => $affectationParcellaire->_rev, 'signataire' => "Cave coopérative"));
    $form->save();

    $affectationParcellaire->validate();
    $affectationParcellaire->save();
    $t->ok($affectationParcellaire->isValidee(), "L'affectation parcellaire est validé");
    if (sfConfig::get('app_document_validation_signataire')) {
        $t->is($affectationParcellaire->signataire, "Cave coopérative", "Le signataire a été enregistré");
    }
}


