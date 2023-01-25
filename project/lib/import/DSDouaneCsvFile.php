<?php

class DSDouaneCsvFile extends DouaneImportCsvFile {

    const CSV_ENTETES = '#Type;Campagne;Identifiant;CVI;Raison Sociale;Code Commune;Commune;Lieu de stockage;Code INAO;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Hash;Millesime;Conditionnement;Volume'."\n";

    public function convert($options = array()) {
        if (!$this->filePath) {
    		throw new sfException("La cible du fichier n'est pas spécifiée.");
    	}

        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        $type = "DS";

        preg_match('/ds-.+_(.+).csv/', $this->filePath, $campagne);
        $campagne = substr_replace($campagne[1], '/', 2, 0);

        $cvi = $csv[1][1];
        $siret = $csv[3][1];
        $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);

        $identifiant = $etablissement->getIdentifiant();
        $raison_sociale =  $etablissement->getNom();
        $code_commune = $etablissement->getCodePostal();
        $commune = $etablissement->getCommune();

        #Recupere les lieux
        $array_csv_all_from_lieux = array_slice($csv,7);

        $line_csv = 7;
        foreach($array_csv_all_from_lieux as $lieux){
            if(!$lieux[1]){
                break;
            }
            $array_lieux[]= $lieux[0];
            $line_csv++;
        }

        if(!$array_lieux){
            throw new sfException("PAS DE DECLARATION DE STOCK POUR LE CVI : ".$cvi);
        }

        #Recupere les produits par lieu
        $array_csv_all_from_produits = array_slice($csv, $line_csv+1);

        $i = 0;
        foreach($array_csv_all_from_produits as $pages_lieux){
            if(!isset($pages_lieux[1])){
                break;
            }
            if(!$pages_lieux[1]){
                $i++;
            }
            $array_produits[$array_lieux[$i]][] = $pages_lieux;
        }


        foreach($array_produits as $k => $v){
            if($k == $array_lieux[0]){
                $array_produits[$k] = array_slice($v,1);
                continue;
            }
            $array_produits[$k] = array_slice($v,2);
        }

        foreach($array_produits as $lieu_de_stockage => $produits){
            foreach($produits as $ligne_produit){
                $code_inao = trim($ligne_produit[0]);
                $produit = $this->configuration->findProductByCodeDouane($code_inao);
                if (!$produit) {
                    $libelle = $ligne_produit[1];
                    $infos_produit = ";;;;;;;$libelle;";
                } else {
                    $infos_produit = $produit->getCertification()->getKey().';'.$produit->getGenre()->getKey().';'.$produit->getAppellation()->getKey().';'.$produit->getMention()->getKey().';'.$produit->getLieu()->getKey().';'.$produit->getCouleur()->getKey().';'.$produit->getCepage()->getKey().";".$produit->getLibelleComplet().';'.$produit->getHash();
                }
                $millesime = $ligne_produit[2];
                $conditionnement = $ligne_produit[3];
                $volume = $ligne_produit[4];

                $line = "$type;$campagne;$identifiant;$cvi;$raison_sociale;$code_commune;$commune;$lieu_de_stockage;$code_inao;$infos_produit;$millesime;$conditionnement;$volume;\n";
                echo($line);
            }
        }

    }
}
