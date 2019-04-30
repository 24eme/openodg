<?php

class importScrapedouaneforparcellaireTask extends sfBaseTask
{
    const EXIT_CODE_ETABLISSEMENT_INCONNU = 1;
    const EXIT_CODE_CVI_INCONNU = 2;
    const EXIT_CODE_ERREUR_LECTURE = 4;
    const EXIT_CODE_GENERATION_PARCELLE = 8;


    protected function configure()
    {
        // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'Le numéro de compte'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            // add your own options here
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = 'import';
        $this->name             = 'scrape-douane-for-parcellaire';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [import:scrape-douane-for-parcellaire|INFO] task does things.
Call it with:

  [php symfony import:scrape-douane-for-parcellaire|INFO]
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
            $this->logSection('etablissement', sprintf("L'établissement de cvi : %s n'existe pas dans la base de donnée ",$cvi));
            exit(self::EXIT_CODE_ETABLISSEMENT_INCONNU);
        }

        // Créer un ParcellaireCsvFile
        try {
            $csv = new Csv($file);
        } catch (Exception $e) {
            $this->logSection('csv', $e->getMessage());
            exit(self::EXIT_CODE_ERREUR_LECTURE);
        }

        // Mettre en forme le fichier via la classe
        try {
            $new_parcellaire = new ParcellaireCsvFile($csv, new ParcellaireCsvFormat);
            $new_parcellaire->convert();
        } catch (Exception $e) {
            $this->logSection('parcelle', $e->getMessage());
            exit(self::EXIT_CODE_GENERATION_PARCELLE);
        }

        // Vérifier s'il y a une différence avec le document actuel
        $old_parcellaire = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);

        if ($old_parcellaire) {
            $old_parcelles = $old_parcellaire->getParcelles();
            $old_produits = $old_parcellaire->declaration;

            $new_parcelles = $new_parcellaire->getParcellaire()->getParcelles();
            $new_produits = $new_parcellaire->getParcellaire()->declaration;

            // Sauver le document si différent
            if (count($old_parcelles) !== count($new_parcelles) ||
                count($old_produits) !== count($new_produits))
            {
                $new_parcellaire->save();
                $this->logSection('import', sprintf("Sauvegarde du nouveau parcellaire (prédédent : %s) : %s",$old_parcellaire->_id,$new_parcellaire->getParcellaire()->_id));
            } else {
                $this->logSection('import', sprintf("Le parcellaire semble le même : %s pas de réimport",$old_parcellaire->_id));
            }
        } else {
            $new_parcellaire->save();
            $this->logSection('import', sprintf("Sauvegarde du nouveau parcellaire (aucun précédent) : %s",$new_parcellaire->getParcellaire()->_id));
        }
    }
}
