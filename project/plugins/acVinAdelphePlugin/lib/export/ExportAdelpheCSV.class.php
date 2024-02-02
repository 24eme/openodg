<?php

class ExportAdelpheCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {
        return "Raison sociale;Adresse;Commune;CVI;Mise en marché de BIB;Part BIB (%);Volume (hl);Contribution (€ HT);Identifiant\n";
    }

    public function __construct($doc, $header = true, $region = null) {
        $this->doc = $doc;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {
        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    public function export() {
      $csv = "";
      if($this->header) {
          $csv .= self::getHeaderCsv();
      }
      $mis_en_marche_bib = 'Non';
      if ($this->doc->conditionnement_bib) {
        $mis_en_marche_bib = ($this->doc->isRepartitionForfaitaire())? 'Oui, sur la base du standard' : 'Oui, sur le réel';

      }
    	$csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
      	$this->protectStr($this->doc->declarant->raison_sociale),
      	$this->protectStr($this->doc->declarant->adresse),
        $this->protectStr($this->doc->declarant->code_postal.' '.$this->doc->declarant->commune),
        $this->protectStr($this->doc->declarant->cvi),
      	$this->protectStr($mis_en_marche_bib),
        $this->formatFloat($this->doc->getTauxBibCalcule()),
        $this->formatFloat($this->doc->volume_conditionne_total),
        $this->formatFloat($this->doc->getPrixTotal()),
        $this->protectStr($this->doc->identifiant),
      );
      return $csv;
    }

    protected function formatFloat($value) {
        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
