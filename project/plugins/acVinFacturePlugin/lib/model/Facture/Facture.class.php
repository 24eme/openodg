<?php

/**
 * Model for Facture
 *
 */
class Facture extends BaseFacture implements InterfaceArchivageDocument, InterfacePieceDocument {

    private $documents_origine = array();
    protected $declarant_document = null;
    protected $archivage_document = null;
    protected $piece_document = null;
    protected $forceFactureMouvements = false;


    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->archivage_document = new ArchivageDocument($this);
        $this->piece_document = new PieceDocument($this);
    }

    public function getCampagne() {

        return $this->_get('campagne');
    }

    public function storeEmetteur() {
        foreach (FactureConfiguration::getInstance()->getInfos($this->region) as $param => $value) {
            if($this->emetteur->exist($param)){
                $this->emetteur->$param = $value;
            }
        }
    }

    public function storeDatesCampagne($date_facturation = null, $date_emission = null) {
        if ($date_emission) {
            $this->date_emission = $date_emission;
        }else{
            $this->date_emission = date('Y-m-d');
        }
        $this->date_facturation = $date_facturation;
        if(!$this->date_facturation) {
            $this->date_facturation = $this->date_emission;
        }
        $this->date_echeance = $date_facturation;
        if(FactureConfiguration::getInstance()->getDelaisPaiement()) {
            $date_facturation_object = new DateTime($this->date_facturation);
            $this->date_echeance = $date_facturation_object->modify(FactureConfiguration::getInstance()->getDelaisPaiement())->format('Y-m-d');
        }

        $this->campagne = FactureClient::getInstance()->getCampagneByDate($this->date_facturation);
    }

    public function setModalitePaiement($modalitePaiement) {
        $modalitePaiement = str_replace(array("%iban%", "%bic%"), array(Organisme::getInstance()->getIban(), Organisme::getInstance()->getBic()), $modalitePaiement);

        return $this->_set('modalite_paiement', $modalitePaiement);
    }

    public function constructIds() {
        if(FactureConfiguration::getInstance()->deprecatedNumeroFactureIsId()){ // Pour nantes obsolète
          $this->numero_facture = FactureClient::getInstance()->getNextNoFactureCampagneFormatted($this->identifiant, $this->campagne,FactureConfiguration::getInstance()->getNumeroFormat());
        }else{
          $date_emission_object = new DateTime($this->date_emission);
          $this->numero_facture = FactureClient::getInstance()->getNextNoFacture($this->identifiant, $date_emission_object->format('Ymd'));
        }
        $this->_id = FactureClient::getInstance()->getId($this->identifiant, $this->numero_facture);
    }


    public function getNumeroAva(){
        if($this->exist('numero_ava') && $this->_get('numero_ava')) {
            return $this->_get('numero_ava');
        }
        return $this->_get('numero_odg');
    }

    public function getNumeroOdg(){
        if(FactureConfiguration::getInstance()->deprecatedNumeroFactureIsId()) { // Pour nantes obsolète
            return $this->getNumeroFacture();
        }

        if($this->exist('numero_odg') && $this->_get('numero_odg')) {
            return $this->_get('numero_odg');
        }

        if(FactureConfiguration::getInstance()->getNumeroFormat()) {

            return sprintf(FactureConfiguration::getInstance()->getNumeroFormat(), substr($this->campagne, (int)preg_replace('/^%([0-9]+d).+$/', '\1', FactureConfiguration::getInstance()->getNumeroFormat())*-1), $this->numero_archive);
        }

        return $this->campagne . $this->numero_archive;
    }

    public function getNumeroReference() {
      return substr($this->numero_facture,6,2).' '.substr($this->numero_facture,0,6);
    }

    public function getTaxe() {
        return $this->total_ttc - $this->total_ht;
    }

    public function getLignesForPdf() {
        if(!FactureConfiguration::getInstance()->isLigneUnique()){
          return $this->_get('lignes');
        }
        $libelles = array();
        $detailsArr = array();
        $produits_identifiants_analytiques = array();
        $total_ht = 0.0;
        $total_tva = 0.0;
        $quantite = 0.0;
        $prix_unitaire = 0.0;
        foreach ($this->_get('lignes') as $keyLigne => $ligne) {
          foreach ($ligne->details as $detail) {
            $quantite = $detail->quantite;
            $prix_unitaire += $detail->prix_unitaire;
            $total_ht += $ligne->montant_ht;
            $total_tva += $ligne->montant_tva;

            $libelles[] =$ligne->libelle;
            $produits_identifiants_analytiques[] =$ligne->produit_identifiant_analytique;
          }
        }
        $ligneForPdf = new stdClass();
        $ligneForPdf->quantite = $quantite;
        $ligneForPdf->prix_unitaire = $prix_unitaire;
        $ligneForPdf->montant_ht = $total_ht;
        $ligneForPdf->montant_tva = $total_tva;
        $ligneForPdf->libelle = implode(',',$libelles);
        $ligneForPdf->$total_ht = implode(',',$produits_identifiants_analytiques);
        return $ligneForPdf;
    }

    public function facturerMouvements() {
        foreach ($this->getLignes() as $l) {
            $l->facturerMouvements();
        }
    }

    public function defacturer() {
        if (!$this->isRedressable())
            return;
        foreach ($this->getLignes() as $ligne) {
            $ligne->defacturerMouvements();
        }
        $this->statut = FactureClient::STATUT_REDRESSEE;
    }

    public function isRedressee() {
        return ($this->statut == FactureClient::STATUT_REDRESSEE);
    }

    public function isRedressable() {
        return ($this->statut != FactureClient::STATUT_REDRESSEE && $this->statut != FactureClient::STATUT_NONREDRESSABLE);
    }

    public function getEcheancesArray() {
        $e = $this->_get('echeances')->toArray();
        usort($e, 'Facture::triEcheanceDate');
        return $e;
    }

    public function getLignesArray() {
        $l = $this->_get('lignes')->toArray();
        usort($l, 'Facture::triOrigineDate');
        return $l;
    }

    static function triOrigineDate($ligne_0, $ligne_1) {
        return self::triDate("origine_date", $ligne_0, $ligne_1);
    }

    static function triEcheanceDate($ligne_0, $ligne_1) {
        return self::triDate("echeance_date", $ligne_0, $ligne_1);
    }

    static function triDate($champ, $ligne_0, $ligne_1) {
        if ($ligne_0->{$champ} == $ligne_1->{$champ}) {

            return 0;
        }
        return ($ligne_0->{$champ} > $ligne_1->{$champ}) ? -1 : +1;
    }

    public function addLigne($configCollection) {
        $ligne = $this->lignes->add($configCollection->getKey());
        $ligne->libelle = $configCollection->libelle;
        $ligne->produit_identifiant_analytique = $configCollection->code_comptable;

        return $ligne;
    }

    /** facturation par mvts **/
    public function storeLignesByMouvementsView($mouvement_agreges, $mouvements_originaux = null) {
            if (!$mouvement_agreges->value->categorie) {
                throw new sfException('Categorie missing in mouvement '.$mouvement->id);
            }
            $keyLigne = str_replace("%detail_identifiant%",$mouvement_agreges->value->detail_identifiant,$mouvement_agreges->value->categorie);
            $ligne = $this->lignes->add($keyLigne);
            $ligne->libelle = $mouvement_agreges->value->type_libelle;
            if (!$mouvements_originaux) {
                $mouvements_originaux = array($mouvement_agreges);
            }
            foreach($mouvements_originaux as $mouvement) {
                $ligne->origine_mouvements->add($mouvement->id)->add(null, $mouvement->key[MouvementFactureView::KEY_ORIGIN]);
            }

            $quantite = 0;
            $template = $this->getTemplate(substr($mouvement_agreges->value->campagne, 0, 4));
            $code_comptable = false;
            if (!$template) {
                throw new sfException("No template found (".$mouvement_agreges->id.")");
            }

            if (strpos($mouvement_agreges->id, 'MOUVEMENTSFACTURE') !== false) {
              $ligne->produit_identifiant_analytique = $mouvement_agreges->value->identifiant_analytique;
            } else {
              foreach ($template->getCotisations() as $cotisName => $cotis) {
                  if($cotis->code_comptable) {
                      $code_comptable = true;
                      $cotisName = str_replace('%detail_identifiant%', $mouvement_agreges->value->detail_identifiant, $cotisName);
                      if ($mouvement_agreges->value->categorie == $cotisName) {
                          $ligne->produit_identifiant_analytique = $cotis->code_comptable;
                          break;
                      }
                  }
              }
            }
            $detail = $ligne->details->add();
            $detail->prix_unitaire = $mouvement_agreges->value->taux;
            $detail->taux_tva = $mouvement_agreges->value->tva;
            $detail->libelle = $mouvement_agreges->value->detail_libelle;
            if(isset($mouvement_agreges->value->unite) && $mouvement_agreges->value->unite) {
                $detail->add('unite', $mouvement_agreges->value->unite);
            }
            $detail->quantite = $mouvement_agreges->value->quantite;
            $ligne->updateTotaux();
            if ($code_comptable && !$ligne->produit_identifiant_analytique) {
                throw new sfException("Pas d'identifiant analytique trouvé pour ".$mouvement_agreges->value->categorie." (".$mouvement_agreges->id.")");
            }
    }

    public function orderLignesByCotisationsKeys() {
        $lignes = $this->_get('lignes')->toArray();
        ksort($lignes);

        $this->remove('lignes');
        $factureLignes = $this->add('lignes');
        foreach ($lignes as $cotisName => $l) {
            $factureLignes->add($cotisName,$l);
        }
    }

    public function storePapillons() {
      $papillons = FactureConfiguration::getInstance()->getEcheancesArray();
      $nbPapillons = count($papillons);
      $cpt = 0;
      $cumul = 0.0;
      foreach ($papillons as $pvalue) {
        $cpt++;
        $montant_papillon = $this->total_ttc / $pvalue["montant_division"];
        if($cpt == $nbPapillons){
          $montant_papillon = $this->total_ttc - $cumul;
        }
        $montant_papillon_str = sprintf("%01.02f",$montant_papillon);
        $cumul += floatval($montant_papillon_str);
        $dateField = $pvalue["field"];
        if($pvalue["date_jour_mois"]){
          $d = $this->get($dateField);
          $date_echeance = new DateTime($d);
          $date_jour_mois = $pvalue["date_jour_mois"].".".date('Y');
          $this->updateEcheance($pvalue["libelle"],$date_jour_mois,$montant_papillon_str);
        }
        else{
          $this->updateEcheance($pvalue["libelle"],$pvalue["libelle_date"],$montant_papillon_str);
        }
      }
    }

    public function updateEcheance($echeance_code, $date, $montant_ht) {
        if(count($this->echeances) >= count(FactureConfiguration::getInstance()->getEcheancesArray())){
                return;
        }

        $echeance = new stdClass();
        $echeance->echeance_code = $echeance_code;
        $echeance->montant_ttc = $this->ttc($montant_ht);
        $echeance->echeance_date = $date;
        $this->add("echeances")->add(count($this->echeances), $echeance);
    }

    public function storeOrigines() {
        foreach ($this->getLignes() as $ligne) {
            foreach ($ligne->origine_mouvements as $idorigine => $null) {
                if (!array_key_exists($idorigine, $this->origines->toArray(true, false)))
                    $this->origines->add($idorigine, $idorigine);
            }
        }
    }

    public function getOrigineTypes() {
        $origines = [];
        foreach($this->origines as $id) {
            $origines[] = explode("-", $id)[0];
        }

        return  $origines;
    }

    public function storeTemplates(array $templates = null) {
        if ($templates) {
            foreach ($templates as $template) {
                $this->templates->add($template, $template);
            }
        }
    }

    public function updateTotaux() {
        $this->lignes->updateTotaux();
        $this->updateTotalHT();
        $this->updateTotalTaxe();
        $this->updateTotalTTC();


    }

    public function updateTotalHT()
    {
        $this->total_ht = 0;
        foreach ($this->lignes as $ligne) {
        	$this->total_ht += $ligne->montant_ht;
        }
        $this->total_ht = round($this->total_ht, 2);
    }

    public function updateTotalTTC()
    {
    	$this->total_ttc = round($this->total_ht + $this->total_taxe, 2);
    }

    public function updateTotalTaxe()
    {
    	$this->total_taxe = 0;
        foreach ($this->lignes as $ligne) {
        	$this->total_taxe += $ligne->montant_tva;
        }
        $this->total_taxe = round($this->total_taxe, 2);
    }

    public function addPrelevementAutomatique()
    {
      if (!MandatSepaConfiguration::getInstance()->isActive()) {
        return false;
      }
      $paiement = $this->add('paiements')->add();
      $paiement->montant =  $this->total_ttc;
      $paiement->type_reglement = FactureClient::FACTURE_PAIEMENT_PRELEVEMENT_AUTO;
      $paiement->add('execute',false);
      $paiement->date = date('Y-m-d',strtotime($this->date_facturation.'+15 days'));
      $this->versement_sepa = 0;
    }

    public function getNbLignesMouvements() {
        $nbLigne = 0 ;
        if (FactureConfiguration::getInstance()->isLigneUnique()) {
            return 1;
        }
        foreach ($this->lignes as $lignesType) {
            $nbLigne += count($lignesType->details) + FactureConfiguration::getInstance()->isLigneDetailWithTitle() * 1;
        }
        return $nbLigne;
    }

    protected function ttc($p) {
      $taux_tva = $this->getTauxTva()/100;
      return round($p + $p * $taux_tva, 2);
    }

    public function getTauxTva() {
        if($this->exist('taux_tva') && !is_null($this->_get('taux_tva'))){
            return round($this->_get('taux_tva'),2);
        }
        $config_tva = sfConfig::get('app_tva_taux');
        $date_facturation = str_replace('-', '', $this->date_facturation);
        $taux_f = 0.0;
        foreach ($config_tva as $date => $taux) {
            if($date_facturation >= $date){
                $taux_f = round($taux,2);
            }
        }
        return $taux_f;
    }

    public function save() {
        if ($this->total_ht > 0 && FactureConfiguration::getInstance()->hasEcheances()) {
          $this->storePapillons();
        }

        $this->updateVersementSepa();

        $saved = parent::save();

        $this->saveDocumentsOrigine();

        return $saved;
    }

    public function saveDocumentsOrigine() {
        foreach ($this->origines as $docid) {
            $doc = FactureClient::getInstance()->getDocumentOrigine($docid);
	    if ($doc) {
	      $doc->save(false);
	    }
        }
    }

    public function getTemplate($campagne = null) {
        if ($campagne) {
            foreach ($this->templates as $template_id) {
                if (strpos($template_id, $campagne) !== false) {
                    return TemplateFactureClient::getInstance()->find($template_id);
                }
            }
        }

        $template_id = $this->getTemplateId();
        if ($template_id) {
            return TemplateFactureClient::getInstance()->find($template_id);
        }
        return null;
    }

    public function getCampageTemplate() {

        return preg_replace('/^[A-Z]+-[A-Z]+-([A-Z]+-)?/', '', $this->getTemplateId());
    }

    public function getTemplateId() {
        foreach($this->templates as $template_id) {

            return $template_id;
        }

        return null;
    }

    public function forceFactureMouvements() {
        $this->forceFactureMouvements = true;
    }

    protected function preSave() {
        if (($this->isNew() || $this->forceFactureMouvements) && $this->statut != FactureClient::STATUT_REDRESSEE) {
            $this->forceFactureMouvements = false;
            $this->facturerMouvements();
            $this->storeOrigines();
        }

        if (!$this->versement_comptable) {
            $this->versement_comptable = 0;
        }
        if (!$this->exist('paiements') || !count($this->paiements)) {
            $this->versement_comptable_paiement = 1;
            $this->versement_sepa = 1;
        }
        $this->updateVersementComptablePaiement();

        if (FactureConfiguration::getInstance()->hasFacturationParRegion() && $this->region && !$this->exist('type_archive')) {
            $this->add('type_archive', $this->type.'_'.$this->region);
        }

        $this->archivage_document->preSave();
        $this->numero_odg = $this->getNumeroOdg();
    }

    public function storeDeclarant($doc) {
    	$this->numero_adherent = ($doc->exist('identifiant_interne'))? $doc->identifiant_interne : $doc->identifiant;
        $declarant = $this->declarant;
        $declarant->nom = $doc->nom_a_afficher;
        //$declarant->num_tva_intracomm = $this->societe->no_tva_intracommunautaire;
        $declarant->adresse = $doc->adresse;
        if($doc->exist('adresse_complementaire') && $doc->adresse_complementaire) {
            $declarant->adresse .= ", ".$doc->adresse_complementaire;
        }
        $declarant->commune = $doc->commune;
        $declarant->code_postal = $doc->code_postal;
        $declarant->raison_sociale = ($doc->exist('raison_sociale'))? $doc->raison_sociale : $doc->societe_informations->raison_sociale;

		$this->code_comptable_client = preg_replace("/^[0]+/", "", $this->numero_adherent);

        if(method_exists($doc,"getSociete")) {
            $this->code_comptable_client = ($doc->getSociete()->exist("code_comptable_client") && $doc->getSociete()->code_comptable_client) ? $doc->getSociete()->code_comptable_client : $doc->identifiant;
        }
    }

    public function isPayee() {

        return $this->date_paiement;
    }

    public function isVersementComptablePaiement() {

        return $this->versement_comptable_paiement && count($this->paiements) > 0;
    }

    public function getPaiements() {
        if (!$this->exist('paiements')) {
            $f = new Facture();
            $f->add('paiements');
            return $f->paiements;
        }
        return $this->_get('paiements');
    }

    public function updateMontantPaiement() {
        $this->_set('montant_paiement', $this->paiements->getPaiementsTotal());
    }

    public function getMontantPaiement() {
        $this->updateMontantPaiement();

        return Anonymization::hideIfNeeded($this->_get('montant_paiement'));
    }

    public function hasNonPaiement() {
        return $this->getMontantPaiement() < $this->total_ttc;
    }

    public function getCodeComptableClient() {
      return $this->_get('code_comptable_client');
    }

    public function getSociete() {
        return SocieteClient::getInstance()->find($this->identifiant);
    }

    public function getCompte() {

        return CompteClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function getPrefixForRegion() {
        return EtablissementClient::getPrefixForRegion($this->region);
    }

    public function hasAvoir(){
        return ($this->exist('avoir') && !is_null($this->get('avoir')));
    }

    public function getCvi(){
      $societeMasterCompte = $this->getSociete()->getMasterCompte();
      if($societeMasterCompte->exist('etablissement_informations') &&
         $societeMasterCompte->etablissement_informations->exist('cvi') &&
         $cvi = $societeMasterCompte->etablissement_informations->cvi){
           return $cvi;
      }
      $etablissement = $this->getSociete()->getEtablissementPrincipal();
      if($etablissement->exist('cvi') &&
         $cvi = $etablissement->cvi){
           return $cvi;
      }
      return null;
    }

    public function isAvoir() {

        return $this->total_ht < 0.0;
    }

    /*** ARCHIVAGE ***/

    public function getNumeroArchive() {

        return $this->_get('numero_archive');
    }

    public function isArchivageCanBeSet() {

        return true;
    }

    /*** FIN ARCHIVAGE ***/

    /*** VERSEMENT COMPTABLE ***/

    public function setVerseEnCompta($paiement = false) {
        if($paiement) {
            return $this->_set('versement_comptable_paiement', 1);
        }

        return $this->_set('versement_comptable', 1);
    }

    public function setDeVerseEnCompta($paiement = false) {
        if($paiement) {
            return $this->_set('versement_comptable_paiement', 0);
        }

        return $this->_set('versement_comptable', 0);
    }

    /*** VERSEMENT COMPTABLE ***/

    public function addOneMessageCommunication($message_communication = null) {
        $this->add('message_communication', $message_communication);
    }

    public function hasMessageCommunication() {
        return $this->exist('message_communication');
    }

    public function getMessageCommunicationWithDefault() {
        if($this->exist('message_communication')){
            return $this->_get('message_communication');
        }
        return "";
    }

    protected function doSave() {
    	$this->piece_document->generatePieces();
    }

    /**** PIECES ****/

    public function getAllPieces() {
    	$type = ($this->isAvoir())? 'Avoir' : 'Facture';
    	$date = new DateTime($this->date_facturation);
    	return (!$this->getDateFacturation())? array() : array(array(
    		'identifiant' => str_replace('E', '', $this->getIdentifiant()),
    		'date_depot' => $this->getDateFacturation(),
    		'libelle' => $type.' n° '.$this->numero_odg.' du '.$date->format('d/m/Y').' - '.number_format($this->total_ht, 2, '.', ' ').' € HT',
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('facturation_pdf', $this);
    }

    public function hasUrlPublic() {
        return true;
    }

    public function generateUrlPublicPiece($source = null, $absolute = false) {

        return sfContext::getInstance()->getRouting()->generate('facturation_pdf_auth', array('id' => $this->_id, 'auth' => UrlSecurity::generateAuthKey($this->_id)), $absolute);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return null;
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return false;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }
    /**** FIN DES PIECES ****/

    public function getTotalHt() {
        return Anonymization::hideIfNeeded($this->_get('total_ht'));
    }
    public function getTotalTtc() {
        return Anonymization::hideIfNeeded($this->_get('total_ttc'));
    }
    public function getTotalTaxe() {
        return Anonymization::hideIfNeeded($this->_get('total_taxe'));
    }

    public function isTelechargee() {
        if(!$this->exist('date_telechargement')) {

            return false;
        }

        return (bool) $this->date_telechargement;
    }

    public function getNbPaiementsAutomatique(){
        if($this->paiements){
          return count($this->paiements);
        }
        return 0;
    }

    public function setTelechargee($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        if ($this->exist('date_telechargement') && $this->date_telechargement) {
            return;
        }

        if (! $this->exist('date_telechargement')) {
            $this->add('date_telechargement');
        }

        $this->_set('date_telechargement', $date);
    }

    public function updateVersementComptablePaiement() {
        if(!$this->exist('paiements') && $this->_get('montant_paiement') && $this->_get('date_paiement') && $this->exist('reglement_paiement')) {
            $paiement = $this->add('paiements')->add();
            $paiement->montant = $this->_get('montant_paiement');
            $paiement->commentaire = $this->_get('reglement_paiement');
            $paiement->date = $this->_get('date_paiement');
            $paiement->versement_comptable = $this->versement_comptable_paiement;
            $this->remove('reglement_paiement');
        }
        $this->updateMontantPaiement();

        $versement = true;
        $date = null;
        foreach ($this->paiements as $p) {
            $versement = $versement && $p->versement_comptable;
            if ($p->date > $date) {
                $date = $p->date;
            }
        }
        $this->versement_comptable_paiement = $versement * 1;
        $this->date_paiement = $date;
    }

    public function updateDatePaiementFromPaiements() {
        $date = null;
        foreach($this->paiements as $p) {
            if ($p->date > $date) {
                $date = $p->date;
            }
        }
        return $this->date_paiement = $date;
    }

    public function updateVersementSepa(){
        $versement_sepa = 1;
        foreach($this->paiements as $paiement){
            if (! $paiement->exist('execute')) {
                continue;
            }
            if(!$paiement->execute){
                $versement_sepa = 0;
            }
        }
        $this->versement_sepa = $versement_sepa;
    }
}
