<?php

class DouaneImportCsvFile {

    protected $filePath = null;
    protected $doc = null;
    protected $campagne = null;
    protected $configuration = null;

    public $cvi = '';
    public $drev_produit_filter = '';
    public $etablissement = null;
    public $identifiant = null;
    public $raison_sociale = '';
    public $commune = '';

    public function __construct($filePath, $doc = null, $drev_produit_filter = null, $cvi = null) {
        $this->filePath = $filePath;
        $this->doc = $doc;
        $this->configuration = ConfigurationClient::getConfiguration();
        if ($doc) {
            $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($doc->campagne);
        }else{
            $this->campagne = ConfigurationClient::getInstance()->buildCampagne(date('Y-m-d'));
        }
        $this->cvi = null;
        if ($cvi) {
            $this->cvi = $cvi;
        }
        $this->drev_produit_filter = $drev_produit_filter;
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

    public static function getNewInstanceFromType($type, $file, $doc = null, $drev_produit_filter = null, $cvi = null)  {
        switch ($type) {
            case 'DR':
                if(preg_match('/.csv$/', $file)) {
                    return new DRDouaneCsvFile($file, $doc, $drev_produit_filter, $cvi);
                }
                if(preg_match('/.json$/', $file)) {
                    return new DRDouaneJsonFile($file, $doc, $drev_produit_filter, $cvi);
                }
                return new DRDouaneCsvFile($file, $doc, $drev_produit_filter, $cvi);
            case 'SV11':
                return new SV11DouaneCsvFile($file, $doc, $drev_produit_filter, $cvi);
            case 'SV12':
                return new SV12DouaneCsvFile($file, $doc, $drev_produit_filter, $cvi);
        }

        return null;
    }
    public static function getTypeFromFile($file)  {
      if (preg_match('/production/i', $file)) {
          $str = file_get_contents($file, false, null, 0, 200);
          if (preg_match('/APPORTEUR/', $str)) {
              return 'SV11';
          }else{
              return 'SV12';
          }
      }
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
      if (is_a($this, 'DRDouaneJsonFile')) {
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
        $doc[] = '"'.$this->etablissement->cvi.'"' ;
        $doc[] = self::cleanRaisonSociale($this->etablissement->raison_sociale);
        $doc[] = null;
        $doc[] = ($this->etablissement->siege->commune) ? $this->etablissement->siege->commune : $this->etablissement->commune;
      }else {
        $doc[] = ($this->cvi) ? '"'.$this->cvi.'"' : null;
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

    public function getFamilleCalculeeFromLigneDouane($has_volume_cave = false, $has_volume_coop = false, $has_volume_nego = false) {
        return DouaneProduction::getFamilleCalculeeFromTypeAndLigneDouane($this->getCsvType(), $has_volume_cave, $has_volume_coop, $has_volume_nego);
    }


    public function convertByDonnees() {
        if (!$this->doc->exist('donnees') || count($this->doc->donnees) < 1) {
            return null;
        }
        $csv = '';
        $configuration = ConfigurationClient::getCurrent();
        $this->etablissement = EtablissementClient::getInstance()->find($this->doc->identifiant);
        $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($this->doc->campagne);
        if (!$this->etablissement) {
            return null;
        }
        $produits = array();
        try {
            foreach ($this->doc->getEnhancedDonnees($this->drev_produit_filter) as $donnee) {
                if ($produit = $donnee->produit_conf) {
                    $p = $donnee->produit_csv;
                    $p[] = $donnee->categorie;
                    $p[] = $donnee->categorie_libelle;
                    $p[] = str_replace('.', ',', $donnee->valeur);

                    $donnee->tiers_cvi = str_replace('"', '', $donnee->tiers_cvi);
                    if ($donnee->tiers_cvi && (!$donnee->tiers || !$donnee->tiers_raison_sociale || !$donnee->tiers_commune)) {
                        DouaneProduction::fillItemWithTiersData($donnee, $donnee->tiers_cvi, $donnee->tiers_raison_sociale);
                    }
                    $p[] = $donnee->tiers_cvi;
                    if ($donnee->tiers_cvi && !$donnee->tiers) {
                        $p[] = DouaneImportCsvFile::cleanRaisonSociale("CVI non reconnu : ".preg_replace('/(^"|"$)/', '', $donnee->tiers_raison_sociale));
                    }else {
                        $p[] =  DouaneImportCsvFile::cleanRaisonSociale($donnee->tiers_raison_sociale);
                    }
                    $p[] = null;
                    $p[] = $donnee->tiers_commune;

                    $p[] = $donnee->colonneid;
                    $p[] = Organisme::getCurrentOrganisme();
                    $p[] = $produit->getHash();
                    $p[] = $donnee->drev_id;
                    $p[] = $donnee->drev_produit_filter;
                    $p[] = $this->doc->_id;
                    $p[] = $donnee->document_famille;
                    $p[] = substr($this->campagne, 0, 4);
                    $p[] = $donnee->colonne_famille;
                    $p[] = implode('|', DouaneImportCsvFile::extractLabels($p[11]));
                    $p[] = $this->getHabilitationStatus(($this->getCsvType() == 'DR') ? HabilitationClient::ACTIVITE_PRODUCTEUR : HabilitationClient::ACTIVITE_VINIFICATEUR, $produit);
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

    public function loadEtablissement() {
        if ($this->etablissement && $this->identifiant) {
            return;
        }
        $this->etablissement = ($this->doc)? $this->doc->getEtablissementObject() : null;
        $this->identifiant = ($this->etablissement)? $this->etablissement->identifiant : null;
    }

    public function getRelatedDrev() {
        $this->loadEtablissement();
        return DRevClient::getInstance()->retrieveRelatedDrev($this->identifiant, $this->campagne, $this->drev_produit_filter);
    }

    public static function extractLabels($mentionComplementaire) {
        $labels = array();

        $wordSeparatorStart = "(^|[ \/\-,\.\'\?°\(]{1})";
        $wordSeparatorEnd = "([ \/\-,\.\?°\)]{1}|$)";

        if($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(conversion|conv|convertion|cab|reconversion|c3|ciii)'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_CONVERSION_BIO] = DRevClient::DENOMINATION_CONVERSION_BIO;
        } elseif(DRevConfiguration::getInstance()->hasDenominationBiodynamie() && $mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(biodinami|biodynami|demeter|bio-dynami)\w*'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_BIODYNAMIE] = DRevClient::DENOMINATION_BIODYNAMIE;
        } elseif($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(ab|bio|biologique|BIOLOGIQUE|FR-BIO-[0-9]+)'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_BIO] = DRevClient::DENOMINATION_BIO;
        } elseif($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(VIN ?BIOL|agriculture biol|AGRICBIOLOGIQUE)/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_BIO] = DRevClient::DENOMINATION_BIO;
        }

        if($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(feuilles?)'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_JEUNE_VIGNE] = DRevClient::DENOMINATION_JEUNE_VIGNE;
        }

        if(array_key_exists(DRevClient::DENOMINATION_JEUNE_VIGNE, $labels) && array_key_exists(DRevClient::DENOMINATION_BIO, $labels)) {
            unset($labels['DENOMINATION_BIO']);
            $labels[DRevClient::DENOMINATION_CONVERSION_BIO] = DRevClient::DENOMINATION_CONVERSION_BIO;
        }

        if($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(non bio)'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            unset($labels['DENOMINATION_BIO']);
        }

        if($mentionComplementaire && preg_match('/'.$wordSeparatorStart.'(hve|h.v.e.|haute valeur environnementale|hve3)'.$wordSeparatorEnd.'/i', $mentionComplementaire)) {
            $labels[DRevClient::DENOMINATION_HVE] = DRevClient::DENOMINATION_HVE;
        }

        ksort($labels);

        return $labels;
    }
    private $habilitation = null;
    public function getHabilitationStatus($activite, $prodconfobj) {
        if (!$this->doc) {
            return;
        }
        if (!$this->habilitation) {
            $this->habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->doc->getEtablissementObject()->identifiant, $this->doc->date_depot);
        }
        $ph = ($this->habilitation && $prodconfobj) ? $this->habilitation->getProduitByProduitConf($prodconfobj) : null;
        $pa = ($ph && $ph->activites->exist($activite) ) ? $ph->activites->get($activite) : null;
        $default_hab_status = ($ph) ? 'PAS PRODUCTEUR' : 'SANS HABILITATION';
        return ($pa && $pa->statut) ? $pa->statut : $default_hab_status;
    }

}
