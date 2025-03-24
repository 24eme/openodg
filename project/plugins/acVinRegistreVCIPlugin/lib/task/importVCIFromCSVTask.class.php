<?php

class importVCIFromCSVTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('fichier', sfCommandArgument::REQUIRED, "Fichier csv des VCI"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'VCIFromCSV';
        $this->briefDescription = 'Import les VCI via un csv';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['fichier']) as $line) {
            $data = str_getcsv($line, ";");
            $campagne = $data[0];
            $cvi = $data[1];
            $id = "REGISTREVCI-".$cvi."-".($campagne);
            $etablissement = EtablissementClient::getInstance()->findByIdentifiant($cvi, acCouchdbClient::HYDRATE_JSON);

            if(!$etablissement) {
                echo "Établissement non trouvé : ".$cvi."\n";
                continue;
            }

            $registre = RegistreVCIClient::getInstance()->find($id);

            if(!$registre) {
                echo "Registre non trouvé : ".$id."\n";
                continue;
            }

            $confProduit = ConfigurationClient::getConfiguration()->identifyProductByLibelle($data[8]);

            if(!$confProduit) {
                echo "Produit non trouvé ".$id." : ".$data[8]."\n";
                continue;
            }

            if(!preg_match("/\(([0-9]+|CAVEPARTICULIERE)\)/", $data[10], $matches)) {
                echo "CVI stockage non trouvé : ".$data[10]."\n";
                continue;
            }

            $cviStockage = $matches[1];

            if ($cviStockage != RegistreVCIClient::LIEU_CAVEPARTICULIERE && !EtablissementClient::getInstance()->findByIdentifiant($cviStockage, acCouchdbClient::HYDRATE_JSON)) {
                echo "Établissement de stockage non trouvé : ".$cviStockage."\n";
                continue;
            }

            $volumes = [
                'destruction' => $this->formatFloat($data[12]),
                'complement' => $this->formatFloat($data[13]),
                'substitution' => $this->formatFloat($data[14]),
                'rafraichi' => $this->formatFloat($data[15]),
            ];

            foreach($volumes as $typeMouvement => $volume) {
                $mouvement = $registre->updateVCI($confProduit->getHash(), $typeMouvement, $volume, $cviStockage, "Import");
                if(!$mouvement) {
                    continue;
                }
                echo "Mouvement ajouté au registre ".$registre->_id." de ".$mouvement->produit_libelle." en ".$mouvement->mouvement_type." de ".$mouvement->volume." hl stocké chez ".$mouvement->detail_libelle."\n";
            }

            $registre->save();
        }
    }

    protected function formatFloat($value) {

        return (float) str_replace(',', '.', $value);
    }

}
