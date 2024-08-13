<?php

class importParcellaireFromCsvDouaneTask extends sfBaseTask
{
    const EXIT_CODE_ETABLISSEMENT_INCONNU = 1;
    const EXIT_CODE_CVI_INCONNU = 2;
    const EXIT_CODE_ERREUR_LECTURE = 4;
    const EXIT_CODE_GENERATION_PARCELLE = 8;


    protected function configure()
    {
        // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('path', sfCommandArgument::REQUIRED, "Le path vers le fichier d'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            // add your own options here
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = 'import';
        $this->name             = 'parcellaire-from-csv-douane';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [import:-douane-for-parcellaire|INFO] Importe un parcellaire depuis un fichier d'import en csv.
Call it with:

  [php symfony import:parcellaire-from-csv-douane|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $file = $arguments['path'];
        $matches = array();
        if(!file_exists($file) || !preg_match("/parcellaire-([0-9]+)-(.+)/", $file, $matches)){
          throw new \sfException("Le fichier pointé n'existe pas");
        }

        if(count($matches) < 2){
          throw new \sfException("Le fichier pointé n'existe pas");
        }
        $cvi = $matches[1];

        $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);

        if (! $etablissement instanceof Etablissement) {
            echo sprintf("L'établissement de cvi : %s n'existe pas dans la base de donnée ",$cvi)."\n";
            exit(self::EXIT_CODE_ETABLISSEMENT_INCONNU);
        }

        // Mettre en forme le fichier via la classe
        try {
            $new_parcellaire = ParcellaireClient::getInstance()->findOrCreate(
                $etablissement,
                date('Y-m-d'),
                'PRODOUANE'
            );
            $parcellairecsv = new ParcellaireCsvFile($new_parcellaire, $file);
            $parcellairecsv->convert();
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            exit(self::EXIT_CODE_GENERATION_PARCELLE);
        }

        // Vérifier s'il y a une différence avec le document actuel
        $old_parcellaire = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);

        if ($old_parcellaire) {
            $old_parcelles = $old_parcellaire->getParcelles();
            $old_produits = $old_parcellaire->declaration;

            $new_parcelles = $new_parcellaire->getParcelles();
            $new_produits = $new_parcellaire->declaration;

            // Sauver le document si différent
            if (count($old_parcelles) !== count($new_parcelles) ||
                count($old_produits) !== count($new_produits))
            {
                $new_parcellaire->save();
                echo sprintf("Sauvegarde du nouveau parcellaire (prédédent : %s) : %s",$old_parcellaire->_id,$new_parcellaire->_id)."\n";
            } else {
                echo sprintf("Le parcellaire semble le même : %s pas de réimport",$old_parcellaire->_id)."\n";
            }
        } else {
            $new_parcellaire->save();
            echo sprintf("Sauvegarde du nouveau parcellaire (aucun précédent) : %s",$new_parcellaire->_id)."\n";
        }
    }
}
