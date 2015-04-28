<?php

class FixParcellaireAcheteursTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'parcellaire-acheteurs';
        $this->briefDescription = "Corrige les acheteurs qui n'on pas été affecté";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $p = ParcellaireClient::getInstance()->find($arguments['doc_id']);
        if(!$p) {
            return;
        }
        if(!$p->validation) {
            return;
        }

        $acheteurs = array();

        foreach($p->acheteurs as $type => $acheteurs_type) {
            foreach($acheteurs_type as $acheteur) {
                $acheteurs[$acheteur->getHash()] = $acheteur;
            }
        }
        
        $corrige = false;
        foreach($p->declaration->getProduitsCepageDetails() as $detail) {
            if(count($detail->getAcheteurs())) {
                continue;
            }

            $corrige = true;
            //echo $detail->getHash()."\n";

            foreach($acheteurs as $acheteur) {
                $detail->addAcheteur($acheteur);
            }
            
            //print_r($detail->getCepage()->getAcheteurs()->toArray(true, false));
        }

        if(!$corrige) {
            return;
        }

        if(count($acheteurs) > 1) {
            echo sprintf("WARNING;Plusieurs acheteurs;%s\n", $p->_id);
            return;
        }

        $p->save();

        echo sprintf("CORRIGÉE;%s;%s\n", $p->_id, $p->acheteurs->getFirst()->getFirst()->nom);
    }
}