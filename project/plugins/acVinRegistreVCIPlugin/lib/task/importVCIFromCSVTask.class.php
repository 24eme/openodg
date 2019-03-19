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
            $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$cvi);

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
                echo "Produit non trouvé : ".$data[8]."\n";
                continue;
            }

            if(!$registre->exist($confProduit->getHash())) {
                echo "Registre produit non trouvé : ".$registre->_id.":".$confProduit->getHash()."\n";
                continue;
            }

            $produit = $registre->get($confProduit->getHash());

            if(!preg_match("/\(([0-9]+|CAVEPARTICULIERE)\)/", $data[10], $matches)) {
                echo "CVI stockage non trouvé : ".$data[10]."\n";
                continue;
            }

            $cvi = $matches[1];

            if(!$produit->exist('details/'.$cvi)) {
                echo "Registre stockage non trouvé : ".$cvi."\n";
                continue;
            }

            $produitDetail = $produit->get('details/'.$cvi);

            if($produitDetail->destruction == $data[12] && $produitDetail->complement == $data[13] && $produitDetail->substitution == $data[14] && $produitDetail->rafraichi == $data[15] && $produitDetail->constitue == $data[16]) {
                //echo "Registre déjà ok\n";
                continue;
            }

            if($data[12] && $produitDetail->destruction != $data[12]) {
                $registre->addLigne($confProduit->getHash(), 'destruction', $data[12], $cvi);
            }
            if($data[13] && $produitDetail->complement != $data[13]) {
                $registre->addLigne($confProduit->getHash(), 'complement', $data[13], $cvi);
            }
            if($data[14] && $produitDetail->complement != $data[14]) {
                $registre->addLigne($confProduit->getHash(), 'substitution', $data[14], $cvi);
            }
            if($data[15] && $produitDetail->rafraichi != $data[15]) {
                $registre->addLigne($confProduit->getHash(), 'rafraichi', $data[15], $cvi);
            }
            if($data[16] && $produitDetail->constitue != $data[16]) {
                $registre->addLigne($confProduit->getHash(), 'constitue', $data[16], $cvi);
            }

            $registre->save();
            echo "Registre mise à jour ".$registre->_id." : stock fin ".$produitDetail->stock_final." hl\n";
        }
    }

}
