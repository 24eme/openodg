<?php
/**
 * Model for Lot
 *
 */

abstract class Lot extends acCouchdbDocumentTree
{
    const STATUT_AFFECTE_DEST = "01_AFFECTE_DEST";
    const STATUT_NONPRELEVABLE = "NON_PRELEVABLE";
    const STATUT_ATTENTE_PRELEVEMENT = "02_ATTENTE_PRELEVEMENT";
    const STATUT_PRELEVE = "03_PRELEVE";
    const STATUT_ATTABLE = "04_ATTABLE";
    const STATUT_ANONYMISE = "05_ANONYMISE";
    const STATUT_DEGUSTE = "06_DEGUSTE";
    const STATUT_CONFORME = "08_CONFORME";
    const STATUT_NONCONFORME = "08_NON_CONFORME";
    const STATUT_AFFECTE_SRC = "10_AFFECTE_SRC";
    const STATUT_MANQUEMENT_EN_ATTENTE = "09_MANQUEMENT_EN_ATTENTE";
    const STATUT_RECOURS_OC = "11_RECOURS_OC";
    const STATUT_CONFORME_APPEL = "12_CONFORME_APPEL";
    const STATUT_NONCONFORME_LEVEE = "15_NONCONFORME_LEVEE";

    const STATUT_CHANGE = "CHANGE";

    const STATUT_ELEVAGE = "02_ELEVAGE_EN_ATTENTE";
    const STATUT_ELEVAGE_EN_ATTENTE = "02_ELEVAGE_EN_ATTENTE";
    const STATUT_ELEVE = "03_ELEVE";

    const STATUT_CHANGE_DEST = "01_CHANGE_DEST";

    const STATUT_REVENDIQUE = "01_REVENDIQUE";
    const STATUT_ENLEVE = "01_ENLEVE";
    const STATUT_CONDITIONNE = "01_CONDITIONNE";
    const STATUT_REVENDICATION_SUPPRIMEE = "01_REVENDICATION_SUPPRIMEE";
    const STATUT_NONAFFECTABLE = "09_NON_AFFECTABLE";
    const STATUT_AFFECTABLE = "09_AFFECTABLE_ENATTENTE";

    const STATUT_CHANGE_SRC = "99_CHANGE_SRC";
    const STATUT_CHANGEABLE = "00_CHANGEABLE";
    const STATUT_DECLASSE = "10_DECLASSE";

    const CONFORMITE_CONFORME = "CONFORME";
    const CONFORMITE_NONCONFORME_MINEUR = "NONCONFORME_MINEUR";
    const CONFORMITE_NONCONFORME_MAJEUR = "NONCONFORME_MAJEUR";
    const CONFORMITE_NONCONFORME_GRAVE = "NONCONFORME_GRAVE";
    const CONFORMITE_NONCONFORME_ANALYTIQUE = "NONCONFORME_ANALYTIQUE";
    const CONFORMITE_NONTYPICITE_CEPAGE = "NONTYPICITE_CEPAGE";

    const SPECIFICITE_UNDEFINED = "UNDEFINED";
    const SPECIFICITE_PRIMEUR = "Primeur";

    const TYPE_ARCHIVE = 'Lot';
    const TABLE_IGNORE = 'IGNORE';

    public static $libellesStatuts = array(
        self::STATUT_NONPRELEVABLE => 'Non prélevable',
        self::STATUT_ATTENTE_PRELEVEMENT => 'En attente de prélèvement',
        self::STATUT_PRELEVE => 'Prélevé',
        self::STATUT_ATTABLE => 'Attablé',
        self::STATUT_ANONYMISE => 'Anonymisé',
        self::STATUT_DEGUSTE => 'Dégusté',
        self::STATUT_CONFORME => 'Conforme',
        self::STATUT_NONCONFORME => 'Non conforme',
        self::STATUT_NONCONFORME_LEVEE => 'Non conformité levée',
        self::STATUT_RECOURS_OC => 'En recours OC',
        self::STATUT_CONFORME_APPEL => 'Conforme en appel',
        self::STATUT_AFFECTE_SRC => 'Affecté à une dégustation (source)',
        self::STATUT_AFFECTE_DEST => 'Affecté à une dégustation (destination)',
        self::STATUT_CHANGE => 'Changé',
        self::STATUT_CHANGE_SRC => 'Changé (source)',
        self::STATUT_CHANGE_DEST => 'Changé (destination)',
        self::STATUT_DECLASSE => 'Déclassé',
        self::STATUT_ELEVAGE_EN_ATTENTE => 'En élevage',
        self::STATUT_ELEVE => 'Fin de l\'élevage',

        self::STATUT_MANQUEMENT_EN_ATTENTE => 'Manquement en attente',

        self::STATUT_REVENDIQUE => 'Revendiqué',
        self::STATUT_ENLEVE => 'Enlevé',
        self::STATUT_CONDITIONNE => 'Conditionné',
        self::STATUT_REVENDICATION_SUPPRIMEE => 'Revendication supprimée',
        self::STATUT_NONAFFECTABLE => 'Réputé conforme',
        self::STATUT_AFFECTABLE => 'Affectable',
    );

