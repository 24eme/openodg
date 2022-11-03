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


    public static function getHeaderCsv() {
        return "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Tag Engagement;Statut Engagement;Libelle Engagement\n";
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
        $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s",
        $drev->campagne,
        $drev->_id,
        $drev->declarant->cvi,
        $drev->declarant->siret,
        $this->protectStr($drev->declarant->raison_sociale),
        $this->protectStr($drev->declarant->adresse),
        $drev->declarant->code_postal,
        $this->protectStr($drev->declarant->commune),
        $drev->declarant->email);

        foreach($this->drev->documents as $document){
            $csv .= $ligneBase;
            $libelle = strip_tags(str_replace(";","\;",$document->libelle));
            $csv .= sprintf(";%s;%s\n",$document->statut,$libelle);
        }

         return $csv;
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }


        foreach(DeclarationTousView::getInstance()->getAllByType('DRev') as $json_doc){
            $drev = DRevClient::getInstance()->find($json_doc->id);

            if(!$drev->exist('documents')){
                continue;
            }

            $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s",
            $drev->campagne,
            $drev->_id,
            $drev->declarant->cvi,
            $drev->declarant->siret,
            $this->protectStr($drev->declarant->raison_sociale),
            $this->protectStr($drev->declarant->adresse),
            $drev->declarant->code_postal,
            $this->protectStr($drev->declarant->commune),
            $drev->declarant->email);

            foreach($drev->documents as $tag => $document){
                $csv .= $ligneBase;
                $libelle = strip_tags(str_replace(";","\;",$document->libelle));
                $csv .= sprintf(";%s;%s;%s\n",$tag,$document->statut,$libelle);
            }
        }
        print($csv);
        return $csv;
    }

    public function protectStr($str) {
        return str_replace(';', '−', str_replace('"', '', $str));
    }

}