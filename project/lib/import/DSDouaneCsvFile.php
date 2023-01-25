<?php

class DSDouaneCsvFile extends DouaneImportCsvFile {

    const CSV_TYPE = 0;
    const CSV_CAMPAGNE = 1;
    const CSV_OPERATEUR_RAISON_SOCIALE = 2;
    const CSV_SIRET = 3;
    const CSV_CVI = 4;
    const CSV_MILLESIME = 20;
    const CSV_VOLUME = 21;

    const CSV_ENTETES = '#Type;Campagne;Identifiant;CVI;Raison Sociale;Code Commune;Commune;Lieu de stockage;hash_produit;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;Hash;Millesime;Conditionnement;Volume'."\n";

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

        $lieu_de_stockage=$csv[7][0];

        $array_produits = array_slice($csv, 10);
        foreach ($array_produits as $ligne_produit) {
            if (!isset($ligne_produit[1])) {
                return;
            }
            $code_inao = trim($ligne_produit[0]);
            $produit = $this->configuration->findProductByCodeDouane($code_inao);
            if (!$produit) {
                $infos_produit = ";;;;;;;;;";
            } else {
                $infos_produit = $produit->getCertification()->getKey().';'.$produit->getGenre()->getKey().';'.$produit->getAppellation()->getKey().';'.$produit->getMention()->getKey().';'.$produit->getLieu()->getKey().';'.$produit->getCouleur()->getKey().';'.$produit->getCepage()->getKey().";".$produit->getLibelleComplet().';'.$code_inao.";".$produit->getHash();
            }
            $millesime = $ligne_produit[2];
            $conditionnement = $ligne_produit[3];
            $volume = $ligne_produit[4];

            $line = "$type;$campagne;$identifiant;$cvi;$raison_sociale;$code_commune;$commune;$lieu_de_stockage;$code_inao;$infos_produit;$millesime;$conditionnement;$volume;\n";
            echo($line);
        }
    }
}