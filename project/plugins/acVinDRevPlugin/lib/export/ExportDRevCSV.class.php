<?php

class ExportDRevCSV implements InterfaceDeclarationExportCsv {

    protected $drev = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;Superficie revendiqué;Volume revendiqué issu de la récolte;Volume revendiqué issu du vci;Volume revendiqué net total;VCI Stock précédent;VCI Destruction;VCI Complément;VCI Substitution;VCI Rafraichi;VCI Constitué;VCI Stock final;Type de declaration;Date d'envoi à l'OI;Numéro du lot;Date Rev;Produit (millesime);Destination (Date);Date de validation VCI;Date de validation ODG\n";
    }

    public function __construct($drev, $header = true) {
        $this->drev = $drev;
        $this->header = $header;
    }

    public function getFileName() {

        return $this->drev->_id . '_' . $this->drev->_rev . '.csv';
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->drev->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        if($this->drev->isAutomatique()) {
            $mode = 'AUTOMATIQUE';
        }

        $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s",
            $this->drev->campagne, $this->drev->identifiant, $this->drev->declarant->cvi, $this->drev->declarant->siret, $this->protectStr($this->drev->declarant->raison_sociale),
            $this->protectStr($this->drev->declarant->adresse), $this->drev->declarant->code_postal, $this->protectStr($this->drev->declarant->commune), $this->drev->declarant->email);
        $date_envoi_oi = ($this->drev->exist('envoi_oi') && $this->drev->envoi_oi)? $this->drev->envoi_oi : "";
        if($date_envoi_oi){
          $date_envoi_oi = date_create($date_envoi_oi)->format('Y-m-d H:i:s');
        }
        $date_vci = $this->drev->validation;
        $date_odg = $this->drev->validation_odg;

        foreach($this->drev->declaration->getProduitsWithoutLots() as $produit) {

            $configProduit = $produit->getConfig();
            $certification = $configProduit->getCertification()->getKey();
            $genre = $configProduit->getGenre()->getKey();
            $appellation = $configProduit->getAppellation()->getKey();
            $mention = $configProduit->getMention()->getKey();
            $lieu = $configProduit->getLieu()->getKey();
            $couleur = $configProduit->getCouleur()->getKey();
            $cepage = $configProduit->getCepage()->getKey();
            $inao = $configProduit->getCodeDouane();

            $libelle_complet = $produit->getLibelleComplet();

            $csv .= $ligneBase;
            $csv .= sprintf(";Revendication;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $certification,$genre,$appellation,$mention,$lieu,$couleur,$cepage,$inao, trim($libelle_complet), $this->formatFloat($produit->superficie_revendique),
                $this->formatFloat($produit->volume_revendique_issu_recolte), $this->formatFloat($produit->volume_revendique_issu_vci), $this->formatFloat($produit->volume_revendique_total),
                $this->formatFloat($produit->vci->stock_precedent), $this->formatFloat($produit->vci->destruction),$this->formatFloat($produit->vci->complement),
                $this->formatFloat($produit->vci->substitution), $this->formatFloat($produit->vci->rafraichi), $this->formatFloat($produit->vci->constitue), $this->formatFloat($produit->vci->stock_final),
                $mode, $date_envoi_oi, null, null, null, null, $date_vci, $date_odg
                );
        }

        foreach($this->drev->lots as $lot) {
            //Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO
            $configProduit = $lot->getConfig();
            $certification = $configProduit->getCertification()->getKey();
            $genre = $configProduit->getGenre()->getKey();
            $appellation = $configProduit->getAppellation()->getKey();
            $mention = $configProduit->getMention()->getKey();
            $lieu = $configProduit->getLieu()->getKey();
            $couleur = $configProduit->getCouleur()->getKey();
            $cepage = $configProduit->getCepage()->getKey();
            $inao = $configProduit->getCodeDouane();

            $libelle_complet = $lot->getProduitLibelle();

            $numLot = $lot->getNumero();
            $dateRev = $lot->date;
            $destination = $lot->getDestinationType()." ".$lot->getDestinationDate();

            $csv .= $ligneBase;
            $csv .= sprintf(";Revendication;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $certification,$genre,$appellation,$mention,$lieu,$couleur,$cepage,$inao,
                trim($libelle_complet), null, $this->formatFloat($lot->volume), null, $this->formatFloat($lot->volume), null,null,null, null, null, null, null,
                $mode, $date_envoi_oi, $numLot, $dateRev, $lot->millesime,$destination, $date_vci, $date_odg
            );
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
