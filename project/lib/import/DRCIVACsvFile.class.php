<?php

class DRCIVACsvFile extends CIVACsvFile
{
  const CSV_ACHETEUR_CVI = 0;
  const CSV_ACHETEUR_LIBELLE = 1;
  const CSV_RECOLTANT_CVI = 2;
  const CSV_RECOLTANT_LIBELLE = 3;
  const CSV_APPELLATION = 4;
  const CSV_LIEU = 5;
  //  const CSV_COULEUR = 6;
  const CSV_CEPAGE = 6;
  const CSV_VTSGN = 7;
  const CSV_DENOMINATION = 8;
  const CSV_SUPERFICIE = 9;
  const CSV_VOLUME = 10;
  const CSV_USAGES_INDUSTRIELS = 11;
  const CSV_SUPERFICIE_TOTALE = 12;
  const CSV_VOLUME_TOTAL = 13;
  const CSV_USAGES_INDUSTRIELS_TOTAL = 14;
  const CSV_VCI = 15;
  const CSV_VCI_TOTAL = 16;
  const CSV_HASH = 20;

  private $file = null;
  private $separator = null;
  private $csvdata = null;
  private $ignore = null;

  public function getFileName() {
    return $this->file;
  }

  public function __construct($file, $ignore_first_if_comment = 1) {
    $this->ignore = $ignore_first_if_comment;
    if (!file_exists($file) && !preg_match('/^http/', $file))
      throw new Exception("Cannont access $file");

    $this->file = $file;
    $handle = fopen($this->file, 'r');
    if (!$handle)
      throw new Exception('invalid_file');
    $buffer = fread($handle, 500);
    fclose($handle);
    $buffer = preg_replace('/$[^\n]*\n/', '', $buffer);
    if (!$buffer) {
      throw new Exception('invalid_file');
    }
    if (!preg_match('/("?)[0-9]{10}("?)([,;\t])/', $buffer, $match)) {
      throw new Exception('invalid_csv_file');
    }
    $this->separator = $match[3];
  }

    public static function getHashProduitByLine($line) {

        return $line[self::CSV_HASH];
    }

  public function getCsvRecoltant($cvi) {
    $lignes = array();
    foreach ($this->getCsv() as $line) {
      if ($line[self::CSV_RECOLTANT_CVI] == $cvi)
      $lignes[] = $line;
    }
    return $lignes;
  }

    public function getCsvAcheteur($cvi) {
    $lignes = array();
    foreach ($this->getCsv() as $line) {
      if ($line[self::CSV_ACHETEUR_CVI] == $cvi)
      $lignes[] = $line;
    }
    return $lignes;
  }

  private static function clean($array) {
    for($i = 0 ; $i < count($array) ; $i++) {
      $array[$i] = preg_replace('/^ +/', '', preg_replace('/ +$/', '', $array[$i]));
    }
    return $array;
  }

  public function getCsv() {
    if ($this->csvdata)
      return $this->csvdata;

    $handler = fopen($this->file, 'r');
    if (!$handler)
      throw new Exception('Cannot open csv file anymore');
    $this->csvdata = array();
    while (($data = fgetcsv($handler, 0, $this->separator)) !== FALSE) {
      $this->csvdata[] = self::clean($data);
    }
    fclose($handler);
    if ($this->ignore && !preg_match('/^\d{10}$/', $this->csvdata[0][0]))
      array_shift($this->csvdata);
    return $this->csvdata;
  }

  public function updateDRevProduitDetail(DRev $drev) {
      $drev->resetProduitDetail();
      foreach ($this->getCsvAcheteur($drev->identifiant) as $line) {
          $hashProduit = DRCIVACsvFile::getHashProduitByLine($line);

          if (!preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_CEPAGE])) {

              continue;
          }

          $hashProduit = preg_replace("/(mentionVT|mentionSGN)/", "mention", $hashProduit);
          $hashProduit = preg_replace('|/recolte.|', '/declaration/', $hashProduit);

          if (!$drev->getConfiguration()->exist($hashProduit)) {
              continue;
          }

          $config = $drev->getConfiguration()->get($hashProduit)->getNodeRelation('revendication');

