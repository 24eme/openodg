<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_interlocuteur') as $k => $v) {
    /* if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) { */
    /*   $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]); */
    /*   foreach($soc->getInterlocuteursWithOrdre() as $interlocuteur) { */
    /*     //$interlocuteur->delete(); */
    /*   } */
    /* } */
}


$t = new lime_test();
$t->comment('Création des différents interlocuteurs');

$societeviti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_societe')->getSociete();

$compteInterlocuteur = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societeviti);
$compteInterlocuteur->nom = "Actualys";
$compteInterlocuteur->prenom = "Jean";
$compteInterlocuteur->add('droits');
$compteInterlocuteur->save();

$t->comment('Compte interlocuteur : '.$compteInterlocuteur->identifiant);
$t->isnt($compteInterlocuteur->identifiant, $societeviti->identifiant, "La societe a un compte séparé");

$compteInterlocuteur->addTag('test', 'test');
$compteInterlocuteur->addTag('test', 'test_interlocuteur');
$tagsauto_attendus = ['interlocuteur'];
$droits_attendus = [];

$tagsauto = $compteInterlocuteur->tags->automatique->toArray(true, false);
$droits = $compteInterlocuteur->droits->toArray(true, false);
sort($tagsauto);
sort($droits);
sort($tagsauto_attendus);
sort($droits_attendus);

$t->is($tagsauto, $tagsauto_attendus, 'Le compte à les tags auto attendus');
$t->is($droits, $droits_attendus, 'Le compte à les droits attendus');

$t->comment('Création société dégustateur');
$societedegust = SocieteClient::getInstance()->createSociete("SARL JEAN DEGUSTE", SocieteClient::TYPE_AUTRE);
$societedegust->email = "email@societe.com";
$societedegust->site_internet = "www.societe.fr";
$societedegust->telephone_perso = "00 00 00 00 00";
$societedegust->telephone_bureau = "11 11 11 11 11";
$societedegust->telephone_mobile = "22 22 22 22 22";
$societedegust->fax = "33 33 33 33 33";
$societedegust->adresse = "Adresse 1 ";
$societedegust->adresse_complementaire = "Adresse 2 ";
$societedegust->commune = "Neuilly sur seine";
$societedegust->pays = "FR";
$societedegust->insee = "94512";
$societedegust->save();

$compteDegustateur = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societedegust);
$compteDegustateur->nom = 'Actualys';
$compteDegustateur->prenom = 'Degustateur';

$droits_attendus = null;

if ($application == 'igp13') {
    $compteDegustateur->add('droits');
    $compteDegustateur->droits->add(null, 'degustateur:porteur_de_memoire');
    $compteDegustateur->droits->add(null, 'degustateur:technicien');
    $compteDegustateur->droits->add(null, 'degustateur:usager_du_produit');

    $droits_attendus = ['degustateur:porteur_de_memoire', 'degustateur:technicien', 'degustateur:usager_du_produit'];
    $tagsauto_attendus = ['interlocuteur', 'degustateur_porteur_de_memoire', 'degustateur_technicien', 'degustateur_usager_du_produit', 'degustateur'];
}

$compteDegustateur->save();
$t->comment('Compte degustateur : '.$compteDegustateur->identifiant);

$tagsauto = $compteDegustateur->tags->automatique->toArray(true, false);
sort($tagsauto);
sort($tagsauto_attendus);
$t->is($tagsauto, $tagsauto_attendus, 'Le compte dégustateur à les tags auto attendus');

if ($compteDegustateur->exist('droits')) {
    $droits = $compteDegustateur->droits->toArray(true, false);
    sort($droits);
    sort($droits_attendus);
    $t->is($droits, $droits_attendus, 'Le compte degustateur à les droits attendus');
} else {
    $t->ok(true, "test sur les droits dégustateurs HS");
}
