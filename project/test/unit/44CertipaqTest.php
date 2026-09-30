<?php
$readonly = !(getenv('WRITE'));
require_once(dirname(__FILE__).'/../bootstrap/common.php');

if(!sfConfig::get('app_certipaq_oauth')) {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled (not configured)");
    return;
}
$nb_tests = 56;
if (!$readonly) {
    $nb_tests += 8;
}
$t = new lime_test($nb_tests);


$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
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

$t->comment("Identifiant de l'opérateur ".$operateur_test->raison_sociale." : ".$operateur_test->id);
$infos_operateur = CertipaqOperateur::getInstance()->recuperation($operateur_test->id);
$t->is(array_keys((array) $infos_operateur), array('id','dr_type_entreprise_id','raison_sociale','nom_entreprise','siret','cvi','adresse','complement_adresse','cp','ville','pays','canton','localisation','latitude','longitude','telephone','portable','fax','email','observations','sites','organismes_rattachement'), "On a bien les infos attentues pour la requete opérateur par id");
$t->is($infos_operateur->id, $operateur_test->id, "On récupère les infos opérateurs (".$operateur_test->raison_sociale.")");
$t->is($infos_operateur->cvi, $operateur_test->cvi, "C'est le bon cvi (".$operateur_test->cvi.")");
$t->ok(count($infos_operateur->sites), "Il a des sites");
$t->ok(count($infos_operateur->sites[0]->habilitations), "Il a des habilitations");

$hab = $infos_operateur->sites[0]->habilitations[0];
$t->ok($hab->dr_statut_habilitation->cle, "On récupère le statut de l'habilitation ".$hab->dr_statut_habilitation->libelle);
$certipaq_produits = CertipaqDeroulant::getInstance()->getCertipaqProduitsFromCdcId($hab->dr_cdc_id);
$certipaq_produit_id = array_key_first($certipaq_produits);
$certipaq_produit = $certipaq_produits[$certipaq_produit_id];

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
    $data = array('millesime' => $millesime, 'superficie' => 0, 'volume' => 650);
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf, $data);
    throw new sfException("Erreur DR non détectée");
} catch (Exception $e) {
    $t->is($e->getMessage(), 'HTTP Error 400 (https://democertipaq.jeteste.dev/api/declaration/revendication) : {"errors":["Le param\u00e8tre surface_ha est manquant"]}', "La création d'une ligne de DR impossible car la superficie 0");
}

try {
    $data = array('millesime' =>  1900, 'superficie' => 50, 'volume' => 650);
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf,$data);
    throw new sfException("Erreur millesime non détectée");
} catch (Exception $e) {
    $t->is($e->getMessage(), 'HTTP Error 400 (https://democertipaq.jeteste.dev/api/declaration/revendication) : {"errors":["Le param\\u00e8tre millesime est invalide"]}', "La création d'une ligne de DR impossible avec un millesime à 0");
}

