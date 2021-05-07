<?php

class ExportLotsCSV {

    protected $header = false;

    public static function getHeaderCsv() {
        return "Campagne;Identifiant;Col vide;Col vide;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Col vide;Statut de lot;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepages;Col vide;Col vide;Produit;Col vide;Col vide;Col vide;Col vide;Volume revendiqué;Col vide;Col vide;Col vide;Col vide;Col vide;Col vide;Col vide;Col vide;Col vide;Num logement Opérateur;Date lot;Produit (millesime);Destination;Col vide;Col vide;Doc ID;Lot unique ID;Num dossier;Num lot;Elevage;Détails;Spécificités;Centilisation;Date prélévement;Conformité;Conformité en appel;\n";
    }

    public function __construct($header = true) {
        $this->header = $header;
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function exportAll() {
        $csv = "";
        if ($this->header) {
            $csv .= $this->getHeaderCsv();
        }
        foreach(MouvementLotView::getInstance()->getByStatut(null)->rows as $lot) {
          $values = (array)$lot->value;

          if (isset($values['leurre']) && $values['leurre']) {
            continue;
          }

          $adresse = explode(' — ', $values['adresse_logement']);
          $produit = explode('/', str_replace('DEFAUT', '', $values['produit_hash']));
          $cepages = ($values['cepages'])? implode(',', array_keys((array)$values['cepages'])) : '';
          $date = explode('T', explode(' ',$values['date'])[0])[0];
          $statut = $lot->key[MouvementLotView::KEY_STATUT];

          $csv .= str_replace('donnée non présente dans l\'import', '', sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
              $values['campagne'],
              $values['declarant_identifiant'],
              null,
              null,
              $values['declarant_nom'],
              $this->protectStr($adresse[1]),
              $adresse[2],
              $this->protectStr($adresse[3]),
              null,
              $statut,
              $produit[3],
              $produit[5],
              $produit[7],
              $produit[9],
              $produit[11],
              $produit[13],
              $cepages,
              null,
              null,
              trim($values['produit_libelle']),
              null,
              null,
              null,
              null,
              $this->formatFloat($values['volume']),
              null,
              null,
              null,
              null,
              null,
              null,
              null,
              null,
              null,
              $this->protectStr($values['numero_logement_operateur']),
              $date,
              $this->protectStr($values['millesime']),
              $values['destination_type'],
              null,
              null,
              $values['id_document'],
              $values['unique_id'],
              $values['numero_dossier'],
              $values['numero_archive'],
              (isset($values['elevage']) && $values['elevage'])? 1 : '',
              (isset($values['details']))? $values['details'] : '',
              (isset($values['specificite']))? $values['specificite'] : '',
              (isset($values['centilisation']))? $values['centilisation'] : '',
              (isset($values['preleve']))? $values['preleve'] : '',
              (isset($values['conformite']))? $values['conformite'] : '',
              (isset($values['conforme_appel']))? $values['conforme_appel'] : ''
          ));
        }
        return $csv;
    }

}
