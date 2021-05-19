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
for($i = 1; $i <= 5; $i++) {
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

$t->comment("Création de la SV11");

$campagne = (date('Y')-1)."";
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

$t->is(count($apporteurs), count($vitis), "Il y a ".count($vitis)." apporteurs");

foreach($vitis as $viti) {
    $t->is($apporteurs[$viti->_id]->_id, $viti->_id, "L'apporteur ".$viti->_id." est présent");
}

$formApporteurs = new SV11ApporteursForm($sv11);

$t->is(count($formApporteurs->getFormFieldSchema()), count($apporteurs), "Il y a les 5 apporteurs dans le form");
$t->is($formApporteurs->getDefaults(), array_fill_keys(array_keys($apporteurs), 1), "Tous les apporteurs sont cochés par défaut");
$values = $formApporteurs->getDefaults();
unset($values[$vitis[0]->_id]);
$formApporteurs->bind($values);

$t->ok($formApporteurs->isValid(), "Le formulaire est valide");

$formApporteurs->save();

$coop = EtablissementClient::getInstance()->find($coop->_id);

foreach($vitis as $viti) {
    if($vitis[0]->_id == $viti->_id) {
        $viti = EtablissementClient::getInstance()->find($viti->_id);
        $t->ok(!$viti->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop->_id), "La liaison entre le viti ".$viti->_id." et la cave coop ".$coop->_id." n'a pas été créé");
        $t->ok(!$coop->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $viti->_id), "La liaison entre la cave coop ".$coop->_id." et le viti ".$viti->_id." n'a pas été créé");
        continue;
    }

    $viti = EtablissementClient::getInstance()->find($viti->_id);
    $t->ok($viti->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop->_id), "La liaison entre le viti ".$viti->_id." et la cave coop ".$coop->_id." a été créé");
    $t->ok($coop->existLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $viti->_id), "La liaison entre la cave coop ".$coop->_id." et le viti ".$viti->_id." a été créé");
}
