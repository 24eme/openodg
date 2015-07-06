<?php

class fixFactureCodeComptableTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'facture-code-comptable';
        $this->briefDescription = "Permet d'ajouter les codes comptables aux factures";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $f = FactureClient::getInstance()->find($arguments['doc_id']);

        if(!$f) {

            throw new sfException(sprintf("Facture introuvable %s", $arguments['doc_id']));
        }

        // Set date échéance
        $date_facturation_object = new DateTime($f->date_facturation);
        $f->date_echeance = $date_facturation_object->modify('+30 days')->format('Y-m-d');

        // Set code comptable produit
        
        $template = $f->getTemplate();
        foreach($f->lignes as $ligne_key => $ligne) {
            $ligne->produit_identifiant_analytique = $template->cotisations->get($ligne_key)->code_comptable;
        }

        $f->save();
        echo sprintf("SUCCESS;Facture mise à jour %s\n", $f->_id);
    }
}