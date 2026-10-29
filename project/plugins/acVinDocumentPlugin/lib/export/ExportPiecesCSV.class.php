<?php

class ExportPiecesCSV {

    protected $habilitation = null;
    protected $header = false;

    public static function getHeaderCsv() {
        return "identifiant;libelle;mime;date depot;visibilite;categorie;source;piece numero;fichiers;doc id\n";
    }

    public function __construct($header = true) {
        $this->header = $header;
    }

    public function getFileName() {

        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function protectStr($str) {
    	return str_replace(';', '-',  str_replace('"', '', $str));
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function exportAll() {
        $csv = "";
        if ($this->header) {
            $csv .= $this->getHeaderCsv();
        }
        foreach(PieceAllView::getInstance()->getAll() as $piece) {
            $csv .= $piece->key[PieceAllView::KEYS_IDENTIFIANT].";";
            $csv .= $this->protectStr($piece->key[PieceAllView::KEYS_LIBELLE]).";";
            $csv .= $piece->key[PieceAllView::KEYS_MIME].";";
            $csv .= $piece->key[PieceAllView::KEYS_DATE_DEPOT].";";
            $csv .= $piece->key[PieceAllView::KEYS_VISIBILITE].";";
            $csv .= $piece->key[PieceAllView::KEYS_CATEGORIE].";";
            $csv .= $piece->key[PieceAllView::KEYS_SOURCE].";";
            $csv .= $piece->value[PieceAllView::VALUES_KEY].";";
            $csv .= join('|', $piece->value[PieceAllView::VALUES_FICHIERS]).";";
            $csv .= $piece->id."\n";
        }
        return $csv;
    }

}
