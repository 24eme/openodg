<?php
class ExportLotsCSV {

    protected $header = false;
    protected $appName = null;
    protected $lots = array();

    public static function getHeaderCsv() {
        return "Origine;Id Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Campagne;Date commission;Date lot;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Statut de lot;Destination;Date de destination;Pays de destination;Elevage;Centilisation;Date prélévement;Conformité;Date de conformité en appel;Organisme;Doc Id;Lot unique Id;Declarant Lot unique Id;Hash produit\n";
    }

    public function __construct($header = true, $appName = null) {
        $this->header = $header;
        $this->appName = $appName;
    }

    public function getUniqueLotsLastStatut() {
      if ($this->lots) {
        return $this->lots;
      }
      foreach(MouvementLotView::getInstance()->getByStatut(null)->rows as $lot) {
        $values = (array)$lot->value;
        $uniqueLotId = $values['unique_id'];
        $statut = $values['statut'];
        $numeroOrdre = $values['document_ordre'];
        $positionLotCourant = $values['document_ordre'].$values[$uniqueLotId]['statut'];
        if (isset($values['leurre']) && $values['leurre']) {
          continue;
        }
        if (isset($this->lots[$uniqueLotId])) {
          $positionLotExistant = $this->lots[$uniqueLotId]['document_ordre'].$this->lots[$uniqueLotId]['statut'];
          if ($positionLotCourant > $positionLotExistant) {
            $this->lots[$uniqueLotId] = $values;
          }
        } else {
          $this->lots[$uniqueLotId] = $values;
        }
      }
      return $this->lots;
    }

    public function exportAll() {
        $csv = "";
        if ($this->header) {
            $csv .= $this->getHeaderCsv();
        }
        $lots = $this->getUniqueLotsLastStatut();
        foreach($lots as $lot) {
          $adresse = null;
          $code_postal = null;
          $commune = null;
          $adresseTab = explode(' — ', $lot['adresse_logement']);
          if (preg_match('/^([0-9]{5})$/', $adresseTab[2])) {
              $adresse = $adresseTab[1];
              $code_postal = $adresseTab[2];
              $commune = $adresseTab[3];
          } elseif (preg_match('/^(.+)([0-9]{5})(.+)$/', $adresseTab[1], $m)) {
            $adresse = trim($m[1]);
            $code_postal = $m[2];
            $commune = trim($m[3]);
          }
          $produit = explode('/', str_replace('DEFAUT', '', $lot['produit_hash']));
          $cepages = ($lot['cepages'])? implode(',', array_keys((array)$lot['cepages'])) : '';
          $date = preg_split('/( |T)/', $lot['date'], -1, PREG_SPLIT_NO_EMPTY);
          $statut = (isset(Lot::$libellesStatuts[$lot['statut']]))? Lot::$libellesStatuts[$lot['statut']] : $lot['statut'];
          if (!$statut && boolval($lot['affectable'])) {
            $statut = Lot::$libellesStatuts[Lot::STATUT_REVENDIQUE];
          }
          if (!$statut && !boolval($lot['affectable'])) {
            $statut = Lot::$libellesStatuts[Lot::STATUT_NONAFFECTABLE];
          }
          if (!isset($lot['conformite'])) {
            $lot['conformite'] = '';
          }
          $conformite = (isset(Lot::$libellesConformites[$lot['conformite']]))? Lot::$libellesConformites[$lot['conformite']] : $lot['conformite'];
          $destination = null;
          $destination_date = null;
          if (isset($lot['destination_type'])) {
            $destination = isset(DRevClient::$lotDestinationsType[$lot['destination_type']])? DRevClient::$lotDestinationsType[$lot['destination_type']] : $lot['destination_type'];
          }
          if (isset($lot['destination_date'])) {
            $destination_date = $lot['destination_date'];
          }
          $contenances = ConditionnementConfiguration::getInstance()->getContenances();
          $centilisation = null;
          if (isset($lot['centilisation'])) {
            $centilisation = isset($contenances[$lot['centilisation']])? $contenances[$lot['centilisation']] : $lot['centilisation'];
          }
          $csv .= str_replace('donnée non présente dans l\'import', '', sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
              $lot['initial_type'],
              $lot['declarant_identifiant'],
              VarManipulator::protectStrForCsv($lot['declarant_nom']),
              VarManipulator::protectStrForCsv($adresse),
              $code_postal,
              VarManipulator::protectStrForCsv($commune),
              $lot['campagne'],
              (isset($lot['date_commission']))? $lot['date_commission'] : '',
              $date[0],
              $lot['numero_dossier'],
              $lot['numero_archive'],
              VarManipulator::protectStrForCsv($lot['numero_logement_operateur']),
              $produit[3],
              $produit[5],
              $produit[7],
              $produit[9],
              $produit[11],
              $produit[13],
              null,
              VarManipulator::protectStrForCsv($lot['produit_libelle']),
              VarManipulator::protectStrForCsv($cepages),
              $lot['millesime'],
              (isset($lot['specificite']))? VarManipulator::protectStrForCsv($lot['specificite']) : '',
              VarManipulator::floatizeForCsv($lot['volume']),
              VarManipulator::protectStrForCsv($statut),
              $destination,
              $lot['destination_date'],
              VarManipulator::protectStrForCsv($lot['pays']),
              (isset($lot['elevage']) && $lot['elevage'])? '1' : '',
              VarManipulator::protectStrForCsv($centilisation),
              (isset($lot['preleve']))? $lot['preleve'] : '',
              VarManipulator::protectStrForCsv($conformite),
              (isset($lot['conforme_appel']))? $lot['conforme_appel'] : '',
              $this->appName,
              $lot['id_document'],
              $lot['unique_id'],
              $lot['declarant_identifiant'].'-'.$lot['unique_id'],
              $lot['produit_hash']
          ));
        }
        return $csv;
    }

}
