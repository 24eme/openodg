<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');


foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      foreach($soc->getEtablissementsObj() as $k => $etabl) {
        if ($etabl->etablissement) {
        //   foreach (VracClient::getInstance()->retrieveBySoussigne($etabl->etablissement->identifiant)->rows as $k => $vrac) {
        //     $vrac_obj = VracClient::getInstance()->find($vrac->id);
        //     $vrac_obj->delete();
        //   }
        //   foreach (DRMClient::getInstance()->viewByIdentifiant($etabl->etablissement->identifiant) as $id => $drm) {
        //     $drm = DRMClient::getInstance()->find($id);
        //     $drm->delete(false);
        //   }
          $etabl->etablissement->delete();
        }
      }
    }
}


$t = new lime_test(51);
$t->comment('création des différentes établissements');

$societeviti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti_societe')->getSociete();
$societeviti->siret = "00112244557788";
$societeviti->save();

$etablissementviti = $societeviti->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
$etablissementviti->region = EtablissementClient::REGION_CVO;
$etablissementviti->nom = "Etablissement viticulteur";

$etablissementviti->email = "email@etb.com";
$etablissementviti->site_internet = "www.etb.fr";
$etablissementviti->telephone_perso = "44 44 44 44 44";
$etablissementviti->telephone_bureau = "55 55 55 55 55";
$etablissementviti->telephone_mobile = "66 66 66 66 66";
$etablissementviti->fax = "77 77 77 77 77";


$etablissementviti->adresse = "etb Adresse 1 ";
$etablissementviti->siege->adresse = "etb Adresse 1 ";
$etablissementviti->adresse_complementaire = "etb Adresse 2 ";
$etablissementviti->code_postal = '92000';
$etablissementviti->siege->code_postal = '92000';
$etablissementviti->commune = "NEUILLY";
$etablissementviti->siege->commune = "NEUILLY";
$etablissementviti->pays = "FR";
$etablissementviti->insee = "98475";
$etablissementviti->ppm = "P12345678";
$etablissementviti->cvi = "7523700100";


$etablissementviti->save();

$societeviti = $etablissementviti->getSociete();

$t->is($etablissementviti->identifiant, $societeviti->identifiant."01", "L'identifiant de l'établissement respecte celui de la société : ".$etablissementviti->identifiant);

$id = $etablissementviti->getSociete()->getidentifiant();
$compteviti = CompteClient::getInstance()->findByIdentifiant($id."01");
$compteviti->addTag('test', 'test');
$compteviti->addTag('test', 'test_viti');
$compteviti->save();
$t->is($compteviti->tags->automatique->toArray(true, false), array('etablissement','producteur_raisins'), "Création d'un etablissement viti met à jour le compte $compteviti->_id");
$t->is($etablissementviti->region, EtablissementClient::REGION_CVO, "L'établissement est en région CVO après le save");

$t->is($compteviti->_get('email'), $etablissementviti->_get('email'), "L'établissement a le même email que le compte");
$t->is($compteviti->_get('site_internet'), $etablissementviti->_get('site_internet'), "L'établissement a le même site_internet que le compte");
$t->is($compteviti->_get('telephone_perso'), $etablissementviti->_get('telephone_perso'), "L'établissement a le même telephone_perso que le compte");
$t->is($compteviti->_get('telephone_bureau'), $etablissementviti->_get('telephone_bureau'), "L'établissement a le même telephone bureau que le compte");
$t->is($compteviti->_get('telephone_mobile'), $etablissementviti->_get('telephone_mobile'), "L'établissement a le même telephone_mobile que le compte");
$t->is($compteviti->_get('fax'), $etablissementviti->_get('fax'), "L'établissement a le même fax que le compte");


$t->is($compteviti->_get('adresse'), $etablissementviti->_get('adresse'), "L'établissement a la même adresse que le compte");
$t->is($compteviti->_get('adresse_complementaire'), $etablissementviti->_get('adresse_complementaire'), "L'établissement a la même adresse_complementaire que le compte");
$t->is($compteviti->_get('code_postal'), $etablissementviti->_get('code_postal'), "L'établissement a le même code_postal que le compte");
$t->is($compteviti->_get('commune'), $etablissementviti->_get('commune'), "L'établissement a la même commune que le compte");
$t->is($compteviti->_get('pays'), $etablissementviti->_get('pays'), "L'établissement a le même pays que le compte");
$t->is($compteviti->_get('insee'), $etablissementviti->_get('insee'), "L'établissement a le même insee que le compte");
$t->is($compteviti->_get('etablissement_informations')->_get('ppm'), $etablissementviti->_get('ppm'), "L'établissement a le même ppm que le compte");

$t->is($societeviti->siret, $etablissementviti->_get('siret'), "L'établissement a le siret de la société");


