<?php

class ParcellaireUpdateAireTask extends sfBaseTask
{


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('commune_insee', sfCommandArgument::OPTIONAL, "Identifiant INSEE de la commune"),
            new sfCommandArgument('identifiant_inao', sfCommandArgument::OPTIONAL, "Identifiant INAO de l'aire"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'update-aire';
        $this->briefDescription = "Mise à jour de l'aire d'une commune";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $denominations = array();
        $communes = array();
        $empty_args = (!$arguments['commune_insee'] && !$arguments['identifiant_inao']);
        if ($arguments['commune_insee']) {
            $communes[] = $arguments['commune_insee'];
        }
        if ($arguments['identifiant_inao']) {
            $denominations[] = $arguments['identifiant_inao'];
        }else {
            if (count($communes) == 1)  {
                $denominations[] = AireClient::getInstance()->getDelimitationsArrayFromCommune($communes[0]);
            }else{
                foreach(ParcellaireConfiguration::getInstance()->getAiresInfos() as $a) {
                    $denominations[] = $a['denomination_id'];
                }
            }
        }
        foreach($denominations as $d) {
            if ($empty_args) {
                $communes = [];
                foreach(AireClient::getInstance()->getCommunesArrayFromDenominationId($d) as $c) {
                    $communes[$c] = $c;
                }
            }
            foreach($communes as $c) {
                try {
                    $aire = AireClient::getInstance()->createOrUpdateAireFromHttp($c, $d);
                    $aire->save();
                    echo "DEBUG: ".$aire->denomination_libelle." (".$aire->denomination_identifiant.") form ".$aire->commune_libelle." (".$aire->commune_identifiant.") imported\n";
                }catch(sfException $e) {
                    echo "Error: ".$e->getMessage()."\n";
                }
            }
        }
    }



}
