<?php

/**
 * Model for Configuration
 *
 */
class Configuration extends BaseConfiguration {

    const DEFAULT_KEY = 'DEFAUT';
    const DEFAULT_DENSITE = "1.3";

    protected $identifyLibelleProduct = array();
    protected $identifyCodeDouaneProduct = array();
    protected $cepage2produits = array();
    protected $effervescent_vindebase_active = false;

    public function constructId() {
        $this->set('_id', "CONFIGURATION");
    }

    public function getProduits() {

        return $this->declaration->getProduits();
    }

    public function getProduitsByCepage($cepage) {
        $this->setCepages2Produits();
        if (!isset($this->cepage2produits[$cepage])) {
            return [];
        }
        return $this->cepage2produits[$cepage];
    }

    public function setCepages2Produits() {
        if (!count($this->cepage2produits)){
            foreach($this->getProduits() as $p) {
                foreach($p->getCepagesAutorises()->toArray(true, false) as $c) {
                    if (!isset($this->cepage2produits[$c])) {
                        $this->cepage2produits[$c] = [];
                    }
                    $this->cepage2produits[$c][] = $p;
                }
            }
        }
    }

    public function getLieux(){
      $lieux = [];
      foreach ($this->getProduits() as $p) {
          if($p->getLieu()->getKey() != self::DEFAULT_KEY){
            $lieux[$p->getLieu()->getKey()] = $p->getLieu()->getLibelle();
          }
      }
      return $lieux;
    }

    public function formatProduits($date = null, $format = "%format_libelle% (%code_produit%)", $attributes = array()) {

        return $this->declaration->formatProduits($date, null, null, $format, $attributes);
    }

    public function getCepagesAutorises($date = null, $attributes = array()) {
    	$cepages = array();

    	foreach($this->declaration->getProduits($date, "INTERPRO-declaration", null, $attributes) as $produit) {
    		$cepages_autorises = $produit->cepages_autorises->toArray();
    		foreach ($cepages_autorises as $ca) {
    			$cepages[$ca] = $ca;
    		}
    	}
    	return $cepages;
    }

    public static function slugifyProduitLibelle($s) {
        $s = strtolower($s);
        $s = str_replace([' et ', ' de ', ' sur '], ' ', $s);
        $s = str_replace(['é','è', 'ê', 'ë', 'É', 'È', 'Ê', 'Ë'], 'e', $s);
        $s = preg_replace('/(s$)/', '', $s);
        $s = preg_replace("/[ ]+/", " ", $s);
        $s = trim($s);
        $s = KeyInflector::slugify($s);
        return $s;
    }

    public function identifyProductByLibelle($libelle, $verbose = false) {
        if(array_key_exists($libelle, $this->identifyLibelleProduct)) {
            if ($verbose) {
                print_r(['cached' => true, 'produit' => $this->identifyLibelleProduct[$libelle]->getHash(), 'libelle' => $libelle]);
            }
            return $this->identifyLibelleProduct[$libelle];
        }
        $couleurs = array('', ' blanc', ' rouge', ' rosé');
        while (count($couleurs)) {
            $libelle_couleur = $libelle . array_shift($couleurs);
            $libelleSlugify = self::slugifyProduitLibelle($libelle_couleur);

            foreach($this->getProduits() as $produit) {
                $libelleProduitSlugify = self::slugifyProduitLibelle($produit->getLibelleFormat());
                if ($verbose) {
                    echo "stricte égalité: ".$libelleSlugify."/".$libelleProduitSlugify."\n";
                }
                if($libelleSlugify == $libelleProduitSlugify) {
                    $this->identifyLibelleProduct[$libelle] = $produit;

                    return $produit;
                }
            }
            $matches = [];
            foreach($this->getProduits() as $produit) {
                $libelleProduitSlugify = self::slugifyProduitLibelle($produit->getLibelleFormat());
                if ($verbose) {
                    print_r(['strpos dans les deux sens', 'conf produit slug' => $libelleProduitSlugify, 'doc libelle slug' => $libelleSlugify, 'conf original' => $produit->getLibelleFormat(), 'libelle original' => $libelle_couleur]);
                }
                $pc = $this->pc_string($libelleProduitSlugify, $libelleSlugify);
                if($pc) {
                    $matches[$pc] = $produit;
                }
            }
            if (count($matches)) {
                $max = max(array_keys($matches));
                $this->identifyLibelleProduct[$libelle] = $matches[$max];
                return $matches[$max];
            }
            if ($verbose) {
                print_r(['matches' => $matches]);
            }
        }

        return false;
    }