$societenego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_societe')->getSociete();
$etablissementnego = $societenego->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego->region = EtablissementClient::REGION_CVO;
$etablissementnego->nom = "Etablissement negociant de la région";
$etablissementnego->save();
$id = $etablissementnego->getSociete()->getidentifiant();
$comptenego = CompteClient::getInstance()->findByIdentifiant($id."01");
$comptenego->addTag('test', 'test');
$comptenego->addTag('test', 'test_nego');
$comptenego->save();
$t->is($comptenego->tags->automatique->toArray(true, false), array('etablissement','negociant'), "Création d'un etablissement nego met à jour le compte");
$t->is($etablissementnego->region, EtablissementClient::REGION_CVO, "L'établissement est en région CVO après le save");

$t->comment('Liaisons');
$etablissementnego = EtablissementClient::getInstance()->find($etablissementnego->_id);
$etablissementviti = EtablissementClient::getInstance()->find($etablissementviti->_id);
$etablissementnego->addLiaison('COOPERATEUR', $etablissementviti->_id, true);
$etablissementnego->save();
$l_array = $etablissementnego->liaisons_operateurs->toArray(1,0);
$liaisons = array_shift($l_array);
$t->is($liaisons['type_liaison'], "COOPERATEUR", "L'établissement a une liaison Coopérateur");
$t->is($liaisons['id_etablissement'], $etablissementviti->_id, "La liaison est vers le viti $etablissementviti->_id");

$etablissementviti = EtablissementClient::getInstance()->find($etablissementviti->_id);
$l_array = $etablissementviti->liaisons_operateurs->toArray(1,0);
$liaisons = array_shift($l_array);
$t->is($liaisons['type_liaison'], "COOPERATIVE", "La coop a une liaison avec son apporteur");
$t->is($liaisons['id_etablissement'], $etablissementnego->_id, "La liaison est vers la coop $etablissementnego->_id");

$etablissementviti->getSociete()->switchStatusAndSave();
$etablissementviti = EtablissementClient::getInstance()->find($etablissementviti->_id);
$t->is($etablissementviti->statut, CompteClient::STATUT_SUSPENDU, "le viti est bien suspendu");
$t->is(count($etablissementviti->liaisons_operateurs->toArray(1,0)), 1, "le viti suspendu a conservé sa liaison");
$etablissementnego = EtablissementClient::getInstance()->find($etablissementnego->_id);
$t->is(count($etablissementnego->liaisons_operateurs->toArray(1,0)), 0, "le nego n'a plus de liaison avec le viti suspendu");

$etablissementviti->getSociete()->switchStatusAndSave();
$etablissementnego = EtablissementClient::getInstance()->find($etablissementnego->_id);
$t->is(count($etablissementnego->liaisons_operateurs->toArray(1,0)), 1, "le nego a reprise sa liaison avec le viti réactivé");

$t->comment('Suspension / activation etablissement uniquement');
$etablissementviti = EtablissementClient::getInstance()->find($etablissementviti->_id);
$etablissementviti->setStatut(SocieteClient::STATUT_SUSPENDU);
$etablissementviti->save();

$t->ok($etablissementviti->isSuspendu(), "L'établissement viti est suspendu");
$t->is($etablissementviti->getSociete()->isSuspendu(), false, "La societe viti n'est pas suspendue");
$t->is($etablissementviti->isSameCompteThanSociete(), false, "Le compte de la societe est différent");
$t->is($etablissementviti->getMasterCompte()->isSuspendu(), true, "Le compte de l'établissement est suspendu");
$liaisons_viti = $etablissementviti->liaisons_operateurs->toArray(1,0);
$t->is(count($liaisons_viti), 1, "L'etablissement viti suspendu a gardé sa liaison");
$t->is(current($liaisons_viti)['type_liaison'], "COOPERATIVE", "C'est une liaison coop");

$etablissementnego = EtablissementClient::getInstance()->find($etablissementnego->_id);
$liaisons_nego = $etablissementnego->liaisons_operateurs->toArray(1,0);
$t->is(count($liaisons_nego), 0, "le nego n'a plus de liaison avec le viti suspendu");

$etablissementviti = EtablissementClient::getInstance()->find($etablissementviti->_id);
$etablissementviti->setStatut(SocieteClient::STATUT_ACTIF);
$t->is($etablissementviti->isSuspendu(), false, "L'établissement viti est actif");
$liaisons_viti = $etablissementviti->liaisons_operateurs->toArray(1,0);
$t->is(count($liaisons_viti), 1, "L'etablissement viti suspendu a gardé sa liaison");
$t->is(current($liaisons_viti)['type_liaison'], "COOPERATIVE", "C'est une liaison coop");

