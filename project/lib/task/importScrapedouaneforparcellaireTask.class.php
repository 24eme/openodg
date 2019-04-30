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
            new sfCommandArgument('compte', sfCommandArgument::REQUIRED, 'Le numéro de compte'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
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

        // add your code here
        $etablissement = EtablissementClient::getInstance()->find($arguments['compte']);

        if (! $etablissement instanceof Etablissement) {
            $this->logSection('etablissement', "L'établissement ${arguments['compte']} n'existe pas");
            exit(self::EXIT_CODE_ETABLISSEMENT_INCONNU);
        }

        $cvi = $etablissement->cvi;
        if (! $cvi) {
            $this->logSection('etablissement', "CVI inexistant pour l'établissement " . $etablissement->identifiant);
            exit(self::EXIT_CODE_CVI_INCONNU);
        }

        $scrapybin = sfConfig::get('app_scrapy_bin');
        $scrapydocs = sfConfig::get('app_scrapy_documents');

        $exit_code = 0;
        $output = [];
        exec($scrapybin.'/download_parcellaire.sh '.$cvi.' 2> /dev/null', $output, $exit_code);
        if ($exit_code !== 0) {
            throw new Exception("Problème dans l'execution de scrapy [errcode: $exit_code]");
        }

        $files = glob($scrapydocs.'/parcellaire-'.$cvi.'-*.csv');
        if (empty($files)) {
            throw new Exception("Le cvi ${cvi} n'a pas de parcelle");
        }

        $most_recent_file = array_pop($files);

        // Créer un ParcellaireCsvFile
        try {
            $csv = new Csv($most_recent_file);
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
        $old_parcellaire = ParcellaireClient::getInstance()->getLast($arguments['compte']);

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
                $this->logSection('import', "Sauvegarde du nouveau parcellaire");
            } else {
                $this->logSection('import', "Le parcellaire semble le même");
            }
        } else {
            $this->logSection('import', "Il n'y a pas de parcellaire existant");
            $new_parcellaire->save();
        }
    }
}
