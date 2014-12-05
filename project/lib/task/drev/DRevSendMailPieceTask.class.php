<?php

class DRevSendMailRappelDocumentTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'send-mail-rappel-document';
        $this->briefDescription = "Envoi d'un mail de rappel des pièces non recus";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        sfContext::createInstance($this->configuration);

        $this->rows = acCouchdbManager::getClient()
                    ->startkey(array('DRev', '2014', array()))
                    ->endkey(array('DRev', '2014'))
                    ->descending(true)
                    ->getView('declaration', 'tous')->rows;

        foreach($this->rows as $row) {
            if($row->key[7]) {

                continue;
            }
            if(!$row->key[6]) {

                continue;
            }
            if(!$row->key[2]) {

                continue;
            }
            if($row->key[3]) {

                continue;
            }

            $doc = DRevClient::getInstance()->find($row->id);

            if(!$doc->validation || $doc->validation_odg || $doc->isPapier()) {

                continue;
            }

            if($doc->hasCompleteDocuments()) {

                continue;
            }

            if($doc->exist('documents_rappel') && $doc->documents_rappel) {

                continue;
            }

            $dateFrom = new DateTime($doc->validation);
            $dateFrom->modify("+ 15 days");
            $dateTo = new DateTime();

            if($dateFrom->format('Y-m-d') > $dateTo->format('Y-m-d')) {

                continue;
            }

            $sended = Email::getInstance()->sendDRevRappelDocuments($doc);

            if(!$sended) {
                echo sprintf("ERROR;SENDED_FAIL;%s\n", $doc->_id);
                continue;
            }

            $doc->add('documents_rappel', date('Y-m-d'));
            $doc->save();

            echo sprintf("SUCCESS;SENDED;%s\n", $doc->_id);
        }

    }
}