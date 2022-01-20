<?php
/**
 * Model for DRevLot
 *
 */

class DRevLot extends BaseDRevLot
{
    public function getFieldsToFill() {
        $fields = parent::getFieldsToFill();
        //Le produit ne fait pas partie des champs à vérifié car il peut être prérempli par la DR
        unset($fields[array_search("produit_hash", $fields)]);
        $fields[] = 'destination_type';
        return $fields;
    }

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
      $tabCepages=array();
      foreach ($cepages as $c => $volume){
        $total+=$volume;
      }
      foreach ($cepages as $c => $volume){
        $p = ($total)? round(($volume/$total)*100) : 0.0;
        $tabCepages[$c]=$p;
      }
      arsort($tabCepages);
      foreach ($tabCepages as $c => $p) {
        $k++;
        $str.=" ".$c." (".$p.'%)';
        $str.= ($k < count($cepages))? ',' : '';
      }
      return $str;
    }

    public function addCepage($cepage, $repartition) {
        $this->cepages->add($cepage, $repartition);
    }

    public function getNumeroLogementOperateur() {
        if(!$this->exist('numero_logement_operateur')) {
            return null;
        }
        return $this->_get('numero_logement_operateur');
    }

    public function setNumeroLogementOperateur($numero) {
        if(!$this->exist('numero_logement_operateur')) {
            $this->add('numero_logement_operateur');
        }
        return $this->_set('numero_logement_operateur', $numero);
    }

    public function getInitialType() {
        if(is_null($this->_get('initial_type'))) {
            $this->initial_type = $this->getDocumentType();
        }

        return $this->_get('initial_type');
    }

    public function getDocumentType() {

        return DRevClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {
        $this->_set('document_ordre', '01');
        return "01";
    }

    public function getLibelle() {

        return parent::getLibelle();
    }

    public function getMouvementFreeInstance() {

        return DRevMouvementLots::freeInstance($this->getDocument());
    }

    public function getLotInDrevOrigine(){
        $drevSource = DRevClient::getInstance()->find($this->id_document);
        return $drevSource->getLotByNumArchive($this->numero_archive);
    }

    public function getOriginalVolumeIfModifying() {
        if ($this->getDocument()->isFirstNumeroDossier()){
            return 0;
        }
        $diff = $this->getDocument()->getDiffLotVolume();
        if (!count($diff)) {
            return false;
        }
        if ($this->getHash() === '') {
            return false;
        }
        if (isset($diff[$this->getHash()."/unique_id"])) {
            $hash = intval($this->getKey() * 1) - 1;
            $hash = "/lots/$hash";
            if (isset($diff[$hash . "/unique_id"]) &&
                    $diff[$hash . "/unique_id"] == $this->unique_id) {
                return $diff[$hash . "/volume"];
            }
            return false;
        }
        if (!isset($diff[$this->getHash()."/volume"])) {
            return $this->volume;
        }
        return $diff[$this->getHash()."/volume"];
    }

    public function setUniqueId($ui) {
        if (!$ui) {
            throw new sfException("setUniqueId empty");
        }
        return $this->_set('unique_id', $ui);
    }

}
