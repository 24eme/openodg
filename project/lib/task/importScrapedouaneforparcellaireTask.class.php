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
        $scrapydocs = '/tmp'; //sfConfig::get('app_scrapy_documents');

        $files = glob($scrapydocs.'/parcellaire-'.$cvi.'-*.csv');
        if (empty($files)) {
            $this->logSection('cvi', "Le cvi ${cvi} n'a pas de parcelle");
            exit(16);
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
                $this->logSection('import', sprintf("Sauvegarde du nouveau parcellaire (prédédent : %s) : %s",$old_parcellaire->_id,$new_parcellaire->getParcellaire()->_id));
            } else {
                $this->logSection('import', "Le parcellaire semble le même");
            }
        } else {
            $new_parcellaire->save();
            $this->logSection('import', sprintf("Sauvegarde du nouveau parcellaire (aucun précédent) : %s",$new_parcellaire->getParcellaire()->_id));
        }
    }
}
