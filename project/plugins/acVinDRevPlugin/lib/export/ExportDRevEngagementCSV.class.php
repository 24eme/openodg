<?php

class ExportDRevEngagementCSV{

    protected $drev = null;
    protected $header = false;

    const CSV_CAMPAGNE = 0;
    const CSV_DREV_IDENTIFIANT = 1;
    const CSV_CVI = 2;
    const CSV_ENGAGEMENT_STATUT = 3;
    const CSV_ENGAGEMENT_LIBELLE = 4;

    public static function getHeaderCsv() {
        return "Campagne;Identifiant;CVI Declarant;Statut Engagement;Libelle Engagement\n";
    }

    public function __construct($drev, $header = true) {
        $this->drev = $drev;
        $this->header = $header;
    }

    public function getFileName() {
        $name = $this->drev->_id;
        $name .= ($this->region)? "_".$this->region : "";
        $name .= $this->drev->_rev;
        return  $name . 'engagements.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $ligneBase = sprintf("%s;%s;%s",$this->drev->campagne,$this->drev->_id,$this->drev->declarant->cvi);

        foreach($this->drev->documents as $document){
            $csv .= $ligneBase;
            $libelle = strip_tags(str_replace(";","\;",$document->libelle));
            $csv .= sprintf(";%s;%s\n",$document->statut,$libelle);
        }

        return $csv;
    }

}