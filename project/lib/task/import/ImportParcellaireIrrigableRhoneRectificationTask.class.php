<?php

class ImportParcellaireIrrigueRectificatifTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Irrigable ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-irrigable-rectificatif-rhone';
        $this->briefDescription = 'Rectification des materiel dans l\'import des parcellaires irrigables rhone';
        $this->detailedDescription = <<<EOF
        EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);$this->configuration->loadMultiDatabases(null, $databaseManager);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find($arguments['doc_id']);
        $materiels = ParcellaireIrrigableConfiguration::getInstance()->getMateriels();
        foreach($parcellaireIrrigable->declaration as $produit) {
            foreach ($produit->detail as $parcelle) {
                if (! ($parcelle->materiel == '0' | $parcelle->materiel == '1' | $parcelle->materiel == '2' | $parcelle->materiel == '3' | $parcelle->materiel == '4')) {
                    echo "[" . $parcellaireIrrigable->declarant->cvi . "] - " . $parcelle->parcelle_id . " - BON MATERIEL : " . $parcelle->materiel."\n\n";
                } else {
                    echo "[" . $parcellaireIrrigable->declarant->cvi . "] - " . $parcelle->parcelle_id . " - WAS -> " . $parcelle->materiel."\n";
                    $parcelle->materiel = $materiels[$parcelle->materiel]->id;
                    echo "NOW -> " . $parcelle->materiel."\n\n";
                }
            }
        }
        $parcellaireIrrigable->save();
    }
}
