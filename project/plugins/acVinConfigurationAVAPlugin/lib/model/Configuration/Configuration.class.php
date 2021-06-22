<?php

// AVA //

class Configuration extends BaseConfiguration {

    protected $identifyLibelleProduct = array();

    public function getProduits() {

        return $this->declaration->getProduits();
    }

    public function getTemplatesFactures()
    {
    	$factures = array();
    	if ($this->exist('factures')) {
    		foreach ($this->factures as $type => $id) {
    			$factures[$type] = acCouchdbManager::getClient()->find($id);
    		}
    	}
    	return $factures;
    }

    public function getAppellationsLieuDit()
    {
      $result = array();
      foreach ($this->declaration->getCertifications() as $certification) {
        foreach ($certification->getAppellations() as $key => $appellation) {
          if ($appellation->exist('detail_lieu_editable') && $appellation->detail_lieu_editable) {
            $result[$key] = $appellation->libelle;
          }
        }
      }
      return $result;
    }

    public function getArrayAppellationsMout() {
        $appellations = $this->getRecolte()->getNoeudAppellations();
        $appellations_array_mouts = array();
        foreach ($appellations->filter('^appellation') as $appellation_key => $appellation) {
            if ($appellation->getMout() == 1) {
                $appellations_array_mouts[$appellation_key] = $appellation;
            }
        }
        return $appellations_array_mouts;
    }

    public function getArrayAppellations() {
        $appellations = $this->getRecolte()->getNoeudAppellations();
        $appellations_array = array();
        foreach ($appellations->filter('^appellation') as $appellation_key => $appellation) {
            $appellations_array[$appellation_key] = $appellation;
        }
        return $appellations_array;
    }

