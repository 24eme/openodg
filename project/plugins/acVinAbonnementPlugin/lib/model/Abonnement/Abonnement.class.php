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

        return TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-ABONNEMENT-".str_replace("-", "", $this->getDateDebut())."-".str_replace("-", "", $this->getDateFin()));
    }

    public function getMouvementsCalcule() {
        $templateFacture = $this->getTemplateFacture();
        $cotisations = $templateFacture->generateCotisations($this);

        $identifiantCompte = $this->getIdentifiant();

        $mouvements = array();

        $rienAFacturer = true;

        foreach($cotisations as $cotisation) {
            $mouvement = AbonnementMouvement::freeInstance($this);
            $mouvement->categorie = $cotisation->getCollectionKey();
            $mouvement->type_hash = $cotisation->getDetailKey();
            $mouvement->type_libelle = $cotisation->getLibelle();
            $mouvement->quantite = $cotisation->getQuantite();
            $mouvement->taux = $cotisation->getPrix();
            $mouvement->facture = 0;
            $mouvement->facturable = 1;
            $mouvement->date = $this->date_debut;
            $mouvement->date_version = $this->date_debut;
            $mouvement->version = null;
            $mouvement->template = $templateFacture->_id;

            if($mouvement->quantite) {
                $rienAFacturer = false;
            }

            $mouvements[$mouvement->getMD5Key()] = $mouvement;
        }

        if($rienAFacturer) {

            return array($identifiantCompte => array());
        }

        return array($identifiantCompte => $mouvements);
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
