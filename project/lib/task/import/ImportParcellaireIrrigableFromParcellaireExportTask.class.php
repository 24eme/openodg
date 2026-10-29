<?php

class ImportParcellaireIrrigableFromParcellaireExportTask extends sfBaseTask
{

    /*
    protected $currentEtablissementKey = null;
    protected $currentEtablissement = null;
    */
    protected $currentIrrigable = null;
    protected $currentParcellaire = null;


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('millesime', sfCommandArgument::REQUIRED, "Millesime"),
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('dryrun', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
            new sfCommandOption('message', null, sfCommandOption::PARAMETER_REQUIRED, 'Message pour materiel et ressource', 'A définir'),
            new sfCommandOption('identifiant', null, sfCommandOption::PARAMETER_REQUIRED, 'limit to a specific identifiant', ''),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-irrigable-from-parcellaire-export';
        $this->briefDescription = 'Import des irrigables depuis l export des parcellaire';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);$this->configuration->loadMultiDatabases(null, $databaseManager);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->message = $options['message'];

        $handle = fopen($arguments['csv'], "r");
        $hashes = [];
        while (($datas = fgetcsv($handle, 0, ";")) !== false) {
            if ($options['identifiant'] && strpos($options['identifiant'], $datas[1]) === false) {
                continue;
            }
            if ( ! $this->currentIrrigable || $this->currentIrrigable->identifiant != $datas[1]) {
                $this->importHashes($hashes);
                $this->currentIrrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($datas[1], $arguments['millesime']);
                $this->currentParcellaire = ParcellaireClient::getInstance()->find($datas[33]);
                $hashes = [];
/*
                foreach($this->currentIrrigable->getParcelles() as $p) {
                    $hashes[] = $p->getHash();
                }
*/
            }
            $p = $this->currentParcellaire->getParcelleFromParcellaireId($datas[32])->getParcelleParcellaire();
            $h = $p->getHash();
            if ($p->getKey() != $p->getParcelleId()) {
                $h = $p->getParent()->getHash() . "/" . $p->getParcelleId();
            }
            $hashes[] = $h;
        }
        $this->importHashes($hashes);
    }

    protected function importHashes($hashes) {
        if (!count($hashes)) {
            return;
        }
        $produits = [];
        try {
            $this->currentIrrigable->setParcellesFromParcellaire($hashes, false);
        }catch(Exception $e) {
            echo "ERROR on ".$this->currentIrrigable->_id.' ('.$this->currentParcellaire->_id.")\n";
        }
        foreach($this->currentIrrigable->getParcelles() as $p) {
            if ($p->materiel || $p->materiel === 0) {
                continue;
            }
            $p->materiel = $this->message;
            $p->ressource = $this->message;
            $a = $p->getAppellation()->getLibelle();
            $produits[$a] = $a;
        }
        if (!$this->currentIrrigable->validation) {
            $this->currentIrrigable->validate();
        }
        $this->currentIrrigable->observations = 'Déclaration automatique '.implode(' ', array_keys($produits)).' réalisée le '.date('d/m/Y'). ' (parcelles indiquées comme "'.$this->message.'")';
        $this->currentIrrigable->save();
        echo $this->currentIrrigable->_id." importé\n";
    }
}
