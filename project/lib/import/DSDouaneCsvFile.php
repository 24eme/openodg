<?php

class DSDouaneCsvFile extends DouaneImportCsvFile {

    const CSV_TYPE = 0;
    const CSV_CAMPAGNE = 1;
    const CSV_OPERATEUR_RAISON_SOCIALE = 2;
    const CSV_SIRET = 3;
    const CSV_CVI = 4;
    const CSV_MILLESIME = 20;
    const CSV_VOLUME = 21;

    const CSV_ENTETES = '#Type;Campagne;Identifiant;CVI;Raison Sociale;Code Commune;Commune;Lieu de stockage;Code INAO;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Hash;Millesime;Conditionnement;Volume'."\n";

    public function convert($options = array()) {
        if (!$this->filePath) {
    		throw new sfException("La cible du fichier n'est pas spécifiée.");
    	}

        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        $type = "DS";

        preg_match('/_(.+).csv/', $this->filePath, $campagne);
        $campagne = substr_replace($campagne[1], '/', 2, 0);

        $cvi = $csv[1][1];
        $siret = $csv[3][1];
        $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);

        $identifiant = $etablissement->getIdentifiant();
        $raison_sociale =  $etablissement->getNom();
        $code_commune = $etablissement->getCodePostal();
        $commune = $etablissement->getCommune();

        #Recupere les lieux
        $array_slice = array_slice($csv,7);

        $line_csv = 7;
        foreach($array_slice as $lieux){
            if(!$lieux[0]){ //ligne vide
                break;
            }
            $array_lieux[]= $lieux[0];
            $line_csv++;
        }

        #Recupere les produits par lieu
        $array_slice = array_slice($csv, $line_csv+1);

        $i = 0;
        foreach($array_slice as $pages_lieux){
            if(!$pages_lieux[0]){ //ligne vide
                $i++;
                continue;
            }
            $array_produits[$array_lieux[$i]][] = $pages_lieux;
        }

        //on supprime les 2 premiers élements pour chaque lieux :
        foreach($array_produits as $k => $v){
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