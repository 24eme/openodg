<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

foreach (CompteTagsView::getInstance()->listByTags('test', 'test') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      foreach($soc->getEtablissementsObj() as $k => $etabl) {
        //   foreach (VracClient::getInstance()->retrieveBySoussigne($etabl->etablissement->identifiant)->rows as $k => $vrac) {
        //     $vrac_obj = VracClient::getInstance()->find($vrac->id);
        //     $vrac_obj->delete();
        //   }
        //   foreach (DRMClient::getInstance()->viewByIdentifiant($etabl->etablissement->identifiant) as $id => $drm) {
        //     $drm = DRMClient::getInstance()->find($id);
        //     $drm->delete(false);
        //   }
        if ($etabl->etablissement) {
            $etabl->etablissement->delete();
        }
      }
      $soc->delete();
    }
}


$t = new lime_test(31);
$t->comment('création des différentes sociétés');

$codePostalRegion = "92100";

if($application == "ivbd") {
    $codePostalRegion = "24100";
}

$societeviti = SocieteClient::getInstance()->createSociete("SARL ACTUALYS JEAN", SocieteClient::TYPE_OPERATEUR);


$societeviti->email = "email@societe.com";
$societeviti->site_internet = "www.societe.fr";
$societeviti->telephone_perso = "00 00 00 00 00";
$societeviti->telephone_bureau = "11 11 11 11 11";
$societeviti->telephone_mobile = "22 22 22 22 22";
$societeviti->fax = "33 33 33 33 33";


$societeviti->adresse = "Adresse 1 ";
$societeviti->adresse_complementaire = "Adresse 2 ";
$societeviti->code_postal = $codePostalRegion;
$societeviti->commune = "Neuilly sur seine";
$societeviti->pays = "FR";
$societeviti->insee = "94512";



$societeviti->save();
$t->comment($societeviti->_id);
$t->ok(preg_match('/^'.SocieteClient::getInstance()->getSocieteFormatIdentifiantRegexp().'$/', $societeviti->identifiant), "L'identifiant ".$societeviti->identifiant." respecte le format ".SocieteClient::getInstance()->getSocieteFormatIdentifiant());
$t->is($societeviti->date_modification, date('Y-m-d'), "La date de modification de la société à la date du jour");
$id = $societeviti->getidentifiant();
$t->is($societeviti->code_comptable_client, $societeviti->identifiant, "Le code comptable a bien été créé");
$t->is($societeviti->raison_sociale, "SARL ACTUALYS JEAN", "La raison sociale est correcte");
$t->is($societeviti->getIntitule(), "SARL", "L'intitulé est SARL");
$t->is($societeviti->getRaisonSocialeWithoutIntitule(), "ACTUALYS JEAN", "La raison sociale sans l'intitule");
$t->comment("compte 1 : ".$id);
$compteSociete = CompteClient::getInstance()->findByIdentifiant($id);

$t->is($compteSociete->identifiant, $societeviti->identifiant, "La societe a un compte séparé");

$t->is($compteSociete->_get('email'), $societeviti->_get('email'), "La societe a le même email que le compte");
$t->is($compteSociete->_get('site_internet'), $societeviti->_get('site_internet'), "La societe a le même site_internet que le compte");
$t->is($compteSociete->_get('telephone_perso'), $societeviti->_get('telephone_perso'), "La societe a le même telephone_perso que le compte");
$t->is($compteSociete->_get('telephone_bureau'), $societeviti->_get('telephone_bureau'), "La societe a le même telephone bureau que le compte");
$t->is($compteSociete->_get('telephone_mobile'), $societeviti->_get('telephone_mobile'), "La societe a le même telephone_mobile que le compte");
$t->is($compteSociete->_get('fax'), $societeviti->_get('fax'), "La societe a le même fax que le compte");


$t->is($compteSociete->_get('adresse'), $societeviti->siege->adresse, "La societe a la même adresse que le compte");
$t->is($compteSociete->_get('adresse_complementaire'), $societeviti->siege->adresse_complementaire, "La societe a la même adresse_complementaire que le compte");
$t->is($compteSociete->_get('code_postal'), $societeviti->siege->code_postal, "La societe a le même code_postal que le compte");
$t->is($compteSociete->_get('commune'), $societeviti->siege->commune, "La societe a la même commune que le compte");
$t->is($compteSociete->_get('pays'), $societeviti->siege->pays, "La societe a le même pays que le compte");
$t->is($compteSociete->_get('insee'), $societeviti->siege->insee, "La societe a le même insee que le compte");


