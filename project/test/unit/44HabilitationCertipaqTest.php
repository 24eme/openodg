<?php
$readonly = !(getenv('WRITE'));
require_once(dirname(__FILE__).'/../bootstrap/common.php');

if(!sfConfig::get('app_certipaq_oauth')) {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled (not configured)");
    return;
}
$nb_tests = 49;
if (!$readonly) {
    $nb_tests += 8;
}
$t = new lime_test($nb_tests);


$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$millesime = date('Y') - 1;

$t->ok(CertipaqService::getInstance()->getToken(), "CertipaqService arrive à récupérer un token");
$profil = (array) CertipaqService::getInstance()->getProfil();
$t->is(array_keys($profil), array('nom', 'prenom'), "On récupère l'info du profil");

$operateurs = CertipaqOperateur::getInstance()->getAll();
$t->ok(count($operateurs), "On est capable de récupérer tous les opérateurs");

$operateur_test = array_shift($operateurs);
$t->is(array_keys((array)$operateur_test), array('id','dr_type_entreprise_id','raison_sociale','nom_entreprise','siret','cvi','adresse','complement_adresse','cp','ville','pays','canton','localisation','latitude','longitude','telephone','portable','fax','email','observations'), "L'operateur de test récupéré a bien les bons attributs");

$resultat = CertipaqOperateur::getInstance()->findByCviOrSiret($operateur_test->cvi);
$t->ok($resultat, "On récupère les infos du viti sur la base du cvi");
$t->is($resultat->cvi, $operateur_test->cvi, "C'est le cvi qu'on a demandé");

$resultat = CertipaqOperateur::getInstance()->findByCviOrSiret($operateur_test->siret);
$t->ok($resultat, "On récupère les infos du viti sur la base du siret");
$t->is($resultat->siret, $operateur_test->siret, "C'est le siret qu'on a demandé");

$t->comment("Identifiant opérateur: ".$operateur_test->id);
$infos_operateur = CertipaqOperateur::getInstance()->recuperation($operateur_test->id);
$t->is(array_keys((array) $infos_operateur), array('id','dr_type_entreprise_id','raison_sociale','nom_entreprise','siret','cvi','adresse','complement_adresse','cp','ville','pays','canton','localisation','latitude','longitude','telephone','portable','fax','email','observations','sites','organismes_rattachement'), "On a bien les infos attentues pour la requete opérateur par id");
$t->is($infos_operateur->id, $operateur_test->id, "On récupère les infos opérateurs");
$t->is($infos_operateur->cvi, $operateur_test->cvi, "C'est le bon cvi");
$t->ok(count($infos_operateur->sites), "Il a des sites");
$t->ok(count($infos_operateur->sites[0]->habilitations), "Il a des habilitations");

$t->ok($infos_operateur->sites[0]->habilitations[0]->dr_statut_habilitation->cle, "On récupère le statut de l'habilitation ".$infos_operateur->sites[0]->dr_statut_habilitation->libelle);

$certipaq_produit = array_shift(CertipaqDeroulant::getInstance()->getListeProduitsCahiersDesCharges());
$produit_conf = CertipaqDeroulant::getInstance()->getConfigurationProduitFromProduitId($certipaq_produit->id);
$t->ok($produit_conf, "retrouve la conf du produit depuis le premier id de la liste renvoyée par l'API (".$certipaq_produit->libelle.")");
$certipaq_produit_res = CertipaqDeroulant::getInstance()->getCertipaqProduitFromConfigurationProduit($produit_conf);
$t->is($certipaq_produit_res->id, $certipaq_produit->id, "Depuis la configuration, on retrouve bien l'id certipaq");
$t->is($certipaq_produit_res->dr_cdc_id, $certipaq_produit->dr_cdc_id, "Le certipaq produit contient le cdc_id");
$t->is($certipaq_produit_res->dr_cdc_famille_id, $certipaq_produit->dr_cdc_famille_id, "Le certipaq produit contient le cdc_famille_id");

$habilitation = CertipaqOperateur::getInstance()->getHabilitationFromOperateurProduitAndActivite($infos_operateur, $certipaq_produit, CertipaqDeroulant::ACTIVITE_PRODUCTEUR);
$t->ok($habilitation->dr_cdc_famille_id, "L'habilitation du produit de test (".$certipaq_produit->libelle.") pour l'activité producteur a bien un cdc_famille_id");

$etablissement = new Etablissement();
$etablissement->raison_sociale = $infos_operateur->raison_sociale;
$etablissement->cvi = $infos_operateur->cvi;
$etablissement->siret = str_replace(' ', '', $infos_operateur->siret);

$op = CertipaqOperateur::getInstance()->findByEtablissement($etablissement);
$t->is($op->id, $infos_operateur->id, "Récupère les infos d'un opérateur depuis établissement");
$t->ok($op->sites, "Les infos de l'opérateur depuis établissement contienne les infos de leurs sites");
try {
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf, $millesime, 0, 650);
    throw new sfException("Erreur DR non détectée");
} catch (Exception $e) {
    $t->is($e->getMessage(), 'HTTP Error 400 : {"errors":["Le param\u00e8tre surface_ha est manquant"]}', "La création d'une ligne de DR impossible car la superficie 0");
}

