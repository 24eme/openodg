<?php

class RegistreVCIGenerateSuivantTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'registrevci';
        $this->name = 'generate-suivant';
        $this->briefDescription = "Génére le registre VCI de la campagne suivante";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $registre = RegistreVCIClient::getInstance()->findMasterByIdentifiantAndCampagne($arguments['identifiant'], $arguments['campagne']);
        if(!$registre) {
            return;
        }

        if(RegistreVCIClient::getInstance()->findMasterByIdentifiantAndCampagne($arguments['identifiant'], $arguments['campagne']+1, acCouchdbClient::HYDRATE_JSON)) {
            return;
        }

        try {
            $registreSuivant = $registre->generateSuivante();
        } catch (Exception $e) {
            echo $registre->_id.";".$e->getMessage()."\n";
            return;
        }

        $registreSuivant->save();

        echo $registreSuivant->_id."\n";
    }
}
