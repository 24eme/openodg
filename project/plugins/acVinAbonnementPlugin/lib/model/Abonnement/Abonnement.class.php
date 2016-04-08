<?php
/**
 * Model for Abonnement
 *
 */

class Abonnement extends BaseAbonnement {
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
        $this->mouvement_document = new MouvementDocument($this);
    }

    public function constructId() {
        $this->periode = sprintf("%s-%s", str_replace("-", "", $this->date_debut), str_replace("-", "", $this->date_fin));
        $this->set('_id', 'ABONNEMENT-' . $this->identifiant . '-' . $this->periode);
    }

    /**** MOUVEMENTS ****/

    public function getMouvements() {

        return $this->_get('mouvements');
    }

    public function getTemplateFacture() {

        return "TEMPLATE-FACTURE-ABONNEMENT-".str_replace("-", "", $this->getDateDebut())."-".str_replace("-", "", $this->getDateFin());
    }

    public function getMouvementsCalcule() {

        return array($this->getIdentifiant() => array($this->getTemplateFacture() => array("facturable" => 1, "facture" => 0)));
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