if (!$readonly) {
  try {
      $data = array('millesime' => $millesime, 'superficie' => 50, 'volume' => 650);
    $res = CertipaqDRev::getInstance()->createUneLigne($etablissement, $produit_conf, $data);
    $t->ok($res->id, "La création d'une ligne de DR ne provoque pas d'erreur (".$res->id.")");
  } catch (Exception $e) {
    $t->fail($e->getMessage(), "La création d'une ligne de DR ne provoque pas d'erreur");
  }
  $certi_drev = CertipaqDRev::getInstance()->findLigne($res->id);
  $t->is($certi_drev->dr_cdc_produit->libelle, $certipaq_produit->libelle, "la drev contient bien  une résolution du produit choisi : ".$certipaq_produit->libelle);
  $t->ok($certi_drev->dr_cdc->libelle, "la drev contient bien une résolution du cdc");
  $t->ok($certi_drev->dr_cdc_famille->libelle, "la drev contient bien une résolution de la famille");
  $t->is($certi_drev->dr_etat_demande->libelle, "Validée", "la ligne de DR est bien validée");
  $t->is($certi_drev->operateur->id, $infos_operateur->id, "la drev contient bien un résolution de l'operateur");
  $t->is($certi_drev->operateurs_sites->id, $op->sites[0]->id, "le site est bien résolus");
  $t->is($certi_drev->operateurs_sites->id, $certi_drev->entrepot_operateurs_sites->id, "le site et l'entrepot ont les même id (et sont bien résolus)");
}
$res = CertipaqDRev::getInstance()->findbyOperateurIdAndMillesime($infos_operateur->id, $millesime);
$certi_drev = array_pop($res);
$t->ok($certi_drev, "a une première drev");
$t->is($certi_drev->dr_cdc_produit->libelle, $certipaq_produit->libelle, "la première drev contient bien  une résolution du produit choisi : ".$certipaq_produit->libelle);
$t->ok($certi_drev->dr_cdc->libelle, "la première drev contient bien une résolution du cdc");
$t->ok($certi_drev->dr_cdc_famille->libelle, "la première drev contient bien une résolution de la famille");
$t->is($certi_drev->operateur->id, $infos_operateur->id, "la première drev contient bien un résolution de l'operateur");
$t->is($certi_drev->operateurs_sites->id, $op->sites[0]->id, "le site de la 1ère drev est bien résolus");
$t->is($certi_drev->operateurs_sites->id, $certi_drev->entrepot_operateurs_sites->id, "le site et l'entrepot de la 1ère drev ont les même id (et sont bien résolus)");

$multi_operateur = null;
foreach($operateurs as $o) {
        $multi_operateur = $o;
        $multi_operateur_certi_produits = array();
        $multi_operateur_config_produits = array();
        foreach($o->dr_cdc_id as $cdc_id) {
            foreach (CertipaqDeroulant::getInstance()->getCertipaqProduitsFromCdcId($cdc_id) as $id => $certi_prod) {
                if (!$certi_prod) {
                    continue;
                }
                $produit_conf = CertipaqDeroulant::getInstance()->getConfigurationProduitFromProduitId($certi_prod->id);
                if (!$produit_conf) {
                    continue;
                }
                if ($produit_conf->getRendement() <= 0) {
                    continue;
                }
                $multi_operateur_certi_produits[$id] = $certi_prod;
                $multi_operateur_config_produits[$id] = $produit_conf;
            }
        }
        if (count($multi_operateur_config_produits) > 2) {
            break;
        }
    }
}

$t->comment("DRev complete pour l'opérateur ".$multi_operateur->raison_sociale." - ".$multi_operateur->id);

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $millesime);
$drev->declarant->cvi = $multi_operateur->cvi;
$drev->declarant->siret = $multi_operateur->siret;

$denomination2test = "Denomination de test";
$madenomination = $denomination2test;
$i = 0;
$hashes = [];
foreach($multi_operateur_config_produits as $id => $produit_conf) {
    $i++;
    $hash = $produit_conf->getHash();
    $produit_rev = $drev->addProduit($hash, $madenomination);
    $hashes[$hash] = $hash;
    $produit_rev->superficie_revendique = $i * 1.1;
    $produit_rev->volume_revendique_issu_recolte = $i * 50;
    if ($i == 1) {
        $produit_rev->vci->constitue = 20;
        $produit_rev->volume_revendique_issu_vci = 20;
    }
    $madenomination = '';
    if ($i < 3) {
        break;
    }
}
$drev->save();
$i = count($hashes);
$t->comment($drev->_id);

