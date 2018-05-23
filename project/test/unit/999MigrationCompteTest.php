<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if($application == "rhone") {

  $t = new lime_test(1);

  $numSocieteAutre = '022773'; #017826,043414,023906,023167,000330,022324,021834,043221,020673,023076,023078,021685,015124,022773

  $t->comment("Cas de test pour la migration : AUTRE ($numSocieteAutre) OPERATEUR (013435) (001728)");

  $migration_societe_task = new societeMigateAllComptesTask($this->dispatcher, $this->formatter);
  $migration_etablissement_task = new societeMigateAllComptesTask($this->dispatcher, $this->formatter);

  $t->comment("Sauvegarde de tout les comptes de AUTRE ($numSocieteAutre)");

  $societe1 = SocieteClient::getInstance()->find('SOCIETE-'.$numSocieteAutre);
  $comptesInterloc1 = array();

  foreach ($societe1->getContactsObj() as $key => $c) {
    $c_json = $c->toJson();
    if($c_json->compte_type == 'INTERLOCUTEUR'){
      unset($c_json->_id);
      unset($c_json->_rev);
      unset($c_json->identifiant);
      unset($c_json->date_modification);
      $comptesInterloc1[$key] = $c_json;
    }
  }

  $t->comment("migration de la société $numSocieteAutre et ses comptes");

  ob_start();
  $migration_societe_task->run(array('societe_id' => 'SOCIETE-'.$numSocieteAutre),array('verbose' => "1",'withSave' => "1", 'application' => "rhone"));
  $migration_societe_retour = ob_get_contents();
  ob_end_clean();
  $t->comment('Rapport de migration : '.$migration_societe_retour);

  $newsociete1 = SocieteClient::getInstance()->find('SOCIETE-'.$numSocieteAutre);
  foreach ($newsociete1->getContactsObj() as $newkey => $c) {
    $c_json = $c->toJson();
    if($c_json->compte_type == 'INTERLOCUTEUR'){
      unset($c_json->_id);
      unset($c_json->_rev);
      unset($c_json->identifiant);
      unset($c_json->date_modification);
      $md5NewCompte = md5(serialize($c_json));
      $found = false;
      $oldid = null;
      $newid = null;
      foreach ($comptesInterloc1 as $oldkey => $oldCompte) {
        if(md5(serialize($oldCompte)) == $md5NewCompte){
          $oldid = $oldkey;
          $newid = $newkey;
          $found = true;
          break;
        }
      }

      $t->is($found, true, $oldid." : un interlocuteur créé remplacera l'ancien et est lui est identique : $newid");
      $numInterlocuteur = preg_replace("/COMPTE-[0-9]{6}/","",$newid);
      $endIdSup10 = intval($numInterlocuteur) > 9 ;
      $t->is($endIdSup10, true, $newid." : l'interlocuteur créé a une terminaison d'id sup à 9");
    }
  }

  $migration_diff_comptes_task = new societeMigateVerifComptesTask($this->dispatcher, $this->formatter);
  ob_start();
  $migration_diff_comptes_task->run(array('societe_id' => 'SOCIETE-'.$numSocieteAutre),array( 'application' => "rhone"));
  $migration_diff_comptes_retour = ob_get_contents();
  ob_end_clean();

  $t->is($migration_diff_comptes_retour, "OK","La sauvegarde des champs du compte société s'est bien déroulée");

  $migration_comptes_reecriture_task = new societeMigateReecritureComptesTask($this->dispatcher, $this->formatter);
  ob_start();
  $migration_comptes_reecriture_task->run(array('societe_id' => 'SOCIETE-'.$numSocieteAutre),array( 'application' => "rhone"));
  $migration_comptes_reecriture_task_retour = ob_get_contents();
  ob_end_clean();

  $t->is($migration_comptes_reecriture_task_retour, "OK","La réécriture des champs du compte société s'est bien déroulée");

}
