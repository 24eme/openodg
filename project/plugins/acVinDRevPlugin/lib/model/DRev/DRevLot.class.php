<?php
/**
 * Model for DRevLot
 *
 */

class DRevLot extends BaseDRevLot
{
    public function getConfigProduit() {
            return $this->getConfig();
    }

    public function getConfig() {
        if ($this->produit_hash) {
            return $this->getDocument()->getConfiguration()->get($this->produit_hash);
        }
    }
    public function getNumero(){
        return $this->_get('numero');
    }

    public function getProduitRevendique() {
        if($this->getDocument()->exist($this->produit_hash)) {

            return $this->getDocument()->addProduit($this->produit_hash);
        }

        if($this->getConfigProduit()->getParent()->exist('DEFAUT') && $this->getDocument()->exist($this->getConfigProduit()->getParent()->get('DEFAUT')->getHash())) {

            return $this->getDocument()->addProduit($this->getConfigProduit()->getParent()->get('DEFAUT')->getHash());
        }

        return null;
    }
    public function lotPossible(){
      $hashCompatibles = array();
      $hash = $this->_get('produit_hash');
      $hashCompatibles[] = $hash;
      $hashCompatibles[] = preg_replace('|/[^/]+$|', '/DEFAUT', $hash);
      $hashCompatibles[] = preg_replace('|/[^/]+(/couleurs/[^/]+/cepages/[^/]+)$|', '/DEFAUT\1', $hash);
      $hashCompatibles[] = preg_replace('|/[^/]+(/couleurs/[^/]+/cepages)/[^/]+$|', '/DEFAUT\1/DEFAUT', $hash);

      foreach ($hashCompatibles as $hashCompatible) {
          if ($this->document->exist($hashCompatible)) {
              return true;
              break;
          }
      }

      $hash_couleur = preg_replace('/\/DEFAUT$/', '', $hash);
      if (preg_match('/cepages$/', $hash_couleur)) {
          foreach($this->document->getProduits() as $p) {
              if (strpos($p->getHash(), $hash_couleur) !== false) {
                  return true;
              }
          }
      }

     return false;
    }

    public function setProduitHash($hash) {
        if($hash != $this->_get('produit_hash')) {
            $this->produit_libelle = null;
        }
        parent::_set('produit_hash', $hash);
        $this->getProduitLibelle();
    }

    public function getDestinationType(){
        return $this->_get("destination_type");
    }

    public function getDestinationDate(){
        return $this->_get("destination_date");
    }

    public function getProduitLibelle() {
		if(!$this->_get('produit_libelle') && $this->produit_hash) {
			$this->produit_libelle = $this->getConfig()->getLibelleComplet();
		}

		return $this->_get('produit_libelle');
	}

    public function getCepagesLibelle() {
        $libelle = null;
        foreach($this->cepages as $cepage => $repartition) {
            if($libelle) {
                $libelle .= ", ";
            }
            $libelle .= $cepage . " (".$repartition."%)";
        }
        return $libelle;
    }

    public function isCleanable() {

        if(!$this->exist('produit_hash') || !$this->produit_hash){
          return true;
        }

        foreach($this as $key => $value) {
            if($key == 'millesime' && $value = $this->getDocument()->getCampagne()) {

                continue;
            }
            if($value instanceof acCouchdbJson && !count($value->toArray(true, false))) {
                continue;
            }

            if($value) {

                return false;
            }
        }

        return true;
    }

    public function getDestinationDateFr()
    {

        return Date::francizeDate($this->destination_date);
    }

    public function addCepage($cepage, $repartition) {
        $this->cepages->add($cepage, $repartition);
    }

    public function isProduitValidateOdg(){
      foreach($this->getDocument()->getProduitsLots() as $produit) {
          if(!$produit->isValidateOdg()){
            return false;
          }
      }
      return true;
    }

    public function hasVolumeAndHashProduit(){
      return $this->volume && $this->produit_hash;
    }

    public function getDateVersionfr(){
      if($this->date){
        return Date::francizeDate($this->date);
      }
      return date("d/m/Y");
    }

    public function getDrevDocOrigine(){
      if(!$this->exist('id_document') || !$this->id_document){
        return null;
      }
      return DRevClient::getInstance()->find($this->id_document);
    }

    public function getCepagesToStr(){
      $cepages = $this->cepages;
      $str ='';
      $k=0;
      $total = 0.0;
      foreach ($cepages as $c => $volume){ $total+=$volume; }

      foreach ($cepages as $c => $volume){
        $k++;
        $p = ($total)? round(($volume/$total)*100) : 0.0;
        $str.= $c." (".$p.'%)';
        $str.= ($k < count($cepages))? ', ' : '';
      }
      return $str;
    }

    public function hasBeenEdited(){
      return ($this->getDocument()->hasVersion() && $this->exist('id_document') && $this->id_document);
    }

}
