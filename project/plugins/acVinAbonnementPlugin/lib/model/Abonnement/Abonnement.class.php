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
        $this->mouvement_document = new MouvementFacturesDocument($this);
    }

    public function constructId() {
        $this->periode = sprintf("%s-%s", str_replace("-", "", $this->date_debut), str_replace("-", "", $this->date_fin));
        $this->set('_id', 'ABONNEMENT-' . $this->identifiant . '-' . $this->periode);
    }

    /**** MOUVEMENTS ****/

    public function getMouvementsFactures() {

        return $this->_get('mouvements');
    }

    public function getTemplateFactureName() {

        return "TEMPLATE-FACTURE-AOC-".(explode("-", $this->date_debut)[0] - 1);
    }

    public function getTemplateFacture() {

        return TemplateFactureClient::getInstance()->find($this->getTemplateFactureName());
    }

    public function getMouvementsFacturesCalcule() {
        $templateFacture = $this->getTemplateFacture();
        if (!$templateFacture) {
            throw new sfException($this->getTemplateFactureName()." not found");
        }
        $cotisations = $templateFacture->generateCotisations($this);

        $identifiantCompte = $this->getIdentifiant();

        $mouvements = array();

        $rienAFacturer = true;

        foreach($cotisations as $cotisation) {
            $mouvement = AbonnementMouvementFactures::freeInstance($this);
            $mouvement->categorie = $cotisation->getCollectionKey();
            $mouvement->type_hash = $cotisation->getDetailKey();
            $mouvement->type_libelle = $cotisation->getConfigCollection()->getLibelle();
            $mouvement->detail_libelle = $cotisation->getLibelle();
            $mouvement->quantite = $cotisation->getQuantite();
            $mouvement->taux = $cotisation->getPrix();
            $mouvement->tva = $cotisation->getTva();
            $mouvement->facture = 0;
            $mouvement->facturable = 1;
            $mouvement->date = $this->date_debut;
            $mouvement->date_version = $this->date_debut;
            $mouvement->version = null;
            $mouvement->template = $templateFacture->_id;
            $mouvement->type = AbonnementClient::TYPE_MODEL;

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

    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
    }

    public function generateMouvementsFactures() {

        return $this->mouvement_document->generateMouvementsFactures();
    }

    public function findMouvementFactures($cle, $id = null){
      return $this->mouvement_document->findMouvementFactures($cle, $id);
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

    public function clearMouvementsFactures(){
        $this->remove('mouvements');
        $this->add('mouvements');
    }

    /**** FIN DES MOUVEMENTS ****/

    public function save() {
        if(!$this->exist('mouvements') || !count($this->mouvements)) {
            $this->generateMouvementsFactures();
        }
        return parent::save();
    }
}
