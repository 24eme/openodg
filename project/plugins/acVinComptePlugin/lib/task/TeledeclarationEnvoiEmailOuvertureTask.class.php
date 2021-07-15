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

    $compte = CompteClient::getInstance()->findByIdentifiant($arguments['identifiant']);
    $urlportail = $arguments['urlportail'];
    if(!$compte){
      throw new sfException("Le compte associé n'existe pas");
    }

    $compteSociete = $compte->getMasterCompte();
    if($compteSociete->_id != $compte->_id){
      echo $compte->_id." n'est pas le compte principal\n";
    }

    $resultSend = $this->sendEmail($compte);
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
      $subject = "Ouverture de votre plateforme déclarative des ".Organisme::getInstance()->getNom()."";
      $firstMail = $destMail[0];
      $message = $this->getMailer()->compose(array(Organisme::getInstance()->getEmail() => Organisme::getInstance()->getNom()), $firstMail ,$subject, $body);

      $resultSend = $mailer->send($message);
      echo "Mail envoyé à $firstMail pour l'ouverture de son compte ($compte->identifiant) \n";
      return $resultSend;
  }

    protected function getBodyMail($compte){

    $identifiant = $compte->getSociete()->identifiant;

    $codeCreation = str_replace("{TEXT}","", $compte->mot_de_passe);

    $body = "Madame, Monsieur,

    Veuillez trouver ci dessous vos accès pour le portail du ".Organisme::getInstance()->getNom()." :

    ".Organisme::getInstance()->getUrl()."

    Ce portail permet de déclarer de vos Revendications, Changement de dénomination, Déclassement et la visualisation de vos factures.

    Nous saurions trop vous conseiller de, d'ores et déjà, créer votre compte en préparation de la prochaine campagne.

    La saisie en ligne sera pour vous un gain de temps, et l'assurance de la réception de vos demandes (DREV notamment) en temps et heure !

    Pour l'activer, il faut créer votre compte sous l'encart Première Connexion avec les identifiant suivant :

    Votre identifiant :$identifiant

    Votre code de création de compte : $codeCreation

    Nous vous souhaitons une bonne navigation sur le portail ".Organisme::getInstance()->getUrl()."!

    Pour toute question, merci de contacter ".Organisme::getInstance()->getResponsable()." du lundi au jeudi au ".Organisme::getInstance()->getTelephone()." .

    Belle journée !";

    return $body;
    }

}
