<?php

class PMC extends BasePMC
{
    public function constructId() {
        if (!$this->date) {
            $this->date = date("Y-m-d H:i:s");
        }
        $idDate = preg_replace('/[^0-9]/', '', $this->date);
        if (strlen($idDate) < 8) {
            throw new sfException("Mauvais format de date pour la construction de l'id");
        }
        $id = 'PMC-' . $this->identifiant . '-' . $idDate;
        $this->set('_id', $id);
    }

    public function initDoc($identifiant, $campagne, $date = null) {
        $this->identifiant = $identifiant;
        $this->date = $date;
        if (!$this->date) {
            $this->date = date("Y-m-d H:i:s");
        }
        $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($campagne);
        $etablissement = $this->getEtablissementObject();
        $this->constructId();
    }

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
      $date = null;
      if ($this->getValidation()) {
        $dt = new DateTime($this->getValidation());
        $date = $dt->format('Y-m-d');
      }
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $date,
    		'libelle' => 'Déclaration de mise en circulation '.$this->getCampagne().' '.implode(', ', $this->getRegions()).' '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function getDateFr() {

        return preg_replace("/^([0-9]{4})-([0-9]{2})-([0-9]{2})/", '\3/\2/\1', substr($this->date, 0, 10));
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('pmc_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('pmc_visualisation', array('id' => $id));
    }

    public function getStatutRevendique() {

        return Lot::STATUT_DECLARE;
    }

    public function isNonConformite()
    {
        return $this->getType() === PMCNCCLient::TYPE_MODEL;
    }

    public function getMaster()
    {
        return $this;
    }

    public function generateModificative()
    {
        return $this->version_document->generateModificative();
    }

    public function verifyGenerateModificative()
    {
        return false;
    }

    public function save($saveDependants = true) {

        return $this->saveDeclaration($saveDependants);
    }

    /** Facturation **/
    public function aFacturer()
    {
        $pmcs = PMCClient::getInstance()->findPMCsByCampagne($this->identifiant, $this->campagne);

        uasort($pmcs, function ($a, $b) {
            return $a->_id > $b->_id;
        });

        if (current($pmcs)->_id === $this->_id) {
            return true;
        }

        return false;
    }

    public function getVolumeLotsFacturables(TemplateFactureCotisationCallbackParameters $produitFilter){

        return $this->getVolumeRevendiqueLots($produitFilter);
    }

    public function getProduits($region = null) {
        if (!$this->exist('declaration') || !count($this->get('declaration'))) {
            return array();
        }
        return $this->declaration->getProduits($region);
    }

    public function getHabilitation() {
        return HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->getDate());
    }

    public function addLot($imported = false, $auto_millesime = true) {
        return parent::addLot($imported, false);
    }

    public function getMillesimes() {
        $millesimes = array();
        foreach($this->getLots() as $lot) {
            if (intval($lot->millesime) == $lot->millesime) {
                $millesimes[$lot->millesime] = $lot->millesime;
            }
        }
        return array_keys($millesimes);
    }
}
