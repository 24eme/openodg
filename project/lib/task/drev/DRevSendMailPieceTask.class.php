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
        $routing = clone ProjectConfiguration::getAppRouting();
        $context = sfContext::createInstance($this->configuration);
        $context->set('routing', $routing);

        $this->rows = acCouchdbManager::getClient()
                    ->startkey(array('DRev', '2014', array()))
                    ->endkey(array('DRev', '2014'))
                    ->descending(true)
                    ->getView('declaration', 'tous')->rows;

        foreach($this->rows as $row) {

            $doc = DRevClient::getInstance()->find($row->id);

            if($doc->exist('documents_rappel') && $doc->documents_rappel) {
                $doc->add('documents_rappels')->add(null, $doc->documents_rappel);
                $doc->remove('documents_rappel');
                echo "replace rappel ".$doc->_id." \n";
                $doc->save();
            }

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

            $nb_rappel = ($doc->exist('documents_rappels')) ? count($doc->documents_rappels->toArray(true, false)) : 0;

            if($nb_rappel > 1) {

                continue;
            }

            $dateFrom = new DateTime($doc->validation);
            $dateFrom->modify("+15 days");

            if($nb_rappel == 1) {
                $dateFrom = new DateTime($doc->documents_rappels->getLast());
                $dateFrom->modify("+30 days");
            }

            $dateTo = new DateTime();

            if($dateFrom->format('Y-m-d') > $dateTo->format('Y-m-d')) {

                continue;
            }

            $sended = Email::getInstance()->sendDRevRappelDocuments($doc, $nb_rappel);

            if(!$sended) {
                echo sprintf("ERROR;SENDED_FAIL;%s\n", $doc->_id);
                continue;
            }

            $doc->add('documents_rappels')->add(null, date('Y-m-d'));
            $doc->save();

            echo sprintf("SUCCESS;SENDED;%s\n", $doc->_id);
        }

    }
}