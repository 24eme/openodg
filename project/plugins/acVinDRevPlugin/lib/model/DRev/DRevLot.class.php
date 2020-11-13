<?php
/**
 * Model for DRevLot
 *
 */

class DRevLot extends BaseDRevLot
{
    public function getProduitRevendiqueLibelleComplet() {
        $p = $this->getProduitRevendique();
        if ($p) {
            return $p->getLibelleComplet();
        }
        return "";
    }

    public function getProduitRevendique() {
        if($this->getDocument()->exist($this->produit_hash)) {

            return $this->getDocument()->addProduit($this->produit_hash);
        }

        if($this->getConfigProduit() && $this->getConfigProduit()->getParent()->exist('DEFAUT') && $this->getDocument()->exist($this->getConfigProduit()->getParent()->get('DEFAUT')->getHash())) {

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

    public function isProduitValidateOdg(){
      foreach($this->getDocument()->getProduitsLots() as $produit) {
          if(!$produit->isValidateOdg()){
            return false;
          }
      }
      return true;
    }

    public function getDrevDocOrigine(){
      return parent::getDocOrigine();
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

    public function addCepage($cepage, $repartition) {
        $this->cepages->add($cepage, $repartition);
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

}