    public function isCompteAdminExist($login, $mot_de_passe) {
        foreach ($this->compte_admin as $item) {
            if (strlen($item->mot_de_passe) > 6) {
                if ($login == $item->login) {
                    $is_mot_de_passe_valid = false;
                    $cryptage = str_replace(array('{', '}'), array('', ''), substr($item->mot_de_passe, 0, 6));
                    $mot_de_passe_compte = substr($item->mot_de_passe, 6, strlen($item->mot_de_passe) - 6);
                    if ($cryptage == 'SSHA') {
                        $is_mot_de_passe_valid = ($mot_de_passe_compte == sha1($mot_de_passe));
                    } elseif ($cryptage == 'TEXT') {
                        $is_mot_de_passe_valid = ($mot_de_passe_compte == $mot_de_passe);
                    }
                    if ($is_mot_de_passe_valid) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private static function normalizeLibelle($libelle) {
      $libelle = str_ireplace('Alsace Pinot Noir rouge', 'alsace PN rouge', $libelle);
      $libelle = preg_replace('/(\w+)s( |$)/i', '$1$2', $libelle);
      $libelle = str_ireplace('SAINT-', 'saint ', $libelle);
      $libelle = preg_replace('/&nbsp;/', '', strtolower($libelle));
      $libelle = str_replace(array('é', 'è', 'ê'), 'e', $libelle);
      $libelle = str_replace(array('ô'), 'o', $libelle);
      $libelle = preg_replace('/[^a-z ]/', '', preg_replace('/  */', ' ', preg_replace('/&([a-z])[^;]+;/i', '\1', $libelle)));
      $libelle = preg_replace('/^\s+/', '', preg_replace('/\s+$/', '', $libelle));
      $libelle = trim(str_replace(' ', '', $libelle));

      return $libelle;
    }

    public function getRecolte() {

      return $this->getDeclaration();
    }

    public function identifyProductByLibelle($libelle) {
        if(array_key_exists($libelle, $this->identifyLibelleProduct)) {

            return $this->identifyLibelleProduct[$libelle];
        }

        $libelleSlugify = KeyInflector::slugify(preg_replace("/[ ]+/", " ", trim($libelle)));

        foreach($this->declaration->getProduits() as $produit) {
            $libelleProduitSlugify = KeyInflector::slugify(preg_replace("/[ ]+/", " ", trim($produit->getLibelleComplet())));
            //echo $libelleSlugify."/".$libelleProduitSlugify."\n";
            if($libelleSlugify == $libelleProduitSlugify) {
                $this->identifyLibelleProduct[$libelle] = $produit;

                return $produit;
            }
        }

        foreach($this->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION, "ConfigurationAppellation") as $produit) {
            $libelleProduitSlugify = KeyInflector::slugify(preg_replace("/[ ]+/", " ", trim($produit->getLibelleComplet())));

            if($libelleSlugify == $libelleProduitSlugify) {
                $this->identifyLibelleProduct[$libelle] = $produit;

                return $produit;
            }
        }

        return false;
    }

    public function identifyProduct($appellation, $lieu, $cepage, $type_declaration = null) {
      $appid = null;
      $lieuid = 'lieu';
      $cepageid = null;
      $libelle = self::normalizeLibelle($appellation);
      foreach ($this->getRecolte()->getNoeudAppellations()->getChildrenFilter($type_declaration) as $appellation_key => $appellation_obj) {
  if ($libelle == self::normalizeLibelle($appellation_obj->getLibelle())) {
    $appid=$appellation_key;
    break;
  }
      }
      if (!$appid)
  return array("error" => $appellation);

      if ($lieu) {
  $libelle = self::normalizeLibelle($lieu);
  foreach($appellation_obj->mention->getChildrenFilter($type_declaration) as $lieu_key => $lieu_obj) {
    if ($lieu_key == 'lieu')
      break;
    if ($libelle == self::normalizeLibelle($lieu_obj->getLibelle())) {
      $lieuid=$lieu_key;
      break;
    }
  }
      }
      if ($lieuid == 'lieu') {
        if ($appellation_obj->hasManyLieu())
          return array("error" => $appellation.' / '.$lieu);
      }

      $libelle = self::normalizeLibelle($cepage);
      $prodhash = '';
      $evalhash = '';
      $eval = null;
      foreach($appellation_obj->getLieux()->get($lieuid)->getProduitsFilter($type_declaration) as $cepage_obj) {
  $cepage_libelle = self::normalizeLibelle($cepage_obj->getLibelle());
  $cepage_key = $cepage_obj->getKey();
  if ($libelle == $cepage_libelle) {

    $cepageid = $cepage_key;
    $prodhash = $cepage_obj->getHash();
    break;
  }
  //Gestion des cépages tronqués (Gewurzt)
  if (preg_match('/^'.$cepage_libelle.'/', $libelle)) {
    if ($eval === null) {
      $eval = $cepage_key;
      $evalhash = $cepage_obj->getHash();
    } else
      $eval = 0;
  }
      }
      if (!$cepageid) {
  if ($eval) {
    $cepageid = $eval;
    $prodhash = $evalhash;
  } else
    return array("error" => $appellation.' / '.$lieu.' / '.$cepage);
      }
      return array("ids" => $appid.'/'.$lieuid.'/'.$cepageid, "hash" => $prodhash);
    }

    public function getProduitsLibellesByCodeDouane() {
        $produits = array();
        foreach($this->declaration->certification->genre->getAppellations() as $appellation) {
          foreach($appellation->getLieux() as $lieu) {
            foreach($lieu->getCouleurs() as $couleur) {
              foreach($couleur->getCepages() as $cepage) {
                $produits[trim($cepage->getDouane()->getFullAppCode(null).$cepage->getDouane()->getCodeCepage())] = preg_replace("/[ ]+/", " ", sprintf('%s %s %s %s', $appellation->getLibelle(), $lieu->getLibelle(), $couleur->getLibelle(), $cepage->getLibelle()));
                  if ($cepage->hasVtsgn()) {
                    $produits[trim($cepage->getDouane()->getFullAppCode("VT").$cepage->getDouane()->getCodeCepage())] = preg_replace("/[ ]+/", " ",sprintf('%s %s %s %s %s', $appellation->getLibelle(), $lieu->getLibelle(), $couleur->getLibelle(), $cepage->getLibelle(), "VT"));
                    $produits[trim($cepage->getDouane()->getFullAppCode("VT").$cepage->getDouane()->getCodeCepage())] = preg_replace("/[ ]+/", " ",sprintf('%s %s %s %s %s', $appellation->getLibelle(), $lieu->getLibelle(), $couleur->getLibelle(), $cepage->getLibelle(), "SGN"));
                  }
              }
              if ($lieu->hasManyCouleur()) {
                $produits[trim($couleur->getDouane()->getFullAppCode(null))] = preg_replace("/[ ]+/", " ",sprintf('%s %s %s Total', $appellation->getLibelle(), $lieu->getLibelle(), $couleur->getLibelle(), "Total", ""));
              }
          }
          if (!$lieu->hasManyCouleur() && $appellation->hasManyLieu()) {
              $produits[trim($lieu->getDouane()->getFullAppCode(null))] = preg_replace("/[ ]+/", " ",sprintf('%s %s Total', $appellation->getLibelle(), $lieu->getLibelle()));
          }
        }
        if (!$appellation->hasManyLieu() && !$appellation->getLieux()->lieu->hasManyCouleur()) {
            $produits[trim($appellation->getDouane()->getFullAppCode(null))] = preg_replace("/[ ]+/", " ",sprintf('%s Total', $appellation->getLibelle()));
        }
      }

      return $produits;
    }
}
