<?php

class importOperateurIACsvTask extends sfBaseTask
{

  const CSV_RS = 0;
  const CSV_CVI = 1;
  const CSV_PRODUIT = 2;
  const CSV_ACTIVITES = 3;
  const CSV_STATUT = 4;
  const CSV_ADRESSE = 5;
  const CSV_COMPLEMENT = 6;
  const CSV_CP = 7;
  const CSV_VILLE = 8;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'operateur-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");

            
        }
    }
}