    public static $statut2label = array(
            Lot::STATUT_REVENDIQUE => "success",
            Lot::STATUT_CONFORME => "success",
            Lot::STATUT_PRELEVE => "success",
            Lot::STATUT_NONCONFORME => "danger",
            Lot::STATUT_MANQUEMENT_EN_ATTENTE => "primary",
            Lot::STATUT_RECOURS_OC => "warning",
            Lot::STATUT_CONFORME_APPEL => "success",
            Lot::STATUT_DECLASSE => "danger",
            Lot::STATUT_ELEVAGE_EN_ATTENTE => "warning",
            Lot::STATUT_ELEVE => "warning",
            Lot::STATUT_NONAFFECTABLE => "success"
        );

    public static $libellesConformites = array(
      self::CONFORMITE_CONFORME => "Conforme",
      self::CONFORMITE_NONCONFORME_MINEUR => "Non conformité mineure",
      self::CONFORMITE_NONCONFORME_MAJEUR => "Non conformité majeure",
      self::CONFORMITE_NONCONFORME_GRAVE => "Non conformité grave",
      self::CONFORMITE_NONTYPICITE_CEPAGE => "Non typicité cépage",
      self::CONFORMITE_NONCONFORME_ANALYTIQUE => "Non conformité analytique",
    );

