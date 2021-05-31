<?php

class ExportLotsCSV {

    protected $header = false;
    protected $appName = null;
    protected $withHistorique = false;

    public static function getHeaderCsv() {
        return "Application;Declarant Id;Declarant Nom;Date;Campagne;Num Dossier;Num Archive;Provenance Doc Ordre;Provenance Doc Type;Provenance Doc Id;Lot Id;Libelle;Redegustation;Volume;Statut;Detail\n";
    }

    public function __construct($header = true, $appName = null, $withHistorique = false) {
        $this->header = $header;
        $this->appName = $appName;
        $this->withHistorique = $withHistorique;
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    protected function getLots() {
      if ($this->withHistorique) {
        return MouvementLotHistoryView::getInstance()->getAllLotsWithHistorique()->rows;
      } else {
        return MouvementLotHistoryView::getInstance()->getAllLotsByLevel()->rows;
      }
    }

    public function exportAll() {
        $csv = "";
        $lots = $this->getLots();
        if ($this->header) {
            $csv .= $this->getHeaderCsv();
        }
        foreach($lots as $lot) {
          $values = (array)$lot->value;
          if (!isset(Lot::$libellesStatuts[$values['statut']])) {
            continue;
          }
          // Suppression de doublon
          $tabUniqueId = explode('-', $values['lot_unique_id']);
          $IdFromUniqueId = $tabUniqueId[count($tabUniqueId)-1];
          if ($IdFromUniqueId != $values['numero_archive']) {
            continue;
          }
          $statut = Lot::$libellesStatuts[$values['statut']];
          $date = preg_split('/( |T)/', $values['date'], -1, PREG_SPLIT_NO_EMPTY);
          $redegustation = (preg_match("/ème dégustation/", $values['libelle']))? 'oui' : null;
          $volume = str_replace('.', ',', $values['volume']);
          $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
              $this->appName,
              $values['declarant_identifiant'],
              $values['declarant_nom'],
              $date[0],
              $values['campagne'],
              $values['numero_dossier'],
              $values['numero_archive'],
              $values['document_ordre'],
              $values['document_type'],
              $values['document_id'],
              $values['lot_unique_id'],
              $values['libelle'],
              $redegustation,
              $volume,
              $statut,
              $values['detail']
          );
        }
        return $csv;
    }

}
