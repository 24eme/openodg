<?php

class ExportDegustationsManquantes implements InterfaceDeclarationExportCsv {

    protected $tournee = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {
        $header = "Millésime;CVI Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Produit";

        return $header."\n";
    }

    public function __construct($tournee, $header = true, $region = null, $extraFields = false) {
        $this->tournee = $tournee;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {

        return $this->tournee->_id . '_' . $this->tournee->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        if(!$this->tournee->produit) {
            return $csv;
        }

        $rows = DegustationClient::getInstance()
            ->startkey(array($this->tournee->millesime."", 'cuve_'.$this->tournee->appellation))
            ->endkey(array($this->tournee->millesime."", 'cuve_'.$this->tournee->appellation, array()))
            ->reduce(false)
            ->getView('drev', 'lots')->rows;

        foreach($rows as $row) {
            if(!preg_match("|^".$this->tournee->produit."|", $row->key[2])) {
                continue;
            }

            $identifiant = $row->key[4];

            if($this->tournee->degustations->exist($identifiant)) {
                $degustation = $this->tournee->getDegustationObject($identifiant);
                $finded = false;
                foreach($degustation->prelevements as $prelevement) {
                    if($prelevement->hash_produit == $row->key[2]) {
                        $finded = true;
                        break;
                    }
                }

                if($finded) {
                    continue;
                }
            }

            $csv .= sprintf("%s;%s;\"%s\";\"%s\";%s;\"%s\";%s\n",
                    $this->tournee->millesime,
                    $identifiant,
                    $row->value->raison_sociale,
                    $row->value->adresse,
                    $row->value->code_postal,
                    $row->value->commune,
                    $row->value->produit_libelle);
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
