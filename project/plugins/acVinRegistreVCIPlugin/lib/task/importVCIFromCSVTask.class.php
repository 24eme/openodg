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
                echo "Produit non trouvé ".$id." : ".$data[8]."\n";
                continue;
            }
            $produitDetail = null;

            if(!preg_match("/\(([0-9]+|CAVEPARTICULIERE)\)/", $data[10], $matches)) {
                echo "CVI stockage non trouvé : ".$data[10]."\n";
                continue;
            }

            $cvi = $matches[1];

            if(!$registre->exist($confProduit->getHash())) {
                echo "(Registre produit ajouté : ".$registre->_id.":".$confProduit->getHash().")\n";
                continue;
            } else {
                $produit = $registre->get($confProduit->getHash());

                if(!$produit->exist('details/'.$cvi)) {
                    echo "Registre stockage non trouvé ".$registre->_id." : ".$data[10]."\n";
                    continue;
                }

                $produitDetail = $produit->get('details/'.$cvi);

                if($produitDetail->destruction == $this->formatFloat($data[12]) && $produitDetail->complement == $this->formatFloat($data[13]) && $produitDetail->substitution == $this->formatFloat($data[14]) && $produitDetail->rafraichi == $this->formatFloat($data[15]) && $produitDetail->constitue == $this->formatFloat($data[16])) {
                    //echo "Registre déjà ok ".$registre->_id."\n";
                    continue;
                }
            }

            if($data[16] && (!$produitDetail || $produitDetail->constitue != $this->formatFloat($data[16]))) {
                $registre->addLigne($confProduit->getHash(), 'constitue', $this->formatFloat($data[16]), $cvi);
            }
            if($data[12] && (!$produitDetail || $produitDetail->destruction != $this->formatFloat($data[12]))) {
                $registre->addLigne($confProduit->getHash(), 'destruction', $this->formatFloat($data[12]), $cvi);
            }
            if($data[13] && (!$produitDetail || $produitDetail->complement != $this->formatFloat($data[13]) )) {
                $registre->addLigne($confProduit->getHash(), 'complement', $this->formatFloat($data[13]), $cvi);
            }
            if($data[14] && (!$produitDetail || $produitDetail->complement != $this->formatFloat($data[14]))) {
                $registre->addLigne($confProduit->getHash(), 'substitution', $this->formatFloat($data[14]), $cvi);
            }
            if($data[15] && (!$produitDetail || $produitDetail->rafraichi != $this->formatFloat($data[15]))) {
                $registre->addLigne($confProduit->getHash(), 'rafraichi', $this->formatFloat($data[15]), $cvi);
            }

            $produitDetail = $registre->get($confProduit->getHash())->get('details/'.$cvi);

            foreach($registre->getProduitsWithPseudoAppelations() as $pseudoAppellation) {
                if($pseudoAppellation->getLibelle() != $produitDetail->getParent()->getParent()->getAppellation()->getLibelle()) {
                    continue;
                }
                if(!$pseudoAppellation->isPseudoAppellation()) {
                    continue;
                }
                break;
            }

            $registre->save();
            echo "Registre mise à jour ".$registre->_id." : stock fin ".$produitDetail->stock_final." hl (".$pseudoAppellation->getStockFinal()." hl) \n";
        }
    }

    protected function formatFloat($value) {

        return (float) str_replace(',', '.', $value);
    }

}