          if ($config instanceof ConfigurationCepage) {
              continue;
          }

          if ($config instanceof ConfigurationLieu) {
              continue;
          }

          if ($config->getLieu()->hasManyCouleur() && !$config instanceof ConfigurationCouleur) {
              continue;
          }

          if ($config instanceof ConfigurationAppellation) {
              $config = $config->mention->lieu->couleur;
          }

          if ($config instanceof ConfigurationMention) {
              $config = $config->lieu->couleur;
          }

          if (!$config instanceof ConfigurationCouleur) {
              continue;
          }

          $produit = $drev->addProduit($config->getHash());
          $produitDetail = $produit->detail;
          if($line[DRCIVACsvFile::CSV_VTSGN]) {
              $produitDetail = $produit->detail_vtsgn;
          }

          $produitDetail->volume_total += (float) $line[DRCIVACsvFile::CSV_VOLUME_TOTAL];
          $produitDetail->usages_industriels_total += (float) $line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS_TOTAL];
          if(preg_match("/^[0-9\.,]+$/", $line[DRCIVACsvFile::CSV_VCI_TOTAL]) && ((float) $line[DRCIVACsvFile::CSV_VCI_TOTAL]) > 0) {
              $produitDetail->vci_total += (float) $line[DRCIVACsvFile::CSV_VCI_TOTAL];
          }
          $produitDetail->superficie_total += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
          $produitDetail->volume_sur_place += (float) $line[DRCIVACsvFile::CSV_VOLUME];

          if (!$produitDetail->exist('superficie_vinifiee')) {
              $produitDetail->add('superficie_vinifiee');
          }
          if($line[DRCIVACsvFile::CSV_SUPERFICIE] != "") {
              $produitDetail->superficie_vinifiee += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE];
          } else {
              $produitDetail->superficie_vinifiee = null;
          }
          if ($line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS] == "") {
              $produitDetail->usages_industriels_sur_place = -1;
          } elseif ($produitDetail->usages_industriels_sur_place != -1) {
              $produitDetail->usages_industriels_sur_place += (float) $line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS];
          }

          if(preg_match("/^[0-9\.,]+$/", $line[DRCIVACsvFile::CSV_VCI]) && ((float) $line[DRCIVACsvFile::CSV_VCI]) > 0) {
              $produitDetail->vci_sur_place += (float) $line[DRCIVACsvFile::CSV_VCI];
          }
      }

      $drev->updateProduitDetail();
  }

  public function updateDRevCepage(DRev $drev) {
      $drev->resetCepage();

      foreach ($this->getCsvAcheteur($drev->identifiant) as $line) {
          if (
                  preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_APPELLATION]) ||
                  preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_LIEU]) ||
                  preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_CEPAGE])
          ) {

              continue;
          }

          $hashProduit = DRCIVACsvFile::getHashProduitByLine($line);

          $hash = preg_replace("|/detail/.+$|", "", preg_replace('|/recolte.|', '/declaration/', preg_replace("|/detail/[0-9]+$|", "", $hashProduit)));
          $hash = preg_replace("/(mentionVT|mentionSGN)/", "mention", $hash);

          if (!$drev->getConfiguration()->exist($hash)) {
              continue;
          }

          $config = $drev->getConfiguration()->get($hash);
          $detail = $drev->getOrAdd($config->getHash())->addDetailNode($line[DRCIVACsvFile::CSV_LIEU]);
          if ($line[DRCIVACsvFile::CSV_VTSGN] == "VT") {
              $detail->volume_revendique_vt += (float) $line[DRCIVACsvFile::CSV_VOLUME];
              $detail->superficie_revendique_vt += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
          } elseif ($line[DRCIVACsvFile::CSV_VTSGN] == "SGN") {
              $detail->volume_revendique_sgn += (float) $line[DRCIVACsvFile::CSV_VOLUME];
              $detail->superficie_revendique_sgn += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
          } else {
              $detail->volume_revendique += (float) $line[DRCIVACsvFile::CSV_VOLUME];
              $detail->superficie_revendique += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
          }

          $detail->updateTotal();
          $detail->getLibelle();
      }
  }

}
