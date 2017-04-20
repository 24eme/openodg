<?php

class FixDegustationTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('tournee_id', sfCommandArgument::REQUIRED, "Tournee ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'degustation';
        $this->briefDescription = "Corrige les dégustations après une petite refonte";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tournee = TourneeClient::getInstance()->find($arguments['tournee_id']);


        if(!$tournee) {

            throw new sfException("Tournee introuvable");
        }
        echo $tournee->_id."\n";

        if($tournee->libelle == TourneeClient::TYPE_TOURNEE_CONSTAT_VTSGN) {
            return;
        }

        $tournee->millesime = $tournee->getMillesime();
        $tournee->type_tournee = TourneeClient::TYPE_TOURNEE_DEGUSTATION;
        $tournee->organisme = DegustationClient::ORGANISME_DEFAUT;
        $tournee->libelle = $tournee->constructLibelle();

        foreach($tournee->getDegustationsObject() as $degustation) {
            echo $degustation->_id."\n";
            $degustation->millesime = $tournee->millesime;
            $degustation->organisme = $tournee->organisme;
            $degustation->libelle = $tournee->libelle;
        }

        $tournee->save();
        $tournee->saveDegustations();
    }
}
