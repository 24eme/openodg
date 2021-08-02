<?php

class ExportHistoriqueLotsCSV {

    protected $header = false;
    protected $appName = null;

    public static function getHeaderCsv() {
        return "Origine;Id Opérateur;Nom Opérateur;Campagne;Date lot;Num Dossier;Num Lot;Doc Ordre;Doc Type;Libellé du lot;Volume;Statut;Details;Organisme;Doc Id;Lot unique Id\n";
    }

    public function __construct($header = true, $appName = null) {
        $this->header = $header;
        $this->appName = $appName;
    }

    protected function getLots() {
        return MouvementLotHistoryView::getInstance()->getAllLotsWithHistorique()->rows;
    }

    public function exportAll() {
        $csv = "";
        $lots = $this->getLots();
        if ($this->header) {
            $csv .= $this->getHeaderCsv();
        }
        foreach($lots as $lot) {
          $values = (array)$lot->value;
          $statut = (isset(Lot::$libellesStatuts[$values['statut']]))? Lot::$libellesStatuts[$values['statut']] : $values['statut'];
          $date = preg_split('/( |T)/', $values['date'], -1, PREG_SPLIT_NO_EMPTY);
          $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
              $values['initial_type'],
              $values['declarant_identifiant'],
              VarManipulator::protectStrForCsv($values['declarant_nom']),
              $values['campagne'],
              $date[0],
              $values['numero_dossier'],
              $values['numero_archive'],
              $values['document_ordre'],
              $values['document_type'],
              VarManipulator::protectStrForCsv($values['libelle']),
              VarManipulator::floatizeForCsv($values['volume']),
              VarManipulator::protectStrForCsv($statut),
              VarManipulator::protectStrForCsv($values['detail']),
              $this->appName,
              $values['document_id'],
              $values['lot_unique_id'],
          );
        }
        return $csv;
    }
}