try {
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf, 0, 50, 650);
    throw new sfException("Erreur millesime non détectée");
} catch (Exception $e) {
    $t->is($e->getMessage(), 'HTTP Error 400 : {"errors":["Le param\\u00e8tre millesime est invalide"]}', "La création d'une ligne de DR impossible avec un millesime à 0");
}

if (!$readonly) {
  try {
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf, $millesime, 50, 650);
    $t->ok($res->id, "La création d'une ligne de DR ne provoque pas d'erreur");
  } catch (Exception $e) {
    $t->fail($e->getMessage(), "La création d'une ligne de DR ne provoque pas d'erreur");
  }
  $res = new stdClass();
  $res->id = 35;
  $drev = CertipaqDRev::getInstance()->find($res->id);
  $t->is($drev->dr_cdc_produit->libelle, $certipaq_produit->libelle, "la drev contient bien  une résolution du produit choisi : ".$certipaq_produit->libelle);
  $t->ok($drev->dr_cdc->libelle, "la drev contient bien une résolution du cdc");
  $t->ok($drev->dr_cdc_famille->libelle, "la drev contient bien une résolution de la famille");
  $t->is($drev->dr_etat_demande->libelle, "Validée", "la ligne de DR est bien validée");
  $t->is($drev->operateur->id, $infos_operateur->id, "la drev contient bien un résolution de l'operateur");
  $t->is($drev->operateurs_sites->id, $op->sites[0]->id, "le site est bien résolus");
  $t->is($drev->operateurs_sites->id, $drev->entrepot_operateurs_sites->id, "le site et l'entrepot ont les même id (et sont bien résolus)");
}
$res = CertipaqDRev::getInstance()->findbyOperateurAndMillesime($infos_operateur->id, $millesime);
$drev = array_pop($res);
$t->is($drev->dr_cdc_produit->libelle, $certipaq_produit->libelle, "la première drev contient bien  une résolution du produit choisi : ".$certipaq_produit->libelle);
$t->ok($drev->dr_cdc->libelle, "la première drev contient bien une résolution du cdc");
$t->ok($drev->dr_cdc_famille->libelle, "la première drev contient bien une résolution de la famille");
$t->is($drev->operateur->id, $infos_operateur->id, "la première drev contient bien un résolution de l'operateur");
$t->is($drev->operateurs_sites->id, $op->sites[0]->id, "le site de la 1ère drev est bien résolus");
$t->is($drev->operateurs_sites->id, $drev->entrepot_operateurs_sites->id, "le site et l'entrepot de la 1ère drev ont les même id (et sont bien résolus)");


$res = CertipaqDI::getInstance()->getAll();
$res = CertipaqDI::getInstance()->findByOperateurId($infos_operateur->id);
$t->comment("Création d'une demande");
$date = (new DateTime("-6 month"))->format('Y-m-d');
$habilitation_produit = $produit_conf->getAppellation();
$activites = array(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_PRODUCTEUR, HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE);
$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, HabilitationClient::CHAIS_PRINCIPAL, "HABILITATION", $habilitation_produit->getHash(), $activites, "COMPLET", $date, "commentaire",  "Syndicat pour certipaq", false);

$param = CertipaqDI::getInstance()->getParamNouvelOperateurFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
$t->is($param['operateur']['pays'], 'FR', "Le pays de l'opérateur est bien France (FR)");
$t->is($param['operateur']['pays'], 'FR', "Le pays de l'opérateur est bien France (FR)");
$t->is(count($param['adresses']), 3, "il y a bien trois adresse");
$t->is($param['adresses'][0]['dr_adresse_type']->libelle, "Siège social", "La première adresse est bien une adresse de siège");
$t->is($param['adresses'][1]['dr_adresse_type']->libelle, "Facturation", "La première adresse est bien une adresse de facturation");
$t->is($param['adresses'][2]['dr_adresse_type']->libelle, "Prélèvement", "La première adresse est bien une adresse de prélèvement");
$t->is(count($param['sites']), 1, "la demande d'habilitation a bien un site");
$t->is(count($param['habilitations']), 3, "la demande d'habilitation contient bien 6 habilitations (une par activité et produit)");
$t->ok($param['habilitations'][0]['dr_cdc_famille_id'], "la 1ère demande a une famille de cahier des charges");
$t->ok($param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'], "la 1ère demande a un 1er produit de cahier des charges");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'], "la 1ère demande a une activité");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 1ère demande a une activité résolue (".$param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 2eme demande a une activité résolue (".$param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 3eme demande a une activité résolue (".$param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");

$param = CertipaqDI::getInstance()->getParamExtentionHabilitationFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
$t->ok($param['habilitations'][0]['dr_cdc_famille_id'], "la 1ère demande a une famille de cahier des charges");
$t->ok($param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'], "la 1ère demande a un 1er produit de cahier des charges");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'], "la 1ère demande a une activité");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 1ère demande a une activité résolue (".$param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 2eme demande a une activité résolue (".$param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 3eme demande a une activité résolue (".$param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
