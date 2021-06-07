<?php

class ImportParcellairesDouaniersTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Id, identifiant ou cvi d'un établissement"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('noscrapping', null, sfCommandOption::PARAMETER_OPTIONAL, 'Ne scrappe pas les fichiers depuis prodouane et utilise ceux déjà existant', null),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-douanier';
        $this->briefDescription = "Import d'un douanier";
        $this->detailedDescription = <<<EOF
The [import:parcellaires-douaniers|INFO] Importe le parcellaires de tous les ressortissants depuis prodouane.
Call it with:

  [php symfony import:parcellaire-douanier|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $contextInstance = sfContext::createInstance($this->configuration);
        $errors = array();
        $etablissement = EtablissementClient::getInstance()->findAny($arguments['identifiant']);

        if(!$etablissement) {
            echo $arguments['identifiant'] . ";ERREUR;Etablissement non trouvé\n";
            return;
        }

        try {
            $create = ParcellaireClient::getInstance()->saveParcellaire($etablissement, $errors, $contextInstance, !$options['noscrapping']);
        } catch(Exception $e) {
            $errors[] = $e->getMessage();
            $create = false;
        }

        foreach($errors as $error) {
            if(!$error) {
                continue;
            }
            echo $etablissement->_id . ";ERREUR;".$error."\n";
        }

        if($create)  {
            echo $etablissement->_id . ";SUCCES;parcellaire mis à jour\n";
        }
    }
}