    private function pc_string($a, $b) {
        $idx_a = strpos($a, $b);
        $idx_b = strpos($b, $a);
        if (($idx_a === false) && ($idx_b === false)) {
            return 0;
        }
        if (($idx_a !== false)) {
            return strlen($b) / strlen($a);
        }
        if (($idx_b !== false)) {
            return strlen($a) / strlen($b);
        }
    }

    public function identifyProductByCodeDouane($code) {
        if(array_key_exists($code, $this->identifyCodeDouaneProduct)) {
            return $this->identifyCodeDouaneProduct[$code];
        }

        $codeSlugify = KeyInflector::slugify(preg_replace("/[ ]+/", " ", trim($code)));

        foreach($this->getProduits() as $produit) {
            foreach($produit->getCodesDouanes() as $code) {
                $codeProduitSlugify = KeyInflector::slugify(preg_replace("/[ ]+/", " ", trim($code)));
                if($codeSlugify == $codeProduitSlugify) {
                    $this->identifyCodeDouaneProduct[$code][] = $produit;
                }
            }
        }
        if (isset($this->identifyCodeDouaneProduct[$code])) {
          return $this->identifyCodeDouaneProduct[$code];
        }
        return array();
    }

    public function getTemplatesFactures() {
        $factures = array();
        if ($this->exist('factures')) {
            foreach ($this->factures as $type => $id) {
                $factures[$type] = acCouchdbManager::getClient()->find($id);
            }
        }
        return $factures;
    }

    private static function normalizeLibelle($libelle) {
        $libelle = str_ireplace('SAINT-', 'saint ', $libelle);
        $libelle = preg_replace('/&nbsp;/', ' ', strtolower($libelle));
        if (!preg_match('/&[^;]+;/', $libelle)) {
            $libelle = html_entity_decode(preg_replace('/&([^;#])[^;]*;/', '\1', htmlentities($libelle, ENT_NOQUOTES, 'UTF-8')));
        }
        $libelle = str_replace(array('é', 'è', 'ê'), 'e', $libelle);
        $libelle = preg_replace('/[^a-z ]/', '', preg_replace('/  */', ' ', preg_replace('/&([a-z])[^;]+;/i', '\1', $libelle)));
        $libelle = preg_replace('/^\s+/', '', preg_replace('/\s+$/', '', $libelle));

        return $libelle;
    }

    private function getObjectByLibelle($parent, $libelle, $previous_libelles = null) {
        $libelle = ($libelle) ? self::normalizeLibelle($libelle) : 'DEFAUT';
        $obj_id = 'DEFAUT';
        foreach ($parent as $obj_key => $obj_obj) {
            if ($libelle == self::normalizeLibelle($obj_obj->getLibelle())) {
                $obj_id = $obj_key;

                break;
            }
        }
        $next_libelles = $libelle;
        if ($previous_libelles) {
            $next_libelles = $previous_libelles . ' / ' . $libelle;
        }
        if (!$parent->exist($obj_id)) {
            throw new Exception($next_libelles);
        }

        return array('obj' => $obj_obj, 'next_libelles' => $next_libelles);
    }

    public function identifyNodeProduct($certification, $genre, $appellation, $mention, $lieu = 'DEFAUT', $couleur = 'DEFAUT', $cepage = 'DEFAUT', $millesime = null) {
        $hash = $this->identifyProduct($certification, $genre, $appellation, $mention, $lieu, $couleur, $cepage, $millesime);
        $rwhash = ' ';
        while ($rwhash != $hash && $rwhash) {
            if ($rwhash != ' ') {
                $hash = $rwhash;
            }
            $rwhash = preg_replace('/[^\/]*\/DEFAUT\/?$/', '', $hash);
        }

        return $hash;
    }

    public function identifyProduct($certification, $genre, $appellation, $mention = 'DEFAULT', $lieu = 'DEFAUT', $couleur = 'DEFAUT', $cepage = 'DEFAUT', $millesime = null) {
        try {
            $res = $this->getObjectByLibelle($this->declaration->getCertifications(), $certification);
            $res = $this->getObjectByLibelle($res['obj']->getGenres(), $genre, $res['next_libelles']);
            $res = $this->getObjectByLibelle($res['obj']->getAppellations(), $appellation, $res['next_libelles']);
            $res = $this->getObjectByLibelle($res['obj']->getMentions(), $mention, $res['next_libelles']);
            $res = $this->getObjectByLibelle($res['obj']->getLieux(), $lieu, $res['next_libelles']);
            $res = $this->getObjectByLibelle($res['obj']->getCouleurs(), $couleur, $res['next_libelles']);
            $res = $this->getObjectByLibelle($res['obj']->getCepages(), $cepage, $res['next_libelles']);
        } catch (Exception $e) {
            throw new sfException("Impossible d'indentifier le produit (" . $e->getMessage() . " [$certification / $genre / $appellation / $mention / $lieu / $couleur / $cepage / $millesime] )");
        }

        return $res['obj']->getHash();
    }

