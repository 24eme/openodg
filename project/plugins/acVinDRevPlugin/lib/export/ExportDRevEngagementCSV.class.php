<?php

class ExportDRevEngagementCSV{

    protected $drev = null;
    protected $header = false;

    const CSV_CAMPAGNE = 0;
    const CSV_DREV_IDENTIFIANT = 1;
    const CSV_CVI = 2;
    const CSV_SIRET = 3;
    const CSV_NOM = 4;
    const CSV_ADRESSE = 5;
    const CSV_CODEPOSTAL = 6;
    const CSV_COMMUNE = 7;
    const CSV_EMAIL = 8;
    const CSV_ENGAGEMENT_TAG = 9;
    const CSV_ENGAGEMENT_STATUT = 10;
    const CSV_ENGAGEMENT_LIBELLE = 11;
    const CSV_MILLESIME = 12;
    const CSV_PRODUIT_LIBELLE = 13;
    const CSV_PRODUIT_HASH = 14;


    public static function getHeaderCsv() {
        return "Organisme;Doc Id;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Tag Engagement;Statut Engagement;Libelle Engagement;Millesime;Produit;Hash Produit\n";
    }

    public function __construct($drev = null,$header = true) {
        if($drev){
            $this->drev = $drev;
        }
        $this->header = $header;
    }

    public function getFileName() {
        return  'engagements.csv';
    }

    public function exportForOneDRev(){
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $csv .= $this->getCsvLinesByDrev($this->drev);
        return $csv;
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        foreach(DeclarationExportView::getInstance()->getDeclarations('DRev')->rows as $json_doc){
            $drev = DRevClient::getInstance()->find($json_doc->id);
            if(!$drev->exist('documents')){
                continue;
            }
            if(!$drev->isMaster()){
                continue;
            }
            $csv .= $this->getCsvLinesByDrev($drev);
        }
        return $csv;
    }

    public function getCsvLinesByDrev($drev) {
        $csv = "";
        $periode = substr($drev->campagne, 0, 4);
        $conf = ConfigurationClient::getInstance()->getConfiguration($periode.'-10-01');
        $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s",
        Organisme::getCurrentOrganisme(),
        $drev->_id,
        $drev->campagne,
        $drev->identifiant,
        $drev->declarant->famille,
        $drev->declarant->cvi,
        $drev->declarant->siret,
        $this->protectStr($drev->declarant->raison_sociale),
        $this->protectStr($drev->declarant->adresse),
        $drev->declarant->code_postal,
        $this->protectStr($drev->declarant->commune),
        $drev->declarant->email);
        foreach($drev->documents as $key => $document){
            $tabKey = explode('_', $key);
            $tabKeySize = count($tabKey);
            $hashProduit = null;
            $produit = null;
            if (strpos($tabKey[$tabKeySize-1], '/appellations/') !== false) {
                $hashProduit = $tabKey[$tabKeySize-1];
                $produit = $conf->declaration->getOrAdd($hashProduit)->getLibelleComplet();
            }
            $csv .= $ligneBase;
            $libelle = strip_tags(str_replace(";","\;",$document->libelle));
            $csv .= sprintf(";%s;%s",$document->statut,$libelle);
            $csv .= ';'.$periode;
            $csv .= ';'.$produit;
            $csv .= ';'.$hashProduit;
            $csv .= "\n";
        }
        return $csv;
    }

    public function protectStr($str) {
        return str_replace(';', '−', str_replace('"', '', $str));
    }

}
