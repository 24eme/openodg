<?php

class exportChaisCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'chais-csv';
        $this->briefDescription = "Export csv des établissements";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = EtablissementClient::getInstance()->findAll();

        echo "Identifiant établissement;Raison sociale établissement;Nom;Adresse;Code postal;Commune;Partage;Attributs\n";

        foreach($results->rows as $row) {
            $etablissement = EtablissementClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);

            foreach($etablissement->chais as $chai) {

                $attributs = array();
                foreach($chai->attributs as $attribut) {
                    $attributs[] = $attribut;
                }
                sort($attributs);
                $attributs = implode("|", $attributs);

                echo
                str_replace("SOCIETE-", "", $etablissement->id_societe).";".
                $etablissement->raison_sociale.";".
                $chai->nom.";".
                str_replace('"', '', $chai->adresse).";".
                $chai->code_postal.";".
                $chai->commune.";".
                $chai->partage.";".
                $attributs.
                "\n";
            }
        }
    }
}
