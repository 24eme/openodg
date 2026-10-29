<?php

class ExportDocEngagementCSV{

    protected $doc = null;
    protected $header = false;
    private $docTypes = [];

    const CSV_CAMPAGNE = 0;
    const CSV_DOC_IDENTIFIANT = 1;
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

    public function __construct($doc = null,$header = true) {
        if($doc){
            $this->doc = $doc;
        }
        $this->header = $header;
        $this->docTypes[] = DRevClient::TYPE_MODEL;
        if (class_exists('TirageClient')) {
            $this->docTypes[] = TirageClient::TYPE_MODEL;
        }
    }

    public function getFileName() {
        return  'engagements.csv';
    }

    public function exportForOneDoc(){
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $csv .= $this->getCsvLinesByDoc($this->doc);
        return $csv;
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        foreach ($this->docTypes as $type) {
                foreach(DeclarationExportView::getInstance()->getDeclarations($type)->rows as $json_doc){
                    $doc = DeclarationClient::getInstance()->find($json_doc->id);
                    if($doc instanceof DRev && !$doc->isMaster()){
                        continue;
                    }
                    $csv .= $this->getCsvLinesByDoc($doc);
                }

        }

        return $csv;
    }

    public function getCsvLinesByDoc($doc) {
        if (!$doc->exist('documents')) {
            return;
        }

        $csv = "";
        $periode = substr($doc->campagne, 0, 4);
        $conf = ConfigurationClient::getInstance()->getConfiguration($periode.'-10-01');
        $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s",
            Organisme::getCurrentOrganisme(),
            $doc->_id,
            $doc->campagne,
            $doc->identifiant,
            ($doc->declarant->exist('famille')) ? $doc->declarant->famille : null,
            $doc->declarant->cvi,
            $doc->declarant->siret,
            $this->protectStr($doc->declarant->raison_sociale),
            $this->protectStr($doc->declarant->adresse),
            $doc->declarant->code_postal,
            $this->protectStr($doc->declarant->commune),
            $doc->declarant->email
        );

        foreach($doc->documents as $key => $document){
            $tabKey = explode('_', $key);
            $tabKeySize = count($tabKey);
            $hashProduit = null;
            $produit = null;
            if (strpos($tabKey[$tabKeySize-1], '/appellations/') !== false) {
                $hashProduit = $tabKey[$tabKeySize-1];
                $produit = $conf->declaration->getOrAdd($hashProduit)->getLibelleComplet();
            }
            $csv .= $ligneBase;
            $libelle = strip_tags(str_replace(";","\;",$document->exist('libelle') ? $document->libelle : null));
            $csv .= sprintf(";%s;%s;%s",$key,$document->statut,$libelle);
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