    public function identifyLabels($labels, $separateur = '|') {
        $label_keys = array();
        foreach (explode($separateur, $labels) as $l) {
            if ($k = $this->identifyLabel($l)) {
                $label_keys[] = $k;
            }
        }

        return $label_keys;
    }

    public function identifyLabel($label) {
        $label = self::normalizeLibelle($label);
        foreach ($this->labels as $k => $l) {
            if ($label == self::normalizeLibelle($l)) {

                return $k;
            }
        }

        return false;
    }

    public function findProductByCodeDouane($code_douane) {
        $produitsByCodeDouane = array();
        foreach($this->getProduits() as $produit) {
            foreach($produit->getCodesDouanes() as $code) {
            	if ($code) {
                	$produitsByCodeDouane[$code] = $produit;
            	}
            }
        }

        krsort($produitsByCodeDouane);

        foreach($produitsByCodeDouane as $code => $produit) {
            if(trim($code_douane) == $code) {
                return $produit;
            }
        }

        foreach($produitsByCodeDouane as $code => $produit) {
          if(preg_match('/^'.$code_douane.'/', $code)) {
            return $produit;
          }
        }

        foreach($produitsByCodeDouane as $code => $produit) {
            if(preg_match('/^'.$code.'/', $code_douane)) {
                return $produit;
            }
        }

        return false;
    }

    public function setLabelCsv($datas) {
        if ($datas[LabelCsvFile::CSV_LABEL_CODE] && !$this->labels->exist($datas[LabelCsvFile::CSV_LABEL_CODE])) {
            $this->labels->add($datas[LabelCsvFile::CSV_LABEL_CODE], $datas[LabelCsvFile::CSV_LABEL_LIBELLE]);
        }
    }

    public function getMillesimes() {
        $lastMillesime = date('Y');
        $result = array();
        for ($i = $lastMillesime; $i >= 1991; $i--)
            $result[$i] = $i;

        return $result;
    }

    public function getLabelsLibelles($labels) {
        $libelles = array();
        if (is_array($labels)) {
            foreach ($labels as $key) {
                $libelles[$key] = $this->labels[$key];
            }
        }

        return $libelles;
    }

    public function formatLabelsLibelle($labels, $format = "%la%", $separator = ", ") {
        $libelles = $this->getLabelsLibelles($labels);

        return ConfigurationClient::getInstance()->formatLabelsLibelle($libelles, $format, $separator);
    }

    public function updateAlias($hashProduit, $alias) {
        $hashProduitKey = str_replace('/', '-', $hashProduit);
        if (!$this->alias->exist($hashProduitKey))
            $this->alias->add($hashProduitKey, array());
        $pos = count($this->alias->get($hashProduitKey));
        $this->alias->get($hashProduitKey)->add($pos, $alias);
    }

    public function updateAliasForCorrespondances() {
        foreach ($this->correspondances as $newHash => $oldHash) {
            $key = str_replace("/", "-", $oldHash);
            if (!$this->alias->exist($key)) {
                continue;
            }
            $value = $this->alias->get($key);
            $this->alias->remove($key);
            $this->alias->add($newHash, $value);
        }
    }

    public function getCorrespondanceHash($hash) {
        if (!$this->exist('correspondances')) {

            return false;
        }

        $key = str_replace("/", "-", $hash);

        if (!$this->correspondances->exist($key)) {

            return false;
        }

        return $this->correspondances->get($key);
    }

    private function getCorrespondancesInverse() {
        if (!$this->exist('correspondances') || is_null($this->correspondances)) {
            return array();
        }
        $arrayCorrespondances = $this->correspondances->toArray(0, 1);
        return array_flip($arrayCorrespondances);
    }

    public function getProduitWithCorrespondanceInverse($hash) {
        if ($this->exist($hash)) {
            return $this->get($hash);
        }
        $correspondanceInverse = $this->getCorrespondancesInverse();

        $newHash = str_replace('-', '/', $correspondanceInverse[$hash]);
        return $this->get($newHash);
    }

    public function save() {
        parent::save();
        CurrentClient::getInstance()->cacheResetConfiguration();
    }

    public function prepareCache() {
        $this->loadAllData();
    }

    public function hasDontRevendique(){
        foreach ($this->declaration->getDetails() as $keyDetail => $valueDetail) {
            foreach($valueDetail as $keySubDetail => $valueDetail){
              if(preg_match('/dont_revendique/',$keySubDetail)){
                return true;
              }
            }
        }
        return false;
    }

}
