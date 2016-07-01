<?php
/**
 * Model for Tirage
 *
 */

class Tirage extends BaseTirage implements InterfaceDeclarantDocument, InterfaceDeclaration {

    protected $declarant_document;

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
    }

    public function constructId() {
        $this->set('_id', 'TIRAGE-' . $this->identifiant . '-' . $this->campagne . $this->numero);
    }

    public function initDoc($identifiant, $campagne, $numero) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->numero = $numero;
        $this->updateCepages();
        $this->getQualite();
    }

    public function storeDRFromDRev() {
        $drev = $this->getDRev();

        if(!$drev) {

            return false;
        }

        if($drev->isAutomatique()) {

            return false;
        }

        if(!$drev->hasDR()) {

            return false;
        }

        $drContent = file_get_contents($drev->getAttachmentUri('DR.pdf'));

        if(!$drContent) {

            return false;
        }

        return $this->storeAsAttachment($drContent, "DR.pdf", "application/pdf");
    }

    public function hasDR() {

        return $this->_attachments->exist('DR.pdf');
    }

    public function hasSV() {
        return (($this->documents->exist(TirageDocuments::DOC_SV11)
                && ($this->documents->get(TirageDocuments::DOC_SV11)->statut) == TirageDocuments::STATUT_RECU)
                ||
                ($this->documents->exist(TirageDocuments::DOC_SV12)
                && ($this->documents->get(TirageDocuments::DOC_SV12)->statut) == TirageDocuments::STATUT_RECU)
                );


    }

    public function getDRev() {

        return DRevClient::getInstance()->find("DREV-".$this->identifiant."-".$this->campagne);
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function storeEtape($etape) {
        $this->add('etape', $etape);
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->campagne);
    }

    public function getConfigurationCepages() {

        return $this->getConfiguration()->declaration->get('certification/genre/appellation_CREMANT/mention/lieu/couleur')->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_TIRAGE);
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->validation = $date;
    }

    public function hasCompleteDocuments()
    {
        $complete = true;
        foreach($this->getOrAdd('documents') as $document) {
            if ($document->statut != DRevDocuments::STATUT_RECU) {
                $complete = false;
                break;
            }
        }
        return $complete;
    }

    public function isValide() {
        return $this->exist('validation') && $this->validation;
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }

    public function validateOdg() {
        $this->validation_odg = date('Y-m-d');
    }

    public function updateCepages() {
        $cepages = array();
        foreach($this->getConfigurationCepages() as $cepage) {
            $cepages[$cepage->getKey()] = $cepage->getLibelle();
        }
        sort($cepages);
        foreach ($cepages as $keyCep => $libelle) {

            $this->cepages->add($keyCep)->libelle = $libelle;
        }
    }

    public function getDateMiseEnBouteilleDebutObject() {
    	if (!$this->date_mise_en_bouteille_debut) {

    		return null;
    	}

    	return new DateTime($this->date_mise_en_bouteille_debut);
    }

    public function getDateMiseEnBouteilleDebutFr() {
    	$date = $this->getDateMiseEnBouteilleDebutObject();

    	if (!$date) {

    		return null;
    	}

    	return $date->format('d/m/Y');
    }

    public function getDateMiseEnBouteilleFinObject() {
    	if (!$this->date_mise_en_bouteille_fin) {

    		return null;
    	}

    	return new DateTime($this->date_mise_en_bouteille_fin);
    }

    public function getDateMiseEnBouteilleFinFr() {
    	$date = $this->getDateMiseEnBouteilleFinObject();

    	if (!$date) {

    		return null;
    	}

    	return $date->format('d/m/Y');
    }

    public function isMillesimeAnnee() {

        return preg_match("/^[0-9]{4}$/", $this->getMillesime());
    }

    public function setNumero($numero) {

        return $this->_set('numero', sprintf("%02d", $numero));
    }

    public function isNegociant() {
        $etblmt = $this->getEtablissementObject();
        return ($etblmt->familles->exist('NEGOCIANT') &&  $etblmt->familles->get('NEGOCIANT'));
    }

    public function isCaveCooperative() {
        $etblmt = $this->getEtablissementObject();
        return ($etblmt->familles->exist('CAVE_COOPERATIVE') && $etblmt->familles->get('CAVE_COOPERATIVE'));
    }

    public function isViticulteur() {
        return !($this>isNegociant()) && !($this->isCaveCooperative());
    }

    public function getQualite() {
        $q = $this->_get('qualite');
        if ($q) {
            return $q;
        }
        $q = "Viticulteur-Manipulant total ou partiel";
        if ($this->isCaveCooperative()) {
            $q = "Cave coopérative";
        }else if ($this->isNegociant()) {
            $q = "Négociant";
        }
        $this->_set('qualite', $q);
        return $q;
    }
    public function cleanDoc() {
        $tobedeleted = array();
        foreach ($this->composition as $k => $v) {
            if (!$v->nombre) {
                $tobedeleted[] = $k;
            }
        }
        foreach($tobedeleted as $k) {
            $this->composition->remove($k);
        }
    }

    public function getCepagesSelectionnes() {
        $cepagesSelectionnes = array();
        foreach ($this->cepages as $cepageKey => $cepage) {
            if($cepage->selectionne){
            $cepagesSelectionnes[$cepageKey] = $cepage;
            }
        }
        return $cepagesSelectionnes;
    }

    public function getVolumeTotalComposition() {
        $sommeTotal = 0;
        $contenances = sfConfig::get('app_contenances_bouteilles');

        foreach ($this->composition as $compo){
            $hectolitre = $contenances[$compo->contenance] / 10000;
            $sommeTotal+=$compo->nombre * $hectolitre;
        }
        return $sommeTotal;
    }

}
