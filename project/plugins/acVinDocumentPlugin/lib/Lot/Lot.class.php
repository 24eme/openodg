<?php
/**
 * Model for Lot
 *
 */

abstract class Lot extends acCouchdbDocumentTree
{
    const STATUT_AFFECTE_DEST = "01_AFFECTE_DEST";
    const STATUT_PRELEVABLE = "PRELEVABLE";
    const STATUT_NONPRELEVABLE = "NON_PRELEVABLE";
    const STATUT_ATTENTE_PRELEVEMENT = "02_ATTENTE_PRELEVEMENT";
    const STATUT_PRELEVE = "03_PRELEVE";
    const STATUT_ATTABLE = "04_ATTABLE";
    const STATUT_ANONYMISE = "05_ANONYMISE";
    const STATUT_DEGUSTE = "06_DEGUSTE";
    const STATUT_CONFORME = "08_CONFORME";
    const STATUT_AFFECTE_SRC = "07_AFFECTE_SRC";
    const STATUT_NONCONFORME = "08_NON_CONFORME";
    const STATUT_RECOURS_OC = "09_RECOURS_OC";
    const STATUT_CONFORME_APPEL = "10_CONFORME_APPEL";

    const STATUT_CHANGE = "CHANGE";
    const STATUT_DECLASSE = "DECLASSE";
    const STATUT_ELEVAGE = "ELEVAGE";

    const STATUT_REVENDIQUE = "01_REVENDIQUE";
    const STATUT_NONAFFECTABLE = "02_NON_AFFECTABLE";
    const STATUT_AFFECTABLE = "03_AFFECTABLE_ENATTENTE";
    const STATUT_AFFECTE_SRC_DREV = "04_AFFECTE_SRC";

    const STATUT_MANQUEMENT_EN_ATTENTE = "01_MANQUEMENT_EN_ATTENTE";
    const STATUT_TRANSITOIRE_AFFECTATION_EN_ATTENTE = "02_AFFECTATION_EN_ATTENTE";

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

    public function getEtablissement(){
        if(!$this->identifiant){
            return null;
        }
        return EtablissementClient::getInstance()->find($this->identifiant);
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
        return KeyInflector::slugify($this->numero_dossier.'-'.$this->numero_archive);
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

        $this->affectable = true;
        $this->specificite .= ' 2eme degustation';
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

    public function getProvenance()
    {
        return substr($this->id_document, 0, 4);
    }

    abstract public function getMouvementFreeInstance();
    abstract public function getLibelle();

    public function getUniqueId(){
        return KeyInflector::slugify($this->numero_dossier.'-'.$this->numero_archive);
    }


    public function buildMouvement($statut) {
        $mouvement = $this->getMouvementFreeInstance();

        $mouvement->date = $this->date;
        $mouvement->numero_dossier = $this->numero_dossier;
        $mouvement->numero_archive = $this->numero_archive;
        $mouvement->detail = $this->produit_hash;
        $mouvement->libelle = $this->getLibelle();
        $mouvement->detail = null;
        $mouvement->region = '';
        $mouvement->version = $this->getVersion();
        $mouvement->document_ordre = $this->getDocumentOrdre();
        $mouvement->document_type = $this->getDocumentType();
        $mouvement->document_id = $this->getDocument()->_id;
        $mouvement->lot_unique_id = $this->getUniqueId();
        $mouvement->lot_hash = $this->getHash();
        $mouvement->declarant_identifiant = $this->declarant_identifiant;
        $mouvement->declarant_nom = $this->declarant_nom;
        $mouvement->campagne = $this->getCampagne();
        $mouvement->statut = $statut;

        return $mouvement;
    }

    public function getMouvement($statut) {
        $hash = "/mouvements_lots/".$this->declarant_identifiant."/".$this->getUniqueId()."-".KeyInflector::slugify($statut);

        if(!$this->getDocument()->exist($hash)) {

            return null;
        }

        return $this->getDocument()->get($hash);
    }

    public function getLotDocumentOrdre($documentOrdre) {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvements($this->declarant_identifiant, $this->numero_dossier, $this->numero_archive, sprintf("%02d", $documentOrdre));

        $docId = null;
        foreach($mouvements->rows as $mouvement) {
            $docId = $mouvement->id;
            break;
        }

        if(!$docId) {

            return null;
        }

        $doc = DeclarationClient::getInstance()->find($docId);

        return $doc->get($mouvement->value->lot_hash);
    }

    public function getLotFils()
    {

        return $this->getLotDocumentOrdre($this->document_ordre * 1 + 1);
    }


    public function getLotPere()
    {

        return $this->getLotDocumentOrdre($this->document_ordre * 1 - 1);
    }

    abstract public function getDocumentOrdre();

    abstract public function getDocumentType();

    public function getVersion() {

        return $this->getDocument()->getVersion();
    }

    public function isAffectable() {

        return !$this->isAffecte() && $this->exist('affectable') && $this->affectable;
    }

    public function isAffecte() {

        return $this->exist('document_fils') && $this->document_fils;
    }

    public function getCampagne() {

        return $this->getDocument()->getCampagne();
    }

}
