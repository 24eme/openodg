<?php

class ExportAdelpheCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {
        return "Campagne;Raison sociale;Adresse;Commune;CVI;Mise en marché de BIB;Part BIB (%);Volume conditionne total (hl);Contribution (€);Identifiant;Volume bouteille (hl);Volume BIB (hl);Quantité bouteille normale (uc);Prix bouteille normale (€);Quantité bouteille allégée (uc);Prix bouteille allégée (€);Quantité carton (uc);Prix carton (€);Quantité BIB 3L (uc);Prix BIB 3L (€);Quantité BIB 5L (uc);Prix BIB 5L (€);Quantité BIB 10L (uc);Prix BIB 10L (€)\n";
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
        $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
        $this->protectStr($this->doc->campagne),
      	$this->protectStr($this->doc->declarant->raison_sociale),
      	$this->protectStr($this->doc->declarant->adresse),
        $this->protectStr($this->doc->declarant->code_postal.' '.$this->doc->declarant->commune),
        $this->protectStr($this->doc->declarant->cvi),
      	$this->protectStr($mis_en_marche_bib),
        $this->formatFloat($this->doc->getTauxBibCalcule()),
        $this->formatFloat($this->doc->volume_conditionne_total),
        $this->doc->cotisation_prix_total,
        $this->protectStr($this->doc->identifiant),
        $this->formatFloat($this->doc->volume_conditionne_bouteille),
        $this->formatFloat($this->doc->volume_conditionne_bib),
        $this->doc->cotisation_prix_details->BOUTEILLES_NORMALES->quantite,
        $this->doc->cotisation_prix_details->BOUTEILLES_NORMALES->prix,
        $this->doc->cotisation_prix_details->BOUTEILLES_ALLEGEES->quantite,
        $this->doc->cotisation_prix_details->BOUTEILLES_ALLEGEES->prix,
        $this->doc->cotisation_prix_details->BOUTEILLES_CARTONS->quantite,
        $this->doc->cotisation_prix_details->BOUTEILLES_CARTONS->prix,
        $this->doc->cotisation_prix_details->BIB_3L->quantite,
        $this->doc->cotisation_prix_details->BIB_3L->prix,
        $this->doc->cotisation_prix_details->BIB_5L->quantite,
        $this->doc->cotisation_prix_details->BIB_5L->prix,
        $this->doc->cotisation_prix_details->BIB_10L->quantite,
        $this->doc->cotisation_prix_details->BIB_10L->prix,
      );
      return $csv;
    }

    protected function formatFloat($value) {
        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
