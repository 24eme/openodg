<?php
/**
 * Model for Abonnement
 *
 */

class Abonnement extends BaseAbonnement {
    protected $declarant_document = null;
    protected $mouvement_document = null;

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
        $this->mouvement_document = new MouvementDocument($this);
    }

    public function constructId() {
        $this->set('_id', 'ABONNEMENT-' . $this->identifiant . '-' . $this->campagne);
    }

    /**** MOUVEMENTS ****/

    public function getMouvements() {

        return $this->_get('mouvements');
    }

    public function getMouvementsCalcule() {
        
        return array($this->getIdentifiant() => array("TEMPLATE-FACTURE-ABONNEMENT-2014" => array("facturable" => 1, "facture" => 0)));
    }

    public function getMouvementsCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsCalculeByIdentifiant($identifiant);
    }
    
    public function generateMouvements() {

        return $this->mouvement_document->generateMouvements();
    }    
    
    public function findMouvement($cle, $id = null){
      return $this->mouvement_document->findMouvement($cle, $id);
    }

    public function facturerMouvements() {

        return $this->mouvement_document->facturerMouvements();
    }

    public function isFactures() {

        return $this->mouvement_document->isFactures();
    }

    public function isNonFactures() {

        return $this->mouvement_document->isNonFactures();
    }

    public function clearMouvements(){
        $this->remove('mouvements');
        $this->add('mouvements');
    }

    /**** FIN DES MOUVEMENTS ****/
}