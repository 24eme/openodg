<?php

class INAOHabilitationCsvFile
{
  const CSV_PRODUIT_LIBELLE = 1;
  const CSV_OPERATEUR_RAISON_SOCIALE = 2;
  const CSV_SIRET = 3;
  const CSV_CVI = 4;
  const CSV_ADRESSE = 5;
  const CSV_CODE_POSTAL = 6;
  const CSV_COMMUNE = 7;
  const CSV_TELEPHONE = 8;
  const CSV_EMAIL = 9;
  const CSV_PRODUCTEUR_RAISINS = 10;
  const CSV_PRODUCTEUR_MOUTS = 11;
  const CSV_ELABORATEUR = 12;
  const CSV_VINIFICATEUR = 13;
  const CSV_ELEVEUR = 14;
  const CSV_CONDITIONNEUR = 15;
  const CSV_VENDEUR_VRAC = 16;

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