    public static $shortLibellesConformites = array(
      self::CONFORMITE_CONFORME => "",
      self::CONFORMITE_NONCONFORME_MINEUR => "Mineure",
      self::CONFORMITE_NONCONFORME_MAJEUR => "Majeure",
      self::CONFORMITE_NONCONFORME_GRAVE => "Grave",
      self::CONFORMITE_NONTYPICITE_CEPAGE => "Typ. cép.",
      self::CONFORMITE_NONCONFORME_ANALYTIQUE => "Analytique",
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
        $defaults['millesime'] = $this->millesime;
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

    public function getFieldsToFill() {
        return  array('numero_logement_operateur', 'millesime', 'volume', 'destination_date', 'elevage', 'specificite', 'produit_hash');
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

    public function getPays() {
        if(!$this->exist('pays')) {

            return null;
        }

        return $this->_get('pays');
    }

    public function getCentilisation() {
        if(!$this->exist('centilisation')) {

            return null;
        }

        return $this->_get('centilisation');
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
        if ($type == DegustationClient::DEGUSTATION_TRI_MILLESIME) {
            return ($this->millesime) ? $this->millesime : 'XXXX';
         }
         if ($type == DegustationClient::DEGUSTATION_TRI_MANUEL) {
             return $this->position;
         }
         if (!$this->getConfig()||$type == DegustationClient::DEGUSTATION_TRI_NUMERO_ANONYMAT) {
           $numero= intval(substr($this->numero_anonymat, 1));
           return $numero;
         }
        if ($type == DegustationClient::DEGUSTATION_TRI_APPELLATION) {
            return $this->getConfig()->getAppellation()->getKey();
        }
        if ($type == DegustationClient::DEGUSTATION_TRI_COULEUR) {
            return $this->getConfig()->getCouleur()->getKey();
        }
        if ($type == DegustationClient::DEGUSTATION_TRI_GENRE) {
            return $this->getConfig()->getGenre()->getKey();
        }
        if ($type == DegustationClient::DEGUSTATION_TRI_CEPAGE) {
            return $this->getCepagesLibelle();
        }
        if ($type == DegustationClient::DEGUSTATION_TRI_PRODUIT) {
            return $this->_get('produit_hash').$this->_get('details');
        }
        throw new sfException('unknown type of value : '.$type);
    }

    public function isCleanable() {

        return $this->isEmpty();
    }

    public function getDestinationDateFr()
    {

        return Date::francizeDate($this->destination_date);
    }

    public function hasVolumeAndHashProduit(){
      return $this->volume && $this->produit_hash;
    }
    public function hasDocumentOrigine() {

      if (!$this->getDocOrigine()) {
        return false;
      }

      return true;
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
      return DeclarationClient::getInstance()->findCache($this->id_document);
    }

    public function isCurrent(){
        return $this->id_document == $this->getDocument()->_id;
    }

    public function hasBeenEdited(){
        return !$this->isCurrent();
    }

    public function setOrigineDocumentId($id) {
        $this->id_document = $id;
    }

    public function getOrigineDocumentId() {
        return $this->id_document;
    }


    public function getIntitulePartiel(){
      $libelle = 'lot '.$this->declarant_nom;
      if ($this->numero_logement_operateur) {
          $libelle .= ' ('.$this->numero_logement_operateur.')';
      }
      $libelle .= ' de '.$this->produit_libelle;
      if ($this->millesime){
        $libelle .= ' ('.$this->millesime.')';
      }
      return $libelle;
    }

    public function switchEleve($date = null){
        return $this->eleve($this->elevage, $date);
    }

    public function eleve($iselevage, $date = null){
        if(!$date){
            $date = date('Y-m-d');
        }
        if ($iselevage) {
            $this->elevage = false;
            $this->eleve = $date;
        }else{
            $this->elevage = true;
            $this->eleve = null;
        }
    }

    public function isPreleve(){
        return $this->preleve !== null;
    }

    public function isLeurre()
    {
        return $this->exist('leurre') && $this->leurre;
    }

    public function isDeclasse() {
        return !($this->produit_hash);
    }

    public function getUnicityKey(){
        return $this->getUniqueId();
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
        if (in_array(DegustationClient::DEGUSTATION_TRI_APPELLATION, $tri)) {
            $format .= '%a% ';
        }
        if (in_array(DegustationClient::DEGUSTATION_TRI_GENRE, $tri)) {
            $format .= '%g% ';
        }
        if (in_array(DegustationClient::DEGUSTATION_TRI_COULEUR, $tri)) {
            $format .= '%co% ';
        }
        $libelle = $this->getConfig()->getLibelleFormat(null, $format)." ";
        if (in_array(DegustationClient::DEGUSTATION_TRI_MILLESIME, $tri)) {
            $libelle .= $this->millesime.' ';
        }
        if (in_array(DegustationClient::DEGUSTATION_TRI_CEPAGE, $tri)) {
            $libelle .= "- ".$this->details.' ';
        }
        return $libelle;
    }

    public function isControle(){
        return ($this->affectable || $this->id_document_affectation);
    }

    public function isSecondPassage()
    {
        return $this->getNombrePassage() > 1;
    }

    public function isRedegustationDejaConforme() {
        foreach(LotsClient::getInstance()->getHistory($this->declarant_identifiant, $this->unique_id) as $mvt){
            if (in_array($mvt->key[MouvementLotHistoryView::KEY_STATUT], [Lot::STATUT_CONFORME, Lot::STATUT_NONAFFECTABLE]) && $mvt->key[MouvementLotHistoryView::KEY_ORIGINE_DOCUMENT_ID] != $this->getDocument()->_id) {
                return true;
            }
        }
        return false;
    }

    public function hasSpecificitePassage()
    {
        return preg_match("/ème dégustation/", $this->specificite);
    }

    public function getTextPassage()
    {
        $nb = $this->isSecondPassage() ? $this->getNombrePassage().'ème' : '1er';
        return $nb." passage";
    }

    public function getNombrePassage()
    {
        //On passe par le lot précédent pour connaitre son nombre d'affecté
        //car dans on est appelé depuis le save on n'est pas encore sauvé et la vue n'est donc pas à jour
        //alors que le prédécesseur est sauvé
        $lotProvenance = $this->getLotProvenance();
        if (!$lotProvenance) {
            return 0;
        }
        return MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lotProvenance) + 1;
    }

    public static function generateTextePassage($lot, $nb)
    {
        $specificite = $lot->specificite;

        $specificite = preg_replace('/(, )?\d(er|ème) dégustation/', '', $specificite);

        if ($nb > 1) {
            if ($specificite) {
                return sprintf('%s, %dème dégustation', $specificite, $nb);
            }
            return sprintf('%dème dégustation', $nb);
        }

        return $specificite;
    }

    public function updateSpecificiteWithDegustationNumber()
    {
        $nombrePassage = $this->getNombrePassage();
        $this->specificite = self::generateTextePassage($this, $nombrePassage);
    }

    public function redegustation()
    {
        // Tagguer le lot avec un flag special
        // Regenerer les mouvements

        $this->affectable = true;
    }

    public function setNumeroTable($numero) {
        $ret = $this->_set('numero_table', $numero);
        $this->position = '999999';
        $this->generateAndSetPosition();
        return $ret;
    }

    public function generateAndSetPosition() {
        $table = ($this->numero_table) ? $this->numero_table : 99;
        $i = 0;
        $position = 0;
        foreach($this->getDocument()->getLotsTableOrFreeLotsCustomSort($table, !($this->numero_table)) as $lot) {
            $i++;
            if ($lot == $this) {
                $position = $i;
                break;
            }
        }
        if ($position) {
            $this->position = sprintf("%02d%03d0", $table, $position);
        }
    }

    public function getPosition()
    {
      if (!$this->_get('position')) {
          $this->generateAndSetPosition();
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

    public function changePosition($sens)
    {
      if (!$this->numero_table) {
        return;
      }
      $this->getDocument()->tri = DegustationClient::DEGUSTATION_TRI_MANUEL;
      if ($sens > 0) {
          return $this->switchPosition($this, $this->getLotInPrevPosition());
      }else {
          return $this->switchPosition($this->getLotInNextPosition(), $this);
      }
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

    public function addCepage($cepage, $repartition) {
        $this->cepages->add($cepage, $repartition);
    }

    public function getCepagesLibelle($withRepartition = true) {
        $libelle = null;
        foreach($this->getPourcentagesCepages() as $cepage => $repartition) {
            if($libelle) {
                $libelle .= ", ";
            }
            $libelle .= $cepage;
            if($withRepartition) {
                $libelle .= " (".number_format($repartition, 2, ',', ' ')."%)";
            }
        }
        return $libelle;
    }

    public function getPourcentagesCepages() {
      $volume_total = 0;
      $cepages = array();
      foreach($this->cepages as $volume) {
        $volume_total += $volume;
      }
      foreach($this->cepages as $cep => $volume) {
        if (!isset($cepages[$cep])) {
            $cepages[$cep] = 0;
        }
        $vol = ($volume_total>0)? round(($volume/$volume_total) * 100) : 0;
        $cepages[$cep] += $vol;
      }
      return $cepages;
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

    public function getTypeDocument()
    {
        return substr($this->id_document, 0, 4);
    }

    public function getTypeProvenance()
    {
        return substr($this->id_document_provenance, 0, 4);
    }

    abstract public function getMouvementFreeInstance();

    public function getLibelle()
    {
        $libelle = $this->getProduitLibelle();

        if($this->millesime) {
            $libelle .= " ".$this->millesime;
        }
        if($this->specificite) {
            $libelle .= " ".$this->specificite;
        }

        if($this->getCepagesLibelle(false)) {
            $libelle .= " ".$this->getCepagesLibelle(false);
        }

        $libelle .= " (N° ".$this->numero_logement_operateur.")";

        return $libelle;

    }

    public function getUniqueId(){
        if(is_null($this->_get('unique_id'))) {
            $this->set('unique_id', KeyInflector::slugify($this->campagne."-".$this->numero_dossier.'-'.$this->numero_archive));
        }

        return $this->_get('unique_id');
    }

    public function setCampagne($campagne) {
        $this->unique_id = null;

        $this->_set('campagne', $campagne);

        $this->getUniqueId();
    }

    public function setNumeroArchive($numeroArchive) {
        $this->unique_id = null;

        $this->_set('numero_archive', $numeroArchive);

        $this->getUniqueId();
    }

    public function setNumeroDossier($numeroDossier) {
        $this->unique_id = null;

        $this->_set('numero_dossier', $numeroDossier);

        $this->getUniqueId();
    }

    public function buildMouvement($statut, $detail = null, $date = null, $numero_archive_incremente = false) {
        $mouvement = $this->getMouvementFreeInstance();

        if (!$date) {
            $date = $this->date;
        }
        $mouvement->date = $date;
        $mouvement->numero_dossier = $this->numero_dossier;
        if (!$numero_archive_incremente) {
            $mouvement->numero_archive = $this->numero_archive;
        }else{
            $mouvement->numero_archive = substr($this->numero_archive, 0, -1);
        }
        $mouvement->libelle = $this->getLibelle();
        $mouvement->detail = $detail;
        $mouvement->volume = $this->volume;
        $mouvement->version = $this->getVersion();
        $mouvement->document_ordre = $this->getDocumentOrdre();
        $mouvement->document_type = $this->getDocumentType();
        $mouvement->document_id = $this->getDocument()->_id;
        $mouvement->origine_type = $this->getOrigineType();
        $mouvement->lot_unique_id = $this->getUniqueId();
        $mouvement->lot_hash = $this->getHash();
        $mouvement->declarant_identifiant = $this->declarant_identifiant;
        $mouvement->declarant_nom = $this->declarant_nom;
        $mouvement->campagne = $this->getCampagne();
        $mouvement->statut = $statut;

        return $mouvement;
    }

    public function getMouvements() {
        if(!$this->getDocument()->exist("/mouvements_lots/".$this->declarant_identifiant)) {

            return array();
        }

        $mouvements = array();

        foreach($this->getDocument()->get("/mouvements_lots/".$this->declarant_identifiant) as $m) {
            if($m->lot_unique_id != $this->unique_id) {
                continue;
            }
            $mouvements[$m->getKey()] = $m;
        }

        return $mouvements;
    }

    public function getMouvement($statut) {
        $hash = "/mouvements_lots/".$this->declarant_identifiant."/".$this->getUniqueId()."-".KeyInflector::slugify($statut);

        if(!$this->getDocument()->exist($hash)) {

            return null;
        }

        return $this->getDocument()->get($hash);
    }

    public function getLotDocumentOrdre($documentOrdre, $numero_archive_incremente = false) {
        if ($numero_archive_incremente) {
            $numero_archive = substr($this->numero_archive, 0, -1);
        }else{
            $numero_archive = $this->numero_archive;
        }

        return LotsClient::getInstance()->find($this->declarant_identifiant, $this->campagne, $this->numero_dossier, $numero_archive, sprintf("%02d", $documentOrdre));
    }

    public function getOrigineType() {
        if(is_null($this->_get('origine_type'))) {
            $this->origine_type = $this->getLotDocumentOrdre(1)->origine_type;
        }

        return $this->_get('origine_type');
    }

    public function updateDocumentDependances() {
        $this->getOrigineType();
        $lotAffectation = $this->getLotAffectation();
        if($lotAffectation) {
            $this->id_document_affectation = $lotAffectation->getDocument()->_id;
        }else {
            $this->id_document_affectation = null;
        }
        $lotProvenance = $this->getLotProvenance();
        if($lotProvenance) {
            $this->id_document_provenance = $lotProvenance->getDocument()->_id;
        }else{
            $this->id_document_provenance = null;
        }
    }

    public function getLotAffectation()
    {
        return $this->getLotDocumentOrdre(intval($this->document_ordre) + 1);
    }

    public function getLotProvenance()
    {
        return $this->getLotDocumentOrdre(intval($this->document_ordre) - 1);
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

        return preg_match('/^DEGUST/', $this->id_document_affectation);
    }

    public function isChange() {

        return preg_match('/^CHGTDENOM/', $this->id_document_affectation);
    }

    public function getDestinationShort()
    {
        $dest = [];

        if (strpos($this->id_document_provenance, 'TRANSACTION') !== false) {
            $dest[] = 'VRAC';
        }

        if (strpos($this->id_document_provenance, 'CONDITIONNEMENT') !== false) {
            $dest[] = 'CONDITIONNEMENT';
        }

        if (strpos($this->destination_type, 'VRAC') !== false) {
            $dest[] = 'VRAC';
        }

        if (strpos($this->destination_type, 'CONDITIONNEMENT') !== false) {
            $dest[] = 'CONDITIONNEMENT';
        }

        return implode(' / ', array_unique($dest));
    }
    public function isInElevage()
    {
      return ($this->elevage);
    }

    public function getAdresseLogement() {
        return Anonymization::hideIfNeeded($this->_get('adresse_logement'));
    }

    public function getDeclarantNom() {
        return Anonymization::hideIfNeeded($this->_get('declarant_nom'));
    }

}
