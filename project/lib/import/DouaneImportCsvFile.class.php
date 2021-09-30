<?php

class DouaneImportCsvFile {

    protected $filePath = null;
    protected $doc = null;
    protected $campagne = null;
    protected $configuration = null;

    public function __construct($filePath, $doc = null) {
        $this->filePath = $filePath;
        $this->doc = $doc;
        $this->configuration = ConfigurationClient::getConfiguration();
        if ($doc) {
            $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($doc->campagne);
        }else{
            $this->campagne = ConfigurationClient::getInstance()->buildCampagne(date('Y-m-d'));
        }
        $this->cvi = null;
        set_time_limit(30000);
    }

    public static function clean($array) {
      for($i = 0 ; $i < count($array) ; $i++) {
        $array[$i] = preg_replace("/[ ]+/", " ", preg_replace('/^ +/', '', preg_replace('/ +$/', '', $array[$i])));
      }
      return $array;
    }

    public static function numerizeVal($val, $nbDecimal = 2) {
    	return (is_numeric($val))? str_replace('.', ',', sprintf('%01.'.$nbDecimal.'f', str_replace(',', '.', $val))) : str_replace('.', ',', $val);
    }

    public static function cleanStr($val) {
    	return str_replace(';', ' - ', preg_replace('/^ */', '', preg_replace('/ *$/', '', str_replace(array("\r", "\r\n", "\n"), ' ', html_entity_decode($val)))));
    }

    public static function getNewInstanceFromType($type, $file, $doc = null)  {
        switch ($type) {
            case 'DR':
                return new DRDouaneCsvFile($file, $doc);
            case 'SV11':
                return new SV11DouaneCsvFile($file, $doc);
            case 'SV12':
                return new SV12DouaneCsvFile($file, $doc);
        }

        return null;
    }
    public static function getTypeFromFile($file)  {
      if (preg_match('/sv11/i', $file)) {
        return 'SV11';
      }
      if (preg_match('/sv12/i', $file)) {
        return 'SV12';
      }
      return 'DR';
    }

    public function getCsvType() {
      if (is_a($this, 'SV11DouaneCsvFile')) {
        return "SV11";
      }
      if (is_a($this, 'SV12DouaneCsvFile')) {
        return "SV12";
      }
      if (is_a($this, 'DRDouaneCsvFile')) {
        return "DR";
      }
    }

    public static function cleanRaisonSociale($s) {
      return '"'.preg_replace('/ -$/', '', trim(preg_replace('/  */', ' ', str_replace('"', ' - ', preg_replace('/"$/', '', preg_replace('/^"/', '', $s)))))).'"';
    }

    public function getEtablissementRows() {
      $doc = array();
      $doc[] = $this->getCsvType();
      $doc[] = $this->campagne;
      if (!isset($this->etablissement)) {
        $this->etablissement = null;
      }
      if (!$this->etablissement) {
        $this->etablissement = ($this->doc)? $this->doc->getEtablissementObject() : null;
      }
      if (!$this->etablissement && $this->cvi) {
        $this->etablissement = EtablissementClient::getInstance()->findByCvi($this->cvi);
      }
      $doc[] = ($this->etablissement)? $this->etablissement->identifiant : null;
      if ($this->etablissement) {
        $doc[] = $this->etablissement->cvi ;
        $doc[] = self::cleanRaisonSociale($this->etablissement->raison_sociale);
        $doc[] = null;
        $doc[] = $this->etablissement->siege->commune;
      }else {
        $doc[] = ($this->cvi) ? $this->cvi : null;
        $rs = (isset($this->raison_sociale)) ? $this->raison_sociale : null;
        $doc[] = self::cleanRaisonSociale($rs);
        $doc[] = null;
        $doc[] = (isset($this->commune)) ? $this->commune : null;
      }
      return $doc;
    }

    public function setCampagne($c){
      $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($c);
    }

    public function convertByDonnees() {
        if (!$this->doc->exist('donnees') || count($this->doc->donnees) < 1) {
            return null;
        }
        $csv = '';
        $configuration = ConfigurationClient::getCurrent();
        $categories = DouaneCsvFile::getCategories();
        $this->etablissement = EtablissementClient::getInstance()->find($this->doc->identifiant);
        $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($this->doc->campagne);
        if (!$this->etablissement) {
            return null;
        }

        $produits = array();
        $colonnesid = array();
        $colonneid = 0;
        try {
            foreach ($this->doc->donnees as $donnee) {
                if ($produit = $configuration->declaration->get($donnee->produit)) {
                    $p = array();
                    if ($donnee->bailleur && $b = EtablissementClient::getInstance()->find($donnee->bailleur)) {
                        $p[] = $b->raison_sociale;
                        $p[] = $b->ppm;
                    } else {
                        $p[] = null;
                        $p[] = null;
                    }
                    $p[] = $produit->getCertification()->getKey();
                    $p[] = $produit->getGenre()->getKey();
                    $p[] = $produit->getAppellation()->getKey();
                    $p[] = $produit->getMention()->getKey();
                    $p[] = $produit->getLieu()->getKey();
                    $p[] = $produit->getCouleur()->getKey();
                    $p[] = $produit->getCepage()->getKey();
                    $p[] = $produit->code_douane;
                    $p[] = $produit->getLibelleFormat();
                    $p[] = $donnee->complement;
                    $produitid = join("", $p);
                    if (!isset($colonnesid[$produitid]) || !$colonnesid[$produitid]) {
                        $colonnesid[$produitid] = ++$colonneid;
                    }
                    $p[] = $donnee->categorie;
                    $p[] = (isset($categories[$donnee->categorie]))? preg_replace('/^[0-9]+\./', '', $categories[$donnee->categorie]) : null;
                    $p[] = str_replace('.', ',', $donnee->valeur);
                    if ($donnee->tiers && $t = EtablissementClient::getInstance()->find($donnee->tiers)) {
                        $p[] = $t->cvi;
                        $p[] = DouaneImportCsvFile::cleanRaisonSociale($t->raison_sociale);
                        $p[] = null;
                        $p[] = $t->siege->commune;
                    } else {
                        $p[] = null;
                        $p[] = null;
                        $p[] = null;
                        $p[] = null;
                    }
                    $p[] = $colonnesid[$produitid];
                    $produits[] = $p;
                }
            }
        }catch(Exception $e) {
            throw new sfException('problem with '.$this->doc->_id.' : '.$e);
        }
        $drInfos = $this->getEtablissementRows();
        foreach ($produits as $k => $p) {
            $csv .= implode(';', $drInfos).';'.implode(';', $p)."\n";
        }
        return $csv;
    }
}