$compteSociete->addTag('test', 'test');
$compteSociete->addTag('test', 'test_viti_societe');
$compteSociete->save();
$t->is($compteSociete->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société viti crée un compte du même type");

$societenegocvo = SocieteClient::getInstance()->createSociete("société négo de la région test", SocieteClient::TYPE_OPERATEUR);
$societenegocvo->pays = "FR";
$societenegocvo->code_postal = $codePostalRegion;
$societenegocvo->commune = "Neuilly sur seine";
$societenegocvo->save();
$id = $societenegocvo->getidentifiant();
$t->comment("compte 2 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_region_societe');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société négo crée un compte du même type");

$societenegocvo = SocieteClient::getInstance()->createSociete("société négo 2 de la région test", SocieteClient::TYPE_OPERATEUR);
$societenegocvo->pays = "FR";
$societenegocvo->code_postal = $codePostalRegion;
$societenegocvo->commune = "Neuilly sur seine";
$societenegocvo->save();
$id = $societenegocvo->getidentifiant();
$t->comment("compte 3 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_region_2_societe');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société négo 2 crée un compte du même type");

$societenegohors = SocieteClient::getInstance()->createSociete("société négo hors région test", SocieteClient::TYPE_OPERATEUR);
$societenegohors->pays = "BE";
$societenegohors->code_postal = "1000";
$societenegohors->commune = "Bruxelles";
$societenegohors->save();
$id = $societenegohors->getidentifiant();
$t->comment("compte 4 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test');
$compte->addTag('test', 'test_nego_horsregion_societe');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société négo hors région crée un compte du même type");

$societecourtier = SocieteClient::getInstance()->createSociete("société courtier test", SocieteClient::TYPE_COURTIER);
$societecourtier->pays = "FR";
$societecourtier->code_postal = $codePostalRegion;
$societecourtier->commune = "Neuilly sur seine";
$societecourtier->save();
$id = $societecourtier->getidentifiant();
$t->comment("compte 5 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test_courtier_societe');
$compte->addTag('test', 'test');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société courtier crée un compte du même type");

$societeintermediaire = SocieteClient::getInstance()->createSociete("société intermédiaire test", SocieteClient::TYPE_COURTIER);
$societeintermediaire->pays = "FR";
$societeintermediaire->code_postal = $codePostalRegion;
$societeintermediaire->commune = "Neuilly sur seine";
$societeintermediaire->save();
$id = $societeintermediaire->getidentifiant();
$t->comment("compte 5 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test_intermediaire_societe');
$compte->addTag('test', 'test');
$compte->save();
$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société intermédiaire crée un compte du même type");

$societeintermediaire = SocieteClient::getInstance()->createSociete("société cooperative test", SocieteClient::TYPE_OPERATEUR);
$societeintermediaire->pays = "FR";
$societeintermediaire->code_postal = $codePostalRegion;
$societeintermediaire->commune = "Neuilly sur seine";
$societeintermediaire->save();
$id = $societeintermediaire->getidentifiant();
$t->comment("compte 6 : ".$id);
$compte = CompteClient::getInstance()->findByIdentifiant($id);
$compte->addTag('test', 'test_cooperative_societe');
$compte->addTag('test', 'test');
$compte->save();

$t->is($compte->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création de société intermédiaire crée un compte du même type");

$societeviti->date_modification = '2017-01-01';
$societeviti->save();
try {
  $societeviti->switchStatusAndSave();
  $t->is($societeviti->statut , SocieteClient::STATUT_SUSPENDU, "Changement de statut (suspendu) de la societe viti");
  $societeviti->date_modification = '2017-01-01';
  $societeviti->save();
  $societeviti->switchStatusAndSave();
  $t->is($societeviti->statut , SocieteClient::STATUT_ACTIF, "Changement de statut (actif) de la societe viti");
}catch(sfException $e) {
  $t->fail("Changement de statut de la societe viti");
}

$societedegust = SocieteClient::getInstance()->createSociete("SARL JEAN DEGUSTE", SocieteClient::TYPE_AUTRE);
$societedegust->email = "email@societe.com";
$societedegust->site_internet = "www.societe.fr";
$societedegust->telephone_perso = "00 00 00 00 00";
$societedegust->telephone_bureau = "11 11 11 11 11";
$societedegust->telephone_mobile = "22 22 22 22 22";
$societedegust->fax = "33 33 33 33 33";
$societedegust->adresse = "Adresse 1 ";
$societedegust->adresse_complementaire = "Adresse 2 ";
$societedegust->code_postal = $codePostalRegion;
$societedegust->commune = "Neuilly sur seine";
$societedegust->pays = "FR";
$societedegust->insee = "94512";
$societedegust->add('region', "TESTREGION");
$societedegust->save();
$id = $societedegust->getidentifiant();
$t->comment("compte 7 : ".$id);

$t->is($societedegust->getMasterCompte()->region, $societedegust->region, "La region a été copié dans le compte");
$t->is($societedegust->getMasterCompte()->tags->automatique->toArray(true, false), array('societe', 'autre'), "Création d'une société autre créé un compte du même type");

$compteDegustateur = $contact = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societedegust);
$t->isnt($compteDegustateur->identifiant, $societedegust->identifiant, "La societe a un interlocuteur séparé");
