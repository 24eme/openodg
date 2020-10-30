<?php
/**
 * Model for Lot
 *
 */

abstract class Lot extends acCouchdbDocumentTree
{
    const STATUT_ATTENTE_PRELEVEMENT = "ATTENTE_PRELEVEMENT";
    const STATUT_PRELEVE = "PRELEVE";
    const STATUT_DEGUSTE = "DEGUSTE";

    const CONFORMITE_CONFORME = "CONFORME";
    const CONFORMITE_NONCONFORME_MINEUR = "NONCONFORME_MINEUR";
    const CONFORMITE_NONCONFORME_MAJEUR = "NONCONFORME_MAJEUR";
    const CONFORMITE_NONCONFORME_GRAVE = "NONCONFORME_GRAVE";
    const CONFORMITE_NONTYPICITE_CEPAGE = "NONTYPICITE_CEPAGE";



    public static $libellesStatuts = array(
        self::STATUT_ATTENTE_PRELEVEMENT => 'En attente de prélèvement',
        self::STATUT_PRELEVE => 'Prélevé',
        self::STATUT_DEGUSTE => 'Dégusté'
    );


    public static $libellesConformites = array(
      self::CONFORMITE_CONFORME => "Conforme",
      self::CONFORMITE_NONCONFORME_MINEUR => "Non conformité mineure",
      self::CONFORMITE_NONCONFORME_MAJEUR => "Non conformité majeure",
      self::CONFORMITE_NONCONFORME_GRAVE => "Non conformité grave",
      self::CONFORMITE_NONTYPICITE_CEPAGE => "Non typicité cépage"
    );

    public static $shortLibellesConformites = array(
      self::CONFORMITE_CONFORME => "",
      self::CONFORMITE_NONCONFORME_MINEUR => "Mineure",
      self::CONFORMITE_NONCONFORME_MAJEUR => "Majeure",
      self::CONFORMITE_NONCONFORME_GRAVE => "Grave",
      self::CONFORMITE_NONTYPICITE_CEPAGE => "Typ. cép."
    );

    public static $nonConformites = array(
        self::CONFORMITE_NONCONFORME_MINEUR,
        self::CONFORMITE_NONCONFORME_MAJEUR,
        self::CONFORMITE_NONCONFORME_GRAVE,
        self::CONFORMITE_NONTYPICITE_CEPAGE
    );

    public static function getLibelleStatut($statut) {
        $libelles = self::$libellesStatuts;
        return (isset($libelles[$statut]))? $libelles[$statut] : $statut;
    }

    public static function getLibelleConformite($conformite) {
        $libelles = self::$libellesConformites;
        return (isset($libelles[$conformite]))? $libelles[$conformite] : $conformite;
    }

    public function getGeneratedMvtKey() {
        return self::generateMvtKey($this);
    }

    public static function generateMvtKey($lot) {
        return KeyInflector::slugify($lot->origine_document_id.'/'.$lot->origine_mouvement);
    }

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

    public function getCouleurLibelle() {
        return $this->getConfig()->getCouleur()->getLibelleComplet();
    }

    public function getProduitLibelle() {
		if(!$this->_get('produit_libelle') && $this->produit_hash) {
			$this->produit_libelle = $this->getConfig()->getLibelleComplet();
		}

		return $this->_get('produit_libelle');
	}

    public function isCleanable() {

        if(!$this->exist('produit_hash') || !$this->produit_hash){
          return true;
        }

        foreach($this as $key => $value) {
            if($key == 'millesime' && $value = $this->getDocument()->getCampagne()) {

                continue;
            }
            if($key == 'produit_hash' || $key == "produit_libelle") {
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

    public function hasVolumeAndHashProduit(){
      return $this->volume && $this->produit_hash;
    }

    public function getDateVersionfr(){
      if($this->date){
        return Date::francizeDate($this->date);
      }
      return date("d/m/Y");
    }

    public function getDocOrigine(){
      if(!$this->exist('id_document') || !$this->id_document){
        return null;
      }
      return acCouchdbManager::getClient()->find($this->id_document);
    }

    public function hasBeenEdited(){
      return ($this->getDocument()->hasVersion() && $this->exist('id_document') && $this->id_document);
    }

    public function setOrigineDocumentId($id) {
        $this->id_document = $id;
    }

    public function getOrigineDocumentId() {
        return $this->id_document;
    }


    public function getIntitulePartiel(){
      $libelle = 'lot '.$this->declarant_nom.' ('.$this->numero.') de '.$this->produit_libelle;
      if ($this->millesime){
        $libelle .= ' ('.$this->millesime.')';
      }
      return $libelle;
    }

    public function isPreleve(){
      return ($this->statut == Lot::STATUT_PRELEVE || array_key_exists($this->statut, self::$libellesConformites));
    }

    public function isLeurre()
    {
        return $this->exist('leurre') && $this->leurre;
    }
}
