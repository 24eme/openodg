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
                    ->startkey(array('DRev', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(), array()))
                    ->endkey(array('DRev', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()))
                    ->descending(true)
                    ->reduce(false)
                    ->getView('declaration', 'tous')->rows;

        foreach($this->rows as $row) {
            if($row->key[DeclarationTousView::KEY_MODE] != DeclarationTousView::MODE_TELDECLARATION) {

                continue;
            }

            if($row->key[DeclarationTousView::KEY_STATUT] != DeclarationTousView::STATUT_A_APPROUVER) {

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
                $dateFrom->modify("+15 days");
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
