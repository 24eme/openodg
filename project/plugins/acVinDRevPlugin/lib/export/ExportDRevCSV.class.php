<?php

class ExportDRevCSV implements InterfaceDeclarationExportCsv {

    protected $drev = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    const CSV_CAMPAGNE = 0;
    const CSV_CVI = 2;
    const CSV_PRODUIT_CERTIFICATION = 10;
    const CSV_PRODUIT_GENRE = 11;
    const CSV_PRODUIT_APPELLATION = 12;
    const CSV_PRODUIT_MENTION = 13;
    const CSV_PRODUIT_LIEU = 14;
    const CSV_PRODUIT_COULEUR = 15;
    const CSV_PRODUIT_CEPAGE = 16;
    const CSV_PRODUIT_INAO = 17;
    const CSV_PRODUIT_DENOMINATION_COMPLEMENTAIRE = 18;
    const CSV_SUPERFICIE_REVENDIQUE = 20;
    const CSV_VOLUME_REVENDIQUE_ISSU_RECOLTE = 21;
    const CSV_VOLUME_REVENDIQUE_ISSU_VCI = 22;
    const CSV_VOLUME_REVENDIQUE_ISSU_MUTAGE = 23;
    const CSV_VOLUME_REVENDIQUE = 24;
    const CSV_VCI_STOCK_PRECEDENT = 25;
    const CSV_VCI_STOCK_DESTRUCTION= 26;
    const CSV_VCI_STOCK_COMPLEMENT = 27;
    const CSV_VCI_STOCK_SUBSTITUTION = 28;
    const CSV_VCI_STOCK_RAFRAICHI = 29;
    const CSV_VCI_STOCK_CONSTITUE = 30;
    const CSV_TYPE_DREV = 32;
    const CSV_LOT_NUMERO_CUVE = 34;
    const CSV_LOT_DESTINATION = 37;
    const CSV_DATE_VALIDATION_DECLARANT = 38;
    const CSV_DATE_VALIDATION_ODG = 39;

    public static function getHeaderCsv() {

        return "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Certification;Certification Libelle;Genre;Genre Libelle;Appellation;Appellation Libelle;Mention;Mention Libelle;Lieu;Lieu Libelle;Couleur;Couleur Libelle;Cepage;Cepage Libelle;INAO;Dénomination complémentaire;Produit;Superficie revendiqué;Volume revendiqué issu de la récolte;Volume revendiqué issu du vci;Volume revendiqué issu du mutage;Volume revendiqué net total;VCI Stock précédent;VCI Destruction;VCI Complément;VCI Substitution;VCI Rafraichi;VCI Constitué;VCI Stock final;Type de declaration;Date d'envoi à l'OI;Numéro du lot;Date Rev;Produit (millesime);Destination;Date de validation Déclarant;Date de validation ODG;Doc ID\n";
    }

    public function __construct($drev, $header = true, $region = null, $extraFields = false) {
        $this->drev = $drev;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {
        $name = $this->drev->_id;
        $name .= ($this->region)? "_".$this->region : "";
        $name .= $this->drev->_rev;
        return  $name . '.csv';
    }

    public function protectStr($str) {
    	return str_replace(';', '−', str_replace('"', '', $str));
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
        $date_declarant = $this->drev->validation;
        if($date_declarant){
          $date_declarant = date_create($date_declarant)->format('Y-m-d');
        }
        $date_odg = $this->drev->validation_odg;
        if($date_odg){
          $date_odg = date_create($date_odg)->format('Y-m-d');
        }
        foreach($this->drev->declaration->getProduitsWithoutLots($this->region) as $produit) {

            $configProduit = $produit->getConfig();
            $certification = $configProduit->getCertification()->getKey();
            $certificationLibelle = $configProduit->getCertification()->getLibelle();
            $genre = $configProduit->getGenre()->getKey();
            $genreLibelle =  $configProduit->getGenre()->getLibelle();
            $appellation = $configProduit->getAppellation()->getKey();
            $appellationLibelle = $configProduit->getAppellation()->getLibelle();
            $mention = $configProduit->getMention()->getKey();
            $mentionLibelle = $configProduit->getMention()->getLibelle();
            $lieu = $configProduit->getLieu()->getKey();
            $lieuLibelle = $configProduit->getLieu()->getLibelle();
            $couleur = $configProduit->getCouleur()->getKey();
            $couleurLibelle = $configProduit->getCouleur()->getLibelle();
            $cepage = $configProduit->getCepage()->getKey();
            $cepageLibelle = $configProduit->getCepage()->getLibelle();
            $inao = $configProduit->getCodeDouane();

            $denomination = $produit->denomination_complementaire;
            $libelle_complet = $produit->getLibelleComplet();
            $validation_odg = ($produit->exist('validation_odg') && $produit->validation_odg)? $produit->validation_odg : $date_odg;
            $csv .= $ligneBase;
            $csv .= sprintf(";Revendication;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $certification,$certificationLibelle,$genre,$genreLibelle,$appellation,$appellationLibelle,$mention,$mentionLibelle,$lieu,$lieuLibelle,$couleur,$couleurLibelle,$cepage,$cepageLibelle,$inao,$denomination,trim($libelle_complet), $this->formatFloat($produit->superficie_revendique),
                $this->formatFloat($produit->volume_revendique_issu_recolte), $this->formatFloat($produit->volume_revendique_issu_vci), $this->formatFloat($produit->volume_revendique_issu_mutage), $this->formatFloat($produit->volume_revendique_total),
                $this->formatFloat($produit->vci->stock_precedent), $this->formatFloat($produit->vci->destruction),$this->formatFloat($produit->vci->complement),
                $this->formatFloat($produit->vci->substitution), $this->formatFloat($produit->vci->rafraichi), $this->formatFloat($produit->vci->constitue), $this->formatFloat($produit->vci->stock_final),
                $mode, $date_envoi_oi, null, null, null, null, $date_declarant, $validation_odg, $this->drev->_id
                );
        }
        if($this->drev->exist('lots') && count($this->drev->lots) && (is_null($this->region) || $this->region == DeclarationClient::REGION_LOT)){
          foreach($this->drev->lots as $lot) {
            $configProduit = $lot->getConfig();
            if(!$configProduit){
                continue;
            }
            $inao = $configProduit->getCodeDouane();

            $libelle_complet = $lot->getProduitLibelle();

            $numLot = ($lot->exist('numero_logement_operateur'))? $lot->numero_logement_operateur : $lot->numero_cuve;
            $dateRev = $lot->date;
            $destination = $lot->getDestinationType()." ".$lot->getDestinationDate();

            $csv .= $ligneBase;
            $csv .= sprintf(";Revendication;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                DeclarationExportCsv::getProduitKeysCsv($configProduit),$inao,null,
                trim($libelle_complet), null, $this->formatFloat($lot->volume), null, null, $this->formatFloat($lot->volume), null,null,null, null, null, null, null,
                $mode, $date_envoi_oi, $this->protectStr($numLot), $dateRev, $this->protectStr($lot->millesime),$destination, $date_declarant, $date_odg, $this->drev->_id
            );
          }
        }
        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
