<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_nom') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
    }
}

SocieteClient::getInstance()->clearSingleton();

$nomSociete = "société viti test contacts";
$nomModifieSociete = "société viti test contacts modifiées";
$nomEtablissement = "établissement viti test contacts";
$nomModifieEtablissement = "établissement viti test contacts modifiés";

$t = new lime_test(40);
$t->comment("Création d'une société");

$societe = SocieteClient::getInstance()->createSociete($nomSociete, SocieteClient::TYPE_OPERATEUR);
$societe->pays = "FR";
$societe->adresse = "48 rue dulud";
$societe->code_postal = "92100";
$societe->commune = "Neuilly sur seine";
$societe->email = 'email@example.org';
$societe->save();

$id = $societe->getidentifiant();
$compte01 = $societe->getMasterCompte();
$compte01->addTag('test', 'test');
$compte01->addTag('test', 'test_nom');
$compte01->save();

$compteStandalone = CompteClient::getInstance()->find($societe->getMasterCompte()->_id);

$t->is($societe->raison_sociale, $nomSociete, "La raison sociale de la société est :  \"".$nomSociete."\"");
$t->is($compteStandalone->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($compteStandalone->nom, $compteStandalone->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->is($compteStandalone->code_postal, $societe->code_postal, "Le code postal du compte et de la société sont identiques");
$t->ok(in_array("societe", $compteStandalone->tags->automatique->toArray(true, false)),  "Le compte de la société possède le tag \"societe\"");

$t->comment("Localisation");

$adresse = $compte01->adresse;
$commune = $compte01->commune;
$code_postal = $compte01->code_postal;
$coordonnees = $compte01->calculCoordonnees($adresse, $commune, $code_postal);

$t->is($coordonnees['lat'], 48.880861, "La latitude retournée par BANO est correcte");
$t->is($coordonnees['lon'], 2.266949, "La longitude retournée par BANO est correcte");

$updatedLatLon = $compte01->updateCoordonneesLongLat(true);

if(!$updatedLatLon)
  throw new \Exception("Error Processing in updating latlon", 1);

$coordonnees = array_values($compte01->getCoordonneesLatLon())[0];

$t->ok(number_format($coordonnees[0], 2) == 48.88 &&  number_format($coordonnees[1], 2) == 2.27, "Les champs des coordonnées (lat, lon) du compte ont été mises à jours.");

$t->comment("Modification des informations de la société");
$societe->code_postal = "75014";
$societe->telephone_mobile = "060000000";
$societe->save();
$compteStandalone = CompteClient::getInstance()->find($societe->getMasterCompte()->_id);

$t->is($compteStandalone->code_postal, $societe->code_postal, "Le code postal du compte et de la société sont identiques");
$t->is($compteStandalone->telephone_mobile, $societe->telephone_mobile, "Le téléphone mobile du compte et de la société sont identiques");

$t->comment("Création d'un établissement ayant la même adresse pour la société");

$etablissement = $societe->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissement->nom = "établissement viti test contacts";
$etablissement->save();

$t->is($societe->raison_sociale, $nomSociete, "La raison sociale de la société est toujours :  \"".$nomSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($societe->getMasterCompte()->nom, $societe->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->isnt($societe->getMasterCompte()->_id, $etablissement->getMasterCompte()->_id, "La société et l'établissement ont le même id");
$t->ok(!in_array("etablissement", CompteClient::getInstance()->find($societe->getMasterCompte()->_id)->tags->automatique->toArray(true, false)), "Le compte de la société ne possède pas le tag \"etablissement\"");

$t->comment("Test synchro des suspension");

$etbId = $etablissement->_id;
$etablissement->statut = CompteClient::STATUT_SUSPENDU;
$etablissement->save();

$etbCompte = $etablissement->getMasterCompte();
$t->is($etablissement->getSociete()->statut, CompteClient::STATUT_ACTIF, "La société de l'établissement n'est pas suspendu");
$t->is($etablissement->statut, CompteClient::STATUT_SUSPENDU, "L'établissement est suspendu");

$t->is($etbCompte->statut, CompteClient::STATUT_SUSPENDU, "Le compte est suspendu");



$etablissement = EtablissementClient::getInstance()->find($etbId);
$etablissement->statut = CompteClient::STATUT_ACTIF;
$etablissement->save();
$t->is($etablissement->statut, CompteClient::STATUT_ACTIF, "L'établissement est actif");
$etbCompte = $etablissement->getMasterCompte();
$t->is($etbCompte->statut, CompteClient::STATUT_ACTIF, "Le compte est actif");


$t->comment("Modification de la raison sociale de la société");

$societe->raison_sociale = $nomModifieSociete;
$societe->siret = "FR01234567891234";
$societe->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($societe->getMasterCompte()->nom, $societe->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte sont identiques");
$t->isnt($societe->getMasterCompte()->_id, $etablissement->getMasterCompte()->_id, "La société et l'établissement ont des comptes bien séparés");
$t->is($societe->siret, $societe->getMasterCompte()->societe_informations->siret, "Le siret de la société et de son compte rattaché sont identiques");

$t->comment("Dissociation du compte d'établissement et de la société");

$etablissement = EtablissementClient::getInstance()->find($etbId);

$etablissement->adresse = "rue dulud";
$etablissement->region = "PIERREFEU_83";
$etablissement->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est toujours :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");
$t->is($etablissement->nom, $nomEtablissement, "Le nom de l'établissement est : \"".$nomEtablissement."\"");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->nom, "Le nom du compte et de l'établissement sont identiques");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte de l'établissement sont identiques");
$t->is($etablissement->getMasterCompte()->region, $etablissement->region, "La \"region\" de l'établissement et de sont compte principale sont identiques");
$t->ok(!in_array("etablissement", $societe->getMasterCompte()->tags->automatique->toArray(true, false)), "Le compte de la société ne possède plus le tag \"etablissement\"");
$t->ok(in_array("etablissement", CompteClient::getInstance()->find($etablissement->getMasterCompte()->_id)->tags->automatique->toArray(true, false)), "Le compte ".$etablissement->getMasterCompte()->_id." de l'établissement possède le tag \"etablissement\"");

$t->comment("Modification du nom de l'établissement");

$etablissement->nom = $nomModifieEtablissement;
$etablissement->save();

$t->is($societe->raison_sociale, $nomModifieSociete, "La raison sociale de la société est toujours :  \"".$nomModifieSociete."\"");
$t->is($societe->getMasterCompte()->nom, $societe->raison_sociale, "Le nom du compte et de la société sont identiques");

$t->is($etablissement->nom, $nomModifieEtablissement, "Le nom de l'établissement est : \"".$nomModifieEtablissement."\"");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->nom, "Le nom du compte et de l'établissement sont identiques");
$t->is($etablissement->getMasterCompte()->nom, $etablissement->getMasterCompte()->nom_a_afficher, "Le \"nom\" et le \"nom à afficher\" du compte de l'établissement sont identiques");

$t->comment("Modification des informations de l'établissement");
$etablissement->code_postal = "75013";
$etablissement->telephone_mobile = "070000000";
$etablissement->save();
$compteStandalone = CompteClient::getInstance()->find($etablissement->getMasterCompte()->_id);

$t->is($compteStandalone->code_postal, $etablissement->code_postal, "Le code postal du compte et de la société sont identiques");
$t->is($compteStandalone->telephone_mobile, $etablissement->telephone_mobile, "Le téléphone mobile du compte et de la société sont identiques");
