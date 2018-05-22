<?php

class TeledeclarationEnvoiEmailOuvertureTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addArguments(array(
      new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, 'Societe identifiant'),
    ));
    $this->addOptions(array(
			    new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
			    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
			    new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'teledeclaration';
    $this->name             = 'envoiEmailOuverture';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if(!isset($arguments['identifiant'])){
      throw new sfException("Cette tache doit être appelé avec un identifiant de société");
    }
    if(!preg_match("/^[A-Z]*[0-9]{5,8}$/",$arguments['identifiant'])){
      throw new sfException("L'identifiant de la société ". $arguments['identifiant']." est mal formé");
    }

    $compte = CompteClient::getInstance()->findByIdentifiant($arguments['identifiant']."01");
    if(!$compte){
      throw new sfException("Le compte associé n'existe pas");
    }

    $compteSociete = $compte->getMasterCompte();
    if($compteSociete->_id != $compte->_id){
      echo $compte->_id." n'est pas le compte principal\n";
    }

    $resultSend = $this->sendEmail($compte);
    var_dump($resultSend);
  }

  protected function sendEmail($compte) {
      $destMail = $compte->getSociete()->getEmails();
      if(!count($destMail)){
          echo "opérateur $compte->_id sans mail \n";
          return null;
      }
      echo "envoi du mail \n";
      $mailer = $this->getMailer();

      $body = $this->getBodyMail($compte);
      $subject = "Ouverture de votre portail interprofessionnel www.ivbdpro.fr et dématérialisation des DRMS";
      $firstMail = $destMail[0];
      $message = $this->getMailer()->compose(
                  array(sfConfig::get('app_email_plugin_from_adresse') => sfConfig::get('app_email_plugin_from_name')), $firstMail ,$subject, $body);

                  var_dump($subject,$body);
      // $resultSend = $mailer->send($message);
      echo "Mail envoyé à $firstMail pour l'ouverture de son compte ($compte->identifiant) \n";
      return $resultSend;
  }

    protected function getBodyMail($compte){

    $identifiant = $compte->getSociete()->identifiant;
    if($compte->getStatutTeledeclarant() != CompteClient::STATUT_TELEDECLARANT_INACTIF){
        throw new sfException("Le compte $compte->_id a déjà été créé !");
    }

    $codeCreation = str_replace("{TEXT}","", $compte->mot_de_passe);

    $body = "Madame, Monsieur,



Votre identifiant : $identifiant

Votre code de création de compte : $codeCreation


Nous vous souhaitons une bonne navigation sur le portail www.xxx.fr !";

    return $body;
    }

}
