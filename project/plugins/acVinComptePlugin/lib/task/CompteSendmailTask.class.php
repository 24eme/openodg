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
                new sfCommandOption('test', null, sfCommandOption::PARAMETER_REQUIRED, 'Email de test', ''),
                new sfCommandOption('check', null, sfCommandOption::PARAMETER_REQUIRED, "Pas d'envoi, il y a juste un dump des parametres de mails", false),
                new sfCommandOption('replyto_email', null, sfCommandOption::PARAMETER_REQUIRED, "Email utilisé pour le reply to", false),
                new sfCommandOption('replyto_name', null, sfCommandOption::PARAMETER_REQUIRED, "Nom utilisé pour le reply to", false),
      // add your own options here
    ));

    $this->namespace        = 'compte';
    $this->name             = 'sendmail';
    $this->briefDescription = 'Envoi le mail de création de compte';
    $this->detailedDescription = <<<'EOF'
Envoi un mail avec les informations de création de compte (login + code creation)
Il faut passer en paramètre : l'identifiant (XXX001001), le sujet du mail, et le
fichier avec le corps du mail

  [php symfony compte:sendmail XXX001001 "Création de compte" mail.txt|INFO]

Le fichier doit contenir les placeholders %login% et %code_creation%
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);$this->configuration->loadMultiDatabases(null, $databaseManager);
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

    if ($options['test']) {
        $email = $options['test'];
    }
    $replyto = array(sfConfig::get('app_email_plugin_reply_to_adresse') => sfConfig::get('app_email_plugin_reply_to_name'));
    if ($options['replyto_email']) {
        $replyto = array($options['replyto_email'] => $options['replyto_email']);
        if ($options['replyto_name']) {
            $replyto[$options['replyto_email']] = $options['replyto_name'];
        }
    }

    $message = Email::getInstance()->newMailInstance();

    $regions = HabilitationClient::getInstance()->getRegionsHabilites($compte->getSociete()->getEtablissementPrincipal()->getIdentifiant());

    foreach (explode(';', $email) as $an_email) {
        try {
            $message->setSubject($this->parseTemplate($arguments['subject'], $compte));
            $message->setBody($this->parseTemplate(file_get_contents($arguments['body_template']), $compte));
            $message->setReplyTo($replyto);
            $message->setTo($an_email);

            if ($options['check']) {
                print_r([
                    "subject" => $message->getSubject(),
                    "body" => $message->getBody(),
                    "replyto" => $message->getReplyTo(),
                    "to" => $message->getTo(),
                    "from" => $message->getFrom(),
                    "cc" => $message->getCc(),
                ]);
                continue;
            }
            $resultSend = $this->getMailer()->send($message);
        }catch(Exception $e) {
            echo "ERROR;$compte->_id ($email);Mail error : ".$e->getMessage()."\n";
            continue;
        }
        if(!$resultSend) {
            echo "ERROR;$compte->_id ($email);Mail non envoyé\n";
            continue;
        }
        echo "SUCCESS;$compte->_id : " . $compte->nom_a_afficher . " ($email);Mail envoyé à ".date('Y-m-d H:i:s')."\n";
    }
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
