<?php

class INAOHabilitationCsvFile
{
  const CSV_PRODUIT_LIBELLE = 0;
  const CSV_OPERATEUR_RAISON_SOCIALE = 1;
  const CSV_ETABLISSEMENT_RAISON_SOCIALIE = 2;
  const CSV_CVI = 3;
  const CSV_SIRET = 4;
  const CSV_SIEGE_ADRESSE_1 = 5;
  const CSV_SIEGE_ADRESSE_2 = 6;
  const CSV_SIEGE_BOITE_POSTALE = 7;
  const CSV_SIEGE_ADRESSE_3 = 8;
  const CSV_SIEGE_CODE_POSTAL = 9;
  const CSV_SIEGE_COMMUNE = 10;
  const CSV_RESPONSABLE_NOM = 11;
  const CSV_RESPONSABLE_QUALITE = 12;
  const CSV_RESPONSABLE_TELEPHONE = 13;
  const CSV_RESPONSABLE_PORTABLE = 14;
  const CSV_RESPONSABLE_TELECOPIE = 15;
  const CSV_RESPONSABLE_EMAIL = 16;
  const CSV_DI_DATE_DEPOT = 17;
  const CSV_DI_DATE_ENREGISTREMENT = 18;
  const CSV_PRODUCTEUR_RAISINS = 19;
  const CSV_PRODUCTEUR_MOUTS = 20;
  const CSV_VINIFICATEUR = 21;
  const CSV_ELEVEUR = 22;
  const CSV_ELABORATEUR = 23;
  const CSV_CONDITIONNEUR = 24;
  const CSV_ACHAT = 25;
  const CSV_CAVISTE = 26;
  const CSV_AUTRE = 27;

  protected $file_path;
  protected $produits4cvi;

  public function __construct($file_path) {
    $this->file_path = $file_path;
    $this->csv = new CSV($file_path, ';');
    $this->lignes = array();
    foreach($this->csv->getLignes() as $l) {
        $this->lignes[] = $l;
    }
    $this->produits4cvi = array();
  }

  public function isHabilite($cvi, $produit) {
      $produit_slug = KeyInflector::slugify($produit);
      if (!isset($this->produits4cvi[$cvi])) {
          $this->produits4cvi[$cvi] = $this->getProduits4Id($cvi);
      }
      foreach($this->produits4cvi[$cvi] as $ligne) {
          if (preg_match("/^".KeyInflector::slugify($ligne[self::CSV_PRODUIT_LIBELLE])."/", $produit_slug)) {
              return ($ligne[self::CSV_VINIFICATEUR]);
          }
      }
  }

  public function getProduits4Id($cvi) {
      $produits = array();
      foreach($this->lignes as $l) {
          if ($l[self::CSV_CVI] == $cvi) {
              $produits[] = $l;
          }
      }
      if (!count($produits)) {
          foreach($this->lignes as $l) {
              if ($l[self::CSV_SIRET] == $cvi) {
                  $produits[] = $l;
              }
          }
      }
      return $produits;
  }

  public function getLignes() {
      return $this->lignes;
  }

}
