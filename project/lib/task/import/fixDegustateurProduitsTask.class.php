<?php

class FixDegustateurProduitsTask extends sfBaseTask
{

    const CSV_EXPERT_ALSACE         = 0;
    const CSV_EXPERT_CREMANT        = 1;
    const CSV_EXPERT_VT_SGN         = 2;
    const CSV_EXPERT_MAGW           = 3;
    const CSV_EXPERT_GC             = 4;
    const CSV_IDENTIFIANT           = 5;

    protected function configure()
    {
        $this->addArguments(array(
           new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Donnees au format CSV")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'DegustateurProduits';
        $this->briefDescription = "Corrige le parcellaire passé en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            $compte = CompteClient::getInstance()->find('COMPTE-D'.sprintf("%06d", $data[self::CSV_IDENTIFIANT]));

            if(!$compte) {
                echo sprintf("ERROR;Compte non trouvé %s;%s", $data[self::CSV_IDENTIFIANT], $line);
                continue;
            }

            if(trim($data[self::CSV_EXPERT_ALSACE]) == "x") {
                $compte->infos->produits->add("-declaration-certification-genre-appellation_ALSACE", "AOC Alsace");
                echo sprintf("INFO;Expert alsace;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_CREMANT])) {
                echo sprintf("INFO;Expert crémant;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_VT_SGN])) {
                echo sprintf("INFO;Expert vt sgn;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_MAGW])) {
                echo sprintf("INFO;Expert marc de gewurtz;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_GC])) {
                echo sprintf("INFO;Expert grand gru;%s\n", $line);
            }

            $compte->save();
        }
    }
}