$etablissementnego = EtablissementClient::getInstance()->find($etablissementnego->_id);
$liaisons_nego = $etablissementnego->liaisons_operateurs->toArray(1,0);
$t->is(count($liaisons_nego), 1, "le nego a retrouvé sa liaison avec le viti");
$t->is(current($liaisons_nego)['type_liaison'], "COOPERATEUR", "C'est une liaison coop");

$t->comment('Ajout établissement');
$societenego = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_region_2_societe')->getSociete();
$etablissementnego = $societenego->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego->region = EtablissementClient::REGION_CVO;
$etablissementnego->nom = "Etablissement negociant 2 de la région";
$etablissementnego->save();
$id = $etablissementnego->getSociete()->getidentifiant();
$comptenego = CompteClient::getInstance()->findByIdentifiant($id."01");
$comptenego->addTag('test', 'test');
$comptenego->save();
$t->is($comptenego->tags->automatique->toArray(true, false), array('etablissement','negociant'), "Création d'un etablissement nego 2 met à jour le compte");

$societenego_horsregion = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego_horsregion_societe')->getSociete();
$etablissementnego_horsregion = $societenego_horsregion->createEtablissement(EtablissementFamilles::FAMILLE_NEGOCIANT);
$etablissementnego_horsregion->region = EtablissementClient::REGION_HORS_CVO;
$etablissementnego_horsregion->nom = "Etablissement negociant hors région";
$etablissementnego_horsregion->save();
$id = $etablissementnego_horsregion->getSociete()->getidentifiant();
$comptenego_horsregion = CompteClient::getInstance()->findByIdentifiant($id."01");
$comptenego_horsregion->addTag('test', 'test');
$comptenego_horsregion->save();
$t->is($comptenego_horsregion->tags->automatique->toArray(true, false), array('etablissement', 'negociant'), "Création d'un etablissement nego_horsregion met à jour le compte");
$t->is($etablissementnego_horsregion->region, EtablissementClient::REGION_HORS_CVO, "L'établissement est hors région CVO après le save");

$societecourtier = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_courtier_societe')->getSociete();
$etablissementcourtier = $societecourtier->createEtablissement(EtablissementFamilles::FAMILLE_COURTIER);
$etablissementcourtier->nom = "Etablissement de courtage";
$etablissementcourtier->save();
$id = $etablissementcourtier->getSociete()->getidentifiant();
$comptecourtier = CompteClient::getInstance()->findByIdentifiant($id."01");
$comptecourtier->addTag('test', 'test');
$comptecourtier->save();
$t->is($comptecourtier->tags->automatique->toArray(true, false), array('etablissement', 'courtier'), "Création d'un etablissement courtier met à jour le compte");

$societeintermediaire = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_intermediaire_societe')->getSociete();
$etablissementintermediaire = $societeintermediaire->createEtablissement(EtablissementFamilles::FAMILLE_REPRESENTANT);
$etablissementintermediaire->region = EtablissementClient::REGION_CVO;
$etablissementcourtier->nom = "Etablissement d'intermediaire de la région";
$etablissementintermediaire->save();
$id = $etablissementintermediaire->getSociete()->getidentifiant();
$compteintermediaire = CompteClient::getInstance()->findByIdentifiant($id."01");
$compteintermediaire->addTag('test', 'test');
$compteintermediaire->save();
$t->is($compteintermediaire->tags->automatique->toArray(true, false), array('etablissement', 'representant'), "Création d'un etablissement intermediaire met à jour le compte");

$societecoop = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_cooperative_societe')->getSociete();
$etablissementcoop = $societecoop->createEtablissement(EtablissementFamilles::FAMILLE_COOPERATIVE);
$etablissementcoop->region = EtablissementClient::REGION_CVO;
$etablissementcoop->nom = "Etablissement coopérative de la région";
$etablissementcoop->save();
$id = $etablissementcoop->getSociete()->getidentifiant();
$comptecoop = CompteClient::getInstance()->findByIdentifiant($id."01");
$comptecoop->addTag('test', 'test');
$comptecoop->addTag('test', 'test_coop');
$comptecoop->save();
$t->is($comptecoop->tags->automatique->toArray(true, false), array( 'etablissement','cooperative'), "Création d'un etablissement coop met à jour le compte");

$viti_compte = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti');
$t->ok($viti_compte, 'le tag test_viti retourne un compte');
$viti = $viti_compte->getEtablissement();
$t->ok($viti, 'le compte test_viti est un établissement');

$nego_compte =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego');
$t->ok($nego_compte, 'le tag test_nego retourne un compte');
$nego = $nego_compte->getEtablissement();
$t->ok($nego, "le compte test_nego est un etablissement");

$coop_compte =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_coop');
$t->ok($coop_compte, 'le tag test_coop retourne un compte');
$coop = $coop_compte->getEtablissement();
$t->ok($coop, "le compte test_coop est un etablissement");
