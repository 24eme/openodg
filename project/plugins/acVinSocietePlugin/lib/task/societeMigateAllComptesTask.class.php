<?php

class societeMigateAllComptesTask extends migrateCompteTask
{
  protected $verbose = null;
  protected $withSave = null;
  protected $contacts = array();
  protected $oldContacts = array();

  protected function configure()
  {
    // // add your own arguments here
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'isVerbose', '0'),
      new sfCommandOption('withSave', null, sfCommandOption::PARAMETER_OPTIONAL, 'isVerbose', '0'),
    ));
    // add your own options here
    $this->addArguments(array(
       new sfCommandArgument('societe_id', sfCommandArgument::REQUIRED, 'ID du societe')
    ));

    $this->namespace        = 'societe';
    $this->name             = 'migate-all-comptes';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony societe:migate-all-comptes SOCIETE-ID|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $this->verbose = boolval($options["verbose"]);
    $this->withSave = boolval($options["withSave"]);
    $societe = SocieteClient::getInstance()->find($arguments['societe_id']);
    if (!$societe) {
      throw new sfException("Societe non trouvée : ".$arguments['societe_id']);
    }
    $compteSociete = $societe->getMasterCompte();
    if(!$this->withSave && !$this->verbose){
      echo "$societe->_id / $compteSociete->_id analyse...\n";
    }
    foreach ($compteSociete->toJson() as $key => $value) {

      if($key == '_id' && str_replace("COMPTE-","",$value) != str_replace("SOCIETE-","",$societe->_id)."01"){
        throw new sfException("La societe et son compte principal ont des ids étranges : ".$value." ".$societe->_id);
      }
      if($key == 'identifiant' && $value != str_replace("SOCIETE-","",$societe->_id)."01"){
        throw new sfException("La societe et son compte principal ont des ids étranges : ".$value." ".$societe->_id);
      }
      if($key == 'origines'){
        if(!in_array($societe->_id,$value)){
        throw new sfException("L'origine du compte $compteSociete->_id n'a pas la société $societe->_id !");
        }
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour origines : ".implode(',',$value)."\n";
        }
      }
      $this->verifySocieteDuplicatedInfos($compteSociete,$societe,$key,$value,$this->verbose);
      $this->verifyAdresseSociete($compteSociete,$societe,$key,$value,$this->verbose);
      $this->displayGroupesTagsAndDroits($compteSociete,$societe,$key,$value,$this->verbose);
      $this->verifyContactSociete($compteSociete,$societe,$key,$value,$this->verbose);
      if($key == "societe_informations"){
        $this->verifySocieteInformationNode($compteSociete,$societe,$value,$this->verbose);
      }
      if($key == 'compte_type'){
        if("SOCIETE" != $value){
          throw new sfException("La type du compte $compteSociete->_id n'est pas SOCIETE ! : ".$value);
        }

        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour type : ".$value."\n";
        }
      }


      if($key == 'lat'){
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour site lat : ".$value."\n";
        }
      }
      if($key == 'lon'){
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour site lon : ".$value."\n";
        }
      }
      if($key == "etablissement_informations"){
        $fields = get_object_vars($value);
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour etablissement informations (".implode(",",array_keys($fields)).") [cvi=$value->cvi, ppm=$value->ppm] \n";
        }
        if(count($fields) > 2){
          throw new sfException("Le nombre de champs d'etablissement information du compte $compteSociete->_id est trop grand $societe->_id : ".implode(",",$fields));
        }
      }
      if($key == 'interpro'){
        if($societe->interpro != $value){
          throw new sfException("L'interpro du compte $compteSociete->_id n'est pas la même que celle dans la société $societe->_id : ".$value);
        }
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour interpro : ".$value."\n";
        }
      }
      if($key == 'statut'){
        if(!is_null($societe->statut) && ($societe->statut != $value)){
          throw new sfException("Le statut du compte $compteSociete->_id n'est pas la même que celle dans la société $societe->_id : ".$value);
        }
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour statut : ".$value."\n";
        }
      }

      if($key == 'teledeclaration_active'){
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour teledeclaration_active_compte : ".$value."\n";
        }
      }
      if($key == 'date_modification'){
        if($this->verbose){
          echo "$societe->_id : Le compte $compteSociete->_id a pour date_modification : ".$value."\n";
        }
      }

      if(!in_array($key,self::$list_fields_analysed)){
        throw new sfException("Le champs $key du compte n'a pas été analysé ");
      }
      $compte_societe_saved = $societe->add("compte_societe_saved");

      $compteSocieteJson = $compteSociete->toJson();
      unset($compteSocieteJson->_id);
      unset($compteSocieteJson->_rev);
      unset($compteSocieteJson->type);
      unset($compteSocieteJson->identifiant);

      foreach ($compteSocieteJson as $key => $value) {
        $compte_societe_saved->add($key,$value);
      }
    }
    if($this->withSave){
      $this->moveContacts($societe);
      echo "Save $societe->_id avec les infos de son compte \n";
      $societe->save();
    }
  }

  public function moveContacts($societe){
    foreach ($societe->getInterlocuteursWithOrdre() as $key => $contact) {
      $compte = CompteClient::getInstance()->find($key);
      if($compte->compte_type == "INTERLOCUTEUR"){
        $this->oldContacts[$compte->_id] = $compte;
        $this->contacts[$compte->_id] = clone $compte;
        echo "L'interlocuteur $compte->_id est à déplacer \n";
      }
    }

    foreach ($this->oldContacts as $idCompte => $oldComteToRemove) {
      $societe->removeContact($oldComteToRemove->_id);
      $societe->save();
    }
    foreach ($this->contacts as $idCompte => $compte) {
      $newCompteInterlocuteur = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);

      foreach ($compte as $key => $value) {
        if($key != '_id' && $key != '_id' && $key != 'identifiant'){
          $newCompteInterlocuteur->add($key,$value);
        }
      }

      $newCompteInterlocuteur->save();
      echo "L'interlocuteur $newCompteInterlocuteur->_id a été créer \n";
    }
  }
}
