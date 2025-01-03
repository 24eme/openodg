<?php

class CompteSendmailTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addArguments(array(
      new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, 'Societe identifiant'),
      new sfCommandArgument('subject', sfCommandArgument::REQUIRED, 'Sujet du mail'),
      new sfCommandArgument('body_template', sfCommandArgument::REQUIRED, 'Fichier du contenu du mail'),
    ));
    $this->addOptions(array(
			    new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
			    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
			    new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'compte';
    $this->name             = 'sendmail';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    sfContext::createInstance($this->configuration);

    $compte = CompteClient::getInstance()->findByIdentifiant($arguments['identifiant']);
    if(!$compte){
        throw new sfException("Le compte ".$arguments['identifiant']." n'existe pas");
    }

    $email = $compte->getSociete()->getTeledeclarationEmail();
    if(!$email){
        echo "ERROR;$compte->_id;L'opérateur n'a pas de mail\n";
        return null;
    }

    $message = Email::getInstance()->newMailInstance();

    $regions = HabilitationClient::getInstance()->getRegionsHabilites($compte->getSociete()->getEtablissementPrincipal()->getIdentifiant());

    $message->setSubject($this->parseTemplate($arguments['subject'], $compte));
    $message->setBody($this->parseTemplate(file_get_contents($arguments['body_template']), $compte));
    $message->setReplyTo(array(sfConfig::get('app_email_plugin_reply_to_adresse') => sfConfig::get('app_email_plugin_reply_to_name')));
    $message->setTo($email);

    $resultSend = $this->getMailer()->send($message);

    if(!$resultSend) {
        echo "ERROR;$compte->_id ($email);Mail non envoyé\n";
        return;
    }

    echo "SUCCESS;$compte->_id : " . $compte->nom_a_afficher . " ($email);Mail envoyé à ".date('Y-m-d H:i:s')."\n";
  }

    protected function parseTemplate($body, $compte){
        if(!$compte->getCodeCreation()) {
            throw new Exception("Pas de code de création");
        }

        $login = $compte->getLogin();
        $codeCreation = $compte->getCodeCreation();

        return str_replace(array("%login%", "%code_creation%"), array($login, $codeCreation), $body);
    }

}
