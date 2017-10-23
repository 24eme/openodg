<?php

class constatSendMailApprouveTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
           new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Id du document de constats"),
           new sfCommandArgument('constat_key', sfCommandArgument::REQUIRED, "Clé du constat"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'constat';
        $this->name = 'send-mail-approuve';
        $this->briefDescription = "Envoi le mail du constat";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        sfContext::createInstance($this->configuration);

        $constats = ConstatsClient::getInstance()->find($arguments['doc_id']);

        if(!$constats) {

            return;
        }

        $constat = $constats->constats->get($arguments['constat_key']);

        if(!$constat) {

            return;
        }

        if($constat->mail_sended) {

            return;
        }

        $constat->sendMailConstatsApprouves();

        if(!$constat->mail_sended) {

            return;
        }

        $constats->save();
        echo "Mail envoyé à ".$this->getDocument()->email."\n";
    }
}
