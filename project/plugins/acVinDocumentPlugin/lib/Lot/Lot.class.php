<?php
/**
 * Model for Lot
 *
 */

abstract class Lot extends acCouchdbDocumentTree
{
    const STATUT_AFFECTE_DEST = "AFFECTE_DEST";
    const STATUT_PRELEVABLE = "PRELEVABLE";
    const STATUT_NONPRELEVABLE = "NON_PRELEVABLE";
    const STATUT_ATTENTE_PRELEVEMENT = "ATTENTE_PRELEVEMENT";
    const STATUT_PRELEVE = "PRELEVE";
    const STATUT_ATTABLE = "ATTABLE";
    const STATUT_ANONYMISE = "ANONYMISE";
    const STATUT_DEGUSTE = "DEGUSTE";
    const STATUT_CONFORME = "CONFORME";
    const STATUT_AFFECTE_SRC = "AFFECTE_SRC";
    const STATUT_NONCONFORME = "NON_CONFORME";
    const STATUT_CHANGE = "CHANGE";
    const STATUT_DECLASSE = "DECLASSE";
    const STATUT_ELEVAGE = "ELEVAGE";

    const CONFORMITE_CONFORME = "CONFORME";
    const CONFORMITE_NONCONFORME_MINEUR = "NONCONFORME_MINEUR";
    const CONFORMITE_NONCONFORME_MAJEUR = "NONCONFORME_MAJEUR";
    const CONFORMITE_NONCONFORME_GRAVE = "NONCONFORME_GRAVE";
    const CONFORMITE_NONTYPICITE_CEPAGE = "NONTYPICITE_CEPAGE";

    const SPECIFICITE_UNDEFINED = "UNDEFINED";

    const TYPE_ARCHIVE = 'Lot';

    public static $libellesStatuts = array(
        self::STATUT_AFFECTE_DEST => 'Affecte dest',
        self::STATUT_PRELEVABLE => 'Prélevable',
        self::STATUT_NONPRELEVABLE => 'Non prélevable',
        self::STATUT_ATTENTE_PRELEVEMENT => 'En attente de prélèvement',
        self::STATUT_PRELEVE => 'Prélevé',
        self::STATUT_ATTABLE => 'Attablé',
        self::STATUT_ANONYMISE => 'Anonymisé',
        self::STATUT_DEGUSTE => 'Dégusté',
        self::STATUT_CONFORME => 'Conforme',
        self::STATUT_NONCONFORME => 'Non conforme',
        self::STATUT_AFFECTE_SRC => 'Affecte src',
        self::STATUT_CHANGE => 'Changé',
        self::STATUT_DECLASSE => 'Déclassé',
        self::STATUT_ELEVAGE => 'En élevage'
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

    public static $statuts_preleves = array(
        self::STATUT_CONFORME,
        self::STATUT_NONCONFORME,
        self::STATUT_PRELEVE,
        self::STATUT_DEGUSTE,
        self::STATUT_CHANGE,
        self::STATUT_DECLASSE
    );

    public static $ordreStatut = [
        // Statuts Degustations
        self::STATUT_CONFORME => "08_STATUT_CONFORME",
        self::STATUT_NONCONFORME => "08_STATUT_NONCONFORME",
        self::STATUT_AFFECTE_SRC => "07_STATUT_AFFECTE_SRC",
        self::STATUT_DEGUSTE => "06_STATUT_DEGUSTE",
        self::STATUT_ANONYMISE => "05_STATUT_ANONYMISE",
        self::STATUT_ATTABLE => "04_STATUT_ATTABLE",
        self::STATUT_PRELEVE => "03_STATUT_PRELEVE",
        self::STATUT_ATTENTE_PRELEVEMENT => "02_STATUT_ATTENTE_PRELEVEMENT",
        self::STATUT_AFFECTE_DEST => "01_STATUT_AFFECTE_DEST",
    ];

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
        return KeyInflector::slugify($lot->id_document.'/'.$lot->origine_mouvement);
    }

    public function getConfigProduit() {
            return $this->getConfig();
    }

    public function getConfig() {
        if ($this->produit_hash) {
            return $this->getDocument()->getConfiguration()->get($this->produit_hash);
        }
        return null;
    }

    public function getDefaults() {
        $defaults = array();
        $defaults['millesime'] = $this->getDocument()->campagne;
        $defaults['statut'] = Lot::STATUT_PRELEVABLE;
        if(DRevConfiguration::getInstance()->hasSpecificiteLot()) {
          $defaults['specificite'] = self::SPECIFICITE_UNDEFINED;
        }

        return $defaults;
    }

    public function initDefault() {
        foreach($this->getDefaults() as $defaultKey => $defaultValue) {
            $this->add($defaultKey, $defaultValue);
        }
    }

    protected function getFieldsToFill() {
        return  array('numero', 'millesime', 'volume', 'destination_type', 'destination_date', 'elevage', 'specificite', 'centilisation');
    }

