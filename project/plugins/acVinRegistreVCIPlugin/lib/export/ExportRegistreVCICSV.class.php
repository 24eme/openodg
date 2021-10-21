<?php

class ExportRegistreVCICSV implements InterfaceDeclarationExportCsv {

    protected $registre = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {

        return "Campagne;CVI;SIRET;Raison sociale;Adresse;Code postal;Commune;Email;Produit;Dénomination complémentaire;Stockage;Stock précédent;Destruction;Complément;Substitution;Rafraichi;Constitue;Stock\n";
    }

    public function __construct($registre, $header = true, $region = null, $extraFields = false) {
        $this->registre = $registre;
        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->registre->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {

        return $this->registre->_id . '_' . $this->registre->_rev . '.csv';
    }

    public function export() {

        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $ligne_base = sprintf("%s;\"%s\";\"%s\";%s;%s;\"%s\";%s;%s", $this->registre->campagne, ($this->etablissement) ? $this->etablissement->cvi : $this->registre->identifiant, $this->etablissement->siret, $this->etablissement->raison_sociale, $this->etablissement->adresse, $this->etablissement->code_postal, $this->etablissement->commune, $this->etablissement->email);

        foreach($this->registre->declaration as $produit) {
            foreach($produit->details as $detail) {
                $csv .= $ligne_base.";".$produit->libelle.";".$detail->denomination_complementaire.";".$detail->stockage_libelle." (".$detail->stockage_identifiant.");".(($detail->stock_precedent) ? $this->formatFloat($detail->stock_precedent) : 0) .";".$this->formatFloat($detail->destruction).";".$this->formatFloat($detail->complement).";".$this->formatFloat($detail->substitution).";".$this->formatFloat($detail->rafraichi).";".$this->formatFloat($detail->constitue).";".$this->formatFloat($detail->stock_final)."\n";
            }
        }


        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
