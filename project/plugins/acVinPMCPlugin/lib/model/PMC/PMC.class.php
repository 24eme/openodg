<?php

class PMC extends BasePMC
{
    public function constructId() {
        if (!$this->date) {
            $this->date = date("Y-m-d");
        }
        $idDate = str_replace('-', '', $this->date);
        if (strlen($idDate) < 8) {
            throw new sfException(" mauvaise date pour une transaction");
        }
        $id = 'PMC-' . $this->identifiant . '-' . $idDate;
        $this->set('_id', $id);
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
    		'libelle' => 'Déclaration de mise en circulation '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('pmc_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('pmc_visualisation', array('id' => $id));
    }

    public function getStatutRevendique() {

        return Lot::STATUT_CONDITIONNE;
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

    public function getVolumeLotsFacturables($produitFilter = null){

        return $this->getVolumeRevendiqueLots($produitFilter);
    }

    public function getProduits($region = null) {
        if (!$this->exist('declaration') || !count($this->get('declaration'))) {
            return array();
        }
        return $this->declaration->getProduits($region);
    }

}