    public function isEmpty() {
      $defaults = $this->getDefaults();
      foreach($this->getFieldsToFill() as $field) {
        if($this->exist($field) && $this->get($field) && !isset($defaults[$field])) {
            return false;
        }
        if($this->exist($field) && $this->get($field) && isset($defaults[$field]) && $defaults[$field] != $this->get($field)) {
            return false;
        }
      }

      return true;
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

    public function getValueForTri($type) {
        $type = strtolower($type);
        $type = str_replace('é', 'e', $type);
        if ($type == 'millesime') {
            return ($this->millesime) ? $this->millesime : 'XXXX';
        }
        if (!$this->getConfig()||$type == 'numero_anonymat') {
          $numero= intval(substr($this->numero_anonymat, 1));
          return $numero;
        }
        if ($type == 'appellation') {
            return $this->getConfig()->getAppellation()->getKey();
        }

        if ($type == 'couleur') {
            return $this->getConfig()->getCouleur()->getKey();
        }
        if ($type == 'genre') {
            return $this->getConfig()->getGenre()->getKey();
        }
        if ($type == 'cepage') {
            return $this->details;
        }
        if ($type == 'produit') {
            return $this->_get('produit_hash').$this->_get('details');
        }
        throw new sfException('unknown type of value : '.$type);
    }

    public function isCleanable() {

        return $this->isEmpty();
    }

    public function isOrigineEditable()
    {
      return $this->getDocOrigine()->getMaster()->isLotsEditable();
    }

    public function getDestinationDateFr()
    {

        return Date::francizeDate($this->destination_date);
    }

    public function hasVolumeAndHashProduit(){
      return $this->volume && $this->produit_hash;
    }

    public function getDateVersionfr(){

      if($this->date && !preg_match("/\d{4}\-\d{2}-\d{2}$/", $this->date)){
        return Date::francizeDate(DateTime::createFromFormat('Y-m-d\TH:i:sO', $this->date)->format('Y-m-d'));
      }

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
      $libelle = 'lot '.$this->declarant_nom.' ('.$this->numero_logement_operateur.') de '.$this->produit_libelle;
      if ($this->millesime){
        $libelle .= ' ('.$this->millesime.')';
      }
      return $libelle;
    }

    public function isPreleve(){
      return in_array($this->statut, self::$statuts_preleves);
    }

    public function isLeurre()
    {
        return $this->exist('leurre') && $this->leurre;
    }

    public function getUnicityKey(){
        return KeyInflector::slugify($this->produit_hash.'/'.$this->volume.'/'.$this->millesime.'/'.$this->numero_dossier.'/'.$this->numero_archive);
    }

    public function getTriHash(array $tri = null) {
        if (!$tri) {
            return $this->produit_hash;
        }
        $hash = '';
        foreach($tri as $type) {
            $hash .= $this->getValueForTri($type);
        }
        return $hash;
    }
    public function getTriLibelle(array $tri = null) {
        if (!$tri||!$this->getConfig()) {
            return $this->produit_libelle;
        }
        $format = '';
        if (in_array('appellation', $tri)) {
            $format .= '%a% ';
        }
        if (in_array('genre', $tri)) {
            $format .= '%g% ';
        }
        if (in_array('couleur', $tri)) {
            $format .= '%co% ';
        }
        $libelle = $this->getConfig()->getLibelleFormat(null, $format)." ";
        if (in_array('millesime', $tri)) {
            $libelle .= $this->millesime.' ';
        }
        if (in_array('cépage', $tri)) {
            $libelle .= "- ".$this->details.' ';
        }
        return $libelle;
    }

    public function isSecondPassage()
    {
        return $this->exist('nombre_degustation') && $this->nombre_degustation > 1;
    }

    public function getTextPassage()
    {
        $nb = $this->isSecondPassage() ? $this->nombre_degustation.'ème' : '1er';
        return $nb." passage";
    }

    public function redegustation()
    {
        // Tagguer le lot avec un flag special
        // Regenerer les mouvements

        if (! $this->exist('nombre_degustation')) {
            $this->add('nombre_degustation', 1);
        }

        $this->nombre_degustation++;

        $this->getDocument()->generateMouvementsLots();
    }

    public function setNumeroTable($numero) {
        $lastLot = $this->getLotInLastPosition($numero);
        if ($lastLot) {
          $this->position = $lastLot->getPosition() + 1;
        }
        return $this->_set('numero_table', $numero);
    }

    public function getPosition()
    {
      if (!$this->_get('position')) {
        $recalcul = true;
      } elseif(!$this->numero_table||substr($this->_get('position'), 0, 2) == '99') {
        $recalcul = true;
      } else {
        $recalcul = false;
      }
      if ($recalcul) {
          $table = ($this->numero_table) ? $this->numero_table : 99;
          $this->position =  sprintf("%02d%03d", $table, $this->getKey());
      }
      return $this->_get('position');
    }

    public function getLotInPrevPosition() {
      $lots = $this->getDocument()->lots;
      $lot = null;
      foreach($lots as $l) {
        if ($l->numero_table == $this->numero_table) {
          if ($l->getPosition() < $this->getPosition()) {
            if ($lot && $l->getPosition() < $lot->getPosition()) {
              continue;
            } else {
              $lot = $l;
            }
          }
        }
      }
      return $lot;
    }

    public function getLotInNextPosition() {
      $lots = $this->getDocument()->lots;
      $lot = null;
      foreach($lots as $l) {
        if ($l->numero_table == $this->numero_table) {
          if ($l->getPosition() > $this->getPosition()) {
            if ($lot && $l->getPosition() > $lot->getPosition()) {
              continue;
            } else {
              $lot = $l;
            }
          }
        }
      }
      return $lot;
    }

    public function switchPosition($toLot, $fromLot) {
        if (!$toLot||!$fromLot) {
          return false;
        }
        $toPos = $toLot->getPosition();
        $toLot->position =  $fromLot->getPosition();
        $fromLot->position = $toPos;
        return true;
    }

    public function upPosition()
    {
      return $this->switchPosition($this, $this->getLotInPrevPosition());
    }

    public function downPosition()
    {
      return $this->switchPosition($this->getLotInNextPosition(), $this);
    }

    public function getLotInLastPosition($numeroTable) {
        $lots = $this->getDocument()->lots;
        $lot = null;
        foreach($lots as $l) {
          if ($l->numero_table == $numeroTable) {
            if (!$lot||$l->getPosition() > $lot->getPosition()) {
                $lot = $l;
            }
          }
        }
        return $lot;
    }


}