$lignes_orig = CertipaqDRev::getInstance()->findbyOperateurIdAndMillesime($multi_operateur->id, $millesime);
if (!$readonly) {
$res = CertipaqDRev::getInstance()->createDRev($drev);
$lignes_post = CertipaqDRev::getInstance()->findbyOperateurIdAndMillesime($multi_operateur->id, $millesime);
$t->is(count($lignes_post), count($lignes_orig) + $i, 'la creation de la DREV certipaq a bien créé '.$i.' lignes');
$t->is($res[$i - 1]->id, $lignes_post[count($lignes_post) - 1]->id, "La recherche retourne bien la dernière ligne créé");
$t->is($res[0]->id, $lignes_post[count($lignes_post) - 1 * count($res)]->id, "La recherche retourne bien la première ligne créé");
}else{
    $lignes_post = $lignes_orig;
    $res = array_slice($lignes_post, count($lignes_post) - $i);
}
$ligne_id = $res[count($res) - 1]->id;
$certi_drev = CertipaqDRev::getInstance()->findLigne($ligne_id);
$t->is($certi_drev->volume_hl, $produit_rev->volume_revendique_issu_recolte, 'la dernière ligne ('.$ligne_id.') a le bon volume');
$t->is($certi_drev->surface_ha, $produit_rev->superficie_revendique, 'la dernière ligne a la bonne superficie');
$ligne_id = $res[0]->id;
$certi_drev = CertipaqDRev::getInstance()->findLigne($ligne_id);
$t->is($certi_drev->volume_hl, 50, 'la première ligne ('.$ligne_id.') a le bon volume');
$t->is($certi_drev->surface_ha, 1.1, 'la première ligne a la bonne superficie');
$t->is($certi_drev->volume_complementaire_individuel_hl, 20, 'la première ligne a le bon vci');
$t->is($certi_drev->observations, $denomination2test, 'la première ligne a la dénomination en observation "'.$denomination2test.'"');

$res = CertipaqDI::getInstance()->getAll();
$res = CertipaqDI::getInstance()->findByOperateurId($infos_operateur->id);
$t->comment("Création d'une demande");
$date = (new DateTime("-6 month"))->format('Y-m-d');
$habilitation_produit = $produit_conf->getAppellation();
$activites = array(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_PRODUCTEUR, HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE);
$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, "HABILITATION", $habilitation_produit->getHash(), $activites, null, "COMPLET", $date, "commentaire",  "Syndicat pour certipaq", false);

$t->comment('Demande convertie en nouvel opérateur');
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

$t->comment("Demande convertie en extention d'habilitation");
$param = CertipaqDI::getInstance()->getParamExtentionHabilitationFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
$t->ok($param['habilitations'][0]['dr_cdc_famille_id'], "la 1ère demande a une famille de cahier des charges");
$t->ok($param['habilitations'][0]['dr_cdc'][0]['dr_cdc_id'], "la 1ère demande a un 1er produit de cahier des charges");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs_id'], "la 1ère demande a une activité");
$t->ok($param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 1ère demande a une activité résolue (".$param['habilitations'][0]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 2eme demande a une activité résolue (".$param['habilitations'][1]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");
$t->ok($param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle, "la 3eme demande a une activité résolue (".$param['habilitations'][2]['dr_activites_operateurs']['dr_activites_operateurs']->libelle.")");

$t->comment("Demande convertie en Nouveau site");
$param = CertipaqDI::getInstance()->getParamNouveauSiteFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
$t->is(count($param['sites']), 1, "la demande d'habilitation a bien un site");
$t->is(count($param['habilitations']), 3, "la demande d'habilitation contient bien 6 habilitations (une par activité et produit)");

$t->comment("Demande convertie en modification d'identite");
$param = CertipaqDI::getInstance()->getParamModificationIdentiteFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
$t->ok($param['operateur'], "il y a objet opérateur");
$t->ok($param['operateur']["objet_modification"], 'a un objet de modification');
$t->ok($param['operateur']["raison_sociale"], 'a raison_sociale');
$t->ok($param['operateur']["nom_entreprise"], 'a nom_entreprise');
$t->ok($param['operateur']["siret"], 'a siret');

/*
$param = CertipaqDI::getInstance()->getParamModificationOutilFromDemande($demande);
$param = CertipaqDeroulant::getInstance()->getParamWithObjFromIds($param);
*/
