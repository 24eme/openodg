<?php

class DouaneFichier extends Fichier {
    public function generateDonnees() {
        $classExport = DeclarationClient::getExportCsvClassName($this->type);
        $export = new $classExport($this, false);

        return $export->getCsv();
    }

    public function generateDonnees() {
        if (!$this->exist('donnees') || count($this->donnees) < 1) {
            $this->add('donnees');
            $generate = false;
            foreach ($this->getCsv() as $datas) {
                $this->addDonnee($datas);
            }
        }
        return false;
    }

    public function addDonnee($data) {
        if (!$data || !isset($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]) || empty($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION])) {
            return null;
        }

        $hash = "certifications/".$data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[DouaneCsvFile::CSV_PRODUIT_GENRE]."/appellations/".$data[DouaneCsvFile::CSV_PRODUIT_APPELLATION]."/mentions/".$data[DouaneCsvFile::CSV_PRODUIT_MENTION]."/lieux/".$data[DouaneCsvFile::CSV_PRODUIT_LIEU]."/couleurs/".$data[DouaneCsvFile::CSV_PRODUIT_COULEUR]."/cepages/".$data[DouaneCsvFile::CSV_PRODUIT_CEPAGE];

        if(!$this->getConfiguration()->declaration->exist($hash)) {
            return null;
        }

        $item = $this->donnees->add();
        $item->produit = $hash;
        $item->produit_libelle = $this->getConfiguration()->declaration->get($hash)->getLibelleComplet();
        $item->complement = $data[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
        $item->categorie = $data[DouaneCsvFile::CSV_LIGNE_CODE];
        $item->valeur = VarManipulator::floatize($data[DouaneCsvFile::CSV_VALEUR]);
        if ($data[DouaneCsvFile::CSV_TIERS_CVI]) {
            if ($tiers = EtablissementClient::getInstance()->findByCvi($data[DouaneCsvFile::CSV_TIERS_CVI])) {
                $item->tiers = $tiers->_id;
            }
        }
        if ($data[DouaneCsvFile::CSV_BAILLEUR_PPM]) {
            if ($tiers = EtablissementClient::getInstance()->findByPPM($data[DouaneCsvFile::CSV_BAILLEUR_PPM])) {
                $item->bailleur = $tiers->_id;
            }
        }

        return $item;
    }

    public function getCategorie(){
        return strtolower($this->type);
    }

    public function calcul($formule, $produitFilter = null) {
        $calcul = $formule;
        $numLignes = preg_split('|[\-+*\/() ]+|', $formule, -1, PREG_SPLIT_NO_EMPTY);
        foreach($numLignes as $numLigne) {
            $datas[$numLigne] = $this->getTotalValeur($numLigne, $produitFilter);
        }

        foreach($datas as $numLigne => $value) {
            $calcul = str_replace($numLigne, $value, $calcul);
        }

        return eval("return $calcul;");
    }

    public function getTotalValeur($numLigne, $produitFilter = null) {
        $value = 0;

        $produitFilter = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
        $produitExclude = (bool) $produitExclude;
        $regexpFilter = "#(".implode("|", explode(",", $produitFilter)).")#";

        foreach($this->donnees as $donnee) {
            if($produitFilter && !$produitExclude && !preg_match($regexpFilter, $donnee->produit)) {
                continue;
            }
            if($produitFilter && $produitExclude && preg_match($regexpFilter, $donnee->produit)) {
                continue;
            }
            if($donnee->categorie != str_replace("L", "", $numLigne)) {
                continue;
            }

            $value += VarManipulator::floatize($donnee->valeur);
        }

        return $value;
    }
}
